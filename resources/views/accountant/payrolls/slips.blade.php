@php
    $formatMoney = fn ($n) => number_format((float) $n, 0, ',', '.') . '₫';
@endphp

<x-accountant-layout title="Bảng lương" subtitle="Chọn phòng ban để xem phiếu lương">
    @include('accountant.payrolls.partials.sub-nav', ['active' => 'slips'])

    <div class="accountant-page">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Bảng lương theo phòng ban</h2>
                <p class="text-sm text-slate-500">Chọn phòng ban → xem bảng lương nhân viên trong phòng</p>
            </div>
            <a href="{{ route('accountant.payroll-periods.index') }}" class="accountant-btn-primary">Quản lý kỳ lương</a>
        </div>

        <form method="GET" class="accountant-card flex flex-wrap items-end gap-4 p-5">
            <div class="min-w-[220px]">
                <label class="accountant-label">Kỳ lương</label>
                <select name="period_id" class="accountant-field" onchange="this.form.submit()">
                    <option value="">Tất cả kỳ</option>
                    @foreach($periods as $period)
                        <option value="{{ $period->id }}" @selected(request('period_id') == $period->id)>
                            {{ $period->name ?? ('Tháng '.$period->month.'/'.$period->year) }}
                        </option>
                    @endforeach
                </select>
            </div>
            @if(request('period_id'))
                <a href="{{ route('accountant.payrolls.slips') }}" class="accountant-btn-secondary">Xóa lọc kỳ</a>
            @endif
        </form>

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-3">
            @include('accountant.partials.stat-card', ['label' => 'Phòng ban', 'value' => $departments->count()])
            @include('accountant.partials.stat-card', ['label' => 'Phiếu lương', 'value' => $totals['payrolls'], 'tone' => 'text-sky-600'])
            @include('accountant.partials.stat-card', ['label' => 'Tổng thực lĩnh', 'value' => $formatMoney($totals['salary']), 'tone' => 'text-amber-700'])
        </div>

        <div class="accountant-card overflow-hidden">
            <div class="border-b border-amber-100/80 px-5 py-4">
                <h3 class="text-sm font-bold text-slate-800">Chọn phòng ban</h3>
                <p class="text-xs text-slate-500">{{ $departments->count() }} phòng ban</p>
            </div>

            <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 xl:grid-cols-3">
                @forelse($departments as $department)
                    <a href="{{ route('accountant.payrolls.slips', array_filter(['department_id' => $department->id, 'period_id' => request('period_id')])) }}"
                       class="group rounded-2xl border border-amber-100/80 bg-gradient-to-br from-amber-50/40 to-sky-50/30 p-5 transition hover:-translate-y-0.5 hover:border-amber-200 hover:shadow-lg hover:shadow-amber-100/60">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h4 class="text-base font-bold text-slate-800 group-hover:text-amber-800">
                                    {{ $department->department_name }}
                                </h4>
                                <p class="mt-1 text-xs text-slate-500">{{ $department->department_code }}</p>
                            </div>
                            <span class="rounded-full bg-white px-2.5 py-1 text-[10px] font-bold text-amber-700 shadow-sm">→</span>
                        </div>
                        <div class="mt-4 grid grid-cols-3 gap-2 text-center">
                            <div class="rounded-xl bg-white/80 px-2 py-2">
                                <p class="text-lg font-bold text-slate-800">{{ $department->employees_count }}</p>
                                <p class="text-[10px] font-medium text-slate-500">Nhân viên</p>
                            </div>
                            <div class="rounded-xl bg-white/80 px-2 py-2">
                                <p class="text-lg font-bold text-sky-600">{{ $department->payrolls_count }}</p>
                                <p class="text-[10px] font-medium text-slate-500">Phiếu lương</p>
                            </div>
                            <div class="rounded-xl bg-white/80 px-2 py-2">
                                <p class="text-sm font-bold leading-tight text-amber-700">{{ $formatMoney($department->total_salary) }}</p>
                                <p class="text-[10px] font-medium text-slate-500">Thực lĩnh</p>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="col-span-full py-12 text-center text-sm text-slate-500">Chưa có phòng ban.</div>
                @endforelse
            </div>
        </div>
    </div>
</x-accountant-layout>
