<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\View\View;


class AdminModuleController extends Controller
{
    public function positions(): View
    {
        return $this->module('Quản lý chức vụ', 'Thiết lập và quản lý các chức vụ, vị trí công việc.');
    }

    public function employees(Request $request): View
    {
        $search = $request->string('search')->trim();

        $employees = Employee::query()
            ->with(['department', 'position'])
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('employee_code', 'like', "%{$search}%")
                    ->orWhere('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            }))
            ->orderBy('employee_code')
            ->paginate(12)
            ->withQueryString();

        $stats = [
            'total' => Employee::count(),
            'active' => Employee::where('status', 'active')->count(),
            'inactive' => Employee::where('status', 'inactive')->count(),
            'resigned' => Employee::where('status', 'resigned')->count(),
        ];

        return view('admin.employees.index', compact('employees', 'stats', 'search'));
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
