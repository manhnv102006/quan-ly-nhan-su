@php
    $hasFilters = collect($filters ?? [])->filter(fn ($v) => filled($v))->isNotEmpty();
@endphp

<x-admin-layout title="Lịch sử hợp đồng">
    <div class="space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Lịch sử hợp đồng</h2>
                <p class="text-sm text-slate-500">Theo dõi ai thêm, sửa, gia hạn, chuyển loại, hủy hoặc chấm dứt hợp đồng của nhân viên nào.</p>
            </div>
            <a href="{{ route('admin.contracts.index') }}" class="admin-btn-secondary">Danh sách hợp đồng</a>
        </div>

        <div class="admin-card p-5 sm:p-6">
            <form method="GET" action="{{ route('admin.contracts.history') }}" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                <div>
                    <label for="search" class="admin-label">Tìm kiếm</label>
                    <input type="text" id="search" name="search" class="admin-field"
                           value="{{ $filters['search'] ?? '' }}"
                           placeholder="Nhân viên, mã HĐ, mô tả...">
                </div>
                <div>
                    <label for="action" class="admin-label">Loại thao tác</label>
                    <select id="action" name="action" class="admin-field">
                        <option value="">— Tất cả —</option>
                        @foreach($actions as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['action'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="employee_id" class="admin-label">Nhân viên</label>
                    <select id="employee_id" name="employee_id" class="admin-field">
                        <option value="">— Tất cả —</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" @selected((string) ($filters['employee_id'] ?? '') === (string) $employee->id)>
                                {{ $employee->full_name }} ({{ $employee->employee_code }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="date_from" class="admin-label">Từ ngày</label>
                    <input type="date" id="date_from" name="date_from" class="admin-field" value="{{ $filters['date_from'] ?? '' }}">
                </div>
                <div>
                    <label for="date_to" class="admin-label">Đến ngày</label>
                    <input type="date" id="date_to" name="date_to" class="admin-field" value="{{ $filters['date_to'] ?? '' }}">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="admin-btn-violet">Lọc</button>
                    @if($hasFilters)
                        <a href="{{ route('admin.contracts.history') }}" class="admin-btn-secondary">Xóa lọc</a>
                    @endif
                </div>
            </form>
        </div>

        <div class="admin-card overflow-hidden">
            <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                <h3 class="text-sm font-bold text-slate-800">
                    {{ $histories->total() }} bản ghi
                    @if($hasFilters)
                        <span class="font-normal text-slate-500">(đã lọc)</span>
                    @endif
                </h3>
            </div>
            <div class="p-5 sm:p-6">
                @include('admin.contracts.partials.history-timeline', ['histories' => $histories])
            </div>
            @if($histories->hasPages())
                <div class="border-t border-slate-100 px-5 py-4">
                    {{ $histories->links() }}
                </div>
            @endif
        </div>
    </div>
</x-admin-layout>
