<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $jobPost->title }} - {{ config('app.name', 'Quản lý nhân sự') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-slate-50 font-sans text-slate-900 antialiased">
        @php
            $workTypes = [
                'full_time' => 'Full-time',
                'part_time' => 'Part-time',
                'remote' => 'Remote',
                'hybrid' => 'Hybrid',
                'contract' => 'Contract',
            ];
        @endphp

        <div class="min-h-screen">
            <header class="border-b border-slate-200 bg-white">
                <div class="mx-auto flex max-w-7xl flex-col gap-4 px-4 py-5 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
                    <a href="{{ route('public.recruitment.index') }}" class="flex min-w-0 items-center gap-3">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                            <x-application-logo class="h-10 w-10 object-contain" />
                        </div>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-bold text-slate-500">{{ config('app.name', 'Quản lý nhân sự') }}</p>
                            <h1 class="truncate text-xl font-black text-slate-950">Chi tiết tuyển dụng</h1>
                        </div>
                    </a>

                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                        <a href="{{ route('public.recruitment.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:border-cyan-300 hover:text-cyan-700">
                            Danh sách tin
                        </a>
                        <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:border-cyan-300 hover:text-cyan-700">
                            Đăng nhập
                        </a>
                    </div>
                </div>
            </header>

            <main class="mx-auto grid max-w-7xl grid-cols-1 gap-6 px-4 py-8 sm:px-6 lg:grid-cols-12 lg:px-8">
                <section class="lg:col-span-8">
                    <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                        <p class="text-sm font-bold text-cyan-700">{{ $jobPost->department?->department_name ?? 'Chưa gắn phòng ban' }}</p>
                        <h2 class="mt-3 break-words text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">{{ $jobPost->title }}</h2>
                        <p class="mt-4 text-sm leading-6 text-slate-600">
                            {{ $jobPost->work_location ?: 'Địa điểm sẽ được trao đổi khi phỏng vấn.' }}
                        </p>

                        <div class="mt-6 grid grid-cols-1 gap-4 border-y border-slate-100 py-5 sm:grid-cols-2">
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

                        <div class="mt-6 space-y-6">
                            <section>
                                <h3 class="text-lg font-black text-slate-950">Mô tả công việc</h3>
                                <div class="mt-3 whitespace-pre-line break-words text-sm leading-7 text-slate-600">{{ $jobPost->description ?: 'Thông tin mô tả công việc sẽ được cập nhật thêm.' }}</div>
                            </section>

                            <section>
                                <h3 class="text-lg font-black text-slate-950">Yêu cầu ứng viên</h3>
                                <div class="mt-3 whitespace-pre-line break-words text-sm leading-7 text-slate-600">{{ $jobPost->requirements ?: 'Yêu cầu chi tiết sẽ được trao đổi khi phỏng vấn.' }}</div>
                            </section>

                            <section>
                                <h3 class="text-lg font-black text-slate-950">Quyền lợi</h3>
                                <div class="mt-3 whitespace-pre-line break-words text-sm leading-7 text-slate-600">{{ $jobPost->benefits ?: 'Quyền lợi sẽ được trao đổi khi phỏng vấn.' }}</div>
                            </section>
                        </div>
                    </div>
                </section>

                <aside class="lg:col-span-4">
                    <div class="sticky top-6 rounded-lg border border-cyan-100 bg-white p-5 shadow-sm">
                        <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">Đang tuyển</span>
                        <h3 class="mt-4 text-xl font-black text-slate-950">Ứng tuyển vị trí này</h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            Gửi thông tin cá nhân và CV nếu có. Hồ sơ sẽ được chuyển đến bộ phận tuyển dụng.
                        </p>

                        <a href="{{ route('public.recruitment.apply', $jobPost) }}" class="mt-5 inline-flex w-full items-center justify-center rounded-xl bg-cyan-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-cyan-700">
                            Ứng tuyển ngay
                        </a>

                        @if ($jobPost->recruiter)
                            <div class="mt-5 rounded-lg bg-slate-50 p-4">
                                <p class="text-sm font-bold text-slate-500">Người phụ trách</p>
                                <p class="mt-1 break-words text-sm font-black text-slate-900">{{ $jobPost->recruiter->full_name }}</p>
                            </div>
                        @endif
                    </div>
                </aside>
            </main>
        </div>
    </body>
</html>
