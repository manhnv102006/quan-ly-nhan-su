<x-admin-layout title="Gán ca làm">

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Gán ca làm</h2>
                <p class="text-sm text-slate-500 mt-1">Danh sách ca đã gán — có thể gán hàng loạt theo tháng/năm tại mục Gán ca.</p>
            </div>
            <a href="{{ route('admin.employee-shifts.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-violet-600 text-white text-sm font-medium hover:bg-violet-700 transition">
                + Gán ca
            </a>
        </div>

        @if (session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4">
                <p class="text-sm font-medium text-emerald-700">{{ session('success') }}</p>
            </div>
        @endif

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/80 text-left">
                            <th class="p-4 font-semibold text-slate-600">Nhân viên</th>
                            <th class="p-4 font-semibold text-slate-600">Phòng ban</th>
                            <th class="p-4 font-semibold text-slate-600">Ca</th>
                            <th class="p-4 font-semibold text-slate-600">Ngày</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($employeeShifts as $item)
                            <tr class="border-b border-slate-100 hover:bg-slate-50/50">
                                <td class="p-4">
                                    <p class="font-medium text-slate-800">{{ $item->employee?->full_name ?? '—' }}</p>
                                    <p class="text-xs text-slate-500">{{ $item->employee?->employee_code ?? '—' }}</p>
                                </td>
                                <td class="p-4 text-slate-600">
                                    {{ $item->employee?->department?->department_name ?? '—' }}
                                </td>
                                <td class="p-4 text-slate-700">
                                    {{ $item->shift?->shift_name ?? '—' }}
                                </td>
                                <td class="p-4 text-slate-700">
                                    {{ $item->work_date->format('d/m/Y') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="p-10 text-center text-slate-500">
                                    Chưa có dữ liệu gán ca.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($employeeShifts->hasPages())
                <div class="border-t border-slate-100 px-4 py-3">
                    {{ $employeeShifts->links() }}
                </div>
            @endif
        </div>
    </div>

</x-admin-layout>
