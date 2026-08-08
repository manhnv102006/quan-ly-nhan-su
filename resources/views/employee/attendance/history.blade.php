@php
    $layout = \App\Support\SelfServiceLayout::component();
    $layoutParams = [
        'title' => 'Lịch sử check-in / check-out',
        'subtitle' => 'Xem lại toàn bộ lần chấm công vào và ra theo từng buổi.',
    ];
@endphp

<x-dynamic-component :component="$layout" :attributes="new \Illuminate\View\ComponentAttributeBag($layoutParams)">

    <div class="space-y-6">

        <div class="flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('attendance.index') }}"
               class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800 transition">
                <span>←</span> Chấm công hôm nay
            </a>
        </div>

        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-6">
            <h1 class="text-xl font-bold text-slate-800">Lịch sử check-in / check-out</h1>
            <p class="text-sm text-slate-500 mt-1">
                {{ $employee->full_name }} · {{ $employee->employee_code }}
            </p>
        </div>

        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm p-5 sm:p-6">
            <form action="{{ route('attendance.history') }}" method="GET" class="flex flex-wrap items-end gap-3">
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Tháng</label>
                    <select name="month" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20">
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" @selected($filters['month'] == $m)>Tháng {{ str_pad($m, 2, '0', STR_PAD_LEFT) }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Năm</label>
                    <select name="year" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20">
                        @foreach ([now()->year - 1, now()->year, now()->year + 1] as $y)
                            <option value="{{ $y }}" @selected($filters['year'] == $y)>{{ $y }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Trạng thái</label>
                    <select name="status" class="rounded-xl border border-slate-200 px-3 py-2.5 text-sm outline-none focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20">
                        <option value="">Tất cả</option>
                        <option value="present" @selected($filters['status'] === 'present')>Đi làm</option>
                        <option value="late" @selected($filters['status'] === 'late')>Đi muộn</option>
                        <option value="absent" @selected($filters['status'] === 'absent')>Vắng mặt</option>
                        <option value="leave" @selected($filters['status'] === 'leave')>Nghỉ phép</option>
                    </select>
                </div>
                <button type="submit" class="rounded-xl bg-sky-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-sky-700">Xem</button>
            </form>
        </div>

        @include('shared.attendance.summary-cards', ['summary' => $summary])

        <div class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4 sm:px-6">
                <h3 class="text-sm font-bold text-slate-800">
                    Chi tiết tháng {{ str_pad($filters['month'], 2, '0', STR_PAD_LEFT) }}/{{ $filters['year'] }}
                </h3>
                <p class="text-xs text-slate-500">{{ $sessionRows->count() }} buổi · {{ $attendances->count() }} ngày</p>
            </div>

            @include('shared.attendance.session-history-table', [
                'sessionRows' => $sessionRows,
                'summary' => $summary,
                'showActions' => false,
                'emptyMessage' => 'Chưa có dữ liệu chấm công trong tháng này.',
            ])
        </div>
    </div>

</x-dynamic-component>
