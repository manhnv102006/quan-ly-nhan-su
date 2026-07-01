<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNotificationRequest;
use App\Models\Department;
use App\Models\User;
use App\Services\AdminNotificationService;
use App\Support\NotificationTypeMeta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(
        private AdminNotificationService $notifications,
    ) {}

    public function create(): View
    {
        $user = auth()->user();

        return view('admin.notifications.create', [
            'typeMeta' => NotificationTypeMeta::all(),
            'departments' => Department::query()
                ->where('status', 'active')
                ->withCount([
                    'employees as linked_users_count' => fn ($query) => $query->whereNotNull('user_id'),
                ])
                ->orderBy('department_name')
                ->get(['id', 'department_code', 'department_name']),
            'users' => User::query()
                ->with('role')
                ->where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'role_id']),
            'pendingScheduled' => $this->notifications->pendingScheduledForUser($user),
        ]);
    }

    public function store(StoreNotificationRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $scheduled = ($validated['send_mode'] ?? 'immediate') === 'scheduled';
        $scheduledAt = $scheduled ? Carbon::parse($validated['scheduled_at']) : null;
        $payload = [
            'title' => $validated['title'],
            'content' => $validated['content'],
            'type' => $validated['type'],
        ];

        if ($validated['audience'] === 'departments') {
            return $this->storeForDepartments($request, $validated, $payload, $scheduled, $scheduledAt);
        }

        $schedulePayload = [
            'audience' => $validated['audience'],
            'user_ids' => array_map('intval', $validated['user_ids'] ?? []),
        ];

        if ($scheduled) {
            if ($validated['audience'] === 'selected' && $schedulePayload['user_ids'] === []) {
                return back()->withInput()->withErrors(['user_ids' => 'Vui lòng chọn ít nhất một người nhận.']);
            }

            $this->notifications->schedule(
                $request->user(),
                $payload,
                $schedulePayload,
                $scheduledAt,
            );

            return redirect()
                ->route('admin.notifications.create')
                ->with('success', 'Đã lên lịch gửi thông báo lúc '.$scheduledAt->format('d/m/Y H:i').'.');
        }

        $recipientIds = $this->notifications->activeRecipientIds(
            $validated['audience'],
            $validated['user_ids'] ?? [],
        );

        if ($recipientIds === []) {
            return back()
                ->withInput()
                ->withErrors(['user_ids' => 'Không tìm thấy người nhận hợp lệ.']);
        }

        $this->notifications->create($request->user(), $payload, $recipientIds);

        return redirect()
            ->route('notifications.index')
            ->with('success', 'Đã gửi thông báo tới '.count($recipientIds).' người nhận.');
    }

    private function storeForDepartments(
        StoreNotificationRequest $request,
        array $validated,
        array $payload,
        bool $scheduled,
        ?Carbon $scheduledAt,
    ): RedirectResponse {
        $processed = 0;

        foreach ($validated['department_ids'] as $departmentId) {
            $deptId = (int) $departmentId;
            $recipientIds = $this->notifications->recipientIdsForDepartments([$deptId]);

            if ($recipientIds === []) {
                continue;
            }

            $deptPayload = array_merge($payload, ['department_id' => $deptId]);
            $schedulePayload = [
                'audience' => 'departments',
                'department_ids' => [$deptId],
            ];

            if ($scheduled) {
                $this->notifications->schedule(
                    $request->user(),
                    $deptPayload,
                    $schedulePayload,
                    $scheduledAt,
                );
            } else {
                $this->notifications->create($request->user(), $deptPayload, $recipientIds);
            }

            $processed += $scheduled ? 1 : count($recipientIds);
        }

        if ($processed === 0) {
            return back()
                ->withInput()
                ->withErrors(['department_ids' => 'Không tìm thấy tài khoản nào liên kết với phòng ban đã chọn.']);
        }

        if ($scheduled) {
            return redirect()
                ->route('admin.notifications.create')
                ->with('success', "Đã lên lịch {$processed} thông báo phòng ban, gửi lúc ".$scheduledAt->format('d/m/Y H:i').'.');
        }

        return redirect()
            ->route('notifications.index')
            ->with('success', "Đã gửi thông báo tới {$processed} người nhận theo phòng ban.");
    }
}
