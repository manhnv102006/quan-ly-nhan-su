@php
    $hasFilters = collect($filters ?? [])->filter(fn ($v) => filled($v))->isNotEmpty();
@endphp

<x-admin-layout title="Hợp đồng - {{ $employee->full_name }}">
    <div class="space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                @include('admin.contracts.partials.breadcrumb', ['department' => $department, 'employee' => $employee])
                <h2 class="mt-2 text-2xl font-bold text-slate-800">{{ $employee->full_name }}</h2>
                <p class="text-sm text-slate-500">
                    {{ $employee->employee_code }}
                    · {{ $employee->position?->position_name ?? '—' }}
                    · {{ $department?->department_name ?? '—' }}
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if($department)
                    <a href="{{ route('admin.contracts.by-department', $department) }}" class="admin-btn-secondary">← Nhân viên</a>
                @endif
                <a href="{{ route('admin.contracts.history', ['employee_id' => $employee->id]) }}" class="admin-btn-secondary">Lịch sử</a>
                <a href="{{ route('admin.contracts.create', ['employee_id' => $employee->id]) }}" class="admin-btn-violet">Thêm HĐ</a>
            </div>
        </div>

        <div class="admin-card p-5 sm:p-6">
            <form action="{{ route('admin.contracts.by-employee', $employee) }}" method="GET"
                  class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div>
                    <label for="status" class="admin-label">Trạng thái</label>
                    <select id="status" name="status" class="admin-field">
                        <option value="">Tất cả</option>
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="contract_type_id" class="admin-label">Loại HĐ</label>
                    <select id="contract_type_id" name="contract_type_id" class="admin-field">
                        <option value="">Tất cả</option>
                        @foreach($contractTypes as $type)
                            <option value="{{ $type->id }}" @selected(($filters['contract_type_id'] ?? '') == $type->id)>{{ $type->contract_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="admin-btn-primary">Lọc</button>
                    @if($hasFilters)
                        <a href="{{ route('admin.contracts.by-employee', $employee) }}" class="admin-btn-secondary">Xóa lọc</a>
                    @endif
                </div>
            </form>
        </div>

        <div class="admin-card overflow-hidden">
            <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                <h3 class="text-sm font-bold text-slate-800">Hợp đồng của nhân viên</h3>
                <p class="text-xs text-slate-500">{{ $contracts->total() }} hợp đồng</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px]">
                    <thead>
                        <tr class="bg-slate-50">
                            <th class="px-5 py-3 text-left text-xs font-bold uppercase text-slate-500">Mã HĐ</th>
                            <th class="px-5 py-3 text-left text-xs font-bold uppercase text-slate-500">Loại HĐ</th>
                            <th class="px-5 py-3 text-right text-xs font-bold uppercase text-slate-500">Lương</th>
                            <th class="px-5 py-3 text-left text-xs font-bold uppercase text-slate-500">Thời hạn</th>
                            <th class="px-5 py-3 text-center text-xs font-bold uppercase text-slate-500">Trạng thái</th>
                            <th class="px-5 py-3 text-center text-xs font-bold uppercase text-slate-500">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($contracts as $contract)
                            <tr class="transition hover:bg-slate-50/60">
                                <td class="px-5 py-4">
                                    <a href="{{ route('admin.contracts.show', $contract) }}"
                                       class="text-sm font-bold text-violet-600 hover:text-violet-700">
                                        {{ $contract->contract_code }}
                                    </a>
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-700">{{ $contract->contractType?->contract_name ?? '—' }}</td>
                                <td class="px-5 py-4 text-right text-sm font-semibold text-slate-800">
                                    {{ number_format($contract->salary, 0, ',', '.') }}₫
                                </td>
                                <td class="px-5 py-4 text-xs font-medium text-slate-700">
                                    {{ optional($contract->start_date)->format('d/m/Y') ?? '—' }}
                                    →
                                    {{ optional($contract->end_date)->format('d/m/Y') ?? 'Không xác định' }}
                                    @if($contract->isExpiringSoon())
                                        <span class="mt-1 block text-[11px] font-bold text-violet-600">Sắp hết hạn</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-center">
                                    @include('admin.contracts.partials.status-badge', ['contract' => $contract])
                                </td>
                                <td class="px-5 py-4">
                                    @include('admin.contracts.partials.row-actions', ['contract' => $contract])
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-14 text-center text-sm text-slate-500">
                                    Nhân viên chưa có hợp đồng nào.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($contracts->hasPages())
                <div class="border-t border-slate-100 px-5 py-4 sm:px-6">
                    {{ $contracts->links() }}
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>
