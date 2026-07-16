@php
    $formatMoney = fn ($n) => number_format((float) $n, 0, ',', '.') . '₫';
    $totalOutstanding = $departments->sum('outstanding_amount');
    $totalPending = $departments->sum('pending_count');
@endphp

<x-accountant-layout title="Tạm ứng lương" subtitle="Phòng ban → Nhân viên → Yêu cầu ứng lương">
    @include('accountant.advances.partials.sub-nav', ['active' => 'requests'])
    <div class="accountant-page">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Tạm ứng lương</h2>
                <p class="text-sm text-slate-500">Duyệt yêu cầu ứng lương theo phòng ban và nhân viên.</p>
            </div>
            <a href="{{ route('accountant.advances.create') }}" class="accountant-btn-primary">+ Tạo yêu cầu</a>
        </div>

        @if(session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>
        @endif

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            @include('accountant.partials.stat-card', ['label' => 'Phòng ban', 'value' => $departments->count()])
            @include('accountant.partials.stat-card', ['label' => 'Chờ duyệt', 'value' => $stats['pending'], 'tone' => 'text-amber-600'])
            @include('accountant.partials.stat-card', ['label' => 'Yêu cầu chờ (PB)', 'value' => $totalPending, 'tone' => 'text-orange-600'])
            @include('accountant.partials.stat-card', ['label' => 'Dư cần trừ', 'value' => $formatMoney($stats['total_outstanding']), 'tone' => 'text-rose-600'])
        </div>

        <div class="accountant-card overflow-hidden">
            <div class="border-b border-cyan-100/80 px-5 py-4">
                <h3 class="text-sm font-bold text-slate-800">Chọn phòng ban</h3>
                <p class="text-xs text-slate-500">{{ $departments->count() }} phòng ban · Dư cần trừ toàn công ty: {{ $formatMoney($totalOutstanding) }}</p>
            </div>

            <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 xl:grid-cols-3">
                @forelse($departments as $department)
                    <a href="{{ route('accountant.advances.index', ['department_id' => $department->id]) }}"
                       class="group rounded-2xl border border-cyan-100/80 bg-gradient-to-br from-cyan-50/40 to-sky-50/30 p-5 transition hover:-translate-y-0.5 hover:border-cyan-200 hover:shadow-lg hover:shadow-cyan-100/60">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h4 class="text-base font-bold text-slate-800 group-hover:text-cyan-800">
                                    {{ $department->department_name }}
                                </h4>
                                <p class="mt-1 text-xs text-slate-500">{{ $department->department_code }}</p>
                            </div>
                            <span class="rounded-full bg-white px-2.5 py-1 text-[10px] font-bold text-cyan-700 shadow-sm">→</span>
                        </div>
                        <div class="mt-4 grid grid-cols-3 gap-2 text-center">
                            <div class="rounded-xl bg-white/80 px-2 py-2">
                                <p class="text-lg font-bold text-slate-800">{{ $department->employees_count }}</p>
                                <p class="text-[10px] font-medium text-slate-500">Nhân viên</p>
                            </div>
                            <div class="rounded-xl bg-white/80 px-2 py-2">
                                <p class="text-lg font-bold {{ $department->pending_count > 0 ? 'text-amber-600' : 'text-slate-600' }}">
                                    {{ $department->pending_count }}
                                </p>
                                <p class="text-[10px] font-medium text-slate-500">Chờ duyệt</p>
                            </div>
                            <div class="rounded-xl bg-white/80 px-2 py-2">
                                <p class="text-lg font-bold {{ $department->outstanding_amount > 0 ? 'text-rose-600' : 'text-slate-600' }}">
                                    {{ $department->advances_count }}
                                </p>
                                <p class="text-[10px] font-medium text-slate-500">Yêu cầu</p>
                            </div>
                        </div>
                        @if($department->outstanding_amount > 0)
                            <p class="mt-3 text-center text-xs font-semibold text-rose-700">
                                Dư cần trừ: {{ $formatMoney($department->outstanding_amount) }}
                            </p>
                        @endif
                    </a>
                @empty
                    <div class="col-span-full py-12 text-center text-sm text-slate-500">Chưa có phòng ban nào.</div>
                @endforelse
            </div>
        </div>
    </div>
</x-accountant-layout>
