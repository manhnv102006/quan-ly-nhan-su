<?php

namespace App\Http\Controllers\Accountant;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $reports = [
            [
                'title' => 'Bảng lương tháng',
                'description' => 'Tổng hợp lương, phụ cấp, khấu trừ theo kỳ',
                'icon' => 'M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0z',
                'href' => route('accountant.payroll-periods.index'),
                'tone' => 'from-amber-500 to-orange-500',
            ],
            [
                'title' => 'Báo cáo bảo hiểm',
                'description' => 'Đóng BHXH, BHYT, BHTN theo nhân viên',
                'icon' => 'M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z',
                'href' => route('accountant.insurance.reports'),
                'tone' => 'from-sky-500 to-indigo-500',
            ],
            [
                'title' => 'Báo cáo thuế TNCN',
                'description' => 'Khấu trừ thuế thu nhập cá nhân',
                'icon' => 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5a3.375 3.375 0 00-3.375-3.375H9.75M9 16.5v.75m3-3v3M15 12v.75M3 4.5h15M4.5 19.5h15a1.5 1.5 0 001.5-1.5V6.75a1.5 1.5 0 00-1.5-1.5H4.5a1.5 1.5 0 00-1.5 1.5v11.25a1.5 1.5 0 001.5 1.5z',
                'href' => route('accountant.tax.declaration'),
                'tone' => 'from-violet-500 to-purple-600',
            ],
            [
                'title' => 'Báo cáo chấm công',
                'description' => 'Ngày công, đi muộn, nghỉ theo phòng ban',
                'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z',
                'href' => route('accountant.attendance.index'),
                'tone' => 'from-emerald-500 to-teal-500',
            ],
            [
                'title' => 'Hợp đồng & lương',
                'description' => 'Đối chiếu mức lương trong hợp đồng',
                'icon' => 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z',
                'href' => route('accountant.contracts.index'),
                'tone' => 'from-rose-500 to-pink-500',
            ],
            [
                'title' => 'Tạm ứng lương',
                'description' => 'Danh sách ứng lương & hoàn trả',
                'icon' => 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                'href' => route('accountant.advances.index'),
                'tone' => 'from-cyan-500 to-blue-500',
            ],
        ];

        return view('accountant.reports.index', compact('reports'));
    }
}
