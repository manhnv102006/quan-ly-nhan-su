@php
    $formatMoney = fn ($n) => number_format((float) $n, 0, ',', '.') . '₫';
    $hasFilters = collect($filters ?? [])->filter(fn ($v) => filled($v))->isNotEmpty();
@endphp

@include('accountant.advances.partials.sub-nav', ['active' => 'requests'])

<x-accountant-layout title="Tạm ứng - {{ $employee->full_name }}" subtitle="Yêu cầu ứng lương của nhân viên">
    <div class="accountant-page">
        @if(session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>
        @endif

        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <nav class="mb-2 flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-500">
                    <a href="{{ route('accountant.advances.index') }}" class="text-cyan-700 hover:underline">Phòng ban</a>
                    @if($department)
                        <span>/</span>
                        <a href="{{ route('accountant.advances.index', ['department_id' => $department->id]) }}" class="text-cyan-700 hover:underline">{{ $department->department_name }}</a>
                    @endif
                    <span>/</span>
                    <span class="text-slate-700">{{ $employee->full_name }}</span>
                </nav>
                <h2 class="text-2xl font-bold text-slate-900">{{ $employee->full_name }}</h2>
                <p class="text-sm text-slate-500">
                    {{ $employee->employee_code }}
                    · {{ $employee->position?->position_name ?? '—' }}
                </p>
            </div>
            @if($department)
                <a href="{{ route('accountant.advances.index', ['department_id' => $department->id]) }}" class="accountant-btn-secondary">← Nhân viên</a>
            @endif
        </div>

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-3">
            @include('accountant.partials.stat-card', ['label' => 'Tổng yêu cầu', 'value' => $summary['total']])
            @include('accountant.partials.stat-card', ['label' => 'Chờ duyệt', 'value' => $summary['pending'], 'tone' => 'text-amber-600'])
            @include('accountant.partials.stat-card', ['label' => 'Dư cần trừ', 'value' => $formatMoney($summary['outstanding']), 'tone' => 'text-rose-600'])
        </div>

        <form method="GET" action="{{ route('accountant.advances.index') }}" class="accountant-card flex flex-wrap items-end gap-4 p-5">
            <input type="hidden" name="employee_id" value="{{ $employee->id }}">
            <div class="min-w-[180px]">
                <label class="accountant-label">Trạng thái</label>
                <select name="status" class="accountant-field">
                    <option value="">Tất cả</option>
                    @foreach(\App\Models\SalaryAdvance::STATUS_LABELS as $val => $label)
                        <option value="{{ $val }}" @selected(($filters['status'] ?? '') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="accountant-btn-primary">Lọc</button>
            @if($hasFilters)
                <a href="{{ route('accountant.advances.index', ['employee_id' => $employee->id]) }}" class="accountant-btn-secondary">Xóa lọc</a>
            @endif
        </form>

        <div class="accountant-card overflow-hidden">
            <div class="border-b border-cyan-100/80 px-5 py-4">
                <h3 class="text-sm font-bold text-slate-800">Danh sách yêu cầu tạm ứng</h3>
                <p class="text-xs text-slate-500">{{ $advances->total() }} yêu cầu</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-sm">
                    <thead>
                        <tr class="bg-cyan-50/80 text-left text-xs font-bold uppercase text-slate-500">
                            <th class="px-4 py-3">Mã</th>
                            <th class="px-4 py-3">Ngày</th>
                            <th class="px-4 py-3 text-right">Số tiền</th>
                            <th class="px-4 py-3 text-right">Còn lại</th>
                            <th class="px-4 py-3">TT</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($advances as $advance)
                            <tr class="hover:bg-cyan-50/30">
                                <td class="px-4 py-3 font-mono text-xs font-bold text-cyan-800">
                                    <a href="{{ route('accountant.advances.show', $advance) }}" class="hover:underline">{{ $advance->advance_code }}</a>
                                </td>
                                <td class="px-4 py-3">{{ $advance->request_date?->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-right font-bold">{{ $formatMoney($advance->amount) }}</td>
                                <td class="px-4 py-3 text-right text-rose-700">{{ $formatMoney($advance->remainingBalance()) }}</td>
                                <td class="px-4 py-3">
                                    <span class="accountant-badge {{ $advance->statusBadgeClass() }}">{{ $advance->statusLabel() }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap justify-end gap-1">
                                        @if($advance->canBeApproved())
                                            <form method="POST" action="{{ route('accountant.advances.approve', $advance) }}">
                                                @csrf
                                                <button type="submit" class="accountant-btn-secondary !px-2 !py-1 !text-xs text-emerald-700">Duyệt</button>
                                            </form>
                                        @endif
                                        @if($advance->canBeRejected())
                                            <button type="button" onclick="openReject({{ $advance->id }})" class="accountant-btn-secondary !px-2 !py-1 !text-xs text-rose-700">Từ chối</button>
                                        @endif
                                        <a href="{{ route('accountant.advances.show', $advance) }}" class="accountant-btn-secondary !px-2 !py-1 !text-xs">Chi tiết</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-12 text-center text-slate-500">Nhân viên chưa có yêu cầu tạm ứng.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($advances->hasPages())
                <div class="border-t px-4 py-3">{{ $advances->links() }}</div>
            @endif
        </div>
    </div>

    <div id="rejectModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-slate-900/50" onclick="closeReject()"></div>
        <div class="relative mx-auto mt-24 max-w-md rounded-2xl bg-white p-6 shadow-xl">
            <h3 class="text-lg font-bold">Từ chối tạm ứng</h3>
            <form id="rejectForm" method="POST" class="mt-4 space-y-3">
                @csrf
                <textarea name="rejection_reason" required rows="3" class="accountant-field" placeholder="Lý do từ chối..."></textarea>
                <div class="flex gap-2">
                    <button type="submit" class="accountant-btn-primary bg-rose-600 hover:bg-rose-700">Xác nhận</button>
                    <button type="button" onclick="closeReject()" class="accountant-btn-secondary">Hủy</button>
                </div>
            </form>
        </div>
    </div>

    @push('head')
    <script>
        function openReject(id) {
            document.getElementById('rejectForm').action = `{{ url('accountant/advances') }}/${id}/reject`;
            document.getElementById('rejectModal').classList.remove('hidden');
        }
        function closeReject() { document.getElementById('rejectModal').classList.add('hidden'); }
    </script>
    @endpush
</x-accountant-layout>
