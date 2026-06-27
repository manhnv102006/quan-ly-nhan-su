<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Services\AdminNotificationService;
use App\Support\EmployeeNavigation;
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

        return view('employee.notifications.index', [
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
            'navigation' => EmployeeNavigation::items(),
        ]);
    }

    public function show(Request $request, int $notification): View
    {
        $user = $request->user();
        $item = $this->notifications->findForUser($user, $notification);

        abort_if(! $item, 404);

        $this->notifications->markAsRead($user, $notification);

        $item->is_read = true;
        $item->read_at = $item->read_at ?? now();

        return view('employee.notifications.show', [
            'notification' => $item,
            'navigation' => EmployeeNavigation::items(),
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
            ->route('employee.notifications.index')
            ->with('success', $count > 0 ? "Đã đánh dấu {$count} thông báo là đã đọc." : 'Không có thông báo chưa đọc.');
    }
}
