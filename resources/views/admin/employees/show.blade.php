<x-admin-layout title="Chi tiết nhân viên">

    <div class="space-y-6">

        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Chi tiết nhân viên</h2>
                <p class="text-slate-500 mt-1">Xem thông tin chi tiết của nhân viên</p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('admin.employees.edit', $employee) }}"
                   class="px-5 py-3 rounded-xl bg-violet-600 text-white font-medium hover:bg-violet-700 transition">
                    Sửa nhân viên
                </a>

                <a href="{{ route('admin.employees') }}"
                   class="px-5 py-3 rounded-xl bg-slate-200 text-slate-700 font-medium hover:bg-slate-300 transition">
                    ← Quay lại
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <div class="lg:col-span-2 space-y-6">

                <div class="bg-white rounded-3xl shadow-sm border border-slate-100">
                    <div class="px-6 py-5 border-b border-slate-100">
                        <h3 class="text-lg font-semibold text-slate-800">Thông tin cơ bản</h3>
                    </div>

                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-medium text-slate-500 mb-2">Mã nhân viên</label>
                                <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 font-semibold text-slate-800">
                                    {{ $employee->employee_code }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-500 mb-2">Họ và tên</label>
                                <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 font-semibold text-slate-800">
                                    {{ $employee->full_name }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-500 mb-2">Giới tính</label>
                                <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200">
                                    @if ($employee->gender === 'male')
                                        Nam
                                    @elseif ($employee->gender === 'female')
                                        Nữ
                                    @else
                                        Khác
                                    @endif
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-500 mb-2">Ngày sinh</label>
                                <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200">
                                    {{ $employee->date_of_birth?->format('d/m/Y') ?? '—' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-3xl shadow-sm border border-slate-100">
                    <div class="px-6 py-5 border-b border-slate-100">
                        <h3 class="text-lg font-semibold text-slate-800">Thông tin liên hệ</h3>
                    </div>

                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-medium text-slate-500 mb-2">Email</label>
                                <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200">
                                    {{ $employee->email }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-500 mb-2">Số điện thoại</label>
                                <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200">
                                    {{ $employee->phone }}
                                </div>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-slate-500 mb-2">Địa chỉ</label>
                                <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200">
                                    {{ $employee->address ?: 'Chưa cập nhật' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-3xl shadow-sm border border-slate-100">
                    <div class="px-6 py-5 border-b border-slate-100">
                        <h3 class="text-lg font-semibold text-slate-800">Thông tin công việc</h3>
                    </div>

                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-medium text-slate-500 mb-2">Phòng ban</label>
                                <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200">
                                    @if ($employee->department)
                                        <a href="{{ route('admin.departments.detail', $employee->department_id) }}"
                                           class="text-violet-600 hover:text-violet-700 font-medium">
                                            {{ $employee->department->department_name }}
                                        </a>
                                    @else
                                        <span class="text-slate-400">Chưa gán</span>
                                    @endif
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-500 mb-2">Chức vụ</label>
                                <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200">
                                    @if ($employee->position)
                                        <a href="{{ route('admin.positions.show', $employee->position_id) }}"
                                           class="text-violet-600 hover:text-violet-700 font-medium">
                                            {{ $employee->position->position_name }}
                                        </a>
                                    @else
                                        <span class="text-slate-400">Chưa gán</span>
                                    @endif
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-500 mb-2">Ngày vào làm</label>
                                <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200">
                                    {{ $employee->hire_date?->format('d/m/Y') ?? '—' }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-500 mb-2">Trạng thái</label>
                                <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200">
                                    @if ($employee->status === 'active')
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">Đang làm việc</span>
                                    @elseif ($employee->status === 'inactive')
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">Tạm khóa</span>
                                    @else
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-700">Đã nghỉ</span>
                                    @endif
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-500 mb-2">Ngày tạo hồ sơ</label>
                                <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200">
                                    {{ $employee->created_at?->format('d/m/Y H:i') ?? '—' }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-500 mb-2">Cập nhật lần cuối</label>
                                <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200">
                                    {{ $employee->updated_at?->format('d/m/Y H:i') ?? '—' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div>
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8 text-center">
                    <div class="w-28 h-28 mx-auto rounded-3xl bg-violet-100 flex items-center justify-center overflow-hidden">
                        @if ($employee->avatar)
                            <img src="{{ asset('storage/' . $employee->avatar) }}"
                                 alt="{{ $employee->full_name }}"
                                 class="w-full h-full object-cover">
                        @else
                            <span class="text-4xl font-bold text-violet-600">
                                {{ strtoupper(substr($employee->full_name, 0, 1)) }}
                            </span>
                        @endif
                    </div>

                    <h3 class="mt-5 text-xl font-bold text-slate-800">{{ $employee->full_name }}</h3>
                    <p class="text-slate-500 mt-2">{{ $employee->employee_code }}</p>

                    <div class="mt-4">
                        @if ($employee->status === 'active')
                            <span class="inline-flex items-center px-4 py-2 rounded-full bg-emerald-100 text-emerald-700 text-sm font-semibold">Đang làm việc</span>
                        @elseif ($employee->status === 'inactive')
                            <span class="inline-flex items-center px-4 py-2 rounded-full bg-amber-100 text-amber-700 text-sm font-semibold">Tạm khóa</span>
                        @else
                            <span class="inline-flex items-center px-4 py-2 rounded-full bg-rose-100 text-rose-700 text-sm font-semibold">Đã nghỉ</span>
                        @endif
                    </div>

                    @if ($employee->department)
                        <p class="mt-4 text-sm text-slate-500">
                            Phòng ban: <span class="font-semibold text-slate-800">{{ $employee->department->department_name }}</span>
                        </p>
                    @endif

                    @if ($employee->position)
                        <p class="mt-2 text-sm text-slate-500">
                            Chức vụ: <span class="font-semibold text-slate-800">{{ $employee->position->position_name }}</span>
                        </p>
                    @endif
                </div>
            </div>

        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100">
            <div class="px-6 py-5 border-b border-slate-100">
                <h3 class="text-lg font-semibold text-slate-800">Tài khoản hệ thống liên kết</h3>
            </div>

            <div class="p-6">
                @if ($employee->user)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-slate-500 mb-2">Tên đăng nhập</label>
                            <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 font-medium text-slate-800">
                                {{ $employee->user->username }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-500 mb-2">Vai trò</label>
                            <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200">
                                @if ($employee->user->role)
                                    @php
                                        $roleClass = match ($employee->user->role->name) {
                                            'admin' => 'bg-violet-100 text-violet-700',
                                            'manager' => 'bg-blue-100 text-blue-700',
                                            'employee' => 'bg-cyan-100 text-cyan-700',
                                            default => 'bg-slate-100 text-slate-600',
                                        };
                                    @endphp
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold {{ $roleClass }}">
                                        {{ $employee->user->role->label() }}
                                    </span>
                                @else
                                    <span class="text-slate-400">Chưa phân quyền</span>
                                @endif
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-500 mb-2">Trạng thái tài khoản</label>
                            <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200">
                                @if ($employee->user->status === 'active')
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">Hoạt động</span>
                                @else
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">Đã khóa</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mt-5">
                        <a href="{{ route('admin.accounts.show', $employee->user) }}"
                           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-violet-100 text-violet-700 text-sm font-medium hover:bg-violet-200 transition">
                            Xem chi tiết tài khoản →
                        </a>
                    </div>
                @else
                    <div class="py-12 text-center">
                        <div class="w-16 h-16 mx-auto rounded-2xl bg-slate-100 flex items-center justify-center">
                            <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <p class="mt-4 text-slate-500 font-medium">Chưa liên kết tài khoản hệ thống</p>
                        <p class="mt-1 text-sm text-slate-400">Nhân viên này chưa được gán với tài khoản đăng nhập</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-slate-800">Hợp đồng lao động</h3>
                <span class="text-sm text-slate-500">{{ $contracts->count() }} hợp đồng gần đây</span>
            </div>

            <div class="p-6 overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-sm text-slate-500 border-b border-slate-100">
                            <th class="py-3 px-4 font-medium">Mã HĐ</th>
                            <th class="py-3 px-4 font-medium">Loại</th>
                            <th class="py-3 px-4 font-medium">Ngày bắt đầu</th>
                            <th class="py-3 px-4 font-medium">Ngày kết thúc</th>
                            <th class="py-3 px-4 font-medium">Lương</th>
                            <th class="py-3 px-4 font-medium">Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($contracts as $contract)
                            <tr class="border-b border-slate-50 hover:bg-slate-50">
                                <td class="py-3 px-4 font-medium text-slate-800">{{ $contract->contract_code }}</td>
                                <td class="py-3 px-4 text-slate-700">{{ $contract->contractType?->contract_name ?? '—' }}</td>
                                <td class="py-3 px-4 text-slate-700">{{ $contract->start_date?->format('d/m/Y') ?? '—' }}</td>
                                <td class="py-3 px-4 text-slate-700">{{ $contract->end_date?->format('d/m/Y') ?? 'Không xác định' }}</td>
                                <td class="py-3 px-4 text-slate-700">{{ number_format($contract->salary, 0, ',', '.') }} ₫</td>
                                <td class="py-3 px-4">
                                    @if ($contract->status === 'active')
                                        <span class="inline-flex px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold">Hiệu lực</span>
                                    @elseif ($contract->status === 'expired')
                                        <span class="inline-flex px-3 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-semibold">Hết hạn</span>
                                    @else
                                        <span class="inline-flex px-3 py-1 rounded-full bg-rose-100 text-rose-700 text-xs font-semibold">Chấm dứt</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400">Chưa có hợp đồng nào</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <div class="bg-white rounded-3xl shadow-sm border border-slate-100">
                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-800">Chấm công gần đây</h3>
                    <span class="text-sm text-slate-500">{{ $attendances->count() }} bản ghi</span>
                </div>

                <div class="p-6 overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-sm text-slate-500 border-b border-slate-100">
                                <th class="py-3 px-4 font-medium">Ngày</th>
                                <th class="py-3 px-4 font-medium">Giờ vào</th>
                                <th class="py-3 px-4 font-medium">Giờ ra</th>
                                <th class="py-3 px-4 font-medium">Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($attendances as $attendance)
                                <tr class="border-b border-slate-50 hover:bg-slate-50">
                                    <td class="py-3 px-4 text-slate-700">{{ $attendance->attendance_date?->format('d/m/Y') ?? '—' }}</td>
                                    <td class="py-3 px-4 text-slate-700">{{ $attendance->check_in?->format('H:i') ?? '—' }}</td>
                                    <td class="py-3 px-4 text-slate-700">{{ $attendance->check_out?->format('H:i') ?? '—' }}</td>
                                    <td class="py-3 px-4">
                                        @if ($attendance->status === 'present')
                                            <span class="inline-flex px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold">Có mặt</span>
                                        @elseif ($attendance->status === 'late')
                                            <span class="inline-flex px-3 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-semibold">Đi muộn</span>
                                        @elseif ($attendance->status === 'leave')
                                            <span class="inline-flex px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-semibold">Nghỉ phép</span>
                                        @else
                                            <span class="inline-flex px-3 py-1 rounded-full bg-rose-100 text-rose-700 text-xs font-semibold">Vắng</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-slate-400">Chưa có dữ liệu chấm công</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-slate-100">
                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-800">KPI được giao</h3>
                    <span class="text-sm text-slate-500">{{ $employeeKpis->count() }} KPI</span>
                </div>

                <div class="p-6 overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-sm text-slate-500 border-b border-slate-100">
                                <th class="py-3 px-4 font-medium">Tên KPI</th>
                                <th class="py-3 px-4 font-medium">Tiến độ</th>
                                <th class="py-3 px-4 font-medium">Điểm</th>
                                <th class="py-3 px-4 font-medium">Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($employeeKpis as $employeeKpi)
                                <tr class="border-b border-slate-50 hover:bg-slate-50">
                                    <td class="py-3 px-4 font-medium text-slate-800">{{ $employeeKpi->kpi?->title ?? '—' }}</td>
                                    <td class="py-3 px-4 text-slate-700">{{ $employeeKpi->progress }}%</td>
                                    <td class="py-3 px-4 text-slate-700">{{ $employeeKpi->score !== null ? number_format($employeeKpi->score, 1) : '—' }}</td>
                                    <td class="py-3 px-4">
                                        @if ($employeeKpi->status === 'completed')
                                            <span class="inline-flex px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold">Hoàn thành</span>
                                        @elseif ($employeeKpi->status === 'in_progress')
                                            <span class="inline-flex px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-semibold">Đang thực hiện</span>
                                        @else
                                            <span class="inline-flex px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold">Chờ xử lý</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-slate-400">Chưa có KPI nào</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <div class="bg-white rounded-3xl shadow-sm border border-slate-100">
                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-800">Bảng lương gần đây</h3>
                    <span class="text-sm text-slate-500">{{ $payrolls->count() }} kỳ lương</span>
                </div>

                <div class="p-6 overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-sm text-slate-500 border-b border-slate-100">
                                <th class="py-3 px-4 font-medium">Kỳ lương</th>
                                <th class="py-3 px-4 font-medium">Tổng lương</th>
                                <th class="py-3 px-4 font-medium">Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($payrolls as $payroll)
                                <tr class="border-b border-slate-50 hover:bg-slate-50">
                                    <td class="py-3 px-4 text-slate-700">{{ $payroll->payrollPeriod?->name ?? '—' }}</td>
                                    <td class="py-3 px-4 font-medium text-slate-800">{{ number_format($payroll->total_salary, 0, ',', '.') }} ₫</td>
                                    <td class="py-3 px-4">
                                        @if ($payroll->status === 'paid')
                                            <span class="inline-flex px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold">Đã thanh toán</span>
                                        @elseif ($payroll->status === 'approved')
                                            <span class="inline-flex px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-semibold">Đã duyệt</span>
                                        @elseif ($payroll->status === 'pending')
                                            <span class="inline-flex px-3 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-semibold">Chờ duyệt</span>
                                        @else
                                            <span class="inline-flex px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold">Nháp</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-8 text-center text-slate-400">Chưa có bảng lương nào</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-slate-100">
                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-slate-800">Tài liệu hồ sơ</h3>
                    <span class="text-sm text-slate-500">{{ $documents->count() }} tài liệu</span>
                </div>

                <div class="p-6 overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="text-sm text-slate-500 border-b border-slate-100">
                                <th class="py-3 px-4 font-medium">Tên tài liệu</th>
                                <th class="py-3 px-4 font-medium">Loại</th>
                                <th class="py-3 px-4 font-medium">Ngày tải lên</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($documents as $document)
                                <tr class="border-b border-slate-50 hover:bg-slate-50">
                                    <td class="py-3 px-4 font-medium text-slate-800">{{ $document->document_name }}</td>
                                    <td class="py-3 px-4 text-slate-700">
                                        @php
                                            $docType = match ($document->document_type) {
                                                'cccd' => 'CCCD/CMND',
                                                'cv' => 'CV',
                                                'certificate' => 'Chứng chỉ',
                                                'degree' => 'Bằng cấp',
                                                'contract' => 'Hợp đồng',
                                                default => $document->document_type,
                                            };
                                        @endphp
                                        {{ $docType }}
                                    </td>
                                    <td class="py-3 px-4 text-slate-700">{{ $document->created_at?->format('d/m/Y') ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-8 text-center text-slate-400">Chưa có tài liệu nào</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>

</x-admin-layout>
