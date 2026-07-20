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
            ],
            [
                'key' => 'department',
                'label' => 'Quản lý phòng ban',
                'icon' => 'M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z',
                'children' => [
                    [
                        'label' => 'Đội ngũ',
                        'href' => route('manager.employees.index'),
                        'route' => 'manager.employees*',
                    ],
                    [
                        'label' => 'KPI',
                        'href' => route('manager.kpis.index'),
                        'route' => 'manager.kpis*',
                    ],
                    [
                        'label' => 'Duyệt nghỉ phép',
                        'href' => route('manager.leave-requests.index'),
                        'route' => 'manager.leave-requests*',
                    ],
                    [
                        'label' => 'Duyệt tăng ca',
                        'href' => route('manager.overtime-requests.index'),
                        'route' => 'manager.overtime-requests*',
                    ],
                    [
                        'label' => 'Thông báo',
                        'href' => route('manager.notifications.index'),
                        'route' => 'manager.notifications*',
                    ],
                    [
                        'label' => 'Tuyển dụng',
                        'href' => route('manager.dashboard').'#recruitment',
                    ],
                ],
            ],
            [
                'key' => 'personal',
                'label' => 'Quản lý cá nhân',
                'icon' => 'M15.75 6.75a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z',
                'children' => [
                    [
                        'label' => 'Chấm công',
                        'href' => route('attendance.index'),
                        'route' => 'attendance.*',
                    ],
                    [
                        'label' => 'Nghỉ phép',
                        'href' => route('employee.leave-requests'),
                        'route' => 'employee.leave-requests*',
                    ],
                    [
                        'label' => 'Tăng ca',
                        'href' => route('employee.overtime-requests'),
                        'route' => 'employee.overtime-requests*',
                    ],
                    [
                        'label' => 'Ứng lương',
                        'href' => route('employee.advances.index'),
                        'route' => 'employee.advances.*',
                    ],
                    [
                        'label' => 'Đăng ký NPT',
                        'href' => route('employee.tax-dependents.index'),
                        'route' => 'employee.tax-dependents.*',
                    ],
                    [
                        'label' => 'Hồ sơ cá nhân',
                        'href' => route('profile.edit'),
                        'route' => 'profile.*',
                    ],
                ],
            ],
        ];
    }
}
