@extends('layouts.recruitment')

@section('title', 'Cơ hội nghề nghiệp')
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

    <div class="overflow-hidden bg-[#030712] text-white">
        <section class="relative border-b border-white/10">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_18%_20%,rgba(34,211,238,.22),transparent_30%),radial-gradient(circle_at_82%_8%,rgba(249,115,22,.18),transparent_30%)]"></div>
            <div class="relative mx-auto max-w-[1500px] px-5 py-20 sm:px-8 lg:px-12 lg:py-28">
                <div class="flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <p class="inline-flex rounded-full border border-cyan-300/20 bg-cyan-300/10 px-4 py-2 text-xs font-black uppercase tracking-[0.25em] text-cyan-200">Cơ hội nghề nghiệp</p>
                        <h1 class="mt-7 max-w-5xl text-5xl font-black leading-tight tracking-tight sm:text-7xl">Chọn vị trí phù hợp và ứng tuyển trực tiếp</h1>
                        <p class="mt-6 max-w-3xl text-base leading-8 text-slate-300">Tất cả vị trí đang mở đều có thông tin rõ ràng để bạn xem nhanh, so sánh và gửi hồ sơ mà không cần đăng nhập.</p>
                    </div>
                    <div class="rounded-3xl border border-white/10 bg-white/[0.06] p-6 text-center">
                        <p class="text-5xl font-black text-orange-400">{{ $jobPosts->total() }}</p>
                        <p class="mt-2 text-sm font-black uppercase tracking-wide text-slate-300">Tin đang hiển thị</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="mx-auto max-w-[1500px] px-5 py-16 sm:px-8 lg:px-12">
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
                @forelse ($jobPosts as $jobPost)
                    <article class="group flex h-full min-w-0 flex-col rounded-[2rem] border border-white/10 bg-white/[0.06] p-6 shadow-2xl shadow-black/20 backdrop-blur transition duration-300 hover:-translate-y-2 hover:border-cyan-300/50 hover:bg-white/[0.09]">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <p class="text-sm font-black text-cyan-300">{{ $jobPost->department?->department_name ?? 'Chưa gắn phòng ban' }}</p>
                                <h2 class="mt-3 break-words text-2xl font-black leading-tight text-white">{{ $jobPost->title }}</h2>
                            </div>
                            <span class="shrink-0 rounded-full bg-emerald-400/10 px-3 py-1 text-xs font-black text-emerald-300">Đang tuyển</span>
                        </div>

                        <dl class="mt-6 grid grid-cols-1 gap-3 text-sm text-slate-300">
                            <div class="rounded-2xl border border-white/10 bg-black/20 p-4">
                                <dt class="font-black text-white">Số lượng</dt>
                                <dd class="mt-1">{{ $jobPost->quantity }} người</dd>
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-black/20 p-4">
                                <dt class="font-black text-white">Địa điểm</dt>
                                <dd class="mt-1">{{ $jobPost->work_location ?: 'Trao đổi khi phỏng vấn' }}</dd>
                            </div>
                            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                <div class="rounded-2xl border border-white/10 bg-black/20 p-4">
                                    <dt class="font-black text-white">Hình thức</dt>
                                    <dd class="mt-1">{{ $jobPost->work_type ? ($workTypes[$jobPost->work_type] ?? $jobPost->work_type) : 'Chưa cập nhật' }}</dd>
                                </div>
                                <div class="rounded-2xl border border-white/10 bg-black/20 p-4">
                                    <dt class="font-black text-white">Hạn nộp</dt>
                                    <dd class="mt-1">{{ $jobPost->application_deadline?->format('d/m/Y') ?? 'Không giới hạn' }}</dd>
                                </div>
                            </div>
                        </dl>

                        <div class="mt-6 rounded-2xl border border-orange-300/20 bg-orange-300/10 p-4 text-sm text-slate-300">
                            <span class="font-black text-white">Mức lương:</span>
                            @if ($jobPost->salary_min || $jobPost->salary_max)
                                {{ $jobPost->salary_min ? number_format((float) $jobPost->salary_min, 0, ',', '.') : '0' }}
                                -
                                {{ $jobPost->salary_max ? number_format((float) $jobPost->salary_max, 0, ',', '.') : 'Thỏa thuận' }}
                            @else
                                Thỏa thuận
                            @endif
                        </div>

                        <div class="mt-auto flex flex-col gap-2 pt-6 sm:flex-row">
                            <a href="{{ route('public.recruitment.show', $jobPost) }}" class="inline-flex w-full items-center justify-center rounded-2xl bg-cyan-500 px-4 py-3 text-sm font-black text-white transition hover:bg-cyan-600">Xem chi tiết</a>
                            <a href="{{ route('public.recruitment.apply', $jobPost) }}" class="inline-flex w-full items-center justify-center rounded-2xl bg-white px-4 py-3 text-sm font-black text-slate-950 transition hover:bg-slate-100">Ứng tuyển</a>
                        </div>
                    </article>
                @empty
                    <div class="rounded-[2rem] border border-dashed border-white/15 bg-white/[0.04] p-10 text-center md:col-span-2 xl:col-span-3">
                        <span class="inline-flex rounded-full bg-white/10 px-3 py-1 text-xs font-black uppercase tracking-wide text-slate-300">Tạm chưa mở tuyển</span>
                        <h2 class="mt-4 text-2xl font-black text-white">Chưa có tin tuyển dụng đang mở</h2>
                        <p class="mx-auto mt-3 max-w-md text-sm leading-7 text-slate-400">Các vị trí mới sẽ được cập nhật tại đây sau khi bộ phận nhân sự mở tuyển.</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-10 overflow-x-auto rounded-2xl border border-white/10 bg-white/[0.04] p-4">
                {{ $jobPosts->links() }}
            </div>
        </section>
    </div>
@endsection
