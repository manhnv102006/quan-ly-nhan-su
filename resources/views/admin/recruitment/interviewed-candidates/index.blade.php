<x-admin-layout title="Ung vien da phong van">
    @php
        $statusLabels = [
            'new' => 'Moi',
            'interview' => 'Cho xu ly',
            'passed' => 'Dat',
            'failed' => 'Khong dat',
        ];

        $statusClasses = [
            'new' => 'bg-sky-100 text-sky-700 ring-sky-200',
            'interview' => 'bg-amber-100 text-amber-700 ring-amber-200',
            'passed' => 'bg-emerald-100 text-emerald-700 ring-emerald-200',
            'failed' => 'bg-rose-100 text-rose-700 ring-rose-200',
        ];

        $interviewStatusLabels = [
            'scheduled' => 'Da len lich',
            'completed' => 'Da phong van',
            'cancelled' => 'Da huy',
            'no_show' => 'Khong den',
        ];

        $interviewResultLabels = [
            'pending' => 'Cho ket qua',
            'passed' => 'Dat',
            'failed' => 'Khong dat',
        ];
    @endphp

    @include('admin.recruitment.partials.ui-contrast')

    <div class="recruitment-ui max-w-full overflow-hidden space-y-6">
        <section class="recruitment-hero rounded-[2rem] border border-white/80 bg-white/90 p-5 shadow-sm shadow-slate-200/60 sm:p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
                        <a href="{{ route('admin.recruitment') }}" class="transition hover:text-cyan-600">Tuyen dung</a>
                        <span>/</span>
                        <span class="font-semibold text-slate-700">Ung vien da phong van</span>
                    </div>
                    <h1 class="mt-3 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">Ung vien da phong van</h1>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                        Danh sach rieng cho cac ung vien da co lich phong van. Cac buoc xu ly ket qua dat hoac khong dat se duoc them tai man hinh nay.
                    </p>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row">
                    <a href="{{ route('admin.recruitment.candidates') }}"
                       class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-cyan-200 hover:text-cyan-700">
                        Danh sach ung vien
                    </a>
                    <a href="{{ route('admin.recruitment.interviews') }}"
                       class="recruitment-btn-primary inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-cyan-700">
                        Lich phong van
                    </a>
                </div>
            </div>
        </section>

        <section class="recruitment-stats grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
            @foreach ([
                ['label' => 'Tong da phong van', 'value' => $stats['total'] ?? 0, 'tone' => 'text-slate-900'],
                ['label' => 'Cho xu ly', 'value' => $stats['interview'] ?? 0, 'tone' => 'text-amber-700'],
                ['label' => 'Dat', 'value' => $stats['passed'] ?? 0, 'tone' => 'text-emerald-700'],
                ['label' => 'Khong dat', 'value' => $stats['failed'] ?? 0, 'tone' => 'text-rose-700'],
                ['label' => 'Da nhan viec', 'value' => $stats['converted'] ?? 0, 'tone' => 'text-cyan-700'],
            ] as $item)
                <div class="rounded-[1.5rem] border border-slate-100 bg-white p-5 shadow-sm">
                    <p class="text-sm font-semibold text-slate-500">{{ $item['label'] }}</p>
                    <p class="mt-3 text-3xl font-black {{ $item['tone'] }}">{{ $item['value'] }}</p>
                </div>
            @endforeach
        </section>

        <section class="recruitment-panel overflow-hidden rounded-[2rem] border border-slate-100 bg-white shadow-sm shadow-slate-200/60">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 sm:px-6">
                <div>
                    <h2 class="text-sm font-black text-slate-900">Danh sach ung vien</h2>
                    <p class="mt-1 text-xs text-slate-500">{{ $candidates->total() }} ung vien co lich phong van</p>
                </div>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse ($candidates as $candidate)
                    @php
                        $latestInterview = $candidate->interviews->first();
                        $statusClass = $statusClasses[$candidate->status] ?? 'bg-slate-100 text-slate-700 ring-slate-200';
                    @endphp

                    <article class="grid grid-cols-1 gap-4 p-5 transition hover:bg-slate-50/70 lg:grid-cols-[minmax(0,1.2fr)_minmax(260px,.8fr)_auto] lg:items-center sm:p-6">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1 {{ $statusClass }}">
                                    {{ $statusLabels[$candidate->status] ?? $candidate->status }}
                                </span>
                                @if ($latestInterview)
                                    <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700 ring-1 ring-slate-200">
                                        {{ $interviewResultLabels[$latestInterview->result] ?? $latestInterview->result }}
                                    </span>
                                @endif
                            </div>

                            <h3 class="mt-3 break-words text-lg font-black text-slate-900">{{ $candidate->full_name }}</h3>
                            <p class="mt-1 break-words text-sm text-slate-500">{{ $candidate->email }} · {{ $candidate->phone }}</p>
                            <p class="mt-2 break-words text-sm font-semibold text-slate-700">
                                {{ $candidate->jobPost?->title ?? 'Chua gan tin tuyen dung' }}
                            </p>
                        </div>

                        <div class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2 lg:grid-cols-1">
                            <div class="rounded-2xl bg-slate-50 px-4 py-3">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Phong van gan nhat</p>
                                <p class="mt-1 font-semibold text-slate-800">{{ $latestInterview?->interview_date?->format('d/m/Y H:i') ?? '-' }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 px-4 py-3">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Nguoi phong van</p>
                                <p class="mt-1 break-words font-semibold text-slate-800">{{ $latestInterview?->interviewer?->full_name ?? 'Chua gan' }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 px-4 py-3">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Trang thai lich</p>
                                <p class="mt-1 font-semibold text-slate-800">{{ $interviewStatusLabels[$latestInterview?->status] ?? ($latestInterview?->status ?? '-') }}</p>
                            </div>
                        </div>

                        <div class="flex flex-col gap-2 sm:flex-row lg:flex-col">
                            <a href="{{ route('admin.recruitment.candidates.show', $candidate) }}"
                               class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-cyan-200 hover:text-cyan-700">
                                Xem ho so
                            </a>
                        </div>
                    </article>
                @empty
                    <div class="px-5 py-14 text-center sm:px-6">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-slate-100 text-slate-400">
                            <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M4 11h16M7 21h10a3 3 0 0 0 3-3V7a3 3 0 0 0-3-3H7a3 3 0 0 0-3 3v11a3 3 0 0 0 3 3Z"/>
                            </svg>
                        </div>
                        <h3 class="mt-5 text-lg font-black text-slate-900">Chua co ung vien da phong van</h3>
                        <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500">
                            Khi ung vien co lich phong van, ho se xuat hien tai day de bo phan tuyen dung theo doi rieng.
                        </p>
                    </div>
                @endforelse
            </div>
        </section>

        <div class="overflow-x-auto">
            {{ $candidates->links() }}
        </div>
    </div>
</x-admin-layout>
