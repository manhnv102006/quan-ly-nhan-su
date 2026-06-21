<x-admin-layout title="Quản lý nhân viên">

    <div class="space-y-6">

        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Quản lý nhân viên</h2>
                <p class="text-sm text-slate-500 mt-1">Xem chi tiết Quản lý nhân viên</p>
            </div>
            <a href="{{ route('admin.employees.create') }}" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-violet-600 text-white font-medium hover:bg-violet-700 transition">
                + Thêm nhân viên
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm text-slate-500">Tổng nhân viên</p>
                <h3 class="text-3xl font-bold mt-2 text-slate-900">{{ $stats['total'] }}</h3>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm text-slate-500">Đang làm việc</p>
                <h3 class="text-3xl font-bold mt-2 text-emerald-600">{{ $stats['active'] }}</h3>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm text-slate-500">Tạm khóa</p>
                <h3 class="text-3xl font-bold mt-2 text-amber-600">{{ $stats['inactive'] }}</h3>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

            <div class="px-6 py-4 border-b border-slate-200">
                <h3 class="font-semibold text-slate-800">Danh sách nhân viên</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-500">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-500">Tên nhân viên</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-500">Phòng ban</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-500">Chức vụ</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-500">Email</th>
                            <th class="px-6 py-3 text-center text-xs font-bold uppercase text-slate-500">Trạng thái</th>
                            <th class="px-6 py-3 text-center text-xs font-bold uppercase text-slate-500">Ngày tạo</th>
                            <th class="px-6 py-3 text-center text-xs font-bold uppercase text-slate-500">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($employees as $employee)
                            <tr class="border-b border-slate-200 hover:bg-slate-50 transition">
                                <td class="px-6 py-4 text-slate-700">{{ $employee->id }}</td>
                                <td class="px-6 py-4 text-slate-700 font-medium">{{ $employee->full_name }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $employee->department?->department_name ?? '-' }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $employee->position?->position_name ?? '-' }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $employee->email }}</td>
                                <td class="px-6 py-4 text-center">
                                    @if ($employee->status === 'active')
                                        <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Hoạt động</span>
                                    @elseif ($employee->status === 'inactive')
                                        <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Tạm khóa</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-700">Đã nghỉ</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center text-slate-600">{{ $employee->created_at?->format('d/m/Y') ?? '-' }}</td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('admin.employees.show', $employee->id) }}" class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center hover:bg-blue-200 transition" title="Xem">👁</a>
                                        <a href="{{ route('admin.employees.edit', $employee->id) }}" class="w-8 h-8 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center hover:bg-amber-200 transition" title="Sửa">✏️</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-12 text-slate-400">Không có nhân viên nào</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($employees->hasPages())
                <div class="px-6 py-4 border-t border-slate-200">
                    {{ $employees->links() }}
                </div>
            @endif
        </div>

    </div>

</x-admin-layout>
