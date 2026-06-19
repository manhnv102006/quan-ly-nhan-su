<x-admin-layout title="Chi tiết chức vụ">

    <div class="space-y-6">

        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Chi tiết chức vụ</h2>
                <p class="text-slate-500 mt-1">Xem thông tin chi tiết của chức vụ</p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('admin.positions.edit', $position) }}"
                   class="px-5 py-3 rounded-xl bg-violet-600 text-white font-medium hover:bg-violet-700 transition">
                    Sửa chức vụ
                </a>

                <a href="{{ route('admin.positions') }}"
                   class="px-5 py-3 rounded-xl bg-slate-200 text-slate-700 font-medium hover:bg-slate-300 transition">
                    ← Quay lại
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <div class="lg:col-span-2">
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100">
                    <div class="px-6 py-5 border-b border-slate-100">
                        <h3 class="text-lg font-semibold text-slate-800">Thông tin chức vụ</h3>
                    </div>

                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-medium text-slate-500 mb-2">Tên chức vụ</label>
                                <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 font-semibold text-slate-800">
                                    {{ $position->position_name }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-500 mb-2">Lương cơ bản</label>
                                <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-violet-700 font-semibold">
                                    {{ number_format($position->base_salary, 0, ',', '.') }} ₫
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-500 mb-2">Trạng thái</label>
                                <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200">
                                    @if ($position->status === 'active')
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">Hoạt động</span>
                                    @else
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-slate-200 text-slate-600">Không hoạt động</span>
                                    @endif
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-500 mb-2">Ngày tạo</label>
                                <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200">
                                    {{ $position->created_at?->format('d/m/Y H:i') }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-500 mb-2">Cập nhật lần cuối</label>
                                <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200">
                                    {{ $position->updated_at?->format('d/m/Y H:i') }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-5">
                            <label class="block text-sm font-medium text-slate-500 mb-2">Mô tả</label>
                            <div class="min-h-[120px] px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-700">
                                {{ $position->description ?: 'Không có mô tả' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8 text-center">
                    <div class="w-24 h-24 mx-auto rounded-3xl bg-indigo-100 flex items-center justify-center">
                        <svg class="w-12 h-12 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>

                    <h3 class="mt-5 text-xl font-bold text-slate-800">{{ $position->position_name }}</h3>

                    <p class="text-slate-500 mt-2">
                        {{ number_format($position->base_salary, 0, ',', '.') }} ₫ / tháng
                    </p>

                    <div class="mt-4">
                        @if ($position->status === 'active')
                            <span class="inline-flex items-center px-4 py-2 rounded-full bg-emerald-100 text-emerald-700 text-sm font-semibold">Hoạt động</span>
                        @else
                            <span class="inline-flex items-center px-4 py-2 rounded-full bg-slate-200 text-slate-600 text-sm font-semibold">Không hoạt động</span>
                        @endif
                    </div>

                    <p class="mt-6 text-sm text-slate-500">
                        <span class="font-bold text-slate-800">{{ $employees->count() }}</span> nhân viên đang giữ chức vụ này
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-slate-800">Nhân viên theo chức vụ</h3>
                <span class="text-sm text-slate-500">{{ $employees->count() }} nhân viên</span>
            </div>

            <div class="p-6 overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="text-sm text-slate-500 border-b border-slate-100">
                            <th class="py-3 px-4 font-medium">Mã NV</th>
                            <th class="py-3 px-4 font-medium">Họ tên</th>
                            <th class="py-3 px-4 font-medium">Email</th>
                            <th class="py-3 px-4 font-medium">Số điện thoại</th>
                            <th class="py-3 px-4 font-medium">Ngày vào làm</th>
                            <th class="py-3 px-4 font-medium">Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($employees as $employee)
                            <tr class="border-b border-slate-50 hover:bg-slate-50">
                                <td class="py-3 px-4 text-slate-700">{{ $employee->employee_code }}</td>
                                <td class="py-3 px-4 font-medium text-slate-800">{{ $employee->full_name }}</td>
                                <td class="py-3 px-4 text-slate-700">{{ $employee->email }}</td>
                                <td class="py-3 px-4 text-slate-700">{{ $employee->phone }}</td>
                                <td class="py-3 px-4 text-slate-700">{{ $employee->hire_date ? \Illuminate\Support\Carbon::parse($employee->hire_date)->format('d/m/Y') : '-' }}</td>
                                <td class="py-3 px-4">
                                    @if ($employee->status === 'active')
                                        <span class="inline-flex px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold">Đang làm</span>
                                    @elseif ($employee->status === 'resigned')
                                        <span class="inline-flex px-3 py-1 rounded-full bg-red-100 text-red-700 text-xs font-semibold">Đã nghỉ</span>
                                    @else
                                        <span class="inline-flex px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-xs font-semibold">Không hoạt động</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center">
                                            <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4" />
                                            </svg>
                                        </div>
                                        <p class="mt-4 text-slate-500 font-medium">Chưa có nhân viên nào</p>
                                        <p class="mt-1 text-sm text-slate-400">Chức vụ này chưa được gán cho nhân viên</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</x-admin-layout>
