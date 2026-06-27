<x-admin-layout title="Chi tiết nhân viên">

    <div class="space-y-6">

        {{-- Profile hero --}}
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-violet-600 via-indigo-600 to-cyan-600 text-white shadow-xl shadow-violet-500/20">
            <div class="absolute inset-0 opacity-20">
                <div class="absolute -top-10 -right-10 w-56 h-56 rounded-full bg-white/30 blur-2xl"></div>
                <div class="absolute bottom-0 left-10 w-40 h-40 rounded-full bg-cyan-300/40 blur-2xl"></div>
            </div>
            <div class="relative p-6 sm:p-8">
                <div class="flex flex-col lg:flex-row lg:items-center gap-6">
                    <div class="w-24 h-24 rounded-3xl bg-white/20 backdrop-blur border border-white/30 flex items-center justify-center overflow-hidden shrink-0 shadow-lg">
                        @if ($employee->avatar)
                            <img src="{{ asset('storage/' . $employee->avatar) }}"
                                 alt="{{ $employee->full_name }}"
                                 class="w-full h-full object-cover">
                        @else
                            <span class="text-4xl font-bold text-white">
                                {{ strtoupper(substr($employee->full_name, 0, 1)) }}
                            </span>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-violet-100 text-sm font-medium tracking-wide uppercase">Hồ sơ nhân viên</p>
                        <h2 class="mt-1 text-2xl sm:text-3xl font-bold truncate">{{ $employee->full_name }}</h2>
                        <p class="mt-1 text-violet-100 font-mono text-sm">{{ $employee->employee_code }}</p>
                        <div class="mt-4 flex flex-wrap items-center gap-2">
                            @if ($employee->status === 'active')
                                <span class="inline-flex px-3 py-1 rounded-full bg-emerald-400/20 border border-emerald-300/40 text-emerald-50 text-xs font-semibold">Đang làm việc</span>
                            @elseif ($employee->status === 'inactive')
                                <span class="inline-flex px-3 py-1 rounded-full bg-amber-400/20 border border-amber-300/40 text-amber-50 text-xs font-semibold">Tạm khóa</span>
                            @else
                                <span class="inline-flex px-3 py-1 rounded-full bg-rose-400/20 border border-rose-300/40 text-rose-50 text-xs font-semibold">Đã nghỉ</span>
                            @endif
                            @if ($employee->department)
                                <span class="inline-flex px-3 py-1 rounded-full bg-white/15 border border-white/25 text-white text-xs font-medium">
                                    {{ $employee->department->department_name }}
                                </span>
                            @endif
                            @if ($employee->position)
                                <span class="inline-flex px-3 py-1 rounded-full bg-white/15 border border-white/25 text-white text-xs font-medium">
                                    {{ $employee->position->position_name }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 lg:gap-4 shrink-0">
                        <div class="rounded-2xl bg-white/10 backdrop-blur border border-white/20 px-4 py-3 text-center">
                            <p class="text-2xl font-bold">{{ $documents->count() }}</p>
                            <p class="text-[11px] text-violet-100 mt-0.5">Tài liệu</p>
                        </div>
                        <div class="rounded-2xl bg-white/10 backdrop-blur border border-white/20 px-4 py-3 text-center">
                            <p class="text-2xl font-bold">{{ $contracts->count() }}</p>
                            <p class="text-[11px] text-violet-100 mt-0.5">Hợp đồng</p>
                        </div>
                        <div class="rounded-2xl bg-white/10 backdrop-blur border border-white/20 px-4 py-3 text-center">
                            <p class="text-2xl font-bold">{{ $employeeKpis->count() }}</p>
                            <p class="text-[11px] text-violet-100 mt-0.5">KPI</p>
                        </div>
                        <div class="rounded-2xl bg-white/10 backdrop-blur border border-white/20 px-4 py-3 text-center">
                            <p class="text-2xl font-bold">{{ $attendances->count() }}</p>
                            <p class="text-[11px] text-violet-100 mt-0.5">Chấm công</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Thông tin chi tiết</h2>
                <p class="text-slate-500 mt-1 text-sm">Quản lý hồ sơ, tài liệu và lịch sử công việc</p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                @if ($departments->where('id', '!=', $employee->department_id)->isNotEmpty())
                    <button type="button"
                            id="open-transfer-modal"
                            class="px-5 py-3 rounded-xl bg-blue-100 text-blue-700 font-medium hover:bg-blue-200 transition">
                        Điều chuyển phòng ban
                    </button>
                @endif

                <a href="{{ route('admin.employees.edit', $employee) }}"
                   class="px-5 py-3 rounded-xl bg-violet-600 text-white font-medium hover:bg-violet-700 transition">
                    Sửa nhân viên
                </a>

                <form action="{{ route('admin.employees.destroy', $employee) }}"
                      method="POST"
                      id="delete-form-show">
                    @csrf
                    @method('DELETE')
                    <button type="button"
                            id="open-delete-modal-show"
                            data-employee-name="{{ $employee->full_name }}"
                            class="px-5 py-3 rounded-xl bg-red-100 text-red-700 font-medium hover:bg-red-200 transition">
                        Xóa mềm
                    </button>
                </form>

                <a href="{{ route('admin.employees') }}"
                   class="px-5 py-3 rounded-xl bg-slate-200 text-slate-700 font-medium hover:bg-slate-300 transition">
                    ← Quay lại
                </a>
            </div>
        </div>

        @if (session('error'))
            <div class="flex items-center gap-3 bg-white border border-red-200 shadow-sm rounded-2xl px-5 py-4">
                <p class="text-sm font-medium text-red-700">{{ session('error') }}</p>
            </div>
        @endif

        @if (session('success'))
            <div class="flex items-center gap-3 bg-white border border-emerald-200 shadow-sm rounded-2xl px-5 py-4">
                <p class="text-sm font-medium text-emerald-700">{{ session('success') }}</p>
            </div>
        @endif

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

                <div id="lich-su-dieu-chuyen" class="bg-white rounded-3xl shadow-sm border border-slate-100">
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-slate-800">Lịch sử điều chuyển phòng ban</h3>
                        <span class="text-sm text-slate-500">{{ $transferHistory->count() }} bản ghi</span>
                    </div>

                    <div class="p-6 overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="text-sm text-slate-500 border-b border-slate-100">
                                    <th class="py-3 px-4 font-medium">Từ phòng ban</th>
                                    <th class="py-3 px-4 font-medium">Đến phòng ban</th>
                                    <th class="py-3 px-4 font-medium">Ngày hiệu lực</th>
                                    <th class="py-3 px-4 font-medium">Người thực hiện</th>
                                    <th class="py-3 px-4 font-medium">Ghi chú</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($transferHistory as $transfer)
                                    <tr class="border-b border-slate-50 hover:bg-slate-50">
                                        <td class="py-3 px-4 text-slate-700">{{ $transfer->fromDepartment?->department_name ?? 'Chưa gán' }}</td>
                                        <td class="py-3 px-4 font-medium text-slate-800">{{ $transfer->toDepartment?->department_name ?? '—' }}</td>
                                        <td class="py-3 px-4 text-slate-700">{{ $transfer->effective_date?->format('d/m/Y') ?? '—' }}</td>
                                        <td class="py-3 px-4 text-slate-700">{{ $transfer->transferredBy?->name ?? '—' }}</td>
                                        <td class="py-3 px-4 text-slate-600">{{ $transfer->note ?: '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-8 text-center text-slate-400">Chưa có lịch sử điều chuyển</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <div class="space-y-4">
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                    <p class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-4">Điều hướng nhanh</p>
                    <nav class="space-y-1">
                        <a href="#ho-so-tai-lieu" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-violet-50 hover:text-violet-700 transition">
                            <span class="w-8 h-8 rounded-lg bg-violet-100 text-violet-600 flex items-center justify-center text-xs">📁</span>
                            Tài liệu hồ sơ
                        </a>
                        <a href="#tai-khoan-lien-ket" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-violet-50 hover:text-violet-700 transition">
                            <span class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center text-xs">👤</span>
                            Tài khoản liên kết
                        </a>
                        <a href="#hop-dong" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-violet-50 hover:text-violet-700 transition">
                            <span class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs">📄</span>
                            Hợp đồng lao động
                        </a>
                        <a href="#lich-su-dieu-chuyen" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-slate-600 hover:bg-violet-50 hover:text-violet-700 transition">
                            <span class="w-8 h-8 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center text-xs">↔</span>
                            Lịch sử điều chuyển
                        </a>
                    </nav>
                </div>

                <div class="bg-gradient-to-br from-violet-600 to-indigo-700 rounded-3xl p-6 text-white shadow-lg shadow-violet-500/20">
                    <p class="text-sm font-semibold opacity-90">Liên hệ nhanh</p>
                    <p class="mt-2 text-sm text-violet-100">{{ $employee->email }}</p>
                    <p class="mt-1 text-sm text-violet-100">{{ $employee->phone }}</p>
                    @if ($employee->hire_date)
                        <p class="mt-4 text-xs text-violet-200">Vào làm từ {{ $employee->hire_date->format('d/m/Y') }}</p>
                    @endif
                </div>
            </div>

        </div>

        <div id="tai-khoan-lien-ket" class="bg-white rounded-3xl shadow-sm border border-slate-100">
            <div class="px-6 py-5 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
                <h3 class="text-lg font-semibold text-slate-800">Tài khoản hệ thống liên kết</h3>
                @if ($employee->hasLinkedAccount())
                    <form action="{{ route('admin.employees.unlink-account', $employee) }}"
                          method="POST"
                          id="unlink-account-form">
                        @csrf
                        @method('PATCH')
                        <button type="button"
                                id="open-unlink-account-modal"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-orange-100 text-orange-700 text-sm font-medium hover:bg-orange-200 transition">
                            Gỡ liên kết
                        </button>
                    </form>
                @elseif ($availableAccounts->isNotEmpty())
                    <button type="button"
                            id="open-link-account-modal"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-violet-600 text-white text-sm font-medium hover:bg-violet-700 transition">
                        + Liên kết tài khoản
                    </button>
                @endif
            </div>

            <div class="p-6">
                @if ($employee->hasLinkedAccount())
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
                        @if ($availableAccounts->isNotEmpty())
                            <button type="button"
                                    id="open-link-account-modal-empty"
                                    onclick="document.getElementById('open-link-account-modal')?.click()"
                                    class="mt-5 inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-violet-600 text-white text-sm font-medium hover:bg-violet-700 transition">
                                Liên kết tài khoản ngay
                            </button>
                        @else
                            <p class="mt-4 text-sm text-amber-600">Không còn tài khoản trống để liên kết.</p>
                            <a href="{{ route('admin.accounts.create') }}"
                               class="mt-3 inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-violet-100 text-violet-700 text-sm font-medium hover:bg-violet-200 transition">
                                Tạo tài khoản mới
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <div id="hop-dong" class="bg-white rounded-3xl shadow-sm border border-slate-100">
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

        </div>

        @include('admin.employees.partials.documents-grid', [
            'employee' => $employee,
            'documents' => $documents,
        ])

    </div>

    @include('admin.employees.partials.transfer-department-modal')
    @include('admin.employees.partials.link-account-modal')

    <div id="unlink-account-modal"
         class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm"
         style="display: none;">
        <div class="bg-white rounded-3xl shadow-xl w-full max-w-sm mx-4 p-6 text-center">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-orange-100 flex items-center justify-center">
                <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                </svg>
            </div>
            <h3 class="mt-5 text-lg font-bold text-slate-800">Gỡ liên kết tài khoản?</h3>
            <p class="mt-2 text-sm text-slate-500">
                Tài khoản <span class="font-semibold text-slate-700">{{ $employee->user?->username }}</span>
                sẽ không còn liên kết với nhân viên này.
            </p>
            <div class="mt-6 flex gap-3">
                <button type="button" id="close-unlink-account-modal"
                        class="flex-1 px-5 py-3 rounded-xl bg-slate-100 text-slate-700 font-medium hover:bg-slate-200 transition">
                    Hủy
                </button>
                <button type="button" id="confirm-unlink-account"
                        class="flex-1 px-5 py-3 rounded-xl text-white font-medium transition"
                        style="background-color: #ea580c;">
                    Gỡ liên kết
                </button>
            </div>
        </div>
    </div>

    <div id="delete-confirm-modal"
         class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm">
        <div class="bg-white rounded-3xl shadow-xl w-full max-w-md mx-4 p-6">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-red-100 flex items-center justify-center">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </div>

            <h3 class="mt-5 text-lg font-bold text-slate-800 text-center">Chuyển nhân viên vào thùng rác?</h3>
            <p class="mt-2 text-sm text-slate-500 text-center">
                Bạn có chắc muốn xóa mềm nhân viên
                <span id="delete-employee-name" class="font-semibold text-slate-800">{{ $employee->full_name }}</span>?
            </p>
            <p class="mt-2 text-xs text-amber-600 text-center font-medium">
                Nhân viên sẽ được ẩn khỏi danh sách và có thể khôi phục từ mục「Nhân viên đã xóa」.
            </p>

            <div class="mt-6 flex gap-3">
                <button type="button" id="cancel-delete-btn"
                        class="flex-1 px-5 py-3 rounded-xl bg-slate-100 text-slate-700 font-medium hover:bg-slate-200 transition">
                    Hủy
                </button>
                <button type="button" id="confirm-delete-btn"
                        class="flex-1 px-5 py-3 rounded-xl bg-orange-600 text-white font-medium hover:bg-orange-700 transition">
                    Chuyển vào thùng rác
                </button>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const unlinkModal = document.getElementById('unlink-account-modal');
            const openUnlinkBtn = document.getElementById('open-unlink-account-modal');
            const closeUnlinkBtn = document.getElementById('close-unlink-account-modal');
            const confirmUnlinkBtn = document.getElementById('confirm-unlink-account');
            const unlinkForm = document.getElementById('unlink-account-form');

            if (openUnlinkBtn && unlinkModal) {
                function openUnlinkModal() {
                    unlinkModal.classList.remove('hidden');
                    unlinkModal.classList.add('flex');
                    unlinkModal.style.display = 'flex';
                }

                function closeUnlinkModal() {
                    unlinkModal.classList.add('hidden');
                    unlinkModal.classList.remove('flex');
                    unlinkModal.style.display = 'none';
                }

                openUnlinkBtn.addEventListener('click', openUnlinkModal);
                closeUnlinkBtn?.addEventListener('click', closeUnlinkModal);
                confirmUnlinkBtn?.addEventListener('click', function () {
                    if (unlinkForm) unlinkForm.submit();
                });
                unlinkModal.addEventListener('click', function (event) {
                    if (event.target === unlinkModal) closeUnlinkModal();
                });
            }
        })();

        (function () {
            const modal = document.getElementById('delete-confirm-modal');
            const openBtn = document.getElementById('open-delete-modal-show');
            const cancelBtn = document.getElementById('cancel-delete-btn');
            const confirmBtn = document.getElementById('confirm-delete-btn');
            const form = document.getElementById('delete-form-show');

            function openModal() {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }

            function closeModal() {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }

            openBtn.addEventListener('click', openModal);
            cancelBtn.addEventListener('click', closeModal);

            confirmBtn.addEventListener('click', function () {
                if (form) form.submit();
            });

            modal.addEventListener('click', function (event) {
                if (event.target === modal) closeModal();
            });
        })();
    </script>

</x-admin-layout>
