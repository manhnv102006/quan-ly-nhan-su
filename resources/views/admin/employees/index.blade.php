<x-admin-layout title="Quản lý nhân viên">

    <div class="space-y-6">

        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Danh sách nhân viên</h2>
                <p class="text-sm text-slate-500 mt-1">Tổng cộng {{ $stats['total'] }} nhân viên</p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <form action="{{ route('admin.employees') }}" method="GET" class="flex items-center gap-3">
                    <div class="relative">
                        <input
                            type="text"
                            name="search"
                            value="{{ $search }}"
                            placeholder="Tìm mã, tên, email hoặc số điện thoại"
                            class="w-full min-w-[240px] rounded-2xl border border-slate-200 bg-white py-3 pl-10 pr-4 text-slate-800 text-sm outline-none focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 transition"
                        >
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">🔍</span>
                    </div>
                    <button type="submit" class="inline-flex items-center gap-2 rounded-2xl bg-violet-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-violet-500/20 hover:bg-violet-700 transition">Tìm kiếm</button>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm text-slate-500">Tổng nhân viên</p>
                <h3 class="text-3xl font-bold mt-3 text-slate-900">{{ $stats['total'] }}</h3>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm text-slate-500">Đang làm việc</p>
                <h3 class="text-3xl font-bold mt-3 text-emerald-600">{{ $stats['active'] }}</h3>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm text-slate-500">Tạm khóa</p>
                <h3 class="text-3xl font-bold mt-3 text-amber-600">{{ $stats['inactive'] }}</h3>
            </div>
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm text-slate-500">Đã nghỉ việc</p>
                <h3 class="text-3xl font-bold mt-3 text-rose-600">{{ $stats['resigned'] }}</h3>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100">
                <h3 class="font-semibold text-slate-800">Danh sách nhân viên</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-slate-50">
                            <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500">Mã NV</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500">Họ và tên</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500">Email</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500">SĐT</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500">Phòng ban</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500">Chức vụ</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500">Ngày vào</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500">Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($employees as $employee)
                            <tr class="border-t border-slate-100 hover:bg-slate-50 transition">
                                <td class="px-6 py-4 text-slate-700 font-medium">{{ $employee->employee_code }}</td>
                                <td class="px-6 py-4 font-semibold text-slate-800">{{ $employee->full_name }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $employee->email }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $employee->phone }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $employee->department?->department_name ?? 'Chưa gán' }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $employee->position?->position_name ?? 'Chưa gán' }}</td>
                                <td class="px-6 py-4 text-slate-600">
                                    {{ $employee->hire_date ? \Illuminate\Support\Carbon::parse($employee->hire_date)->format('d/m/Y') : '-' }}
                                </td>
                                <td class="px-6 py-4">
                                    @if ($employee->status === 'active')
                                        <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Hoạt động</span>
                                    @elseif ($employee->status === 'inactive')
                                        <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Tạm khóa</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-700">Đã nghỉ</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-12 text-slate-400">Không có nhân viên phù hợp</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($employees->hasPages())
                <div class="px-6 py-4 border-t border-slate-100">
                    {{ $employees->links() }}
                </div>
            @endif
        </div>

    </div>

</x-admin-layout>
