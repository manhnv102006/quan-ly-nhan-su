@php
    $hasFilters = collect($filters ?? [])->filter(fn ($v) => filled($v))->isNotEmpty();
@endphp

<x-accountant-layout title="Lịch sử thay đổi" subtitle="Theo dõi mọi chỉnh sửa BH · Thuế · Lương · Tạm ứng · Hợp đồng">
    <div class="accountant-page">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">Lịch sử thay đổi</h2>
            <p class="text-sm text-slate-500">Ai sửa · Sửa gì · Giá trị cũ → mới · Thời gian · Nhân viên bị ảnh hưởng</p>
        </div>

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-6">
            @include('accountant.partials.stat-card', ['label' => 'Tổng log', 'value' => $stats['total']])
            @include('accountant.partials.stat-card', ['label' => 'Bảo hiểm', 'value' => $stats['insurance'], 'tone' => 'text-sky-600'])
            @include('accountant.partials.stat-card', ['label' => 'Thuế', 'value' => $stats['tax'], 'tone' => 'text-violet-600'])
            @include('accountant.partials.stat-card', ['label' => 'Lương', 'value' => $stats['payroll'], 'tone' => 'text-amber-600'])
            @include('accountant.partials.stat-card', ['label' => 'Tạm ứng', 'value' => $stats['advance'], 'tone' => 'text-orange-600'])
            @include('accountant.partials.stat-card', ['label' => 'Hợp đồng', 'value' => $stats['contract'], 'tone' => 'text-indigo-600'])
        </div>

        <form method="GET" class="accountant-card flex flex-wrap items-end gap-4 p-5">
            <div class="min-w-[160px]">
                <label class="accountant-label">Module</label>
                <select name="module" class="accountant-field">
                    <option value="">Tất cả</option>
                    @foreach(\App\Models\ModuleChangeLog::MODULE_LABELS as $val => $label)
                        <option value="{{ $val }}" @selected(($filters['module'] ?? '') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[220px] flex-1">
                <label class="accountant-label">Nhân viên</label>
                <select name="employee_id" class="accountant-field">
                    <option value="">Tất cả</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" @selected(($filters['employee_id'] ?? '') == $emp->id)>
                            {{ $emp->full_name }} ({{ $emp->employee_code }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[180px] flex-1">
                <label class="accountant-label">Tìm kiếm</label>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Trường, giá trị, người sửa..." class="accountant-field">
            </div>
            <div class="min-w-[140px]">
                <label class="accountant-label">Từ ngày</label>
                <input type="date" name="from_date" value="{{ $filters['from_date'] ?? '' }}" class="accountant-field">
            </div>
            <div class="min-w-[140px]">
                <label class="accountant-label">Đến ngày</label>
                <input type="date" name="to_date" value="{{ $filters['to_date'] ?? '' }}" class="accountant-field">
            </div>
            <button type="submit" class="accountant-btn-primary">Lọc</button>
            @if($hasFilters)
                <a href="{{ route('accountant.change-logs.index') }}" class="accountant-btn-secondary">Xóa lọc</a>
            @endif
        </form>

        <div class="accountant-card overflow-hidden">
            <div class="border-b border-slate-100 px-5 py-4">
                <h3 class="text-sm font-bold text-slate-800">Nhật ký thay đổi</h3>
                <p class="text-xs text-slate-500">{{ $logs->total() }} bản ghi</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[1100px] text-sm">
                    <thead>
                        <tr class="bg-slate-50 text-left text-xs font-bold uppercase text-slate-500">
                            <th class="px-4 py-3">Thời gian</th>
                            <th class="px-4 py-3">Module</th>
                            <th class="px-4 py-3">Người sửa</th>
                            <th class="px-4 py-3">Nhân viên</th>
                            <th class="px-4 py-3">Trường / Thao tác</th>
                            <th class="px-4 py-3">Giá trị cũ</th>
                            <th class="px-4 py-3">Giá trị mới</th>
                            <th class="px-4 py-3">Ghi chú</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($logs as $log)
                            <tr class="hover:bg-slate-50/60">
                                <td class="px-4 py-3 whitespace-nowrap text-xs text-slate-500">
                                    {{ $log->created_at?->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="accountant-badge bg-slate-100 text-slate-700">{{ $log->moduleLabel() }}</span>
                                    <span class="mt-1 block text-[10px] text-slate-400">{{ $log->actionLabel() }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-slate-800">{{ $log->user_name }}</p>
                                    <p class="text-xs text-slate-500">{{ $log->user_role ?? '—' }}</p>
                                </td>
                                <td class="px-4 py-3">
                                    @if($log->employee)
                                        <p class="font-medium text-slate-800">{{ $log->employee->full_name }}</p>
                                        <p class="text-xs text-slate-500">{{ $log->employee->employee_code }}</p>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-medium text-slate-700">{{ $log->field_label }}</td>
                                <td class="px-4 py-3 text-slate-500">
                                    @if($log->old_value)
                                        <span class="line-through">{{ $log->old_value }}</span>
                                    @else
                                        <span class="text-slate-300">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-semibold text-slate-800">{{ $log->new_value ?? '—' }}</td>
                                <td class="px-4 py-3 text-xs text-slate-500 max-w-[200px] truncate" title="{{ $log->note }}">{{ $log->note ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-14 text-center text-slate-500">
                                    Chưa có lịch sử thay đổi nào. Các thao tác sửa BH, thuế, lương, tạm ứng, hợp đồng sẽ được ghi lại tại đây.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($logs->hasPages())
                <div class="border-t px-4 py-3">{{ $logs->links() }}</div>
            @endif
        </div>
    </div>
</x-accountant-layout>
