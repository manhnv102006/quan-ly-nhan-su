<x-admin-layout title="Tuyen dung">
    @php
        $cards = [
            [
                'label' => 'Tin tuyen dung',
                'value' => $stats['job_posts'] ?? 0,
                'hint' => 'Tat ca nhu cau tuyen dung dang quan ly',
                'tone' => 'from-sky-500 to-cyan-600',
            ],
            [
                'label' => 'Ung vien',
                'value' => $stats['candidates'] ?? 0,
                'hint' => 'Ho so ung vien da tiep nhan',
                'tone' => 'from-indigo-500 to-sky-600',
            ],
            [
                'label' => 'Phong van',
                'value' => $stats['interviews'] ?? 0,
                'hint' => 'Lich phong van da duoc tao',
                'tone' => 'from-amber-500 to-orange-500',
            ],
            [
                'label' => 'Ung vien dat',
                'value' => $stats['passed_candidates'] ?? 0,
                'hint' => 'San sang chuyen thanh nhan vien',
                'tone' => 'from-emerald-500 to-teal-600',
            ],
        ];

        $modules = [
            [
                'title' => 'Tin tuyen dung',
                'description' => 'Tao va quan ly nhu cau tuyen dung theo phong ban, nguoi phu trach, muc luong va han nop ho so.',
                'route' => route('admin.recruitment.job-posts'),
                'cta' => 'Quan ly tin tuyen dung',
                'badge' => ($stats['job_posts'] ?? 0).' tin',
                'tone' => 'cyan',
            ],
            [
                'title' => 'Ung vien',
                'description' => 'Theo doi ho so, CV, trang thai tuyen dung va chuyen ung vien dat thanh nhan vien.',
                'route' => route('admin.recruitment.candidates'),
                'cta' => 'Quan ly ung vien',
                'badge' => ($stats['candidates'] ?? 0).' ho so',
                'tone' => 'sky',
            ],
            [
                'title' => 'Phong van',
                'description' => 'Len lich phong van, ghi nhan diem danh gia, de xuat tuyen va cap nhat ket qua.',
                'route' => route('admin.recruitment.interviews'),
                'cta' => 'Quan ly phong van',
                'badge' => ($stats['interviews'] ?? 0).' lich',
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
                        Recruitment Center
                    </span>
                    <h2 class="mt-4 text-3xl font-black tracking-tight sm:text-4xl">Dashboard tuyen dung</h2>
                    <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-200">
                        Tong quan nhanh quy trinh tuyen dung: tin dang tuyen, ho so ung vien, phong van va ung vien da dat.
                    </p>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('admin.recruitment.job-posts.create') }}"
                       class="inline-flex items-center justify-center rounded-2xl bg-white px-5 py-3 text-sm font-bold text-slate-900 transition hover:bg-cyan-50">
                        Tao tin tuyen dung
                    </a>
                    <a href="{{ route('admin.recruitment.candidates.create') }}"
                       class="inline-flex items-center justify-center rounded-2xl bg-cyan-500 px-5 py-3 text-sm font-bold text-white transition hover:bg-cyan-400">
                        Them ung vien
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
