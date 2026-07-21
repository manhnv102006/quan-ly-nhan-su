@extends('layouts.recruitment')

@section('title', $jobPost->title)
@section('header_theme', 'dark')

@section('content')
    @php
        $workTypes = [
            'full_time' => 'Full-time',
            'part_time' => 'Part-time',
            'remote' => 'Remote',
            'hybrid' => 'Hybrid',
            'contract' => 'Contract',
        ];
    @endphp

    <div class="bg-[#030712] text-white">
        <section class="relative border-b border-white/10">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(34,211,238,.20),transparent_32%),radial-gradient(circle_at_80%_10%,rgba(249,115,22,.16),transparent_30%)]"></div>
            <div class="relative mx-auto max-w-[1500px] px-5 py-16 sm:px-8 lg:px-12 lg:py-24">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                    <div class="min-w-0">
                        <p class="inline-flex rounded-full border border-cyan-300/20 bg-cyan-300/10 px-4 py-2 text-xs font-black uppercase tracking-[0.25em] text-cyan-200">{{ $jobPost->department?->department_name ?? 'Tin tuyển dụng' }}</p>
                        <h1 class="mt-6 max-w-5xl break-words text-5xl font-black leading-tight tracking-tight sm:text-7xl">{{ $jobPost->title }}</h1>
                    </div>
                    <a href="{{ route('public.recruitment.jobs') }}" class="inline-flex w-full items-center justify-center rounded-2xl border border-white/15 bg-white/5 px-5 py-3 text-sm font-black uppercase text-white transition hover:border-cyan-300/60 hover:bg-cyan-300/10 sm:w-auto">Danh sách tin</a>
                </div>
            </div>
        </section>

        <main class="mx-auto flex max-w-[1500px] flex-col gap-6 px-5 py-12 sm:px-8 lg:flex-row lg:items-start lg:px-12">
            <section class="w-full min-w-0 lg:flex-1">
                @if (session('application_success'))
                    <div class="mb-6 rounded-2xl border border-emerald-300/25 bg-emerald-300/10 px-5 py-4 text-sm font-bold text-emerald-200">
                        {{ session('application_success') }}
                    </div>
                @endif

                <div class="rounded-[2rem] border border-white/10 bg-white/[0.06] p-6 shadow-2xl shadow-black/20 backdrop-blur sm:p-8">
                    <div class="grid grid-cols-1 gap-4 border-b border-white/10 pb-8 sm:grid-cols-2 xl:grid-cols-4">
                        <div class="rounded-2xl bg-black/20 p-4">
                            <p class="text-sm font-bold text-slate-400">Số lượng</p>
                            <p class="mt-1 text-lg font-black">{{ $jobPost->quantity }} người</p>
                        </div>
                        <div class="rounded-2xl bg-black/20 p-4">
                            <p class="text-sm font-bold text-slate-400">Hình thức</p>
                            <p class="mt-1 text-lg font-black">{{ $jobPost->work_type ? ($workTypes[$jobPost->work_type] ?? $jobPost->work_type) : 'Chưa cập nhật' }}</p>
                        </div>
                        <div class="rounded-2xl bg-black/20 p-4">
                            <p class="text-sm font-bold text-slate-400">Mức lương</p>
                            <p class="mt-1 text-lg font-black">
                                @if ($jobPost->salary_min || $jobPost->salary_max)
                                    {{ $jobPost->salary_min ? number_format((float) $jobPost->salary_min, 0, ',', '.') : '0' }}
                                    -
                                    {{ $jobPost->salary_max ? number_format((float) $jobPost->salary_max, 0, ',', '.') : 'Thỏa thuận' }}
                                @else
                                    Thỏa thuận
                                @endif
                            </p>
                        </div>
                        <div class="rounded-2xl bg-black/20 p-4">
                            <p class="text-sm font-bold text-slate-400">Hạn nộp</p>
                            <p class="mt-1 text-lg font-black">{{ $jobPost->application_deadline?->format('d/m/Y') ?? 'Không giới hạn' }}</p>
                        </div>
                    </div>

                    <div class="mt-8 space-y-8">
                        <section>
                            <h2 class="text-2xl font-black text-cyan-300">Mô tả công việc</h2>
                            <div class="mt-4 whitespace-pre-line break-words text-sm leading-8 text-slate-300">{{ $jobPost->description ?: 'Thông tin mô tả công việc sẽ được cập nhật thêm.' }}</div>
                        </section>
                        <section>
                            <h2 class="text-2xl font-black text-cyan-300">Yêu cầu ứng viên</h2>
                            <div class="mt-4 whitespace-pre-line break-words text-sm leading-8 text-slate-300">{{ $jobPost->requirements ?: 'Yêu cầu chi tiết sẽ được trao đổi khi phỏng vấn.' }}</div>
                        </section>
                        <section>
                            <h2 class="text-2xl font-black text-cyan-300">Quyền lợi</h2>
                            <div class="mt-4 whitespace-pre-line break-words text-sm leading-8 text-slate-300">{{ $jobPost->benefits ?: 'Quyền lợi sẽ được trao đổi khi phỏng vấn.' }}</div>
                        </section>
                    </div>
                </div>
            </section>

            <aside class="w-full min-w-0 lg:w-96 lg:shrink-0">
                <div class="rounded-[2rem] border border-cyan-300/20 bg-cyan-300/10 p-6 shadow-2xl shadow-cyan-950/30 lg:sticky lg:top-32">
                    <span class="inline-flex rounded-full bg-emerald-400/10 px-3 py-1 text-xs font-black text-emerald-300">Đang tuyển</span>
                    <h2 class="mt-4 text-2xl font-black">Ứng tuyển vị trí này</h2>
                    <p class="mt-3 text-sm leading-7 text-slate-300">Gửi thông tin cá nhân và CV nếu có. Hồ sơ sẽ được chuyển đến bộ phận tuyển dụng.</p>
                    <a href="{{ route('public.recruitment.apply', $jobPost) }}" class="mt-6 inline-flex w-full items-center justify-center rounded-2xl bg-orange-500 px-5 py-4 text-sm font-black uppercase text-white transition hover:bg-orange-600">Ứng tuyển ngay</a>
                    <div class="mt-6 rounded-2xl border border-white/10 bg-black/20 p-4 text-sm leading-7 text-slate-300">
                        <p><span class="font-black text-white">Địa điểm:</span> {{ $jobPost->work_location ?: 'Trao đổi khi phỏng vấn' }}</p>
                        @if ($jobPost->recruiter)
                            <p class="mt-2"><span class="font-black text-white">Phụ trách:</span> {{ $jobPost->recruiter->full_name }}</p>
                        @endif
                    </div>
                </div>
            </aside>
        </main>
    </div>
@endsection
