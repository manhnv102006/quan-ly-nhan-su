@php
    $currentDepartment = $employee->department?->department_name ?? 'Chưa gán phòng ban';
@endphp

<div class="space-y-6">
    <div class="rounded-2xl border border-violet-100 bg-gradient-to-br from-violet-50 to-indigo-50/80 p-5">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-violet-500">Phòng ban hiện tại</p>
                <p class="mt-2 text-xl font-bold text-slate-800">{{ $currentDepartment }}</p>
                @if ($employee->department?->department_code)
                    <p class="mt-1 text-sm text-slate-500">Mã phòng: {{ $employee->department->department_code }}</p>
                @endif
            </div>
            <div class="rounded-2xl border border-white/80 bg-white/70 px-4 py-3 text-center shadow-sm">
                <p class="text-2xl font-extrabold text-violet-700">{{ $transferHistory->count() }}</p>
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Lần điều chuyển</p>
            </div>
        </div>
    </div>

    @if ($transferHistory->isEmpty())
        <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50/80 px-6 py-12 text-center">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-100 text-2xl">↔</div>
            <p class="mt-4 text-base font-semibold text-slate-700">Chưa có lịch sử điều chuyển</p>
            <p class="mt-1 text-sm text-slate-500">Nhân viên chưa từng được chuyển phòng ban trong hệ thống.</p>
        </div>
    @else
        <ol class="relative space-y-0">
            @foreach ($transferHistory as $index => $transfer)
                @php
                    $isLatest = $index === 0;
                    $isLast = $index === $transferHistory->count() - 1;
                @endphp
                <li class="relative flex gap-4 pb-8 {{ $isLast ? 'pb-0' : '' }}">
                    @unless ($isLast)
                        <span class="absolute left-[15px] top-8 h-[calc(100%-1rem)] w-0.5 bg-gradient-to-b from-violet-200 to-slate-200" aria-hidden="true"></span>
                    @endunless

                    <div class="relative z-10 flex h-8 w-8 shrink-0 items-center justify-center rounded-full border-2 border-white shadow-sm {{ $isLatest ? 'bg-violet-600 text-white' : 'bg-white text-violet-600 ring-2 ring-violet-100' }}">
                        <span class="text-xs font-bold">{{ $transferHistory->count() - $index }}</span>
                    </div>

                    <article class="min-w-0 flex-1 rounded-2xl border {{ $isLatest ? 'border-violet-200 bg-violet-50/40 shadow-sm shadow-violet-100/50' : 'border-slate-100 bg-white' }} p-4 sm:p-5">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                @if ($isLatest)
                                    <span class="inline-flex rounded-full bg-violet-600 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wide text-white">
                                        Gần nhất
                                    </span>
                                @endif
                                <p class="mt-2 text-xs font-bold uppercase tracking-[0.18em] text-slate-400">
                                    Ngày hiệu lực · {{ $transfer->effective_date?->format('d/m/Y') ?? '—' }}
                                </p>
                            </div>
                            <p class="text-xs text-slate-400">
                                Ghi nhận {{ $transfer->created_at?->format('d/m/Y H:i') ?? '—' }}
                            </p>
                        </div>

                        <div class="mt-4 flex flex-wrap items-center gap-2 sm:gap-3">
                            <div class="min-w-0 flex-1 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                                <p class="text-[10px] font-bold uppercase tracking-wide text-slate-400">Từ</p>
                                <p class="mt-1 truncate text-sm font-semibold text-slate-700">{{ $transfer->fromDepartmentName() }}</p>
                            </div>

                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-violet-100 text-violet-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                </svg>
                            </div>

                            <div class="min-w-0 flex-1 rounded-xl border border-violet-200 bg-violet-50 px-4 py-3">
                                <p class="text-[10px] font-bold uppercase tracking-wide text-violet-500">Đến</p>
                                <p class="mt-1 truncate text-sm font-bold text-violet-800">{{ $transfer->toDepartmentName() }}</p>
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-slate-600">
                            <span class="inline-flex items-center gap-1.5">
                                <span class="text-slate-400">👤</span>
                                <span><span class="font-medium text-slate-500">Người thực hiện:</span> {{ $transfer->performerDisplayName() }}</span>
                            </span>
                        </div>

                        @if ($transfer->note)
                            <div class="mt-4 rounded-xl border border-amber-100 bg-amber-50/70 px-4 py-3">
                                <p class="text-[10px] font-bold uppercase tracking-wide text-amber-600">Lý do</p>
                                <p class="mt-1 text-sm leading-relaxed text-amber-900/90">{{ $transfer->note }}</p>
                            </div>
                        @endif
                    </article>
                </li>
            @endforeach
        </ol>
    @endif
</div>
