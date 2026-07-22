@extends('layouts.recruitment')

@section('title', $jobPost->title)

@section('content')
    @php
        $workTypes = [
            'full_time' => 'Full-time',
            'part_time' => 'Part-time',
            'remote' => 'Remote',
            'hybrid' => 'Hybrid',
            'contract' => 'Hợp đồng',
        ];
    @endphp

    <div class="bg-white text-slate-900">
        <section class="border-b border-slate-100 bg-gradient-to-b from-slate-50 to-white">
            <div class="mx-auto flex w-[83%] flex-col gap-6 py-12 lg:flex-row lg:items-end lg:justify-between lg:py-16">
                <div class="min-w-0">
                    <p class="text-sm font-bold text-cyan-700">{{ $jobPost->department?->department_name ?? 'Tin tuyển dụng' }}</p>
                    <h1 class="mt-3 break-words text-3xl font-black leading-tight text-slate-900 sm:text-4xl lg:text-5xl">{{ $jobPost->title }}</h1>
                </div>
                <a href="{{ route('public.recruitment.jobs') }}"
                   class="inline-flex w-full items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-800 transition hover:border-cyan-300 sm:w-auto">
                    Danh sách tin
                </a>
            </div>
        </section>

        <main class="mx-auto flex w-[83%] flex-col gap-8 py-10 lg:flex-row lg:items-start lg:py-12">
            <section class="w-full min-w-0 lg:flex-1">
                @if (session('application_success'))
                    <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-800">
                        {{ session('application_success') }}
                    </div>
                @endif

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                    <div class="grid grid-cols-1 gap-4 border-b border-slate-100 pb-8 sm:grid-cols-2 xl:grid-cols-4">
                        <div class="rounded-xl bg-slate-50 p-4">
                            <p class="text-sm font-semibold text-slate-500">Số lượng</p>
                            <p class="mt-1 text-lg font-bold text-slate-900">{{ $jobPost->quantity }} người</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-4">
                            <p class="text-sm font-semibold text-slate-500">Hình thức</p>
                            <p class="mt-1 text-lg font-bold text-slate-900">{{ $jobPost->work_type ? ($workTypes[$jobPost->work_type] ?? $jobPost->work_type) : 'Chưa cập nhật' }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-4">
                            <p class="text-sm font-semibold text-slate-500">Mức lương</p>
                            <p class="mt-1 text-lg font-bold text-slate-900">
                                @if ($jobPost->salary_min || $jobPost->salary_max)
                                    {{ $jobPost->salary_min ? number_format((float) $jobPost->salary_min, 0, ',', '.') : '0' }}
                                    –
                                    {{ $jobPost->salary_max ? number_format((float) $jobPost->salary_max, 0, ',', '.') : 'Thỏa thuận' }}
                                @else
                                    Thỏa thuận
                                @endif
                            </p>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-4">
                            <p class="text-sm font-semibold text-slate-500">Hạn nộp</p>
                            <p class="mt-1 text-lg font-bold text-slate-900">{{ $jobPost->application_deadline?->format('d/m/Y') ?? 'Không giới hạn' }}</p>
                        </div>
                    </div>

                    <div class="mt-8 space-y-8">
                        <section>
                            <h2 class="text-xl font-bold text-slate-900">Mô tả công việc</h2>
                            <div class="mt-4 whitespace-pre-line break-words text-sm leading-7 text-slate-600">{{ $jobPost->description ?: 'Thông tin mô tả công việc sẽ được cập nhật thêm.' }}</div>
                        </section>
                        <section>
                            <h2 class="text-xl font-bold text-slate-900">Yêu cầu ứng viên</h2>
                            <div class="mt-4 whitespace-pre-line break-words text-sm leading-7 text-slate-600">{{ $jobPost->requirements ?: 'Yêu cầu chi tiết sẽ được trao đổi khi phỏng vấn.' }}</div>
                        </section>
                        <section>
                            <h2 class="text-xl font-bold text-slate-900">Quyền lợi</h2>
                            <div class="mt-4 whitespace-pre-line break-words text-sm leading-7 text-slate-600">{{ $jobPost->benefits ?: 'Quyền lợi sẽ được trao đổi khi phỏng vấn.' }}</div>
                        </section>
                    </div>
                </div>
            </section>

            <aside class="w-full min-w-0 lg:w-96 lg:shrink-0">
                <div class="rounded-2xl border border-cyan-100 bg-cyan-50/50 p-6 shadow-sm lg:sticky lg:top-32">
                    <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">Đang tuyển</span>
                    <h2 class="mt-4 text-2xl font-bold text-slate-900">Ứng tuyển vị trí này</h2>
                    <p class="mt-3 text-sm leading-relaxed text-slate-600">Gửi thông tin cá nhân và CV nếu có. Hồ sơ sẽ được chuyển đến bộ phận tuyển dụng.</p>
                    <a href="{{ route('public.recruitment.apply', $jobPost) }}"
                       class="mt-6 inline-flex w-full items-center justify-center rounded-xl bg-cyan-600 px-5 py-3.5 text-sm font-bold text-white transition hover:bg-cyan-700">
                        Ứng tuyển ngay
                    </a>
                    <div class="mt-6 rounded-xl border border-slate-200 bg-white p-4 text-sm leading-relaxed text-slate-600">
                        <p><span class="font-bold text-slate-800">Địa điểm:</span> {{ $jobPost->work_location ?: 'Trao đổi khi phỏng vấn' }}</p>
                        @if ($jobPost->recruiter)
                            <p class="mt-2"><span class="font-bold text-slate-800">Phụ trách:</span> {{ $jobPost->recruiter->full_name }}</p>
                        @endif
                    </div>
                </div>
            </aside>
        </main>
    </div>
@endsection
