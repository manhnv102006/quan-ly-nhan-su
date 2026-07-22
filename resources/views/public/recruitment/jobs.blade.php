@extends('layouts.recruitment')

@section('title', 'Cơ hội nghề nghiệp')

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
        <section class="relative overflow-hidden border-b border-slate-100 bg-gradient-to-b from-slate-50 via-white to-white">
            <div class="pointer-events-none absolute -right-24 top-0 h-72 w-72 rounded-full bg-cyan-100/60 blur-3xl"></div>
            <div class="pointer-events-none absolute -left-16 bottom-0 h-56 w-56 rounded-full bg-orange-100/50 blur-3xl"></div>

            <div class="relative mx-auto flex w-[83%] flex-col gap-8 py-14 lg:flex-row lg:items-end lg:justify-between lg:py-20">
                <div class="min-w-0">
                    <p class="text-sm font-bold uppercase tracking-wider text-cyan-700">Cơ hội nghề nghiệp</p>
                    <h1 class="mt-3 max-w-3xl text-3xl font-black leading-tight text-slate-900 sm:text-4xl lg:text-5xl">
                        Chọn vị trí phù hợp và ứng tuyển trực tiếp
                    </h1>
                    <p class="mt-4 max-w-2xl text-base leading-relaxed text-slate-600">
                        Tất cả vị trí đang mở đều có thông tin rõ ràng để bạn xem nhanh, so sánh và gửi hồ sơ mà không cần đăng nhập.
                    </p>
                </div>
                <div class="shrink-0 rounded-2xl border border-slate-200 bg-white px-8 py-5 text-center shadow-sm">
                    <p class="text-4xl font-black text-cyan-700">{{ $jobPosts->total() }}</p>
                    <p class="mt-1 text-xs font-semibold uppercase tracking-wide text-slate-500">Tin đang hiển thị</p>
                </div>
            </div>
        </section>

        <section class="mx-auto w-[83%] py-12 lg:py-16">
            <div class="flex flex-col gap-5">
                @forelse ($jobPosts as $jobPost)
                    <article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:border-cyan-200 hover:shadow-md">
                        <div class="flex flex-col lg:flex-row lg:items-stretch">
                            <div class="min-w-0 flex-1 p-6 sm:p-8">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-cyan-700">{{ $jobPost->department?->department_name ?? 'Chưa gắn phòng ban' }}</p>
                                        <h2 class="mt-2 text-2xl font-black text-slate-900">{{ $jobPost->title }}</h2>
                                    </div>
                                    <span class="shrink-0 rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">Đang tuyển</span>
                                </div>

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
                        <h2 class="text-lg font-bold text-slate-900">Chưa có tin tuyển dụng đang mở</h2>
                        <p class="mt-2 text-sm text-slate-500">Các vị trí mới sẽ được cập nhật tại đây sau khi bộ phận nhân sự mở tuyển.</p>
                    </div>
                @endforelse
            </div>

            @if ($jobPosts->hasPages())
                <div class="mt-8">
                    {{ $jobPosts->links() }}
                </div>
            @endif
        </section>
    </div>
@endsection
