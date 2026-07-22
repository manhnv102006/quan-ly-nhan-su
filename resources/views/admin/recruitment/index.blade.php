<x-admin-layout title="Tuyển dụng">
    @php
        $candidateStatus = [
            'new' => ['label' => 'Mới', 'badge' => 'bg-sky-100 text-sky-800 ring-sky-200'],
            'interview' => ['label' => 'Phỏng vấn', 'badge' => 'bg-violet-100 text-violet-800 ring-violet-200'],
            'passed' => ['label' => 'Đạt', 'badge' => 'bg-emerald-100 text-emerald-800 ring-emerald-200'],
            'failed' => ['label' => 'Không đạt', 'badge' => 'bg-rose-100 text-rose-800 ring-rose-200'],
        ];

        $pipeline = [
            ['key' => 'pending_candidates', 'label' => 'Chờ duyệt', 'color' => 'from-sky-400 to-sky-600'],
            ['key' => 'interview_candidates', 'label' => 'Phỏng vấn', 'color' => 'from-violet-400 to-violet-600'],
            ['key' => 'passed_candidates', 'label' => 'Đạt', 'color' => 'from-emerald-400 to-emerald-600'],
            ['key' => 'failed_candidates', 'label' => 'Từ chối', 'color' => 'from-rose-400 to-rose-500'],
        ];

        $totalInPipeline = max(1, ($stats['candidates'] ?? 0));

        $highlightStats = [
            [
                'label' => 'Tin đang tuyển',
                'value' => $stats['open_job_posts'] ?? 0,
                'sub' => ($stats['job_posts'] ?? 0).' tin tổng',
                'icon' => 'briefcase',
                'accent' => 'border-l-sky-500',
            ],
            [
                'label' => 'Hồ sơ ứng viên',
                'value' => $stats['candidates'] ?? 0,
                'sub' => ($stats['pending_candidates'] ?? 0).' cần xử lý',
                'icon' => 'users',
                'accent' => 'border-l-indigo-500',
            ],
            [
                'label' => 'Lịch phỏng vấn',
                'value' => $stats['interviews'] ?? 0,
                'sub' => ($stats['interview_candidates'] ?? 0).' đang PV',
                'icon' => 'calendar',
                'accent' => 'border-l-amber-500',
            ],
            [
                'label' => 'Đã chuyển NV',
                'value' => $stats['converted_candidates'] ?? 0,
                'sub' => ($stats['passed_candidates'] ?? 0).' đạt yêu cầu',
                'icon' => 'badge',
                'accent' => 'border-l-emerald-500',
            ],
        ];

        $modules = [
            [
                'title' => 'Tin tuyển dụng',
                'description' => 'Nhu cầu tuyển, hạn nộp và phòng ban phụ trách.',
                'route' => route('admin.recruitment.job-posts'),
                'cta' => 'Mở danh sách tin',
                'metric' => ($stats['open_job_posts'] ?? 0).' đang mở',
                'gradient' => 'from-sky-500/10 to-cyan-500/5',
                'iconBg' => 'bg-sky-600',
            ],
            [
                'title' => 'Ứng viên',
                'description' => 'CV, trạng thái và chuyển đổi thành nhân viên.',
                'route' => route('admin.recruitment.candidates'),
                'cta' => 'Xem hồ sơ',
                'metric' => ($stats['candidates'] ?? 0).' hồ sơ',
                'gradient' => 'from-indigo-500/10 to-violet-500/5',
                'iconBg' => 'bg-indigo-600',
            ],
            [
                'title' => 'Phỏng vấn',
                'description' => 'Lịch hẹn, điểm đánh giá và kết quả tuyển.',
                'route' => route('admin.recruitment.interviews'),
                'cta' => 'Quản lý lịch PV',
                'metric' => ($stats['interviews'] ?? 0).' buổi',
                'gradient' => 'from-amber-500/10 to-orange-500/5',
                'iconBg' => 'bg-amber-600',
            ],
        ];
    @endphp

    @include('admin.recruitment.partials.ui-contrast')

    <div class="recruitment-ui recruitment-dashboard max-w-full overflow-hidden space-y-6">
        {{-- Hero --}}
        <section class="recruitment-dashboard-hero relative overflow-hidden rounded-[1.75rem] border border-slate-200/80 bg-gradient-to-br from-white via-slate-50 to-cyan-50/80 p-6 shadow-sm sm:p-8">
            <div class="pointer-events-none absolute -right-16 -top-16 h-56 w-56 rounded-full bg-cyan-200/40 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-20 left-1/3 h-48 w-48 rounded-full bg-indigo-200/30 blur-3xl"></div>

            <div class="relative flex flex-col gap-6 xl:flex-row xl:items-center xl:justify-between">
                <div class="min-w-0 max-w-2xl">
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-cyan-700">Trung tâm tuyển dụng</p>
                    <h2 class="mt-2 text-3xl font-black tracking-tight text-slate-900 sm:text-[2.15rem]">Bảng điều khiển tuyển dụng</h2>
                    <p class="mt-3 text-sm leading-relaxed text-slate-600">
                        Theo dõi phễu ứng viên, tin đang mở và lịch phỏng vấn sắp tới — tất cả trên một màn hình.
                    </p>
                </div>

                <div class="flex shrink-0 flex-col gap-2 sm:flex-row sm:items-center">
                    <a href="{{ route('admin.recruitment.job-posts.create') }}"
                       class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-800 shadow-sm transition hover:border-cyan-300 hover:text-cyan-800">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        Tạo tin tuyển dụng
                    </a>
                    <a href="{{ route('admin.recruitment.candidates.create') }}"
                       class="recruitment-btn-primary inline-flex items-center justify-center gap-2 rounded-xl bg-cyan-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md transition hover:bg-cyan-700">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" /></svg>
                        Thêm ứng viên
                    </a>
                    <a href="{{ route('admin.recruitment.interviews.create') }}"
                       class="inline-flex items-center justify-center gap-2 rounded-xl border border-transparent bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-slate-800">
                        Lên lịch PV
                    </a>
                </div>
            </div>
        </section>

        {{-- KPI row --}}
        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($highlightStats as $item)
                <div class="recruitment-card border-l-4 {{ $item['accent'] }} rounded-2xl bg-white p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-medium text-slate-500">{{ $item['label'] }}</p>
                            <p class="mt-2 text-3xl font-black tabular-nums text-slate-900">{{ $item['value'] }}</p>
                            <p class="mt-1 text-xs font-medium text-slate-500">{{ $item['sub'] }}</p>
                        </div>
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-100 text-slate-600">
                            @if ($item['icon'] === 'briefcase')
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.1A2.25 2.25 0 0 1 18 20.5H6a2.25 2.25 0 0 1-2.25-2.25v-4.1m16.5 0A2.25 2.25 0 0 0 18 11.9H6a2.25 2.25 0 0 0-2.25 2.25m16.5 0v-2.4A2.25 2.25 0 0 0 18 9.5H6a2.25 2.25 0 0 0-2.25 2.25v2.4M9 9.5V6.75A2.25 2.25 0 0 1 11.25 4.5h1.5A2.25 2.25 0 0 1 15 6.75V9.5" /></svg>
                            @elseif ($item['icon'] === 'users')
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.965-3.07M12 7.875a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
                            @elseif ($item['icon'] === 'calendar')
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3.75 8.25h16.5M4.5 6.75A2.25 2.25 0 0 1 6.75 4.5h10.5a2.25 2.25 0 0 1 2.25 2.25v10.5a2.25 2.25 0 0 1-2.25 2.25H6.75a2.25 2.25 0 0 1-2.25-2.25V6.75Z" /></svg>
                            @else
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M18.75 4.236c.982.143 1.954.317 2.916.52M18.75 4.236V4.5c0 2.108-.966 3.99-2.48 5.228m0 0a6.023 6.023 0 0 1-2.77.732 6.025 6.025 0 0 1-2.77-.732m5.25 0a6.023 6.023 0 0 0-2.77.732 6.025 6.025 0 0 0-2.77-.732" /></svg>
                            @endif
                        </span>
                    </div>
                </div>
            @endforeach
        </section>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
            {{-- Pipeline --}}
            <section class="recruitment-panel xl:col-span-5 rounded-2xl p-6">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Phễu ứng viên</h3>
                        <p class="mt-1 text-sm text-slate-500">Phân bổ theo giai đoạn xử lý</p>
                    </div>
                    <a href="{{ route('admin.recruitment.candidates') }}" class="text-sm font-semibold text-cyan-700 hover:text-cyan-900">Chi tiết</a>
                </div>

                <div class="mt-6 flex h-3 overflow-hidden rounded-full bg-slate-100">
                    @foreach ($pipeline as $stage)
                        @php $count = $stats[$stage['key']] ?? 0; @endphp
                        @if ($count > 0)
                            <div class="h-full bg-gradient-to-r {{ $stage['color'] }}"
                                 style="width: {{ max(8, ($count / $totalInPipeline) * 100) }}%"
                                 title="{{ $stage['label'] }}: {{ $count }}"></div>
                        @endif
                    @endforeach
                </div>

                <ul class="mt-6 space-y-3">
                    @foreach ($pipeline as $stage)
                        @php $count = $stats[$stage['key']] ?? 0; @endphp
                        <li class="flex items-center justify-between rounded-xl bg-slate-50 px-4 py-3">
                            <div class="flex items-center gap-3">
                                <span class="h-2.5 w-2.5 rounded-full bg-gradient-to-r {{ $stage['color'] }}"></span>
                                <span class="text-sm font-medium text-slate-700">{{ $stage['label'] }}</span>
                            </div>
                            <span class="text-sm font-bold tabular-nums text-slate-900">{{ $count }}</span>
                        </li>
                    @endforeach
                </ul>
            </section>

            {{-- Open jobs --}}
            <section class="recruitment-panel xl:col-span-7 rounded-2xl p-6">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Tin đang mở</h3>
                        <p class="mt-1 text-sm text-slate-500">Vị trí đang nhận hồ sơ</p>
                    </div>
                    <a href="{{ route('admin.recruitment.job-posts') }}" class="text-sm font-semibold text-cyan-700 hover:text-cyan-900">Tất cả tin</a>
                </div>

                @if ($openJobPosts->isEmpty())
                    <div class="mt-8 rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-10 text-center">
                        <p class="text-sm text-slate-500">Chưa có tin tuyển dụng đang mở.</p>
                        <a href="{{ route('admin.recruitment.job-posts.create') }}" class="mt-3 inline-block text-sm font-semibold text-cyan-700">Tạo tin mới</a>
                    </div>
                @else
                    <ul class="mt-5 divide-y divide-slate-100">
                        @foreach ($openJobPosts as $post)
                            <li class="flex flex-col gap-2 py-4 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-slate-900">{{ $post->title }}</p>
                                    <p class="mt-0.5 text-xs text-slate-500">
                                        {{ $post->department?->department_name ?? '—' }}
                                        · SL: {{ $post->quantity }}
                                        @if ($post->application_deadline)
                                            · Hạn: {{ $post->application_deadline->format('d/m/Y') }}
                                        @endif
                                    </p>
                                </div>
                                <span class="inline-flex w-fit shrink-0 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800 ring-1 ring-emerald-200">Đang tuyển</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            {{-- Recent candidates --}}
            <section class="recruitment-panel rounded-2xl p-6">
                <div class="flex items-center justify-between gap-3">
                    <h3 class="text-lg font-bold text-slate-900">Ứng viên mới nhất</h3>
                    <a href="{{ route('admin.recruitment.candidates') }}" class="text-sm font-semibold text-cyan-700">Xem tất cả</a>
                </div>

                @if ($recentCandidates->isEmpty())
                    <p class="mt-6 text-sm text-slate-500">Chưa có hồ sơ ứng viên.</p>
                @else
                    <ul class="mt-4 space-y-2">
                        @foreach ($recentCandidates as $candidate)
                            @php $st = $candidateStatus[$candidate->status] ?? ['label' => $candidate->status, 'badge' => 'bg-slate-100 text-slate-700 ring-slate-200']; @endphp
                            <li>
                                <a href="{{ route('admin.recruitment.candidates.show', $candidate) }}"
                                   class="flex items-center justify-between gap-3 rounded-xl px-3 py-3 transition hover:bg-slate-50">
                                    <div class="min-w-0">
                                        <p class="truncate font-medium text-slate-900">{{ $candidate->full_name }}</p>
                                        <p class="truncate text-xs text-slate-500">{{ $candidate->jobPost?->title ?? '—' }} · {{ $candidate->created_at?->format('d/m/Y') }}</p>
                                    </div>
                                    <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 {{ $st['badge'] }}">{{ $st['label'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>

            {{-- Upcoming interviews --}}
            <section class="recruitment-panel rounded-2xl p-6">
                <div class="flex items-center justify-between gap-3">
                    <h3 class="text-lg font-bold text-slate-900">Phỏng vấn sắp tới</h3>
                    <a href="{{ route('admin.recruitment.interviews') }}" class="text-sm font-semibold text-cyan-700">Lịch PV</a>
                </div>

                @if ($upcomingInterviews->isEmpty())
                    <p class="mt-6 text-sm text-slate-500">Không có lịch phỏng vấn trong thời gian tới.</p>
                    <a href="{{ route('admin.recruitment.interviews.create') }}" class="mt-2 inline-block text-sm font-semibold text-cyan-700">Tạo lịch mới</a>
                @else
                    <ul class="mt-4 space-y-2">
                        @foreach ($upcomingInterviews as $interview)
                            <li class="flex items-start gap-3 rounded-xl border border-slate-100 bg-slate-50/80 px-4 py-3">
                                <div class="mt-0.5 flex h-10 w-10 shrink-0 flex-col items-center justify-center rounded-lg bg-white text-center shadow-sm ring-1 ring-slate-200">
                                    <span class="text-[10px] font-bold uppercase text-cyan-700">{{ $interview->interview_date?->format('M') }}</span>
                                    <span class="text-sm font-black leading-none text-slate-900">{{ $interview->interview_date?->format('d') }}</span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="font-medium text-slate-900">{{ $interview->candidate?->full_name ?? '—' }}</p>
                                    <p class="mt-0.5 text-xs text-slate-500">
                                        {{ $interview->interview_date?->format('H:i, d/m/Y') }}
                                        @if ($interview->interviewer)
                                            · PV: {{ $interview->interviewer->full_name }}
                                        @endif
                                    </p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>

        {{-- Module shortcuts --}}
        <section class="grid grid-cols-1 gap-4 md:grid-cols-3">
            @foreach ($modules as $module)
                <a href="{{ $module['route'] }}"
                   class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-cyan-200 hover:shadow-md">
                    <div class="absolute inset-0 bg-gradient-to-br {{ $module['gradient'] }} opacity-0 transition group-hover:opacity-100"></div>
                    <div class="relative">
                        <div class="flex items-center justify-between">
                            <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl {{ $module['iconBg'] }} text-white shadow-sm">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                            </span>
                            <span class="text-xs font-bold text-slate-500">{{ $module['metric'] }}</span>
                        </div>
                        <h3 class="mt-4 text-lg font-bold text-slate-900">{{ $module['title'] }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-500">{{ $module['description'] }}</p>
                        <p class="mt-4 text-sm font-semibold text-cyan-700 group-hover:text-cyan-900">{{ $module['cta'] }} →</p>
                    </div>
                </a>
            @endforeach
        </section>
    </div>
</x-admin-layout>
