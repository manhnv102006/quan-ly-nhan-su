@extends('layouts.recruitment')

@section('title', $jobPost->title)

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

    <section class="bg-white">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <p class="text-sm font-black text-cyan-700">{{ $jobPost->department?->department_name ?? 'Tin tuyển dụng' }}</p>
                    <h1 class="mt-2 break-words text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">{{ $jobPost->title }}</h1>
                </div>
                <a href="{{ route('public.recruitment.jobs') }}" class="inline-flex w-full items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-black text-slate-700 transition hover:border-cyan-300 hover:text-cyan-700 sm:w-auto">Danh sách tin</a>
            </div>
        </div>
    </section>

    <main class="mx-auto flex max-w-7xl flex-col gap-6 px-4 py-8 sm:px-6 lg:flex-row lg:items-start lg:px-8">
        <section class="w-full min-w-0 lg:flex-1">
            @if (session('application_success'))
                <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-700">
                    {{ session('application_success') }}
                </div>
            @endif

            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="grid grid-cols-1 gap-4 border-b border-slate-100 pb-6 sm:grid-cols-2">
                    <div>
                        <p class="text-sm font-bold text-slate-500">Số lượng</p>
                        <p class="mt-1 text-base font-black text-slate-900">{{ $jobPost->quantity }} người</p>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-500">Hình thức</p>
                        <p class="mt-1 text-base font-black text-slate-900">{{ $jobPost->work_type ? ($workTypes[$jobPost->work_type] ?? $jobPost->work_type) : 'Chưa cập nhật' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-500">Mức lương</p>
                        <p class="mt-1 text-base font-black text-slate-900">
                            @if ($jobPost->salary_min || $jobPost->salary_max)
                                {{ $jobPost->salary_min ? number_format((float) $jobPost->salary_min, 0, ',', '.') : '0' }}
                                -
                                {{ $jobPost->salary_max ? number_format((float) $jobPost->salary_max, 0, ',', '.') : 'Thỏa thuận' }}
                            @else
                                Thỏa thuận
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-500">Hạn nộp</p>
                        <p class="mt-1 text-base font-black text-slate-900">{{ $jobPost->application_deadline?->format('d/m/Y') ?? 'Không giới hạn' }}</p>
                    </div>
                </div>

                <div class="mt-6 space-y-7">
                    <section>
                        <h2 class="text-xl font-black text-slate-950">Mô tả công việc</h2>
                        <div class="mt-3 whitespace-pre-line break-words text-sm leading-7 text-slate-600">{{ $jobPost->description ?: 'Thông tin mô tả công việc sẽ được cập nhật thêm.' }}</div>
                    </section>

                    <section>
                        <h2 class="text-xl font-black text-slate-950">Yêu cầu ứng viên</h2>
                        <div class="mt-3 whitespace-pre-line break-words text-sm leading-7 text-slate-600">{{ $jobPost->requirements ?: 'Yêu cầu chi tiết sẽ được trao đổi khi phỏng vấn.' }}</div>
                    </section>

                    <section>
                        <h2 class="text-xl font-black text-slate-950">Quyền lợi</h2>
                        <div class="mt-3 whitespace-pre-line break-words text-sm leading-7 text-slate-600">{{ $jobPost->benefits ?: 'Quyền lợi sẽ được trao đổi khi phỏng vấn.' }}</div>
                    </section>
                </div>
            </div>
        </section>

        <aside class="w-full min-w-0 lg:w-96 lg:shrink-0">
            <div class="rounded-xl border border-cyan-100 bg-white p-5 shadow-sm lg:sticky lg:top-24">
                <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">Đang tuyển</span>
                <h2 class="mt-4 text-xl font-black text-slate-950">Ứng tuyển vị trí này</h2>
                <p class="mt-2 text-sm leading-6 text-slate-600">Gửi thông tin cá nhân và CV nếu có. Hồ sơ sẽ được chuyển đến bộ phận tuyển dụng.</p>

                <a href="{{ route('public.recruitment.apply', $jobPost) }}" class="mt-5 inline-flex w-full items-center justify-center rounded-xl bg-cyan-600 px-4 py-3 text-sm font-black text-white transition hover:bg-cyan-700">Ứng tuyển ngay</a>

                <div class="mt-5 rounded-xl bg-slate-50 p-4 text-sm leading-6 text-slate-600">
                    <p><span class="font-black text-slate-900">Địa điểm:</span> {{ $jobPost->work_location ?: 'Trao đổi khi phỏng vấn' }}</p>
                    @if ($jobPost->recruiter)
                        <p class="mt-2"><span class="font-black text-slate-900">Phụ trách:</span> {{ $jobPost->recruiter->full_name }}</p>
                    @endif
                </div>
            </div>
        </aside>
    </main>
@endsection
