<?php

namespace App\Support;

use App\Models\TaxDependent;

class AccountantNavigation
{
    public const GROUP_FINANCE = 'finance';

    public const GROUP_PERSONAL = 'personal';

    public static function groupLabels(): array
    {
        return [
            self::GROUP_FINANCE => 'Công việc kế toán',
            self::GROUP_PERSONAL => 'Không gian cá nhân',
        ];
    }

    /**
     * @param  string|array<int, string>|null  $patterns
     * @param  string|array<int, string>|null  $except
     */
    public static function isRouteActive(string|array|null $patterns, string|array|null $except = null): bool
    {
        if (! $patterns) {
            return false;
        }

        if ($except && request()->routeIs($except)) {
            return false;
        }

        return request()->routeIs($patterns);
    }

    public static function items(): array
    {
        $pendingDependentRegistrations = TaxDependent::query()
            ->where('status', TaxDependent::STATUS_PENDING)
            ->count();

        return [
            [
                'label' => 'Dashboard',
                'href' => route('accountant.dashboard'),
                'route' => 'accountant.dashboard',
                'group' => self::GROUP_FINANCE,
                'icon' => 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z',
                'note' => 'Tổng quan tài chính',
            ],
            [
                'key' => 'payroll',
                'label' => 'Quản lý lương',
                'match' => 'accountant.payroll*',
                'group' => self::GROUP_FINANCE,
                'icon' => 'M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0z',
                'note' => 'Kỳ lương, bảng lương, lịch sử',
                'children' => [
                    [
                        'label' => 'Tổng quan',
                        'href' => route('accountant.payrolls.index'),
                        'match' => 'accountant.payrolls.index',
                    ],
                    [
                        'label' => 'Kỳ lương',
                        'href' => route('accountant.payroll-periods.index'),
                        'match' => 'accountant.payroll-periods.*',
                    ],
                    [
                        'label' => 'Bảng lương',
                        'href' => route('accountant.payrolls.slips'),
                        'match' => ['accountant.payrolls.slips', 'accountant.payrolls.pdf', 'accountant.payrolls.excel'],
                    ],
                    [
                        'label' => 'Lịch sử lương',
                        'href' => route('accountant.payrolls.salary-history'),
                        'match' => 'accountant.payrolls.salary-history',
                    ],
                ],
            ],
            [
                'key' => 'insurance',
                'label' => 'Bảo hiểm',
                'match' => 'accountant.insurance.*',
                'group' => self::GROUP_FINANCE,
                'icon' => 'M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z',
                'note' => 'Hồ sơ và báo cáo đóng bảo hiểm',
                'children' => [
                    [
                        'label' => 'Hồ sơ bảo hiểm',
                        'href' => route('accountant.insurance.index'),
                        'match' => 'accountant.insurance.*',
                        'except' => 'accountant.insurance.reports*',
                    ],
                    [
                        'label' => 'Báo cáo đóng bảo hiểm',
                        'href' => route('accountant.insurance.reports'),
                        'match' => 'accountant.insurance.reports*',
                    ],
                ],
            ],
            [
                'key' => 'tax',
                'label' => 'Thuế thu nhập cá nhân',
                'match' => 'accountant.tax.*',
                'group' => self::GROUP_FINANCE,
                'icon' => 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5a3.375 3.375 0 00-3.375-3.375H9.75M9 16.5v.75m3-3v3M15 12v.75M3 4.5h15M4.5 19.5h15a1.5 1.5 0 001.5-1.5V6.75a1.5 1.5 0 00-1.5-1.5H4.5a1.5 1.5 0 00-1.5 1.5v11.25a1.5 1.5 0 001.5 1.5z',
                'note' => 'Tính thuế, duyệt NPT, tờ khai',
                'children' => [
                    [
                        'label' => 'Tính thuế',
                        'href' => route('accountant.tax.index'),
                        'match' => 'accountant.tax.index',
                    ],
                    [
                        'label' => 'Duyệt đăng ký người phụ thuộc',
                        'href' => route('accountant.tax.pending-registrations'),
                        'match' => ['accountant.tax.pending-registrations', 'accountant.tax.registrations.*'],
                        'key' => 'tax-pending',
                        'badge' => $pendingDependentRegistrations,
                    ],
                    [
                        'label' => 'Tờ khai thuế',
                        'href' => route('accountant.tax.declaration'),
                        'match' => 'accountant.tax.declaration*',
                    ],
                    [
                        'label' => 'Quyết toán năm',
                        'href' => route('accountant.tax.settlement'),
                        'match' => 'accountant.tax.settlement*',
                    ],
                ],
            ],
            [
                'key' => 'advances',
                'label' => 'Tạm ứng lương',
                'match' => 'accountant.advances.*',
                'group' => self::GROUP_FINANCE,
                'icon' => 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                'note' => 'Duyệt và trừ tạm ứng',
                'children' => [
                    [
                        'label' => 'Yêu cầu tạm ứng',
                        'href' => route('accountant.advances.index'),
                        'match' => 'accountant.advances.*',
                        'except' => ['accountant.advances.balances', 'accountant.advances.deduct'],
                    ],
                    [
                        'label' => 'Số dư tạm ứng',
                        'href' => route('accountant.advances.balances'),
                        'match' => 'accountant.advances.balances',
                    ],
                    [
                        'label' => 'Trừ vào lương',
                        'href' => route('accountant.advances.deduct'),
                        'match' => 'accountant.advances.deduct',
                    ],
                ],
            ],
            [
                'key' => 'contracts',
                'label' => 'Quản lý hợp đồng',
                'match' => 'accountant.contracts.*',
                'group' => self::GROUP_FINANCE,
                'icon' => 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z',
                'note' => 'Hợp đồng toàn công ty',
                'children' => [
                    [
                        'label' => 'Theo phòng ban',
                        'href' => route('accountant.contracts.index'),
                        'match' => 'accountant.contracts.*',
                        'except' => ['accountant.contracts.salary-overview', 'accountant.contracts.expiring'],
                    ],
                    [
                        'label' => 'Lương và phụ cấp',
                        'href' => route('accountant.contracts.salary-overview'),
                        'match' => 'accountant.contracts.salary-overview',
                    ],
                    [
                        'label' => 'Sắp hết hạn',
                        'href' => route('accountant.contracts.expiring'),
                        'match' => 'accountant.contracts.expiring',
                    ],
                ],
            ],
            [
                'key' => 'attendance',
                'label' => 'Bảng chấm công',
                'match' => 'accountant.attendance.*',
                'group' => self::GROUP_FINANCE,
                'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z',
                'note' => 'Xem công theo phòng ban',
                'children' => [
                    [
                        'label' => 'Theo phòng ban',
                        'href' => route('accountant.attendance.index'),
                        'match' => 'accountant.attendance.*',
                        'except' => 'accountant.attendance.timesheet',
                    ],
                    [
                        'label' => 'Bảng công tháng',
                        'href' => route('accountant.attendance.timesheet'),
                        'match' => 'accountant.attendance.timesheet',
                    ],
                ],
            ],
            [
                'label' => 'Lịch sử thay đổi',
                'href' => route('accountant.change-logs.index'),
                'route' => 'accountant.change-logs.*',
                'group' => self::GROUP_FINANCE,
                'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z',
                'note' => 'Nhật ký sửa bảo hiểm, thuế, lương',
            ],
            [
                'key' => 'reports',
                'label' => 'Báo cáo',
                'match' => 'accountant.reports.*',
                'group' => self::GROUP_FINANCE,
                'icon' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z',
                'note' => 'Xuất báo cáo tài chính',
                'children' => [
                    [
                        'label' => 'Trung tâm báo cáo',
                        'href' => route('accountant.reports.index'),
                        'match' => 'accountant.reports.index',
                    ],
                    [
                        'label' => 'Chi phí lương theo phòng ban',
                        'href' => route('accountant.reports.salary-by-department'),
                        'match' => 'accountant.reports.salary-by-department*',
                    ],
                    [
                        'label' => 'So sánh ngân sách',
                        'href' => route('accountant.reports.budget-comparison'),
                        'match' => 'accountant.reports.budget-comparison*',
                    ],
                    [
                        'label' => 'Xuất báo cáo tài chính',
                        'href' => route('accountant.reports.financial'),
                        'match' => 'accountant.reports.financial*',
                    ],
                ],
            ],
            [
                'label' => 'Chấm công của tôi',
                'href' => route('attendance.index'),
                'route' => 'attendance.*',
                'group' => self::GROUP_PERSONAL,
                'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z',
                'note' => 'Check-in và check-out',
            ],
            [
                'label' => 'Bảng lương của tôi',
                'href' => route('employee.payrolls.index'),
                'route' => 'employee.payrolls.*',
                'group' => self::GROUP_PERSONAL,
                'icon' => 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                'note' => 'Phiếu lương cá nhân',
            ],
            [
                'label' => 'Ứng lương',
                'href' => route('employee.advances.index'),
                'route' => 'employee.advances.*',
                'group' => self::GROUP_PERSONAL,
                'icon' => 'M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0z',
                'note' => 'Gửi yêu cầu ứng lương',
            ],
            [
                'label' => 'Người phụ thuộc',
                'href' => route('employee.tax-dependents.index'),
                'route' => 'employee.tax-dependents.*',
                'group' => self::GROUP_PERSONAL,
                'icon' => 'M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z',
                'note' => 'Đăng ký giảm trừ thuế',
            ],
            [
                'label' => 'Hợp đồng của tôi',
                'href' => route('employee.contracts.index'),
                'route' => 'employee.contracts.*',
                'group' => self::GROUP_PERSONAL,
                'icon' => 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z',
                'note' => 'Hợp đồng lao động của bạn',
            ],
            [
                'label' => 'Nghỉ phép',
                'href' => route('employee.leave-requests'),
                'route' => 'employee.leave-requests*',
                'group' => self::GROUP_PERSONAL,
                'icon' => 'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zM12 16.5h.008v.008H12V16.5z',
                'note' => 'Đơn xin nghỉ phép',
            ],
            [
                'label' => 'Tăng ca',
                'href' => route('employee.overtime-requests'),
                'route' => 'employee.overtime-requests*',
                'group' => self::GROUP_PERSONAL,
                'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z',
                'note' => 'Đơn xin tăng ca',
            ],
            [
                'label' => 'Thông báo',
                'href' => route('employee.notifications.index'),
                'route' => 'employee.notifications*',
                'group' => self::GROUP_PERSONAL,
                'icon' => 'M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0',
                'note' => 'Tin nội bộ',
            ],
            [
                'label' => 'Hồ sơ',
                'href' => route('profile.edit'),
                'route' => 'profile.*',
                'group' => self::GROUP_PERSONAL,
                'icon' => 'M15.75 6.75a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z',
                'note' => 'Thông tin cá nhân',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $child
     */
    public static function isChildActive(array $child): bool
    {
        return self::isRouteActive($child['match'] ?? null, $child['except'] ?? null);
    }
}
