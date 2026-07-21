@extends('layouts.recruitment')

@section('title', 'Việc làm đang tuyển')

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
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
            <p class="text-sm font-black uppercase tracking-wide text-cyan-700">Tuyển dụng</p>
            <div class="mt-3 flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <h1 class="text-3xl font-black tracking-tight text-slate-950 sm:text-4xl">Việc làm đang tuyển</h1>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600">
                        Xem các vị trí đang mở và chọn công việc phù hợp để gửi hồ sơ ứng tuyển.
                    </p>
                </div>
                <p class="text-sm font-bold text-slate-500">{{ $jobPosts->total() }} tin đang hiển thị</p>
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($jobPosts as $jobPost)
                <article class="flex h-full min-w-0 flex-col rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-cyan-200 hover:shadow-md">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-cyan-700">{{ $jobPost->department?->department_name ?? 'Chưa gắn phòng ban' }}</p>
                            <h2 class="mt-2 break-words text-xl font-black leading-7 text-slate-950">{{ $jobPost->title }}</h2>
                        </div>
                        <span class="shrink-0 rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">Đang tuyển</span>
                    </div>

                    <dl class="mt-5 grid grid-cols-1 gap-3 text-sm text-slate-600">
                        <div>
                            <dt class="font-bold text-slate-800">Số lượng</dt>
                            <dd class="mt-1">{{ $jobPost->quantity }} người</dd>
                        </div>
                        <div>
                            <dt class="font-bold text-slate-800">Địa điểm</dt>
                            <dd class="mt-1">{{ $jobPost->work_location ?: 'Trao đổi khi phỏng vấn' }}</dd>
                        </div>
                        <div>
                            <dt class="font-bold text-slate-800">Hình thức</dt>
                            <dd class="mt-1">{{ $jobPost->work_type ? ($workTypes[$jobPost->work_type] ?? $jobPost->work_type) : 'Chưa cập nhật' }}</dd>
                        </div>
                        <div>
                            <dt class="font-bold text-slate-800">Mức lương</dt>
                            <dd class="mt-1">
                                @if ($jobPost->salary_min || $jobPost->salary_max)
                                    {{ $jobPost->salary_min ? number_format((float) $jobPost->salary_min, 0, ',', '.') : '0' }}
                                    -
                                    {{ $jobPost->salary_max ? number_format((float) $jobPost->salary_max, 0, ',', '.') : 'Thỏa thuận' }}
                                @else
                                    Thỏa thuận
                                @endif
                            </dd>
                        </div>
                    </dl>

                    <div class="mt-5 border-t border-slate-100 pt-4">
                        <p class="text-sm text-slate-500">
                            Hạn nộp:
                            <span class="font-bold text-slate-800">{{ $jobPost->application_deadline?->format('d/m/Y') ?? 'Không giới hạn' }}</span>
                        </p>
                    </div>

                    <div class="mt-auto flex flex-col gap-2 pt-5 sm:flex-row">
                        <a href="{{ route('public.recruitment.show', $jobPost) }}" class="inline-flex w-full items-center justify-center rounded-xl bg-cyan-600 px-4 py-3 text-sm font-black text-white transition hover:bg-cyan-700">Xem chi tiết</a>
                        <a href="{{ route('public.recruitment.apply', $jobPost) }}" class="inline-flex w-full items-center justify-center rounded-xl bg-slate-100 px-4 py-3 text-sm font-black text-slate-700 transition hover:bg-slate-200">Ứng tuyển</a>
                    </div>
                </article>
            @empty
                <div class="rounded-xl border border-dashed border-slate-300 bg-white p-8 text-center md:col-span-2 xl:col-span-3">
                    <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-bold uppercase tracking-wide text-slate-600">Tạm chưa mở tuyển</span>
                    <h2 class="mt-4 text-lg font-black text-slate-900">Chưa có tin tuyển dụng đang mở</h2>
                    <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500">Các vị trí mới sẽ được cập nhật tại đây sau khi bộ phận nhân sự mở tuyển.</p>
                </div>
            @endforelse
        </div>

        <div class="mt-8 overflow-x-auto">
            {{ $jobPosts->links() }}
        </div>
    </section>
@endsection
