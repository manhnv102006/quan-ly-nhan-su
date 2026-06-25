<x-admin-layout title="Tuyển dụng">
    @php
        $cards = [
            [
                'label' => 'Tin tuyển dụng',
                'value' => $stats['job_posts'] ?? 0,
                'hint' => 'Tất cả nhu cầu tuyển dụng đang quản lý',
                'tone' => 'from-sky-500 to-cyan-600',
            ],
            [
                'label' => 'Ứng viên',
                'value' => $stats['candidates'] ?? 0,
                'hint' => 'Hồ sơ ứng viên đã tiếp nhận',
                'tone' => 'from-indigo-500 to-sky-600',
            ],
            [
                'label' => 'Phỏng vấn',
                'value' => $stats['interviews'] ?? 0,
                'hint' => 'Lịch phỏng vấn đã được tạo',
                'tone' => 'from-amber-500 to-orange-500',
            ],
            [
                'label' => 'Ứng viên đạt',
                'value' => $stats['passed_candidates'] ?? 0,
                'hint' => 'Sẵn sàng chuyển thành nhân viên',
                'tone' => 'from-emerald-500 to-teal-600',
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

    <div class="max-w-full overflow-hidden space-y-6">
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

        <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($cards as $card)
                <div class="overflow-hidden rounded-[1.75rem] border border-white/80 bg-white p-5 shadow-sm shadow-slate-200/60">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-slate-500">{{ $card['label'] }}</p>
                            <p class="mt-3 text-3xl font-black tracking-tight text-slate-900">{{ $card['value'] }}</p>
                        </div>
                        <div class="h-12 w-12 shrink-0 rounded-2xl bg-gradient-to-br {{ $card['tone'] }} shadow-lg shadow-slate-200"></div>
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
