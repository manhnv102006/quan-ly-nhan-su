@extends('layouts.recruitment')

@section('title', 'Trang chủ tuyển dụng')

@section('content')
    @php
        $heroJob = $featuredJobPosts->first();
    @endphp

    <section class="relative overflow-hidden bg-sky-500">
        <div class="absolute inset-0 opacity-25" style="background-image: radial-gradient(circle at 18px 18px, rgba(255,255,255,.8) 2px, transparent 2px); background-size: 58px 58px;"></div>
        <div class="relative mx-auto max-w-[1700px] px-5 py-12 sm:px-8 lg:px-12 lg:py-16">
            <div class="grid grid-cols-1 items-center gap-0 lg:grid-cols-[minmax(0,1.05fr)_minmax(520px,0.95fr)]">
                <div class="relative z-10 min-h-[360px] overflow-hidden bg-white shadow-2xl lg:min-h-[520px]">
                    <div class="absolute inset-0 bg-gradient-to-br from-cyan-100 via-white to-orange-50"></div>
                    <div class="relative flex h-full min-h-[360px] flex-col justify-between p-8 lg:min-h-[520px] lg:p-12">
                        <div class="flex items-center gap-4">
                            <div class="flex h-20 w-20 items-center justify-center rounded-2xl bg-white shadow ring-1 ring-slate-200">
                                <x-application-logo class="h-16 w-16 object-contain" />
                            </div>
                            <div>
                                <p class="text-sm font-black uppercase tracking-wide text-cyan-700">HRM Careers</p>
                                <h1 class="mt-2 text-4xl font-black text-slate-950 sm:text-5xl">Cổng tuyển dụng nhân sự</h1>
                            </div>
                        </div>

                        <div class="max-w-2xl">
                            <p class="text-xl font-bold leading-9 text-slate-700">Kết nối ứng viên với các vị trí đang mở, tiếp nhận hồ sơ trực tiếp và rút ngắn hành trình tuyển dụng.</p>
                            <div class="mt-8 grid grid-cols-3 gap-3">
                                <div class="rounded-xl bg-white/85 p-4 shadow-sm">
                                    <p class="text-3xl font-black text-orange-500">{{ $stats['open_jobs'] ?? 0 }}</p>
                                    <p class="mt-1 text-sm font-bold text-slate-600">Tin tuyển dụng</p>
                                </div>
                                <div class="rounded-xl bg-white/85 p-4 shadow-sm">
                                    <p class="text-3xl font-black text-orange-500">{{ $stats['applications'] ?? 0 }}</p>
                                    <p class="mt-1 text-sm font-bold text-slate-600">Hồ sơ</p>
                                </div>
                                <div class="rounded-xl bg-white/85 p-4 shadow-sm">
                                    <p class="text-3xl font-black text-orange-500">{{ $stats['departments'] ?? 0 }}</p>
                                    <p class="mt-1 text-sm font-bold text-slate-600">Phòng ban</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative z-20 bg-white p-8 shadow-2xl lg:-ml-28 lg:p-12">
                    <p class="text-sm font-black uppercase tracking-wide text-orange-500">Tin nổi bật</p>
                    <h2 class="mt-4 text-3xl font-black leading-tight text-slate-950 sm:text-4xl">
                        {{ $heroJob?->title ?? 'Khám phá cơ hội nghề nghiệp mới nhất tại HRM' }}
                    </h2>
                    <p class="mt-6 text-base leading-8 text-slate-600">
                        {{ $heroJob?->description ? \Illuminate\Support\Str::limit($heroJob->description, 180) : 'Theo dõi các vị trí đang tuyển, tìm hiểu yêu cầu công việc và gửi hồ sơ trực tiếp cho bộ phận nhân sự.' }}
                    </p>
                    <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                        @if ($heroJob)
                            <a href="{{ route('public.recruitment.show', $heroJob) }}" class="inline-flex items-center justify-center rounded-2xl bg-orange-500 px-8 py-4 text-sm font-black uppercase text-white transition hover:bg-orange-600">Xem thêm</a>
                            <a href="{{ route('public.recruitment.apply', $heroJob) }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-8 py-4 text-sm font-black uppercase text-slate-700 transition hover:border-cyan-300 hover:text-cyan-700">Ứng tuyển</a>
                        @else
                            <a href="{{ route('public.recruitment.jobs') }}" class="inline-flex items-center justify-center rounded-2xl bg-orange-500 px-8 py-4 text-sm font-black uppercase text-white transition hover:bg-orange-600">Xem thêm</a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="mt-8 flex justify-center gap-6">
                <span class="h-2 w-16 rounded-full bg-orange-500"></span>
                <span class="h-2 w-16 rounded-full bg-slate-500/70"></span>
                <span class="h-2 w-16 rounded-full bg-slate-500/70"></span>
                <span class="h-2 w-16 rounded-full bg-slate-500/70"></span>
            </div>
        </div>
    </section>

    <section id="he-sinh-thai" class="bg-white">
        <div class="mx-auto max-w-[1500px] px-5 py-16 sm:px-8 lg:px-12">
            <h2 class="text-4xl font-black text-orange-500">Tầm nhìn tuyển dụng</h2>
            <div class="mt-8 grid grid-cols-1 gap-5 md:grid-cols-3">
                <div class="border-t-4 border-orange-500 bg-slate-50 p-6">
                    <h3 class="text-xl font-black text-slate-950">Minh bạch</h3>
                    <p class="mt-3 text-sm leading-7 text-slate-600">Tin tuyển dụng có thông tin phòng ban, địa điểm, hạn nộp, yêu cầu và quyền lợi rõ ràng.</p>
                </div>
                <div class="border-t-4 border-cyan-600 bg-slate-50 p-6">
                    <h3 class="text-xl font-black text-slate-950">Nhanh gọn</h3>
                    <p class="mt-3 text-sm leading-7 text-slate-600">Ứng viên gửi hồ sơ trực tiếp trên website mà không cần tài khoản đăng nhập.</p>
                </div>
                <div class="border-t-4 border-emerald-500 bg-slate-50 p-6">
                    <h3 class="text-xl font-black text-slate-950">Tập trung</h3>
                    <p class="mt-3 text-sm leading-7 text-slate-600">Hồ sơ tự động vào trang quản trị ứng viên để HR theo dõi và xử lý.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="tin-tuc" class="bg-slate-50">
        <div class="mx-auto max-w-[1500px] px-5 py-16 sm:px-8 lg:px-12">
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-sm font-black uppercase tracking-wide text-cyan-700">Tin tức</p>
                    <h2 class="mt-2 text-4xl font-black text-slate-950">Cập nhật từ HRM</h2>
                </div>
                <a href="{{ route('public.recruitment.about') }}" class="inline-flex w-full items-center justify-center rounded-2xl bg-white px-6 py-3 text-sm font-black text-slate-700 shadow-sm ring-1 ring-slate-200 transition hover:text-cyan-700 md:w-auto">Xem giới thiệu</a>
            </div>

            <div class="mt-8 grid grid-cols-1 gap-5 lg:grid-cols-3">
                <article class="bg-white p-6 shadow-sm">
                    <p class="text-sm font-black text-orange-500">Văn hóa</p>
                    <h3 class="mt-3 text-2xl font-black text-slate-950">Môi trường làm việc hướng đến con người</h3>
                    <p class="mt-4 text-sm leading-7 text-slate-600">Tập trung vào sự rõ ràng trong quy trình, trách nhiệm và phát triển năng lực cá nhân.</p>
                </article>
                <article class="bg-white p-6 shadow-sm">
                    <p class="text-sm font-black text-orange-500">Quy trình</p>
                    <h3 class="mt-3 text-2xl font-black text-slate-950">Ứng tuyển nhanh trong vài phút</h3>
                    <p class="mt-4 text-sm leading-7 text-slate-600">Chọn vị trí, điền thông tin, đính kèm CV nếu có và gửi hồ sơ trực tiếp.</p>
                </article>
                <article class="bg-white p-6 shadow-sm">
                    <p class="text-sm font-black text-orange-500">Cơ hội</p>
                    <h3 class="mt-3 text-2xl font-black text-slate-950">Nhiều phòng ban đang mở tuyển</h3>
                    <p class="mt-4 text-sm leading-7 text-slate-600">Danh sách việc làm được cập nhật theo trạng thái mở tuyển và hạn nộp hồ sơ.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="bg-white">
        <div class="mx-auto max-w-[1500px] px-5 py-16 sm:px-8 lg:px-12">
            <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-sm font-black uppercase tracking-wide text-cyan-700">Cơ hội nghề nghiệp</p>
                    <h2 class="mt-2 text-4xl font-black text-slate-950">Việc làm mới nhất</h2>
                </div>
                <a href="{{ route('public.recruitment.jobs') }}" class="inline-flex w-full items-center justify-center rounded-2xl bg-orange-500 px-6 py-3 text-sm font-black uppercase text-white transition hover:bg-orange-600 md:w-auto">Xem tất cả</a>
            </div>

            <div class="mt-8 grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($featuredJobPosts as $jobPost)
                    <article class="flex min-w-0 flex-col border border-slate-200 bg-white p-6 shadow-sm transition hover:border-cyan-300 hover:shadow-md">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <p class="text-sm font-black text-cyan-700">{{ $jobPost->department?->department_name ?? 'Chưa gắn phòng ban' }}</p>
                                <h3 class="mt-3 break-words text-2xl font-black text-slate-950">{{ $jobPost->title }}</h3>
                            </div>
                            <span class="shrink-0 rounded-full bg-emerald-50 px-3 py-1 text-xs font-black text-emerald-700">Đang tuyển</span>
                        </div>
                        <p class="mt-5 text-sm text-slate-500">Hạn nộp: <span class="font-black text-slate-700">{{ $jobPost->application_deadline?->format('d/m/Y') ?? 'Không giới hạn' }}</span></p>
                        <div class="mt-auto flex flex-col gap-2 pt-6 sm:flex-row">
                            <a href="{{ route('public.recruitment.show', $jobPost) }}" class="inline-flex w-full items-center justify-center rounded-xl bg-cyan-600 px-4 py-3 text-sm font-black text-white transition hover:bg-cyan-700">Xem chi tiết</a>
                            <a href="{{ route('public.recruitment.apply', $jobPost) }}" class="inline-flex w-full items-center justify-center rounded-xl bg-slate-100 px-4 py-3 text-sm font-black text-slate-700 transition hover:bg-slate-200">Ứng tuyển ngay</a>
                        </div>
                    </article>
                @empty
                    <div class="border border-dashed border-slate-300 bg-white p-8 text-center md:col-span-2 xl:col-span-3">
                        <h3 class="text-lg font-black text-slate-900">Chưa có tin tuyển dụng đang mở</h3>
                        <p class="mt-2 text-sm text-slate-500">Các vị trí mới sẽ được cập nhật tại đây sau khi bộ phận nhân sự mở tuyển.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </section>
@endsection
