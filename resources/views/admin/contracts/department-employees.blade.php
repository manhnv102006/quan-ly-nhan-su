<x-admin-layout title="Nhân viên - {{ $department->department_name }}">
    <div class="space-y-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                @include('admin.contracts.partials.breadcrumb', ['department' => $department])
                <h2 class="mt-2 text-2xl font-bold text-slate-800">{{ $department->department_name }}</h2>
                <p class="text-sm text-slate-500">Chọn nhân viên để xem hợp đồng.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.contracts.index') }}" class="admin-btn-secondary">← Phòng ban</a>
                <a href="{{ route('admin.contracts.create') }}" class="admin-btn-violet">Thêm hợp đồng</a>
            </div>
        </div>

        @include('admin.contracts.partials.stats-cards', ['stats' => $stats])

        <div class="admin-card p-5 sm:p-6">
            <form action="{{ route('admin.contracts.by-department', $department) }}" method="GET" class="flex flex-wrap items-end gap-3">
                <div class="min-w-[240px] flex-1">
                    <label for="search" class="admin-label">Tìm trong phòng ban</label>
                    <input type="text" id="search" name="search" value="{{ $search }}"
                           placeholder="Tên hoặc mã nhân viên..."
                           class="admin-field">
                </div>
                <button type="submit" class="admin-btn-primary">Tìm</button>
                @if($search)
                    <a href="{{ route('admin.contracts.by-department', $department) }}" class="admin-btn-secondary">Xóa</a>
                @endif
            </form>
        </div>

        <div class="admin-card overflow-hidden">
            <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                <h3 class="text-sm font-bold text-slate-800">Danh sách nhân viên</h3>
                <p class="text-xs text-slate-500">{{ $employees->count() }} nhân viên</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[720px]">
                    <thead>
                        <tr class="bg-slate-50">
                            <th class="px-5 py-3 text-left text-xs font-bold uppercase text-slate-500">Nhân viên</th>
                            <th class="px-5 py-3 text-left text-xs font-bold uppercase text-slate-500">Chức vụ</th>
                            <th class="px-5 py-3 text-center text-xs font-bold uppercase text-slate-500">Số HĐ</th>
                            <th class="px-5 py-3 text-left text-xs font-bold uppercase text-slate-500">HĐ hiện tại</th>
                            <th class="px-5 py-3 text-center text-xs font-bold uppercase text-slate-500"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($employees as $employee)
                            @php
                                $activeContract = $employee->contracts->first();
                            @endphp
                            <tr class="transition hover:bg-slate-50/60">
                                <td class="px-5 py-4">
                                    <p class="text-sm font-semibold text-slate-800">{{ $employee->full_name }}</p>
                                    <p class="text-xs text-slate-500">{{ $employee->employee_code }}</p>
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-700">{{ $employee->position?->position_name ?? '—' }}</td>
                                <td class="px-5 py-4 text-center text-sm font-semibold text-slate-800">{{ $employee->contracts_count }}</td>
                                <td class="px-5 py-4">
                                    @if($activeContract)
                                        <p class="text-sm font-medium text-violet-600">{{ $activeContract->contract_code }}</p>
                                        <p class="text-xs text-slate-500">{{ $activeContract->contractType?->contract_name ?? '—' }}</p>
                                    @else
                                        <span class="text-sm text-slate-400">Chưa có HĐ hiệu lực</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <a href="{{ route('admin.contracts.by-employee', $employee) }}"
                                       class="inline-flex items-center gap-1 rounded-lg bg-violet-50 px-3 py-1.5 text-xs font-semibold text-violet-700 transition hover:bg-violet-100">
                                        Xem HĐ
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-14 text-center text-sm text-slate-500">
                                    @if($search)
                                        Không tìm thấy nhân viên trong phòng ban này.
                                    @else
                                        Phòng ban chưa có nhân viên.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-admin-layout>
