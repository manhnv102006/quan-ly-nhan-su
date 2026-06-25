<x-admin-layout title="Tuyển dụng">
    @php
        $cards = [
            [
                'label' => 'Tổng tin tuyển dụng',
                'value' => $stats['job_posts'] ?? 0,
                'hint' => 'Nhu cầu tuyển dụng đang được quản lý',
                'tone' => 'bg-cyan-50 text-cyan-700 ring-cyan-100',
                'icon' => 'briefcase',
            ],
            [
                'label' => 'Tổng ứng viên',
                'value' => $stats['candidates'] ?? 0,
                'hint' => 'Hồ sơ ứng viên đã tiếp nhận',
                'tone' => 'bg-sky-50 text-sky-700 ring-sky-100',
                'icon' => 'users',
            ],
            [
                'label' => 'Chờ duyệt',
                'value' => $stats['pending_candidates'] ?? 0,
                'hint' => 'Ứng viên mới cần sàng lọc',
                'tone' => 'bg-amber-50 text-amber-700 ring-amber-100',
                'icon' => 'clock',
            ],
            [
                'label' => 'Phỏng vấn',
                'value' => $stats['interview_candidates'] ?? 0,
                'hint' => 'Ứng viên đang ở vòng phỏng vấn',
                'tone' => 'bg-indigo-50 text-indigo-700 ring-indigo-100',
                'icon' => 'calendar',
            ],
            [
                'label' => 'Đã nhận',
                'value' => $stats['passed_candidates'] ?? 0,
                'hint' => 'Ứng viên có kết quả đạt',
                'tone' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
                'icon' => 'check',
            ],
            [
                'label' => 'Đã từ chối',
                'value' => $stats['failed_candidates'] ?? 0,
                'hint' => 'Ứng viên không phù hợp',
                'tone' => 'bg-rose-50 text-rose-700 ring-rose-100',
                'icon' => 'xmark',
            ],
        ];

        $modules = [
            [
                'title' => 'Tin tuyển dụng',
                'description' => 'Tạo và quản lý nhu cầu tuyển dụng theo phòng ban, người phụ trách, mức lương và hạn nộp hồ sơ.',
                'route' => route('admin.recruitment.job-posts'),
                'cta' => 'Quản lý tin tuyển dụng',
                'badge' => ($stats['job_posts'] ?? 0).' tin',
                'tone' => 'cyan',
            ],
            [
                'title' => 'Ứng viên',
                'description' => 'Theo dõi hồ sơ, CV, trạng thái tuyển dụng và chuyển ứng viên đạt thành nhân viên.',
                'route' => route('admin.recruitment.candidates'),
                'cta' => 'Quản lý ứng viên',
                'badge' => ($stats['candidates'] ?? 0).' hồ sơ',
                'tone' => 'sky',
            ],
            [
                'title' => 'Phỏng vấn',
                'description' => 'Lên lịch phỏng vấn, ghi nhận điểm đánh giá, đề xuất tuyển và cập nhật kết quả.',
                'route' => route('admin.recruitment.interviews'),
                'cta' => 'Quản lý phỏng vấn',
                'badge' => ($stats['interviews'] ?? 0).' lịch',
                'tone' => 'amber',
            ],
        ];
    @endphp

    @include('admin.recruitment.partials.ui-contrast')

    <div class="recruitment-ui max-w-full overflow-hidden space-y-6">
        <section class="relative overflow-hidden rounded-[2rem] bg-slate-950 px-5 py-6 text-white shadow-sm sm:px-7 sm:py-8">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,rgba(34,211,238,0.38),transparent_34%),radial-gradient(circle_at_bottom_right,rgba(14,165,233,0.28),transparent_34%)]"></div>
            <div class="relative flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div class="min-w-0">
                    <span class="inline-flex rounded-full bg-white/10 px-3 py-1 text-xs font-bold uppercase tracking-[0.2em] text-cyan-100 ring-1 ring-white/15">
                        Trung tâm tuyển dụng
                    </span>
                    <h2 class="mt-4 text-3xl font-black tracking-tight sm:text-4xl">Bảng điều khiển tuyển dụng</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-200">
                        Tổng quan nhanh quy trình tuyển dụng: tin đang tuyển, hồ sơ ứng viên, phỏng vấn và ứng viên đã đạt.
                    </p>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('admin.recruitment.job-posts.create') }}"
                       class="inline-flex items-center justify-center rounded-2xl bg-white px-5 py-3 text-sm font-bold text-slate-900 transition hover:bg-cyan-50">
                        Tạo tin tuyển dụng
                    </a>
                    <a href="{{ route('admin.recruitment.candidates.create') }}"
                       class="inline-flex items-center justify-center rounded-2xl bg-cyan-500 px-5 py-3 text-sm font-bold text-white transition hover:bg-cyan-400">
                        Thêm ứng viên
                    </a>
                </div>
            </div>
        </section>

        <section class="recruitment-stats grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
            @foreach ($cards as $card)
                <div class="overflow-hidden rounded-[1.75rem] border border-white/80 bg-white p-5 shadow-sm shadow-slate-200/60">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-500">{{ $card['label'] }}</p>
                            <p class="mt-3 text-3xl font-black tracking-tight text-slate-900">{{ $card['value'] }}</p>
                        </div>
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl ring-1 {{ $card['tone'] }}">
                            @if ($card['icon'] === 'briefcase')
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.1A2.25 2.25 0 0 1 18 20.5H6a2.25 2.25 0 0 1-2.25-2.25v-4.1m16.5 0A2.25 2.25 0 0 0 18 11.9H6a2.25 2.25 0 0 0-2.25 2.25m16.5 0v-2.4A2.25 2.25 0 0 0 18 9.5H6a2.25 2.25 0 0 0-2.25 2.25v2.4M9 9.5V6.75A2.25 2.25 0 0 1 11.25 4.5h1.5A2.25 2.25 0 0 1 15 6.75V9.5" /></svg>
                            @elseif ($card['icon'] === 'users')
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.965-3.07M12 7.875a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
                            @elseif ($card['icon'] === 'clock')
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m5-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                            @elseif ($card['icon'] === 'calendar')
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3.75 8.25h16.5M4.5 6.75A2.25 2.25 0 0 1 6.75 4.5h10.5a2.25 2.25 0 0 1 2.25 2.25v10.5a2.25 2.25 0 0 1-2.25 2.25H6.75a2.25 2.25 0 0 1-2.25-2.25V6.75Z" /></svg>
                            @elseif ($card['icon'] === 'check')
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            @else
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                            @endif
                        </div>
                    </div>
                    <p class="mt-4 text-sm leading-6 text-slate-500">{{ $card['hint'] }}</p>
                </div>
            @endforeach
        </section>

        <section class="grid grid-cols-1 gap-5 lg:grid-cols-3">
            @foreach ($modules as $module)
                <a href="{{ $module['route'] }}"
                   class="group overflow-hidden rounded-[1.75rem] border border-slate-100 bg-white p-6 shadow-sm shadow-slate-200/60 transition hover:-translate-y-1 hover:border-cyan-200 hover:shadow-lg hover:shadow-cyan-100/60">
                    <div class="flex items-center justify-between gap-4">
                        <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">{{ $module['badge'] }}</span>
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl bg-cyan-50 text-cyan-700 transition group-hover:bg-cyan-600 group-hover:text-white">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </span>
                    </div>
                    <h3 class="mt-5 text-xl font-black text-slate-900">{{ $module['title'] }}</h3>
                    <p class="mt-3 text-sm leading-6 text-slate-500">{{ $module['description'] }}</p>
                    <div class="mt-6 text-sm font-bold text-cyan-700">{{ $module['cta'] }}</div>
                </a>
            @endforeach
        </section>
    </div>
</x-admin-layout>
