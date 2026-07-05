@php
    $hasFilters = collect($filters ?? [])->filter(fn ($v) => filled($v))->isNotEmpty();
@endphp

<x-admin-layout title="Quản lý hợp đồng">
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Quản lý hợp đồng</h2>
                <p class="text-sm text-slate-500">Lọc, tìm kiếm, gia hạn, hủy và quản lý thùng rác hợp đồng lao động.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.contracts.trashed') }}"
                   class="admin-btn-secondary">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                    </svg>
                    Thùng rác
                    @if(($stats['trashed'] ?? 0) > 0)
                        <span class="rounded-full bg-rose-100 px-2 py-0.5 text-[10px] font-bold text-rose-600">{{ $stats['trashed'] }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.contracts.create') }}" class="admin-btn-violet">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Thêm hợp đồng
                </a>
            </div>
        </div>

        {{-- Thống kê --}}
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            @foreach ([
                ['label' => 'Tổng hợp đồng', 'value' => $stats['total'], 'tone' => 'text-slate-800', 'badge' => 'All'],
                ['label' => 'Đang hiệu lực', 'value' => $stats['active'], 'tone' => 'text-emerald-600', 'badge' => 'Active'],
                ['label' => 'Hết hiệu lực', 'value' => $stats['expired'], 'tone' => 'text-amber-600', 'badge' => 'Expired'],
                ['label' => 'Sắp hết hạn', 'value' => $stats['expiring_soon'], 'tone' => 'text-violet-600', 'badge' => '30d'],
            ] as $card)
                <div class="admin-stat-card border border-slate-100 bg-white/90">
                    <div class="flex items-start justify-between">
                        <p class="text-xs font-medium text-slate-500">{{ $card['label'] }}</p>
                        <span class="rounded-full bg-violet-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-violet-600">{{ $card['badge'] }}</span>
                    </div>
                    <p class="mt-2 text-2xl font-extrabold tracking-tight {{ $card['tone'] }}">{{ number_format($card['value']) }}</p>
                </div>
            @endforeach
        </div>

        {{-- Bộ lọc --}}
        <div class="admin-card p-5 sm:p-6">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h3 class="text-sm font-bold text-slate-800">Tìm kiếm &amp; lọc</h3>
                    <p class="text-xs text-slate-500">Mã HĐ, nhân viên, trạng thái, phòng ban, thời hạn</p>
                </div>
                @if($hasFilters)
                    <span class="rounded-full bg-violet-50 px-3 py-1 text-xs font-semibold text-violet-700">
                        Đang lọc · {{ $contracts->total() }} kết quả
                    </span>
                @endif
            </div>

            <form action="{{ route('admin.contracts.index') }}" method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="xl:col-span-2">
                    <label for="search" class="admin-label">Mã HĐ / Nhân viên</label>
                    <input type="text" id="search" name="search" value="{{ $filters['search'] ?? '' }}"
                           placeholder="HD001, Nguyễn Văn An..."
                           class="admin-field">
                </div>

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

                <div>
                    <label for="employee_id" class="admin-label">Nhân viên</label>
                    <select id="employee_id" name="employee_id" class="admin-field">
                        <option value="">Tất cả</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" @selected(($filters['employee_id'] ?? '') == $employee->id)>{{ $employee->full_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="department_id" class="admin-label">Phòng ban</label>
                    <select id="department_id" name="department_id" class="admin-field">
                        <option value="">Tất cả</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" @selected(($filters['department_id'] ?? '') == $dept->id)>{{ $dept->department_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="position_id" class="admin-label">Chức vụ</label>
                    <select id="position_id" name="position_id" class="admin-field">
                        <option value="">Tất cả</option>
                        @foreach($positions as $pos)
                            <option value="{{ $pos->id }}" @selected(($filters['position_id'] ?? '') == $pos->id)>{{ $pos->position_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="start_from" class="admin-label">Từ ngày (bắt đầu)</label>
                    <input type="date" id="start_from" name="start_from" value="{{ $filters['start_from'] ?? '' }}" class="admin-field">
                </div>

                <div>
                    <label for="end_to" class="admin-label">Đến ngày (kết thúc)</label>
                    <input type="date" id="end_to" name="end_to" value="{{ $filters['end_to'] ?? '' }}" class="admin-field">
                </div>

                <div class="flex flex-wrap items-end gap-2 md:col-span-2 xl:col-span-4">
                    <button type="submit" class="admin-btn-primary">
                        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                        <span>Tìm kiếm</span>
                    </button>
                    @if($hasFilters)
                        <a href="{{ route('admin.contracts.index') }}" class="admin-btn-secondary">Xóa bộ lọc</a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Bảng --}}
        <div class="admin-card overflow-hidden">
            <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                <h3 class="text-sm font-bold text-slate-800">Danh sách hợp đồng</h3>
                <p class="text-xs text-slate-500">Hiển thị {{ $contracts->count() }} / {{ $contracts->total() }} hợp đồng</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[1100px]">
                    <thead>
                        <tr class="bg-slate-50">
                            <th class="px-5 py-3 text-left text-xs font-bold uppercase text-slate-500">#</th>
                            <th class="px-5 py-3 text-left text-xs font-bold uppercase text-slate-500">Mã HĐ</th>
                            <th class="px-5 py-3 text-left text-xs font-bold uppercase text-slate-500">Nhân viên</th>
                            <th class="px-5 py-3 text-left text-xs font-bold uppercase text-slate-500">Phòng ban</th>
                            <th class="px-5 py-3 text-left text-xs font-bold uppercase text-slate-500">Chức vụ</th>
                            <th class="px-5 py-3 text-left text-xs font-bold uppercase text-slate-500">Loại HĐ</th>
                            <th class="px-5 py-3 text-right text-xs font-bold uppercase text-slate-500">Lương</th>
                            <th class="px-5 py-3 text-left text-xs font-bold uppercase text-slate-500">Thời hạn</th>
                            <th class="px-5 py-3 text-center text-xs font-bold uppercase text-slate-500">Trạng thái</th>
                            <th class="px-5 py-3 text-center text-xs font-bold uppercase text-slate-500">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($contracts as $index => $contract)
                            <tr class="transition hover:bg-slate-50/60">
                                <td class="px-5 py-4 text-xs font-medium text-slate-500">{{ $contracts->firstItem() + $index }}</td>
                                <td class="px-5 py-4">
                                    <a href="{{ route('admin.contracts.show', $contract) }}" class="text-sm font-bold text-violet-600 hover:text-violet-700">
                                        {{ $contract->contract_code }}
                                    </a>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="text-sm font-semibold text-slate-800">{{ $contract->employee->full_name ?? '—' }}</p>
                                    <p class="text-xs text-slate-500">{{ $contract->employee->employee_code ?? '—' }}</p>
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-700">{{ $contract->display_department_name }}</td>
                                <td class="px-5 py-4 text-sm text-slate-700">{{ $contract->display_position_name }}</td>
                                <td class="px-5 py-4 text-sm text-slate-700">{{ $contract->contractType->contract_name ?? '—' }}</td>
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
                                <td colspan="10" class="px-5 py-14 text-center">
                                    <p class="text-sm font-semibold text-slate-600">Không có hợp đồng phù hợp.</p>
                                    @if($hasFilters)
                                        <a href="{{ route('admin.contracts.index') }}" class="mt-2 inline-block text-sm font-medium text-violet-600 hover:text-violet-700">
                                            Xóa bộ lọc để xem tất cả
                                        </a>
                                    @endif
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
