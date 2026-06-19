<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use Illuminate\View\View;


class AdminModuleController extends Controller
{
    public function accounts(): View
    {
        return $this->module('Quản lý tài khoản', 'Quản lý tài khoản đăng nhập, phân quyền và trạng thái người dùng.');
    }

    public function positions(): View
    {
        return $this->module('Quản lý chức vụ', 'Thiết lập và quản lý các chức vụ, vị trí công việc.');
    }

    public function employees(): View
    {
        return $this->module('Quản lý nhân viên', 'Hồ sơ nhân viên, thông tin cá nhân và liên kết tài khoản.');
    }

    public function attendances(): View
    {
        return $this->module('Quản lý chấm công', 'Theo dõi giờ vào ra, ca làm việc và báo cáo chấm công.');
    }

    public function kpis(): View
    {
        return $this->module('Quản lý KPI', 'Thiết lập chỉ tiêu KPI và đánh giá hiệu suất nhân viên.');
    }

    public function payrolls(): View
    {
        return $this->module('Quản lý lương', 'Tính lương, kỳ lương và phiếu lương nhân viên.');
    }

    public function contracts(): View
    {
        return $this->module('Quản lý hợp đồng', 'Hợp đồng lao động, loại hợp đồng và thời hạn.');
    }

    public function recruitment(): View
    {
        return $this->module('Tuyển dụng', 'Tin tuyển dụng, ứng viên và lịch phỏng vấn.');
    }

    private function module(string $title, string $description): View
    {
        return view('admin.modules.placeholder', compact('title', 'description'));
    }
}
