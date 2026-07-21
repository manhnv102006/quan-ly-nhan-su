@extends('layouts.recruitment')

@section('title', 'Trang chủ tuyển dụng | HRM Careers')
@section('header_theme', 'dark')

@section('content')
    @php
        $heroJob = $featuredJobPosts->first();
    @endphp

    <style>
        @keyframes grid-drift {
            from { background-position: 0 0; }
            to { background-position: 72px 72px; }
        }

        @keyframes scan-line {
            0% { transform: translateX(-120%); opacity: 0; }
            20% { opacity: .7; }
            100% { transform: translateX(120%); opacity: 0; }
        }

        @keyframes float-soft {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        @keyframes aurora-shift {
            0%, 100% { transform: translate3d(-8%, -5%, 0) scale(1); opacity: .55; }
            50% { transform: translate3d(8%, 5%, 0) scale(1.12); opacity: .85; }
        }

        @keyframes orbit-spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        @keyframes marquee-left {
            from { transform: translateX(0); }
            to { transform: translateX(-50%); }
        }

        .career-grid {
            background-image:
                linear-gradient(to right, rgba(255,255,255,.07) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255,255,255,.07) 1px, transparent 1px);
            background-size: 72px 72px;
            animation: grid-drift 18s linear infinite;
        }

        .career-scan::after {
            content: "";
            position: absolute;
            inset: 0;
            width: 45%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,.18), transparent);
            animation: scan-line 5s ease-in-out infinite;
        }

        .float-soft { animation: float-soft 5s ease-in-out infinite; }

        .aurora-blob {
            animation: aurora-shift 9s ease-in-out infinite;
            filter: blur(70px);
        }

        .orbit-ring { animation: orbit-spin 24s linear infinite; }

        .marquee-track {
            display: flex;
            width: max-content;
            animation: marquee-left 28s linear infinite;
        }

        .reveal-up {
            opacity: 0;
            transform: translateY(34px) scale(.985);
            transition: opacity .8s cubic-bezier(.16,1,.3,1), transform .8s cubic-bezier(.16,1,.3,1);
        }

        .reveal-up.is-visible {
            opacity: 1;
            transform: translateY(0) scale(1);
        }

        .delay-100 { transition-delay: 100ms; }
        .delay-200 { transition-delay: 200ms; }
        .delay-300 { transition-delay: 300ms; }
        .delay-400 { transition-delay: 400ms; }

        #cursor-glow {
            background: radial-gradient(circle, rgba(34,211,238,.28), rgba(249,115,22,.12) 30%, transparent 68%);
            transform: translate3d(-50%, -50%, 0);
        }

        .scroll-progress {
            transform: scaleX(0);
            transform-origin: left;
            transition: transform .08s linear;
        }

        .glass-noise::before {
            content: "";
            position: absolute;
            inset: 0;
            border-radius: inherit;
            background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,.16) 1px, transparent 0);
            background-size: 18px 18px;
            opacity: .22;
            pointer-events: none;
        }

        .pulse-node {
            box-shadow: 0 0 0 0 rgba(34,211,238,.38);
            animation: pulse-node 2.6s ease-out infinite;
        }

        @keyframes pulse-node {
            70% { box-shadow: 0 0 0 18px rgba(34,211,238,0); }
            100% { box-shadow: 0 0 0 0 rgba(34,211,238,0); }
        }
    </style>

    <div class="relative overflow-hidden bg-[#030712] text-white">
        <div class="fixed left-0 top-0 z-50 h-1 w-full bg-white/5">
            <div id="scroll-progress" class="scroll-progress h-full w-full bg-gradient-to-r from-orange-500 via-cyan-300 to-emerald-300"></div>
        </div>
        <div id="cursor-glow" class="pointer-events-none fixed left-1/2 top-1/2 z-0 h-96 w-96 rounded-full opacity-70 mix-blend-screen blur-2xl"></div>

        <section class="relative isolate overflow-hidden bg-[#030712]">
            <div class="absolute inset-0 career-grid opacity-30"></div>
            <div class="aurora-blob js-depth pointer-events-none absolute -left-28 top-16 h-96 w-96 rounded-full bg-cyan-500/25" data-depth="-0.035"></div>
            <div class="aurora-blob js-depth pointer-events-none absolute -right-28 bottom-10 h-[28rem] w-[28rem] rounded-full bg-orange-500/20" data-depth="0.025" style="animation-delay: -4s;"></div>
            <div class="absolute inset-x-0 top-0 h-40 bg-gradient-to-b from-cyan-500/20 to-transparent"></div>
            <div class="absolute inset-x-0 bottom-0 h-40 bg-gradient-to-t from-[#030712] to-transparent"></div>

            <div class="relative mx-auto max-w-[1500px] px-5 py-14 sm:px-8 lg:px-12 lg:py-20">
                <div class="grid grid-cols-1 items-center gap-10 xl:grid-cols-[minmax(0,1.05fr)_minmax(460px,.95fr)]">
                    <div class="min-w-0 reveal-up">
                        <div class="inline-flex items-center gap-3 rounded-full border border-white/10 bg-white/5 px-4 py-2 text-xs font-black uppercase tracking-[0.25em] text-cyan-200 shadow-2xl shadow-cyan-950/40 backdrop-blur">
                            <span class="h-2 w-2 rounded-full bg-emerald-400 shadow-[0_0_18px_rgba(52,211,153,.9)]"></span>
                            HRM Careers đang mở tuyển
                        </div>

                        <h1 class="mt-7 max-w-5xl text-5xl font-black leading-[0.95] tracking-tight text-white sm:text-7xl lg:text-8xl">
                            Bứt phá sự nghiệp trong hệ sinh thái nhân sự số
                        </h1>

                        <p class="mt-7 max-w-2xl text-base leading-8 text-slate-300 sm:text-lg">
                            Tìm vị trí phù hợp, xem thông tin tuyển dụng rõ ràng và gửi hồ sơ trực tiếp cho HR mà không cần đăng nhập.
                        </p>

                        <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                            <a href="{{ route('public.recruitment.jobs') }}" class="inline-flex items-center justify-center rounded-2xl bg-orange-500 px-7 py-4 text-sm font-black uppercase tracking-wide text-white shadow-xl shadow-orange-950/30 transition hover:-translate-y-0.5 hover:bg-orange-600">
                                Xem vị trí đang tuyển
                            </a>
                            <a href="{{ route('public.recruitment.about') }}" class="inline-flex items-center justify-center rounded-2xl border border-white/15 bg-white/5 px-7 py-4 text-sm font-black uppercase tracking-wide text-white transition hover:-translate-y-0.5 hover:border-cyan-300/70 hover:bg-cyan-300/10">
                                Khám phá văn hóa
                            </a>
                        </div>

                        <div class="mt-12 grid max-w-3xl grid-cols-3 gap-3">
                            <div class="rounded-2xl border border-white/10 bg-white/[0.06] p-5 backdrop-blur">
                                <p class="text-4xl font-black text-orange-400">{{ $stats['open_jobs'] ?? 0 }}</p>
                                <p class="mt-2 text-xs font-bold uppercase tracking-wide text-slate-400">Tin tuyển dụng</p>
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-white/[0.06] p-5 backdrop-blur">
                                <p class="text-4xl font-black text-orange-400">{{ $stats['applications'] ?? 0 }}</p>
                                <p class="mt-2 text-xs font-bold uppercase tracking-wide text-slate-400">Hồ sơ đã nhận</p>
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-white/[0.06] p-5 backdrop-blur">
                                <p class="text-4xl font-black text-orange-400">{{ $stats['departments'] ?? 0 }}</p>
                                <p class="mt-2 text-xs font-bold uppercase tracking-wide text-slate-400">Phòng ban</p>
                            </div>
                        </div>
                    </div>

                    <div class="float-soft relative reveal-up delay-200">
                        <div class="orbit-ring pointer-events-none absolute -inset-8 rounded-[3rem] border border-cyan-300/10"></div>
                        <div class="orbit-ring pointer-events-none absolute -inset-14 rounded-[4rem] border border-orange-300/10" style="animation-duration: 34s; animation-direction: reverse;"></div>
                        <div class="career-scan relative overflow-hidden rounded-[2rem] border border-white/10 bg-white/[0.08] p-6 shadow-2xl shadow-cyan-950/40 backdrop-blur">
                            <div class="rounded-[1.5rem] border border-white/10 bg-[#07111f] p-6">
                                <div class="flex items-center justify-between gap-4">
                                    <div class="flex items-center gap-4">
                                        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white">
                                            <x-application-logo class="h-12 w-12 object-contain" />
                                        </div>
                                        <div>
                                            <p class="text-xs font-black uppercase tracking-[0.25em] text-cyan-300">Tin nổi bật</p>
                                            <p class="mt-1 text-sm text-slate-400">Ứng tuyển trực tiếp</p>
                                        </div>
                                    </div>
                                    <span class="rounded-full bg-emerald-400/10 px-3 py-1 text-xs font-black text-emerald-300">Đang tuyển</span>
                                </div>

                                <h2 class="mt-8 break-words text-3xl font-black leading-tight text-white sm:text-4xl">
                                    {{ $heroJob?->title ?? 'Cơ hội nghề nghiệp mới tại HRM' }}
                                </h2>

                                <p class="mt-5 text-sm leading-7 text-slate-300">
                                    {{ $heroJob?->description ? \Illuminate\Support\Str::limit($heroJob->description, 170) : 'Theo dõi các vị trí mới nhất và gửi hồ sơ ngay khi tìm thấy công việc phù hợp.' }}
                                </p>

                                <div class="mt-8 grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    @if ($heroJob)
                                        <a href="{{ route('public.recruitment.show', $heroJob) }}" class="inline-flex items-center justify-center rounded-2xl bg-cyan-500 px-5 py-4 text-sm font-black text-white transition hover:bg-cyan-600">Xem chi tiết</a>
                                        <a href="{{ route('public.recruitment.apply', $heroJob) }}" class="inline-flex items-center justify-center rounded-2xl bg-white px-5 py-4 text-sm font-black text-slate-950 transition hover:bg-slate-100">Ứng tuyển ngay</a>
                                    @else
                                        <a href="{{ route('public.recruitment.jobs') }}" class="inline-flex items-center justify-center rounded-2xl bg-cyan-500 px-5 py-4 text-sm font-black text-white transition hover:bg-cyan-600 sm:col-span-2">Xem danh sách</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-12 flex justify-center gap-5">
                    <span class="h-2 w-16 rounded-full bg-orange-500"></span>
                    <span class="h-2 w-16 rounded-full bg-white/20"></span>
                    <span class="h-2 w-16 rounded-full bg-white/20"></span>
                    <span class="h-2 w-16 rounded-full bg-white/20"></span>
                </div>
            </div>
        </section>

        <section id="he-sinh-thai" class="border-y border-white/10 bg-black/40">
            <div class="mx-auto max-w-[1500px] overflow-hidden px-5 py-5 sm:px-8 lg:px-12">
                <div class="marquee-track gap-10 text-xs font-black uppercase tracking-[0.3em] text-white/35">
                    <span>Innovation</span>
                    <span>Transparency</span>
                    <span>Human First</span>
                    <span>Performance</span>
                    <span>Growth</span>
                    <span>HRM Careers</span>
                    <span>Innovation</span>
                    <span>Transparency</span>
                    <span>Human First</span>
                    <span>Performance</span>
                    <span>Growth</span>
                    <span>HRM Careers</span>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-[1500px] px-5 py-16 sm:px-8 lg:px-12">
            <div class="reveal-up flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-sm font-black uppercase tracking-[0.25em] text-cyan-300">Cơ hội nghề nghiệp</p>
                    <h2 class="mt-3 text-4xl font-black tracking-tight text-white sm:text-5xl">Việc làm mới nhất</h2>
                </div>
                <a href="{{ route('public.recruitment.jobs') }}" class="inline-flex w-full items-center justify-center rounded-2xl bg-orange-500 px-6 py-3 text-sm font-black uppercase text-white transition hover:-translate-y-0.5 hover:bg-orange-600 md:w-auto">Xem tất cả</a>
            </div>

            <div class="mt-8 grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($featuredJobPosts as $jobPost)
                    <article class="group reveal-up flex min-w-0 flex-col rounded-3xl border border-white/10 bg-white/[0.06] p-6 shadow-2xl shadow-black/30 backdrop-blur transition duration-300 hover:-translate-y-1 hover:border-cyan-300/50 hover:bg-white/[0.09]">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <p class="text-sm font-black text-cyan-300">{{ $jobPost->department?->department_name ?? 'Chưa gắn phòng ban' }}</p>
                                <h3 class="mt-3 break-words text-2xl font-black text-white">{{ $jobPost->title }}</h3>
                            </div>
                            <span class="shrink-0 rounded-full bg-emerald-400/10 px-3 py-1 text-xs font-black text-emerald-300">Đang tuyển</span>
                        </div>

                        <p class="mt-5 text-sm text-slate-400">Hạn nộp: <span class="font-black text-slate-200">{{ $jobPost->application_deadline?->format('d/m/Y') ?? 'Không giới hạn' }}</span></p>

                        <div class="mt-auto flex flex-col gap-2 pt-6 sm:flex-row">
                            <a href="{{ route('public.recruitment.show', $jobPost) }}" class="inline-flex w-full items-center justify-center rounded-2xl bg-cyan-500 px-4 py-3 text-sm font-black text-white transition hover:bg-cyan-600">Xem chi tiết</a>
                            <a href="{{ route('public.recruitment.apply', $jobPost) }}" class="inline-flex w-full items-center justify-center rounded-2xl bg-white px-4 py-3 text-sm font-black text-slate-950 transition hover:bg-slate-100">Ứng tuyển</a>
                        </div>
                    </article>
                @empty
                    <div class="rounded-3xl border border-dashed border-white/15 bg-white/[0.04] p-10 text-center md:col-span-2 xl:col-span-3">
                        <h3 class="text-xl font-black text-white">Chưa có tin tuyển dụng đang mở</h3>
                        <p class="mt-3 text-sm text-slate-400">Các vị trí mới sẽ được cập nhật tại đây sau khi bộ phận nhân sự mở tuyển.</p>
                    </div>
                @endforelse
            </div>
        </section>

        <section id="tin-tuc" class="border-t border-white/10 bg-[#050816]">
            <div class="mx-auto max-w-[1500px] px-5 py-16 sm:px-8 lg:px-12">
                <h2 class="reveal-up text-4xl font-black text-orange-400">Tầm nhìn tuyển dụng</h2>
                <div class="mt-8 grid grid-cols-1 gap-5 md:grid-cols-3">
                    <div class="reveal-up rounded-3xl border border-orange-400/30 bg-orange-400/10 p-6 transition hover:-translate-y-1">
                        <h3 class="text-xl font-black text-white">Minh bạch</h3>
                        <p class="mt-3 text-sm leading-7 text-slate-300">Thông tin phòng ban, địa điểm, hạn nộp, yêu cầu và quyền lợi được trình bày rõ ràng.</p>
                    </div>
                    <div class="reveal-up delay-100 rounded-3xl border border-cyan-400/30 bg-cyan-400/10 p-6 transition hover:-translate-y-1">
                        <h3 class="text-xl font-black text-white">Nhanh gọn</h3>
                        <p class="mt-3 text-sm leading-7 text-slate-300">Ứng viên gửi hồ sơ trực tiếp trên website mà không cần tạo tài khoản.</p>
                    </div>
                    <div class="reveal-up delay-200 rounded-3xl border border-emerald-400/30 bg-emerald-400/10 p-6 transition hover:-translate-y-1">
                        <h3 class="text-xl font-black text-white">Tập trung</h3>
                        <p class="mt-3 text-sm leading-7 text-slate-300">Hồ sơ tự động vào trang quản trị ứng viên để HR theo dõi và xử lý.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="relative overflow-hidden border-t border-white/10 bg-[#030712]">
            <div class="absolute inset-0 career-grid opacity-20"></div>
            <div class="mx-auto max-w-[1500px] px-5 py-20 sm:px-8 lg:px-12">
                <div class="reveal-up max-w-3xl">
                    <p class="text-sm font-black uppercase tracking-[0.25em] text-cyan-300">Hành trình ứng tuyển</p>
                    <h2 class="mt-3 text-4xl font-black tracking-tight text-white sm:text-6xl">4 bước để gia nhập HRM</h2>
                    <p class="mt-5 text-base leading-8 text-slate-300">Quy trình tinh gọn, rõ ràng và thân thiện với ứng viên ngay từ lần đầu truy cập.</p>
                </div>

                <div class="relative mt-12 grid grid-cols-1 gap-5 lg:grid-cols-4">
                    <div class="hidden lg:absolute lg:left-0 lg:right-0 lg:top-12 lg:block lg:h-px lg:bg-gradient-to-r lg:from-cyan-400/0 lg:via-cyan-400/60 lg:to-orange-400/0"></div>

                    <div class="reveal-up relative rounded-3xl border border-white/10 bg-white/[0.06] p-6 backdrop-blur transition hover:-translate-y-1 hover:border-cyan-300/50">
                        <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-cyan-400 text-xl font-black text-slate-950">1</span>
                        <h3 class="mt-6 text-xl font-black text-white">Chọn vị trí</h3>
                        <p class="mt-3 text-sm leading-7 text-slate-300">Xem danh sách việc làm, phòng ban, địa điểm, quyền lợi và hạn nộp.</p>
                    </div>
                    <div class="reveal-up delay-100 relative rounded-3xl border border-white/10 bg-white/[0.06] p-6 backdrop-blur transition hover:-translate-y-1 hover:border-cyan-300/50">
                        <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-cyan-400 text-xl font-black text-slate-950">2</span>
                        <h3 class="mt-6 text-xl font-black text-white">Gửi hồ sơ</h3>
                        <p class="mt-3 text-sm leading-7 text-slate-300">Điền thông tin cá nhân và đính kèm CV nếu có. Không cần đăng nhập.</p>
                    </div>
                    <div class="reveal-up delay-200 relative rounded-3xl border border-white/10 bg-white/[0.06] p-6 backdrop-blur transition hover:-translate-y-1 hover:border-cyan-300/50">
                        <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-orange-400 text-xl font-black text-white">3</span>
                        <h3 class="mt-6 text-xl font-black text-white">HR tiếp nhận</h3>
                        <p class="mt-3 text-sm leading-7 text-slate-300">Hồ sơ tự động vào trang quản trị ứng viên để đội ngũ tuyển dụng xử lý.</p>
                    </div>
                    <div class="reveal-up delay-300 relative rounded-3xl border border-white/10 bg-white/[0.06] p-6 backdrop-blur transition hover:-translate-y-1 hover:border-cyan-300/50">
                        <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-orange-400 text-xl font-black text-white">4</span>
                        <h3 class="mt-6 text-xl font-black text-white">Phản hồi</h3>
                        <p class="mt-3 text-sm leading-7 text-slate-300">Ứng viên được liên hệ khi hồ sơ phù hợp với vị trí đang tuyển.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="border-t border-white/10 bg-black">
            <div class="mx-auto grid max-w-[1500px] grid-cols-1 gap-8 px-5 py-20 sm:px-8 lg:grid-cols-[minmax(0,.85fr)_minmax(0,1.15fr)] lg:px-12">
                <div class="reveal-up lg:sticky lg:top-32 lg:self-start">
                    <p class="text-sm font-black uppercase tracking-[0.25em] text-cyan-300">Không gian phát triển</p>
                    <h2 class="mt-3 text-4xl font-black tracking-tight text-white sm:text-6xl">Phúc lợi không chỉ nằm trên giấy</h2>
                    <p class="mt-5 text-base leading-8 text-slate-300">Mỗi quyền lợi được thiết kế để ứng viên có thể phát triển dài hạn, ổn định và có cảm hứng làm việc.</p>
                </div>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                    <div class="reveal-up rounded-3xl border border-white/10 bg-gradient-to-br from-white/[0.10] to-white/[0.03] p-7 transition hover:-translate-y-1 hover:border-orange-400/60">
                        <p class="text-4xl font-black text-orange-400">01</p>
                        <h3 class="mt-5 text-2xl font-black text-white">Lộ trình rõ ràng</h3>
                        <p class="mt-3 text-sm leading-7 text-slate-300">Từng vị trí có kỳ vọng năng lực và hướng phát triển minh bạch.</p>
                    </div>
                    <div class="reveal-up delay-100 rounded-3xl border border-white/10 bg-gradient-to-br from-white/[0.10] to-white/[0.03] p-7 transition hover:-translate-y-1 hover:border-cyan-400/60 md:mt-10">
                        <p class="text-4xl font-black text-cyan-300">02</p>
                        <h3 class="mt-5 text-2xl font-black text-white">Công cụ làm việc tốt</h3>
                        <p class="mt-3 text-sm leading-7 text-slate-300">Hạ tầng số và quy trình quản trị giúp nhân sự tập trung vào chuyên môn.</p>
                    </div>
                    <div class="reveal-up delay-200 rounded-3xl border border-white/10 bg-gradient-to-br from-white/[0.10] to-white/[0.03] p-7 transition hover:-translate-y-1 hover:border-emerald-400/60">
                        <p class="text-4xl font-black text-emerald-300">03</p>
                        <h3 class="mt-5 text-2xl font-black text-white">Ghi nhận đóng góp</h3>
                        <p class="mt-3 text-sm leading-7 text-slate-300">Dữ liệu công việc và hiệu suất được theo dõi rõ để phản hồi công bằng.</p>
                    </div>
                    <div class="reveal-up delay-300 rounded-3xl border border-white/10 bg-gradient-to-br from-white/[0.10] to-white/[0.03] p-7 transition hover:-translate-y-1 hover:border-purple-400/60 md:mt-10">
                        <p class="text-4xl font-black text-purple-300">04</p>
                        <h3 class="mt-5 text-2xl font-black text-white">Đội ngũ hỗ trợ</h3>
                        <p class="mt-3 text-sm leading-7 text-slate-300">HR, quản lý và phòng ban đồng hành trong từng giai đoạn tuyển dụng.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="relative overflow-hidden border-t border-white/10 bg-[#07111f]">
            <div class="absolute inset-0 opacity-25" style="background-image: radial-gradient(circle at 20px 20px, rgba(255,255,255,.18) 2px, transparent 2px); background-size: 64px 64px;"></div>
            <div class="relative mx-auto max-w-[1500px] px-5 py-20 sm:px-8 lg:px-12">
                <div class="reveal-up flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                    <div>
                        <p class="text-sm font-black uppercase tracking-[0.25em] text-cyan-300">Culture board</p>
                        <h2 class="mt-3 text-4xl font-black tracking-tight text-white sm:text-6xl">Một ngày làm việc tại HRM</h2>
                    </div>
                    <a href="{{ route('public.recruitment.about') }}" class="inline-flex w-full items-center justify-center rounded-2xl border border-white/15 bg-white/5 px-6 py-3 text-sm font-black uppercase text-white transition hover:border-cyan-300/60 hover:bg-cyan-300/10 md:w-auto">Xem giới thiệu</a>
                </div>

                <div class="mt-10 grid grid-cols-1 gap-5 lg:grid-cols-12">
                    <div class="reveal-up min-h-72 rounded-[2rem] border border-white/10 bg-gradient-to-br from-cyan-400/30 via-white/10 to-transparent p-8 lg:col-span-7">
                        <p class="text-sm font-black uppercase tracking-[0.25em] text-cyan-100">Morning sync</p>
                        <h3 class="mt-20 max-w-xl text-4xl font-black text-white">Bắt đầu bằng mục tiêu rõ ràng, dữ liệu rõ ràng.</h3>
                    </div>
                    <div class="reveal-up delay-100 min-h-72 rounded-[2rem] border border-white/10 bg-gradient-to-br from-orange-400/35 via-white/10 to-transparent p-8 lg:col-span-5">
                        <p class="text-sm font-black uppercase tracking-[0.25em] text-orange-100">Deep work</p>
                        <h3 class="mt-20 text-4xl font-black text-white">Không gian tập trung để tạo giá trị thật.</h3>
                    </div>
                    <div class="reveal-up delay-200 min-h-72 rounded-[2rem] border border-white/10 bg-gradient-to-br from-emerald-400/30 via-white/10 to-transparent p-8 lg:col-span-4">
                        <p class="text-sm font-black uppercase tracking-[0.25em] text-emerald-100">Feedback</p>
                        <h3 class="mt-20 text-3xl font-black text-white">Phản hồi nhanh, hành động nhanh.</h3>
                    </div>
                    <div class="reveal-up delay-300 min-h-72 rounded-[2rem] border border-white/10 bg-gradient-to-br from-purple-400/30 via-white/10 to-transparent p-8 lg:col-span-8">
                        <p class="text-sm font-black uppercase tracking-[0.25em] text-purple-100">Growth</p>
                        <h3 class="mt-20 max-w-2xl text-4xl font-black text-white">Mỗi dự án là một cơ hội nâng cấp năng lực cá nhân.</h3>
                    </div>
                </div>
            </div>
        </section>

        <section class="relative overflow-hidden border-t border-white/10 bg-[#020617]">
            <div class="js-depth pointer-events-none absolute left-1/2 top-20 h-80 w-80 rounded-full bg-cyan-400/20 blur-3xl" data-depth="-0.02"></div>
            <div class="js-depth pointer-events-none absolute bottom-10 right-10 h-96 w-96 rounded-full bg-orange-500/15 blur-3xl" data-depth="0.018"></div>
            <div class="relative mx-auto grid max-w-[1500px] grid-cols-1 gap-8 px-5 py-24 sm:px-8 lg:grid-cols-[minmax(0,1fr)_minmax(420px,.72fr)] lg:px-12">
                <div class="reveal-up">
                    <p class="text-sm font-black uppercase tracking-[0.25em] text-orange-300">Recruitment cockpit</p>
                    <h2 class="mt-3 max-w-4xl text-4xl font-black tracking-tight text-white sm:text-6xl">Theo dõi hành trình ứng viên như một trung tâm vận hành số</h2>
                    <p class="mt-6 max-w-2xl text-base leading-8 text-slate-300">
                        Từ lúc ứng viên xem tin, gửi hồ sơ đến khi HR tiếp nhận, mọi điểm chạm đều được thiết kế rõ ràng để giảm thao tác và tăng tốc phản hồi.
                    </p>

                    <div class="mt-10 grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div class="reveal-up rounded-3xl border border-cyan-300/20 bg-cyan-300/10 p-6">
                            <p class="text-4xl font-black text-cyan-200">24/7</p>
                            <p class="mt-3 text-sm font-bold leading-6 text-slate-300">Ứng viên có thể gửi hồ sơ bất kỳ lúc nào.</p>
                        </div>
                        <div class="reveal-up delay-100 rounded-3xl border border-orange-300/20 bg-orange-300/10 p-6">
                            <p class="text-4xl font-black text-orange-300">0</p>
                            <p class="mt-3 text-sm font-bold leading-6 text-slate-300">Không cần tạo tài khoản trước khi ứng tuyển.</p>
                        </div>
                        <div class="reveal-up delay-200 rounded-3xl border border-emerald-300/20 bg-emerald-300/10 p-6">
                            <p class="text-4xl font-black text-emerald-300">New</p>
                            <p class="mt-3 text-sm font-bold leading-6 text-slate-300">Hồ sơ vào admin với trạng thái mới.</p>
                        </div>
                    </div>
                </div>

                <div class="reveal-up delay-200 relative overflow-hidden rounded-[2rem] border border-white/10 bg-white/[0.07] p-5 shadow-2xl shadow-cyan-950/40 backdrop-blur glass-noise">
                    <div class="relative rounded-[1.5rem] border border-white/10 bg-slate-950/80 p-5">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-black uppercase tracking-[0.25em] text-cyan-300">Live board</p>
                                <h3 class="mt-2 text-2xl font-black text-white">Luồng ứng tuyển</h3>
                            </div>
                            <span class="pulse-node h-4 w-4 rounded-full bg-cyan-300"></span>
                        </div>

                        <div class="mt-8 space-y-4">
                            <div class="rounded-2xl border border-white/10 bg-white/[0.06] p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="font-black text-white">Tin đang mở</p>
                                    <span class="rounded-full bg-orange-400/15 px-3 py-1 text-xs font-black text-orange-300">{{ $stats['open_jobs'] ?? 0 }}</span>
                                </div>
                                <div class="mt-3 h-2 overflow-hidden rounded-full bg-white/10">
                                    <div class="h-full w-3/4 rounded-full bg-gradient-to-r from-orange-500 to-cyan-300"></div>
                                </div>
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-white/[0.06] p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="font-black text-white">Hồ sơ đã nhận</p>
                                    <span class="rounded-full bg-cyan-400/15 px-3 py-1 text-xs font-black text-cyan-300">{{ $stats['applications'] ?? 0 }}</span>
                                </div>
                                <div class="mt-3 h-2 overflow-hidden rounded-full bg-white/10">
                                    <div class="h-full w-2/3 rounded-full bg-gradient-to-r from-cyan-300 to-emerald-300"></div>
                                </div>
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-white/[0.06] p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="font-black text-white">Phòng ban tuyển dụng</p>
                                    <span class="rounded-full bg-emerald-400/15 px-3 py-1 text-xs font-black text-emerald-300">{{ $stats['departments'] ?? 0 }}</span>
                                </div>
                                <div class="mt-3 h-2 overflow-hidden rounded-full bg-white/10">
                                    <div class="h-full w-1/2 rounded-full bg-gradient-to-r from-emerald-300 to-orange-300"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="border-t border-white/10 bg-black">
            <div class="mx-auto max-w-[1500px] px-5 py-24 sm:px-8 lg:px-12">
                <div class="reveal-up mx-auto max-w-3xl text-center">
                    <p class="text-sm font-black uppercase tracking-[0.25em] text-cyan-300">Candidate experience</p>
                    <h2 class="mt-3 text-4xl font-black tracking-tight text-white sm:text-6xl">Từng thao tác đều được làm nhẹ hơn</h2>
                    <p class="mt-5 text-base leading-8 text-slate-300">Trang tuyển dụng không chỉ đẹp ở phần nhìn, mà còn giúp ứng viên hiểu nhanh, chọn nhanh và gửi hồ sơ thật tự nhiên.</p>
                </div>

                <div class="mt-12 grid grid-cols-1 gap-5 lg:grid-cols-3">
                    <div class="reveal-up group relative min-h-[420px] overflow-hidden rounded-[2rem] border border-white/10 bg-gradient-to-br from-cyan-500/25 via-white/[0.07] to-transparent p-8 transition duration-500 hover:-translate-y-2 hover:border-cyan-300/60">
                        <div class="absolute -right-16 -top-16 h-48 w-48 rounded-full bg-cyan-300/20 blur-2xl transition duration-500 group-hover:scale-125"></div>
                        <p class="relative text-sm font-black uppercase tracking-[0.25em] text-cyan-100">01</p>
                        <h3 class="relative mt-28 text-4xl font-black text-white">Đọc tin rõ ràng</h3>
                        <p class="relative mt-4 text-sm leading-7 text-slate-300">Thông tin mô tả, hạn nộp, địa điểm và phòng ban được trình bày để ứng viên scan nhanh.</p>
                    </div>
                    <div class="reveal-up delay-100 group relative min-h-[420px] overflow-hidden rounded-[2rem] border border-white/10 bg-gradient-to-br from-orange-500/30 via-white/[0.07] to-transparent p-8 transition duration-500 hover:-translate-y-2 hover:border-orange-300/60 lg:mt-12">
                        <div class="absolute -right-16 -top-16 h-48 w-48 rounded-full bg-orange-300/20 blur-2xl transition duration-500 group-hover:scale-125"></div>
                        <p class="relative text-sm font-black uppercase tracking-[0.25em] text-orange-100">02</p>
                        <h3 class="relative mt-28 text-4xl font-black text-white">Ứng tuyển không ma sát</h3>
                        <p class="relative mt-4 text-sm leading-7 text-slate-300">Form chỉ giữ các trường cần thiết, CV tùy chọn, dữ liệu được kiểm tra ngay trước khi gửi.</p>
                    </div>
                    <div class="reveal-up delay-200 group relative min-h-[420px] overflow-hidden rounded-[2rem] border border-white/10 bg-gradient-to-br from-emerald-500/25 via-white/[0.07] to-transparent p-8 transition duration-500 hover:-translate-y-2 hover:border-emerald-300/60">
                        <div class="absolute -right-16 -top-16 h-48 w-48 rounded-full bg-emerald-300/20 blur-2xl transition duration-500 group-hover:scale-125"></div>
                        <p class="relative text-sm font-black uppercase tracking-[0.25em] text-emerald-100">03</p>
                        <h3 class="relative mt-28 text-4xl font-black text-white">HR xử lý liền mạch</h3>
                        <p class="relative mt-4 text-sm leading-7 text-slate-300">Hồ sơ mới tự động xuất hiện trong quản trị ứng viên để đội ngũ tuyển dụng tiếp nhận.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="relative overflow-hidden border-t border-white/10 bg-[#060b16]">
            <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-cyan-300/70 to-transparent"></div>
            <div class="mx-auto max-w-[1500px] px-5 py-24 sm:px-8 lg:px-12">
                <div class="reveal-up flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                    <div>
                        <p class="text-sm font-black uppercase tracking-[0.25em] text-orange-300">Talent stories</p>
                        <h2 class="mt-3 text-4xl font-black tracking-tight text-white sm:text-6xl">Điểm chạm tạo niềm tin</h2>
                    </div>
                    <a href="{{ route('public.recruitment.jobs') }}" class="inline-flex w-full items-center justify-center rounded-2xl bg-white px-6 py-3 text-sm font-black uppercase text-slate-950 transition hover:-translate-y-0.5 hover:bg-slate-100 md:w-auto">Xem cơ hội</a>
                </div>

                <div class="mt-12 grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
                    <article class="reveal-up rounded-3xl border border-white/10 bg-white/[0.06] p-6">
                        <p class="text-5xl font-black text-cyan-300">“</p>
                        <p class="mt-2 text-sm leading-7 text-slate-300">Mình xem tin, hiểu yêu cầu và gửi hồ sơ trong vài phút. Không phải tạo tài khoản nên rất nhẹ.</p>
                        <p class="mt-6 font-black text-white">Ứng viên Backend</p>
                    </article>
                    <article class="reveal-up delay-100 rounded-3xl border border-white/10 bg-white/[0.06] p-6">
                        <p class="text-5xl font-black text-orange-300">“</p>
                        <p class="mt-2 text-sm leading-7 text-slate-300">Thông tin phòng ban và hạn nộp rõ, mình biết ngay vị trí có phù hợp với mình không.</p>
                        <p class="mt-6 font-black text-white">Ứng viên HR</p>
                    </article>
                    <article class="reveal-up delay-200 rounded-3xl border border-white/10 bg-white/[0.06] p-6">
                        <p class="text-5xl font-black text-emerald-300">“</p>
                        <p class="mt-2 text-sm leading-7 text-slate-300">CV là tùy chọn nhưng form vẫn đầy đủ để HR có thông tin liên hệ cần thiết.</p>
                        <p class="mt-6 font-black text-white">Ứng viên Intern</p>
                    </article>
                    <article class="reveal-up delay-300 rounded-3xl border border-white/10 bg-white/[0.06] p-6">
                        <p class="text-5xl font-black text-purple-300">“</p>
                        <p class="mt-2 text-sm leading-7 text-slate-300">Sau khi gửi hồ sơ, dữ liệu vào admin ngay nên HR không phải gom thông tin thủ công.</p>
                        <p class="mt-6 font-black text-white">Tuyển dụng HRM</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="border-t border-white/10 bg-[#030712]">
            <div class="mx-auto max-w-[1100px] px-5 py-20 sm:px-8 lg:px-12">
                <div class="reveal-up text-center">
                    <p class="text-sm font-black uppercase tracking-[0.25em] text-cyan-300">FAQ</p>
                    <h2 class="mt-3 text-4xl font-black text-white sm:text-6xl">Ứng viên thường hỏi</h2>
                </div>

                <div class="mt-10 space-y-4">
                    <details class="reveal-up group rounded-3xl border border-white/10 bg-white/[0.06] p-6">
                        <summary class="cursor-pointer list-none text-xl font-black text-white">Tôi có cần đăng nhập để ứng tuyển không?</summary>
                        <p class="mt-4 text-sm leading-7 text-slate-300">Không cần. Bạn chỉ cần chọn vị trí, điền thông tin và gửi hồ sơ trực tiếp.</p>
                    </details>
                    <details class="reveal-up delay-100 group rounded-3xl border border-white/10 bg-white/[0.06] p-6">
                        <summary class="cursor-pointer list-none text-xl font-black text-white">CV có bắt buộc không?</summary>
                        <p class="mt-4 text-sm leading-7 text-slate-300">CV là tùy chọn. Tuy nhiên, nếu có CV, HR sẽ dễ đánh giá hồ sơ của bạn hơn.</p>
                    </details>
                    <details class="reveal-up delay-200 group rounded-3xl border border-white/10 bg-white/[0.06] p-6">
                        <summary class="cursor-pointer list-none text-xl font-black text-white">Sau khi gửi hồ sơ, thông tin đi đâu?</summary>
                        <p class="mt-4 text-sm leading-7 text-slate-300">Hồ sơ của bạn được thêm vào danh sách ứng viên trong trang quản trị để bộ phận tuyển dụng xử lý.</p>
                    </details>
                </div>
            </div>
        </section>

        <section class="relative overflow-hidden bg-black px-5 py-20 text-center sm:px-8 lg:px-12">
            <div class="absolute inset-0 career-grid opacity-20"></div>
            <div class="reveal-up relative mx-auto max-w-5xl">
                <p class="text-sm font-black uppercase tracking-[0.3em] text-cyan-300">Bắt đầu ngay</p>
                <h2 class="mt-5 text-5xl font-black tracking-tight text-white sm:text-7xl">Sẵn sàng nâng tầm sự nghiệp?</h2>
                <p class="mx-auto mt-6 max-w-2xl text-base leading-8 text-slate-300">Khám phá các vị trí đang mở và gửi hồ sơ cho đội ngũ tuyển dụng HRM.</p>
                <a href="{{ route('public.recruitment.jobs') }}" class="mt-9 inline-flex items-center justify-center rounded-2xl bg-orange-500 px-9 py-4 text-sm font-black uppercase tracking-wide text-white shadow-xl shadow-orange-950/30 transition hover:-translate-y-0.5 hover:bg-orange-600">Khám phá vị trí ngay</a>
            </div>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            var revealItems = document.querySelectorAll('.reveal-up');
            var progress = document.getElementById('scroll-progress');
            var glow = document.getElementById('cursor-glow');
            var depthItems = document.querySelectorAll('.js-depth');

            if (reduceMotion) {
                revealItems.forEach(function (item) {
                    item.classList.add('is-visible');
                });

                if (progress) {
                    progress.style.transform = 'scaleX(1)';
                }

                return;
            }

            var revealObserver = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                    }
                });
            }, {
                threshold: 0.16,
                rootMargin: '0px 0px -70px 0px'
            });

            revealItems.forEach(function (item) {
                revealObserver.observe(item);
            });

            function updateScrollEffects() {
                var scrollable = document.documentElement.scrollHeight - window.innerHeight;
                var ratio = scrollable > 0 ? window.scrollY / scrollable : 0;

                if (progress) {
                    progress.style.transform = 'scaleX(' + Math.min(Math.max(ratio, 0), 1) + ')';
                }

                depthItems.forEach(function (item) {
                    var depth = parseFloat(item.getAttribute('data-depth') || '0');
                    item.style.translate = '0 ' + (window.scrollY * depth) + 'px';
                });
            }

            window.addEventListener('scroll', updateScrollEffects, { passive: true });
            updateScrollEffects();

            window.addEventListener('pointermove', function (event) {
                if (!glow) {
                    return;
                }

                glow.style.left = event.clientX + 'px';
                glow.style.top = event.clientY + 'px';
            }, { passive: true });
        });
    </script>
@endsection
