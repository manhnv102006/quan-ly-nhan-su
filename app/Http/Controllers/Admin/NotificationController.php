<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNotificationRequest;
use App\Models\Department;
use App\Models\User;
use App\Services\AdminNotificationService;
use App\Support\NotificationTypeMeta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(
        private AdminNotificationService $notifications,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        return view('admin.notifications.index', [
            'notifications' => $this->notifications->paginateForUser($user, [
                'status' => $request->string('status')->toString() ?: 'all',
                'type' => $request->string('type')->toString(),
                'search' => $request->string('search')->trim()->toString(),
            ]),
            'stats' => $this->notifications->statsForUser($user),
            'filters' => [
                'status' => $request->string('status')->toString() ?: 'all',
                'type' => $request->string('type')->toString(),
                'search' => $request->string('search')->trim()->toString(),
            ],
        ]);
    }

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
        $recipientIds = $this->notifications->activeRecipientIds(
            $validated['audience'],
            $validated['user_ids'] ?? [],
            $validated['department_ids'] ?? [],
        );

        if ($recipientIds === []) {
            $errorKey = $validated['audience'] === 'departments' ? 'department_ids' : 'user_ids';
            $errorMessage = $validated['audience'] === 'departments'
                ? 'Không tìm thấy tài khoản nào liên kết với phòng ban đã chọn.'
                : 'Không tìm thấy người nhận hợp lệ.';

            return back()
                ->withInput()
                ->withErrors([$errorKey => $errorMessage]);
        }

        $this->notifications->create($request->user(), [
            'title' => $validated['title'],
            'content' => $validated['content'],
            'type' => $validated['type'],
        ], $recipientIds);

        return redirect()
            ->route('admin.notifications.index')
            ->with('success', 'Đã gửi thông báo tới '.count($recipientIds).' người nhận.');
    }

    public function markAsRead(Request $request, int $notification): RedirectResponse
    {
        $this->notifications->markAsRead($request->user(), $notification);

        return redirect()
            ->back()
            ->with('success', 'Đã đánh dấu thông báo là đã đọc.');
    }

    public function markAllAsRead(Request $request): RedirectResponse
    {
        $count = $this->notifications->markAllAsRead($request->user());

        return redirect()
            ->route('admin.notifications.index')
            ->with('success', $count > 0 ? "Đã đánh dấu {$count} thông báo là đã đọc." : 'Không có thông báo chưa đọc.');
    }
}
