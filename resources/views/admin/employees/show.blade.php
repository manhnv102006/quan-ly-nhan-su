<x-admin-layout title="Chi tiết nhân viên">

    <div class="space-y-6">

        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Chi tiết nhân viên</h2>
                <p class="text-sm text-slate-500 mt-1">{{ $employee->full_name }}</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.employees.edit', $employee->id) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-amber-100 text-amber-600 text-sm font-medium hover:bg-amber-200">✏️ Sửa</a>
                <a href="{{ route('admin.employees') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-200 bg-white text-sm">← Quay lại</a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Main Info Card -->
            <div class="lg:col-span-2 bg-white rounded-3xl shadow-sm border border-slate-100 p-6 space-y-6">

                <div>
                    <h3 class="text-lg font-semibold text-slate-800 mb-4">Thông tin cơ bản</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-bold uppercase text-slate-500">Mã nhân viên</label>
                            <p class="mt-1 text-slate-800 font-medium">{{ $employee->employee_code }}</p>
                        </div>
                        <div>
                            <label class="text-xs font-bold uppercase text-slate-500">Họ và tên</label>
                            <p class="mt-1 text-slate-800 font-medium">{{ $employee->full_name }}</p>
                        </div>
                        <div>
                            <label class="text-xs font-bold uppercase text-slate-500">Giới tính</label>
                            <p class="mt-1 text-slate-800">
                                @if($employee->gender === 'male') Nam
                                @elseif($employee->gender === 'female') Nữ
                                @else Khác
                                @endif
                            </p>
                        </div>
                        <div>
                            <label class="text-xs font-bold uppercase text-slate-500">Ngày sinh</label>
                            <p class="mt-1 text-slate-800">{{ $employee->date_of_birth?->format('d/m/Y') }}</p>
                        </div>
                    </div>
                </div>

                <hr class="border-slate-100">

                <div>
                    <h3 class="text-lg font-semibold text-slate-800 mb-4">Thông tin liên hệ</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-bold uppercase text-slate-500">Email</label>
                            <p class="mt-1 text-slate-800">{{ $employee->email }}</p>
                        </div>
                        <div>
                            <label class="text-xs font-bold uppercase text-slate-500">Số điện thoại</label>
                            <p class="mt-1 text-slate-800">{{ $employee->phone }}</p>
                        </div>
                        <div class="col-span-2">
                            <label class="text-xs font-bold uppercase text-slate-500">Địa chỉ</label>
                            <p class="mt-1 text-slate-800">{{ $employee->address ?? 'Chưa cập nhật' }}</p>
                        </div>
                    </div>
                </div>

                <hr class="border-slate-100">

                <div>
                    <h3 class="text-lg font-semibold text-slate-800 mb-4">Thông tin công việc</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-bold uppercase text-slate-500">Phòng ban</label>
                            <p class="mt-1 text-slate-800">{{ $employee->department?->department_name ?? 'Chưa gán' }}</p>
                        </div>
                        <div>
                            <label class="text-xs font-bold uppercase text-slate-500">Chức vụ</label>
                            <p class="mt-1 text-slate-800">{{ $employee->position?->position_name ?? 'Chưa gán' }}</p>
                        </div>
                        <div>
                            <label class="text-xs font-bold uppercase text-slate-500">Ngày vào làm</label>
                            <p class="mt-1 text-slate-800">{{ $employee->hire_date?->format('d/m/Y') }}</p>
                        </div>
                        <div>
                            <label class="text-xs font-bold uppercase text-slate-500">Trạng thái</label>
                            <p class="mt-1">
                                @if($employee->status === 'active')
                                    <span class="inline-flex px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold">Hoạt động</span>
                                @elseif($employee->status === 'inactive')
                                    <span class="inline-flex px-3 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-semibold">Tạm khóa</span>
                                @else
                                    <span class="inline-flex px-3 py-1 rounded-full bg-rose-100 text-rose-700 text-xs font-semibold">Đã nghỉ</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Sidebar -->
            <div class="space-y-6">

                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
                    <h3 class="text-lg font-semibold text-slate-800 mb-4">Avatar</h3>
                    <div class="w-full aspect-square rounded-2xl bg-slate-100 flex items-center justify-center text-6xl">
                        @if($employee->avatar)
                            <img src="{{ asset('storage/' . $employee->avatar) }}" alt="avatar" class="w-full h-full object-cover rounded-2xl"/>
                        @else
                            {{ strtoupper(substr($employee->full_name, 0, 1)) }}
                        @endif
                    </div>
                </div>

                <div class="bg-slate-50 rounded-3xl border border-slate-200 p-6">
                    <h3 class="text-lg font-semibold text-slate-800 mb-4">Hành động</h3>
                    <div class="space-y-3">
                        <a href="{{ route('admin.employees.edit', $employee->id) }}" class="w-full block text-center px-4 py-3 rounded-xl bg-violet-600 text-white font-medium hover:bg-violet-700 transition">
                            Sửa thông tin
                        </a>
                    </div>
                </div>

            </div>

        </div>

    </div>

</x-admin-layout>
