<?php

namespace App\Support;

class ManagerNavigation
{
    public static function items(): array
    {
        return [
            [
                'label' => 'Dashboard',
                'href' => route('manager.dashboard'),
                'route' => 'manager.dashboard',
                'icon' => 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z',
                'note' => 'Tổng quan điều hành',
            ],
            [
                'label' => 'Đội ngũ',
                'href' => route('manager.employees.index'),
                'route' => 'manager.employees*',
                'icon' => 'M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z',
                'note' => 'Nhân sự & vai trò',
            ],
            [
                'label' => 'Phê duyệt',
                'href' => route('manager.dashboard').'#approvals',
                'icon' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                'note' => 'Đơn nghỉ đang chờ',
            ],
            [
                'label' => 'KPI',
                'href' => route('manager.kpis.index'),
                'route' => 'manager.kpis*',
                'icon' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z',
                'note' => 'Tiến độ phòng ban',
            ],
            [
                'label' => 'Chấm công',
                'href' => route('attendance.index'),
                'route' => 'attendance.*',
                'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z',
                'note' => 'Check-in / Check-out của bạn',
            ],
            [
                'label' => 'Đơn nghỉ phép của tôi',
                'href' => route('employee.leave-requests'),
                'route' => 'employee.leave-requests*',
                'icon' => 'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zM12 16.5h.008v.008H12V16.5z',
                'note' => 'Gửi lên Admin phê duyệt',
            ],
            [
                'label' => 'Đơn tăng ca của tôi',
                'href' => route('employee.overtime-requests'),
                'route' => 'employee.overtime-requests*',
                'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z',
                'note' => 'Gửi lên Admin phê duyệt',
            ],
            [
                'label' => 'Duyệt nghỉ phép',
                'href' => route('manager.leave-requests.index'),
                'route' => 'manager.leave-requests*',
                'icon' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                'note' => 'Duyệt đơn của nhân viên',
            ],
            [
                'label' => 'Duyệt tăng ca',
                'href' => route('manager.overtime-requests.index'),
                'route' => 'manager.overtime-requests*',
                'icon' => 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                'note' => 'Duyệt đơn của nhân viên',
            ],
            [
                'label' => 'Thông báo',
                'href' => route('manager.notifications.index'),
                'route' => 'manager.notifications*',
                'icon' => 'M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0',
                'note' => 'Phòng ban của bạn',
            ],
            [
                'label' => 'Tuyển dụng',
                'href' => route('manager.dashboard').'#recruitment',
                'icon' => 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
                'note' => 'Tin tuyển đang mở',
            ],
            [
                'label' => 'Hồ sơ',
                'href' => route('profile.edit'),
                'icon' => 'M15.75 6.75a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z',
                'note' => 'Thông tin tài khoản',
            ],
        ];
    }
}
