<x-admin-layout title="Quản lý hợp đồng">
    <div class="space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Quản lý hợp đồng</h2>
                <p class="text-sm text-slate-500">Chọn phòng ban → nhân viên → xem hợp đồng.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.contracts.history') }}" class="admin-btn-secondary">Lịch sử thao tác</a>
                <a href="{{ route('admin.contracts.trashed') }}" class="admin-btn-secondary">
                    Thùng rác
                    @if(($stats['trashed'] ?? 0) > 0)
                        <span class="rounded-full bg-rose-100 px-2 py-0.5 text-[10px] font-bold text-rose-600">{{ $stats['trashed'] }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.contracts.create') }}" class="admin-btn-violet">Thêm hợp đồng</a>
            </div>
        </div>

        @include('admin.contracts.partials.stats-cards', ['stats' => $stats])

        <div class="admin-card p-5 sm:p-6">
            <form action="{{ route('admin.contracts.index') }}" method="GET" class="flex flex-wrap items-end gap-3">
                <div class="min-w-[240px] flex-1">
                    <label for="search" class="admin-label">Tìm nhân viên nhanh</label>
                    <input type="text" id="search" name="search" value="{{ $search }}"
                           placeholder="Tên hoặc mã nhân viên..."
                           class="admin-field">
                </div>
                <button type="submit" class="admin-btn-primary">Tìm</button>
                @if($search)
                    <a href="{{ route('admin.contracts.index') }}" class="admin-btn-secondary">Xóa</a>
                @endif
            </form>

            @if($searchResults->isNotEmpty())
                <div class="mt-4 rounded-xl border border-violet-100 bg-violet-50/40 p-4">
                    <p class="mb-3 text-xs font-bold uppercase tracking-wide text-violet-700">Kết quả tìm kiếm</p>
                    <ul class="space-y-2">
                        @foreach($searchResults as $employee)
                            <li>
                                <a href="{{ route('admin.contracts.by-employee', $employee) }}"
                                   class="flex flex-wrap items-center justify-between gap-2 rounded-lg bg-white px-4 py-3 text-sm transition hover:bg-violet-50">
                                    <span>
                                        <span class="font-semibold text-slate-800">{{ $employee->full_name }}</span>
                                        <span class="text-slate-500">· {{ $employee->employee_code }}</span>
                                    </span>
                                    <span class="text-xs text-slate-500">
                                        {{ $employee->department?->department_name ?? '—' }}
                                        · {{ $employee->contracts_count }} HĐ
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @elseif($search)
                <p class="mt-3 text-sm text-slate-500">Không tìm thấy nhân viên phù hợp.</p>
            @endif
        </div>

        <div class="admin-card overflow-hidden">
            <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                <h3 class="text-sm font-bold text-slate-800">Chọn phòng ban</h3>
                <p class="text-xs text-slate-500">{{ $departments->count() }} phòng ban</p>
            </div>

            <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 sm:p-6 xl:grid-cols-3">
                @forelse($departments as $department)
                    <a href="{{ route('admin.contracts.by-department', $department) }}"
                       class="group rounded-2xl border border-slate-100 bg-slate-50/40 p-5 transition hover:border-violet-200 hover:bg-violet-50/30">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h4 class="text-base font-bold text-slate-800 group-hover:text-violet-700">
                                    {{ $department->department_name }}
                                </h4>
                                <p class="mt-1 text-xs text-slate-500">{{ $department->department_code }}</p>
                            </div>
                            <span class="rounded-full bg-white px-2.5 py-1 text-[10px] font-bold text-violet-600 shadow-sm">
                                →
                            </span>
                        </div>
                        <div class="mt-4 grid grid-cols-3 gap-2 text-center">
                            <div class="rounded-xl bg-white px-2 py-2">
                                <p class="text-lg font-bold text-slate-800">{{ $department->employees_count }}</p>
                                <p class="text-[10px] font-medium text-slate-500">Nhân viên</p>
                            </div>
                            <div class="rounded-xl bg-white px-2 py-2">
                                <p class="text-lg font-bold text-emerald-600">{{ $department->active_contracts_count }}</p>
                                <p class="text-[10px] font-medium text-slate-500">Đang hiệu lực</p>
                            </div>
                            <div class="rounded-xl bg-white px-2 py-2">
                                <p class="text-lg font-bold text-slate-700">{{ $department->contracts_count }}</p>
                                <p class="text-[10px] font-medium text-slate-500">Tổng HĐ</p>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full py-12 text-center text-sm text-slate-500">
                        Chưa có phòng ban nào.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-admin-layout>
