<?php

namespace App\Support;

class EmployeeNavigation
{
    public static function items(): array
    {
        return [
            [
                'label' => 'Dashboard',
                'href' => route('employee.dashboard'),
                'route' => 'employee.dashboard',
                'icon' => 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z',
            ],
            [
                'key' => 'attendance',
                'label' => 'Quản lý chấm công',
                'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z',
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
                        'label' => 'Về sớm',
                        'href' => route('employee.early-leave.index'),
                        'route' => 'employee.early-leave*',
                    ],
                ],
            ],
            [
                'label' => 'KPI',
                'href' => route('employee.kpis.index'),
                'route' => 'employee.kpis.*',
                'icon' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z',
            ],
            [
                'key' => 'payroll',
                'label' => 'Quản lý lương',
                'icon' => 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                'children' => [
                    [
                        'label' => 'Bảng lương',
                        'href' => route('employee.payrolls.index'),
                        'route' => 'employee.payrolls.*',
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
                ],
            ],
            [
                'key' => 'profile',
                'label' => 'Quản lý hồ sơ',
                'icon' => 'M15.75 6.75a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z',
                'children' => [
                    [
                        'label' => 'Hợp đồng',
                        'href' => route('employee.contracts.index'),
                        'route' => 'employee.contracts.*',
                    ],
                    [
                        'label' => 'Thông báo',
                        'href' => route('employee.notifications.index'),
                        'route' => 'employee.notifications*',
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
