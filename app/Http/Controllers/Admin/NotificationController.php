<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNotificationRequest;
use App\Models\Department;
use App\Models\User;
use App\Services\AdminNotificationService;
use App\Support\NotificationTypeMeta;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(
        private AdminNotificationService $notifications,
    ) {}

    public function create(): View
    {
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
        ]);
    }

    public function store(StoreNotificationRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $payload = [
            'title' => $validated['title'],
            'content' => $validated['content'],
            'type' => $validated['type'],
        ];

        if ($validated['audience'] === 'departments') {
            $sentCount = 0;

            foreach ($validated['department_ids'] as $departmentId) {
                $recipientIds = $this->notifications->recipientIdsForDepartments([(int) $departmentId]);

                if ($recipientIds === []) {
                    continue;
                }

                $this->notifications->create($request->user(), array_merge($payload, [
                    'department_id' => (int) $departmentId,
                ]), $recipientIds);

                $sentCount += count($recipientIds);
            }

            if ($sentCount === 0) {
                return back()
                    ->withInput()
                    ->withErrors(['department_ids' => 'Không tìm thấy tài khoản nào liên kết với phòng ban đã chọn.']);
            }

            return redirect()
                ->route('notifications.index')
                ->with('success', "Đã gửi thông báo tới {$sentCount} người nhận theo phòng ban.");
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
}
