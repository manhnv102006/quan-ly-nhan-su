<x-admin-layout title="Chi tiết phòng ban">

    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex items-center justify-between">

            <div>
                <h2 class="text-2xl font-bold text-slate-800">
                    Chi tiết phòng ban
                </h2>

                <p class="text-slate-500 mt-1">
                    Xem thông tin chi tiết của phòng ban
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('admin.departments.edit', $department->id) }}" class="px-5 py-3 rounded-xl bg-violet-600 text-white font-medium hover:bg-violet-700 transition">
                    Sửa phòng ban
                </a>

                <a href="{{ route('admin.departments') }}" class="px-5 py-3 rounded-xl bg-slate-200 text-slate-700 font-medium hover:bg-slate-300 transition">
                    ← Quay lại
                </a>
            </div>

        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Thông tin --}}
            <div class="lg:col-span-2">

                <div class="bg-white rounded-3xl shadow-sm border border-slate-100">

                    <div class="px-6 py-5 border-b border-slate-100">

                        <h3 class="text-lg font-semibold text-slate-800">
                            Thông tin phòng ban
                        </h3>

                    </div>

                    <div class="p-6">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                            <div>
                                <label class="block text-sm font-medium text-slate-500 mb-2">
                                    Mã phòng ban
                                </label>

                                <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200">
                                    {{ $department->department_code }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-500 mb-2">
                                    Tên phòng ban
                                </label>

                                <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200">
                                    {{ $department->department_name }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-500 mb-2">
                                    Quản lý
                                </label>

                                <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200">
                                    {{ $department->manager_id ?? 'Chưa chỉ định' }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-500 mb-2">
                                    Giới hạn nhân viên
                                </label>

                                <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200">
                                    {{ $department->maxEmployeesLimit() }} người
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-500 mb-2">
                                    Ngày tạo
                                </label>

                                <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200">
                                    {{ $department->created_at?->format('d/m/Y H:i') }}
                                </div>
                            </div>

                        </div>

                        <div class="mt-5">

                            <label class="block text-sm font-medium text-slate-500 mb-2">
                                Mô tả
                            </label>

                            <div class="min-h-[140px] px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-700">
                                {{ $department->description ?: 'Không có mô tả' }}
                            </div>

                        </div>

                    </div>

                </div>

            </div>

            {{-- Card bên phải --}}
            <div>

                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8 text-center">

                    <div class="w-24 h-24 mx-auto rounded-3xl bg-violet-100 flex items-center justify-center">

                        <svg class="w-12 h-12 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M5 21V7l7-4 7 4v14" />

                        </svg>

                    </div>

                    <h3 class="mt-5 text-xl font-bold text-slate-800">
                        {{ $department->department_name }}
                    </h3>

                    <p class="text-slate-500 mt-2">
                        {{ $department->department_code }}
                    </p>

                    <div class="mt-4">

                        <span class="inline-flex items-center px-4 py-2 rounded-full bg-emerald-100 text-emerald-700 text-sm font-semibold">
                            Active
                        </span>

                    </div>

                </div>

            </div>

        </div>

        {{-- Nhân viên phòng ban --}}
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100">

            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">

                <h3 class="text-lg font-semibold text-slate-800">
                    Nhân viên phòng ban
                </h3>

                <span class="text-sm text-slate-500">
                    {{ $department->employees_count ?? $department->employees->count() }}/{{ $department->maxEmployeesLimit() }} nhân viên
                </span>

            </div>

            <div class="p-6">

                {{-- Bảng nhân viên --}}
                <div class="overflow-x-auto">

                    <table class="w-full text-left">

                        <thead>
                            <tr class="text-sm text-slate-500 border-b border-slate-100">
                                <th class="py-3 px-4 font-medium">Mã NV</th>
                                <th class="py-3 px-4 font-medium">Họ tên</th>
                                <th class="py-3 px-4 font-medium">Giới tính</th>
                                <th class="py-3 px-4 font-medium">Ngày sinh</th>
                                <th class="py-3 px-4 font-medium">Chức vụ</th>
                                <th class="py-3 px-4 font-medium">Email</th>
                                <th class="py-3 px-4 font-medium">Số điện thoại</th>
                                <th class="py-3 px-4 font-medium">Ngày vào làm</th>
                                <th class="py-3 px-4 font-medium">Trạng thái</th>
                            </tr>
                        </thead>

                        <tbody>


                            @forelse($department->employees as $employee)
                            <tr class="border-b border-slate-50 hover:bg-slate-50">
                                <td class="py-3 px-4 text-slate-700">{{ $employee->employee_code }}</td>
                                <td class="py-3 px-4 text-slate-700">{{ $employee->full_name }}</td>
                                <td class="py-3 px-4 text-slate-700">{{ $employee->gender }}</td>
                                <td class="py-3 px-4 text-slate-700">{{ $employee->date_of_birth?->format('d/m/Y') }}</td>
                                <td class="py-3 px-4 text-slate-700">{{ $employee->position->position_name ?? '-' }}</td>
                                <td class="py-3 px-4 text-slate-700">{{ $employee->email }}</td>
                                <td class="py-3 px-4 text-slate-700">{{ $employee->phone }}</td>
                                <td class="py-3 px-4 text-slate-700">{{ $employee->hire_date?->format('d/m/Y') }}</td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold">
                                        {{ $employee->status }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-6">
                                    Chưa có nhân viên nào
                                </td>
                            </tr>
                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

</x-admin-layout>
