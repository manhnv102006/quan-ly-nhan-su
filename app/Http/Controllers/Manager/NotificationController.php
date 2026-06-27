<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreManagerNotificationRequest;
use App\Services\AdminNotificationService;
use App\Support\ManagerDepartmentResolver;
use App\Support\ManagerNavigation;
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
        $managedDepartment = ManagerDepartmentResolver::managedDepartment($user);

        return view('manager.notifications.index', [
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
            'managedDepartment' => $managedDepartment,
            'navigation' => ManagerNavigation::items(),
        ]);
    }

    public function show(Request $request, int $notification): View
    {
        $user = $request->user();
        $managedDepartment = ManagerDepartmentResolver::managedDepartment($user);
        $item = $this->notifications->findForUser($user, $notification);

        abort_if(! $item, 404);

        $this->notifications->markAsRead($user, $notification);

        $item->is_read = true;
        $item->read_at = $item->read_at ?? now();

        return view('manager.notifications.show', [
            'notification' => $item,
            'managedDepartment' => $managedDepartment,
            'navigation' => ManagerNavigation::items(),
        ]);
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
            ->route('manager.notifications.index')
            ->with('success', $count > 0 ? "Đã đánh dấu {$count} thông báo là đã đọc." : 'Không có thông báo chưa đọc.');
    }

    public function create(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $managedDepartment = ManagerDepartmentResolver::managedDepartment($user);

        if (! $managedDepartment) {
            return redirect()
                ->route('manager.notifications.index')
                ->withErrors(['department' => 'Bạn chưa được gắn phòng ban quản lý nên không thể gửi thông báo.']);
        }

        return view('manager.notifications.create', [
            'managedDepartment' => $managedDepartment,
            'members' => $this->notifications->departmentMemberUsers($managedDepartment->id),
            'navigation' => ManagerNavigation::items(),
        ]);
    }

    public function store(StoreManagerNotificationRequest $request): RedirectResponse
    {
        $user = $request->user();
        $managedDepartment = ManagerDepartmentResolver::managedDepartment($user);

        if (! $managedDepartment) {
            return back()
                ->withInput()
                ->withErrors(['department' => 'Bạn chưa được gắn phòng ban quản lý.']);
        }

        $validated = $request->validated();
        $payload = [
            'title' => $validated['title'],
            'content' => $validated['content'],
            'type' => 'system',
            'department_id' => $managedDepartment->id,
        ];

        if ($validated['audience'] === 'all') {
            $recipientIds = $this->notifications->recipientIdsForDepartments([$managedDepartment->id]);
        } else {
            $recipientIds = $this->notifications->filterDepartmentRecipientIds(
                $managedDepartment->id,
                $validated['user_ids'] ?? [],
            );
        }

        if ($recipientIds === []) {
            return back()
                ->withInput()
                ->withErrors(['user_ids' => 'Không tìm thấy thành viên hợp lệ trong phòng ban.']);
        }

        $this->notifications->create($user, $payload, $recipientIds);

        return redirect()
            ->route('manager.notifications.index')
            ->with('success', 'Đã gửi thông báo tới '.count($recipientIds).' thành viên phòng ban.');
    }
}
