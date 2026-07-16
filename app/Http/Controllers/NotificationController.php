<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller as BaseController;
use App\Services\AdminNotificationService;
use App\Support\ManagerDepartmentResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends BaseController
{
    public function __construct(
        private AdminNotificationService $notifications,
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->isManager() && ! $user->isAdmin()) {
            return redirect()->route('manager.notifications.index', $request->query());
        }

        if ($user->isEmployee() && ! $user->isAdmin()) {
            return redirect()->route('employee.notifications.index', $request->query());
        }

        if ($user->isAccountant()) {
            return redirect()->route('employee.notifications.index', $request->query());
        }

        $managedDepartment = $user->isManager()
            ? ManagerDepartmentResolver::managedDepartment($user)
            : null;

        return view('notifications.index', [
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
        ]);
    }

    public function show(Request $request, int $notification): View|RedirectResponse
    {
        $user = $request->user();

        if ($user->isManager() && ! $user->isAdmin()) {
            return redirect()->route('manager.notifications.show', $notification);
        }

        if ($user->isEmployee() && ! $user->isAdmin()) {
            return redirect()->route('employee.notifications.show', $notification);
        }

        if ($user->isAccountant()) {
            return redirect()->route('employee.notifications.show', $notification);
        }

        $item = $this->notifications->findForUser($user, $notification);

        abort_if(! $item, 404);

        $this->notifications->markAsRead($user, $notification);

        $item->is_read = true;
        $item->read_at = $item->read_at ?? now();

        return view('admin.notifications.show', [
            'notification' => $item,
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
            ->route('notifications.index')
            ->with('success', $count > 0 ? "Đã đánh dấu {$count} thông báo là đã đọc." : 'Không có thông báo chưa đọc.');
    }
}
