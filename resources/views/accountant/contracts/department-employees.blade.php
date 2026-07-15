<x-accountant-layout title="Nhân viên - {{ $department->department_name }}" subtitle="Chọn nhân viên để xem hợp đồng">
    <div class="accountant-page">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <nav class="mb-2 flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-500">
                    <a href="{{ route('accountant.contracts.index') }}" class="text-amber-700 hover:underline">Phòng ban</a>
                    <span>/</span>
                    <span class="text-slate-700">{{ $department->department_name }}</span>
                </nav>
                <h2 class="text-2xl font-bold text-slate-900">{{ $department->department_name }}</h2>
                <p class="text-sm text-slate-500">{{ $employees->count() }} nhân viên</p>
            </div>
            <a href="{{ route('accountant.contracts.index') }}" class="accountant-btn-secondary">← Phòng ban</a>
        </div>

        <div class="accountant-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[720px] text-sm">
                    <thead>
                        <tr class="bg-amber-50/80 text-left text-xs font-bold uppercase text-slate-500">
                            <th class="px-5 py-3">Nhân viên</th>
                            <th class="px-5 py-3">Chức vụ</th>
                            <th class="px-5 py-3 text-center">Số HĐ</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($employees as $employee)
                            <tr class="hover:bg-amber-50/40">
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-slate-800">{{ $employee->full_name }}</p>
                                    <p class="text-xs text-slate-500">{{ $employee->employee_code }}</p>
                                </td>
                                <td class="px-5 py-4 text-slate-700">{{ $employee->position?->position_name ?? '—' }}</td>
                                <td class="px-5 py-4 text-center font-semibold">{{ $employee->contracts_count }}</td>
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('accountant.contracts.index', ['employee_id' => $employee->id]) }}"
                                       class="accountant-btn-secondary !py-1.5 !text-xs">
                                        Xem HĐ →
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-14 text-center text-slate-500">Phòng ban chưa có nhân viên.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-accountant-layout>
