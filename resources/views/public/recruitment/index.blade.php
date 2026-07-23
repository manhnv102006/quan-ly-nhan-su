@extends('layouts.recruitment')

@section('title', 'Trang chủ tuyển dụng | HRM Careers')

@section('content')
    @php
        $heroJob = $homepageJobPosts->first();
        $workTypes = [
            'full_time' => 'Full-time',
            'part_time' => 'Part-time',
            'remote' => 'Remote',
            'hybrid' => 'Hybrid',
            'contract' => 'Hợp đồng',
        ];
    @endphp

    <style>
        .reveal-up {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity .6s ease, transform .6s ease;
        }
        .reveal-up.is-visible {
            opacity: 1;
            transform: translateY(0);
        }
    </style>

    <div class="bg-white text-slate-900">
        {{-- Hero --}}
        <section class="relative overflow-hidden border-b border-slate-100 bg-gradient-to-b from-slate-50 via-white to-white">
            <div class="pointer-events-none absolute -right-24 top-0 h-80 w-80 rounded-full bg-cyan-100/60 blur-3xl"></div>
            <div class="pointer-events-none absolute -left-16 bottom-0 h-64 w-64 rounded-full bg-orange-100/50 blur-3xl"></div>

            <div class="relative mx-auto w-[83%] py-14 lg:py-20">
                <div class="grid grid-cols-1 items-center gap-12 lg:grid-cols-2">
                    <div class="reveal-up min-w-0">
                        <span class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-4 py-1.5 text-xs font-bold uppercase tracking-wider text-emerald-800">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            Đang mở tuyển dụng
                        </span>

                        <h1 class="mt-6 text-4xl font-black leading-tight tracking-tight text-slate-900 sm:text-5xl lg:text-[3.25rem]">
                            Phát triển sự nghiệp cùng hệ sinh thái nhân sự HRM
                        </h1>

                        <p class="mt-5 max-w-xl text-base leading-relaxed text-slate-600 sm:text-lg">
                            Tìm vị trí phù hợp, xem thông tin rõ ràng và gửi hồ sơ trực tiếp — không cần đăng nhập.
                        </p>

                        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                            <a href="{{ route('public.recruitment.jobs') }}"
                               class="inline-flex items-center justify-center rounded-xl bg-cyan-600 px-6 py-3.5 text-sm font-bold text-white shadow-md shadow-cyan-600/20 transition hover:bg-cyan-700">
                                Xem vị trí đang tuyển
                            </a>
                            <a href="{{ route('public.recruitment.about') }}"
                               class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-6 py-3.5 text-sm font-bold text-slate-800 transition hover:border-cyan-300 hover:text-cyan-800">
                                Khám phá văn hóa
                            </a>
                        </div>

                        <div class="mt-10 grid grid-cols-3 gap-4 max-w-lg">
                            <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                                <p class="text-2xl font-black text-cyan-700">{{ $stats['open_jobs'] ?? 0 }}</p>
                                <p class="mt-1 text-xs font-semibold text-slate-500">Tin tuyển dụng</p>
                            </div>
                            <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                                <p class="text-2xl font-black text-cyan-700">{{ $stats['applications'] ?? 0 }}</p>
                                <p class="mt-1 text-xs font-semibold text-slate-500">Hồ sơ đã nhận</p>
                            </div>
                            <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                                <p class="text-2xl font-black text-cyan-700">{{ $stats['departments'] ?? 0 }}</p>
                                <p class="mt-1 text-xs font-semibold text-slate-500">Phòng ban</p>
                            </div>
                        </div>
                    </div>

                    <div class="reveal-up rounded-3xl border border-slate-200/80 bg-white p-6 shadow-xl shadow-slate-200/50 sm:p-8">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl border border-slate-100 bg-slate-50">
                                    <x-application-logo class="h-10 w-10 object-contain" />
                                </div>
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-wider text-cyan-700">Tin nổi bật</p>
                                    <p class="text-sm text-slate-500">Ứng tuyển trực tiếp</p>
                                </div>
                            </div>
                            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">Đang tuyển</span>
                        </div>

                        <h2 class="mt-6 text-2xl font-black leading-snug text-slate-900 sm:text-3xl">
                            {{ $heroJob?->title ?? 'Cơ hội nghề nghiệp mới tại HRM' }}
                        </h2>

                        <p class="mt-4 text-sm leading-relaxed text-slate-600">
                            {{ $heroJob?->description ? \Illuminate\Support\Str::limit($heroJob->description, 180) : 'Theo dõi các vị trí mới nhất và gửi hồ sơ khi tìm thấy công việc phù hợp.' }}
                        </p>

                        @if ($heroJob?->department)
                            <p class="mt-3 text-sm font-semibold text-slate-700">{{ $heroJob->department->department_name }}</p>
                        @endif

                        <div class="mt-8 grid grid-cols-1 gap-3 sm:grid-cols-2">
                            @if ($heroJob)
                                <a href="{{ route('public.recruitment.show', $heroJob) }}"
                                   class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-5 py-3 text-sm font-bold text-white transition hover:bg-slate-800">
                                    Xem chi tiết
                                </a>
                                <a href="{{ route('public.recruitment.apply', $heroJob) }}"
                                   class="inline-flex items-center justify-center rounded-xl border border-cyan-200 bg-cyan-50 px-5 py-3 text-sm font-bold text-cyan-900 transition hover:bg-cyan-100">
                                    Ứng tuyển ngay
                                </a>
                            @else
                                <a href="{{ route('public.recruitment.jobs') }}"
                                   class="inline-flex items-center justify-center rounded-xl bg-cyan-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-cyan-700 sm:col-span-2">
                                    Xem danh sách việc làm
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Jobs --}}
        <section id="viec-lam" class="mx-auto w-[83%] py-16">
            <div class="reveal-up flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-sm font-bold uppercase tracking-wider text-cyan-700">Cơ hội nghề nghiệp</p>
                    <h2 class="mt-2 text-3xl font-black text-slate-900 sm:text-4xl">Việc làm đang tuyển</h2>
                    <p class="mt-2 text-sm text-slate-500">
                        Hiển thị tối đa 5 tin mới nhất
                        ({{ $homepageJobPosts->count() }}/{{ $stats['open_jobs'] ?? 0 }} tin đang tuyển).
                    </p>
                </div>
                @if (($stats['open_jobs'] ?? 0) > 5 || $homepageJobPosts->isNotEmpty())
                    <a href="{{ route('public.recruitment.jobs') }}"
                       class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-800 transition hover:border-cyan-300">
                        Trang danh sách đầy đủ
                    </a>
                @endif
            </div>

            <div class="mt-8 flex flex-col gap-5">
                @forelse ($homepageJobPosts as $jobPost)
                    <article class="reveal-up overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:border-cyan-200 hover:shadow-md">
                        <div class="flex flex-col lg:flex-row lg:items-stretch">
                            <div class="min-w-0 flex-1 p-6 sm:p-8">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-cyan-700">{{ $jobPost->department?->department_name ?? 'Chưa gắn phòng ban' }}</p>
                                        <h3 class="mt-2 text-2xl font-black text-slate-900 sm:text-[1.65rem]">{{ $jobPost->title }}</h3>
                                    </div>
                                    <span class="shrink-0 rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">Đang tuyển</span>
                                </div>

                                @if ($jobPost->description)
                                    <p class="mt-4 text-sm leading-relaxed text-slate-600">
                                        {{ \Illuminate\Support\Str::limit(strip_tags($jobPost->description), 220) }}
                                    </p>
                                @endif

                                <dl class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
                                    <div class="rounded-xl border border-slate-100 bg-slate-50/80 px-3 py-3">
                                        <dt class="text-[11px] font-bold uppercase tracking-wide text-slate-400">Số lượng</dt>
                                        <dd class="mt-1 text-sm font-bold text-slate-800">{{ $jobPost->quantity }} người</dd>
                                    </div>
                                    <div class="rounded-xl border border-slate-100 bg-slate-50/80 px-3 py-3">
                                        <dt class="text-[11px] font-bold uppercase tracking-wide text-slate-400">Địa điểm</dt>
                                        <dd class="mt-1 text-sm font-bold text-slate-800">{{ $jobPost->work_location ?: 'Trao đổi sau' }}</dd>
                                    </div>
                                    <div class="rounded-xl border border-slate-100 bg-slate-50/80 px-3 py-3">
                                        <dt class="text-[11px] font-bold uppercase tracking-wide text-slate-400">Hình thức</dt>
                                        <dd class="mt-1 text-sm font-bold text-slate-800">{{ $jobPost->work_type ? ($workTypes[$jobPost->work_type] ?? $jobPost->work_type) : '—' }}</dd>
                                    </div>
                                    <div class="rounded-xl border border-slate-100 bg-slate-50/80 px-3 py-3">
                                        <dt class="text-[11px] font-bold uppercase tracking-wide text-slate-400">Hạn nộp</dt>
                                        <dd class="mt-1 text-sm font-bold text-slate-800">{{ $jobPost->application_deadline?->format('d/m/Y') ?? 'Không giới hạn' }}</dd>
                                    </div>
                                    <div class="rounded-xl border border-slate-100 bg-slate-50/80 px-3 py-3 sm:col-span-2 lg:col-span-2">
                                        <dt class="text-[11px] font-bold uppercase tracking-wide text-slate-400">Mức lương</dt>
                                        <dd class="mt-1 text-sm font-bold text-slate-800">
                                            @if ($jobPost->salary_min || $jobPost->salary_max)
                                                {{ $jobPost->salary_min ? number_format((float) $jobPost->salary_min, 0, ',', '.') : '0' }}
                                                –
                                                {{ $jobPost->salary_max ? number_format((float) $jobPost->salary_max, 0, ',', '.') : 'Thỏa thuận' }} đ
                                            @else
                                                Thỏa thuận
                                            @endif
                                        </dd>
                                    </div>
                                </dl>

                                @if ($jobPost->recruiter)
                                    <p class="mt-4 text-sm text-slate-500">
                                        Liên hệ tuyển dụng:
                                        <span class="font-semibold text-slate-700">{{ $jobPost->recruiter->full_name }}</span>
                                    </p>
                                @endif
                            </div>

                            <div class="flex flex-row gap-3 border-t border-slate-100 bg-slate-50/50 p-5 sm:flex-col sm:justify-center lg:w-52 lg:border-l lg:border-t-0 lg:p-6">
                                <a href="{{ route('public.recruitment.show', $jobPost) }}"
                                   class="inline-flex flex-1 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-800 transition hover:border-cyan-300 lg:flex-none">
                                    Xem chi tiết
                                </a>
                                <a href="{{ route('public.recruitment.apply', $jobPost) }}"
                                   class="inline-flex flex-1 items-center justify-center rounded-xl bg-cyan-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-cyan-700 lg:flex-none">
                                    Ứng tuyển
                                </a>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-12 text-center">
                        <h3 class="text-lg font-bold text-slate-900">Chưa có tin tuyển dụng đang mở</h3>
                        <p class="mt-2 text-sm text-slate-500">Các vị trí mới sẽ được cập nhật tại đây khi admin mở tin.</p>
                    </div>
                @endforelse
            </div>
        </section>

        {{-- Values --}}
        <section class="border-y border-slate-100 bg-slate-50/80">
            <div class="mx-auto w-[83%] py-16">
                <h2 class="reveal-up text-center text-3xl font-black text-slate-900 sm:text-4xl">Tầm nhìn tuyển dụng</h2>
                <div class="mt-10 grid grid-cols-1 gap-5 md:grid-cols-3">
                    <div class="reveal-up rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-orange-100 text-orange-700">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                        </div>
                        <h3 class="mt-4 text-lg font-bold text-slate-900">Minh bạch</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">Phòng ban, địa điểm, hạn nộp, yêu cầu và quyền lợi được trình bày rõ ràng.</p>
                    </div>
                    <div class="reveal-up rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-cyan-100 text-cyan-800">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" /></svg>
                        </div>
                        <h3 class="mt-4 text-lg font-bold text-slate-900">Nhanh gọn</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">Gửi hồ sơ trực tiếp trên website, không cần tạo tài khoản.</p>
                    </div>
                    <div class="reveal-up rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-100 text-emerald-800">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                        </div>
                        <h3 class="mt-4 text-lg font-bold text-slate-900">Tập trung</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">Hồ sơ vào trang quản trị ứng viên để HR theo dõi và xử lý.</p>
                    </div>
                </div>
            </div>
        </section>

        {{-- Steps --}}
        <section class="mx-auto w-[83%] py-16">
            <div class="reveal-up max-w-2xl">
                <p class="text-sm font-bold uppercase tracking-wider text-cyan-700">Hành trình ứng tuyển</p>
                <h2 class="mt-2 text-3xl font-black text-slate-900 sm:text-4xl">4 bước gia nhập HRM</h2>
            </div>
            <ol class="mt-10 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([
                    ['Chọn vị trí', 'Xem danh sách việc làm, phòng ban và hạn nộp.'],
                    ['Gửi hồ sơ', 'Điền thông tin và đính kèm CV nếu có.'],
                    ['HR tiếp nhận', 'Hồ sơ vào hệ thống quản trị ứng viên.'],
                    ['Phản hồi', 'Liên hệ khi hồ sơ phù hợp vị trí.'],
                ] as $index => [$title, $desc])
                    <li class="reveal-up rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-slate-900 text-sm font-black text-white">{{ $index + 1 }}</span>
                        <h3 class="mt-4 font-bold text-slate-900">{{ $title }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $desc }}</p>
                    </li>
                @endforeach
            </ol>
        </section>

        {{-- FAQ --}}
        <section class="border-t border-slate-100 bg-slate-50/50">
            <div class="mx-auto w-[83%] py-16">
                <h2 class="reveal-up text-center text-3xl font-black text-slate-900">Câu hỏi thường gặp</h2>
                <div class="mt-8 space-y-3">
                    <details class="reveal-up group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <summary class="cursor-pointer list-none font-bold text-slate-900">Tôi có cần đăng nhập để ứng tuyển không?</summary>
                        <p class="mt-3 text-sm leading-relaxed text-slate-600">Không. Chọn vị trí, điền thông tin và gửi hồ sơ trực tiếp.</p>
                    </details>
                    <details class="reveal-up rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <summary class="cursor-pointer list-none font-bold text-slate-900">CV có bắt buộc không?</summary>
                        <p class="mt-3 text-sm leading-relaxed text-slate-600">CV là tùy chọn, nhưng nếu có CV HR sẽ đánh giá hồ sơ dễ hơn.</p>
                    </details>
                    <details class="reveal-up rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <summary class="cursor-pointer list-none font-bold text-slate-900">Sau khi gửi hồ sơ, thông tin đi đâu?</summary>
                        <p class="mt-3 text-sm leading-relaxed text-slate-600">Hồ sơ được thêm vào danh sách ứng viên trong trang quản trị để bộ phận tuyển dụng xử lý.</p>
                    </details>
                </div>
            </div>
        </section>

        {{-- CTA --}}
        <section class="mx-auto w-[83%] py-16 text-center">
            <div class="reveal-up rounded-3xl bg-gradient-to-br from-cyan-600 to-cyan-700 px-6 py-12 text-white shadow-lg shadow-cyan-600/25 sm:px-12">
                <h2 class="text-3xl font-black sm:text-4xl">Sẵn sàng nâng tầm sự nghiệp?</h2>
                <p class="mx-auto mt-4 max-w-xl text-cyan-50/95">Khám phá các vị trí đang mở và gửi hồ sơ cho đội ngũ tuyển dụng HRM.</p>
                <a href="{{ route('public.recruitment.jobs') }}"
                   class="mt-8 inline-flex items-center justify-center rounded-xl bg-white px-8 py-3.5 text-sm font-bold text-cyan-800 transition hover:bg-cyan-50">
                    Khám phá vị trí ngay
                </a>
            </div>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var items = document.querySelectorAll('.reveal-up');
            if (!('IntersectionObserver' in window)) {
                items.forEach(function (el) { el.classList.add('is-visible'); });
                return;
            }
            var observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                    }
                });
            }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
            items.forEach(function (el) { observer.observe(el); });
        });
    </script>
@endsection
