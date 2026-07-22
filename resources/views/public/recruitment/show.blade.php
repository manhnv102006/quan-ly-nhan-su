@extends('layouts.recruitment')

@section('title', $jobPost->title)

@section('content')
    @php
        $workTypes = __('recruitment.work_types');
    @endphp

    <div class="bg-white text-slate-900">
        <section class="border-b border-slate-100 bg-gradient-to-b from-slate-50 to-white">
            <div class="mx-auto flex w-[83%] flex-col gap-6 py-12 lg:flex-row lg:items-end lg:justify-between lg:py-16">
                <div class="min-w-0">
                    <p class="text-sm font-bold text-cyan-700">{{ $jobPost->department?->department_name ?? __('recruitment.common.job_post') }}</p>
                    <h1 class="mt-3 break-words text-3xl font-black leading-tight text-slate-900 sm:text-4xl lg:text-5xl">{{ $jobPost->title }}</h1>
                </div>
                <a href="{{ route('public.recruitment.jobs') }}"
                   class="inline-flex w-full items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-800 transition hover:border-cyan-300 sm:w-auto">
                    {{ __('recruitment.show.job_list') }}
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
                            <p class="text-sm font-semibold text-slate-500">{{ __('recruitment.common.quantity') }}</p>
                            <p class="mt-1 text-lg font-bold text-slate-900">{{ __('recruitment.common.people', ['count' => $jobPost->quantity]) }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-4">
                            <p class="text-sm font-semibold text-slate-500">{{ __('recruitment.common.work_type') }}</p>
                            <p class="mt-1 text-lg font-bold text-slate-900">{{ $jobPost->work_type ? ($workTypes[$jobPost->work_type] ?? $jobPost->work_type) : __('recruitment.common.not_updated') }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-4">
                            <p class="text-sm font-semibold text-slate-500">{{ __('recruitment.common.salary') }}</p>
                            <p class="mt-1 text-lg font-bold text-slate-900">
                                @if ($jobPost->salary_min || $jobPost->salary_max)
                                    {{ $jobPost->salary_min ? number_format((float) $jobPost->salary_min, 0, ',', '.') : '0' }}
                                    –
                                    {{ $jobPost->salary_max ? number_format((float) $jobPost->salary_max, 0, ',', '.') : __('recruitment.common.negotiable') }}
                                @else
                                    {{ __('recruitment.common.negotiable') }}
                                @endif
                            </p>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-4">
                            <p class="text-sm font-semibold text-slate-500">{{ __('recruitment.common.deadline') }}</p>
                            <p class="mt-1 text-lg font-bold text-slate-900">{{ $jobPost->application_deadline?->format('d/m/Y') ?? __('recruitment.common.no_deadline') }}</p>
                        </div>
                    </div>

                    <div class="mt-8 space-y-8">
                        <section>
                            <h2 class="text-xl font-bold text-slate-900">{{ __('recruitment.show.description') }}</h2>
                            <div class="mt-4 whitespace-pre-line break-words text-sm leading-7 text-slate-600">{{ $jobPost->description ?: __('recruitment.show.description_empty') }}</div>
                        </section>
                        <section>
                            <h2 class="text-xl font-bold text-slate-900">{{ __('recruitment.show.requirements') }}</h2>
                            <div class="mt-4 whitespace-pre-line break-words text-sm leading-7 text-slate-600">{{ $jobPost->requirements ?: __('recruitment.show.requirements_empty') }}</div>
                        </section>
                        <section>
                            <h2 class="text-xl font-bold text-slate-900">{{ __('recruitment.show.benefits') }}</h2>
                            <div class="mt-4 whitespace-pre-line break-words text-sm leading-7 text-slate-600">{{ $jobPost->benefits ?: __('recruitment.show.benefits_empty') }}</div>
                        </section>
                    </div>
                </div>
            </section>

            <aside class="w-full min-w-0 lg:w-96 lg:shrink-0">
                <div class="rounded-2xl border border-cyan-100 bg-cyan-50/50 p-6 shadow-sm lg:sticky lg:top-32">
                    <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">{{ __('recruitment.common.hiring') }}</span>
                    <h2 class="mt-4 text-2xl font-bold text-slate-900">{{ __('recruitment.show.apply_title') }}</h2>
                    <p class="mt-3 text-sm leading-relaxed text-slate-600">{{ __('recruitment.show.apply_hint') }}</p>
                    <a href="{{ route('public.recruitment.apply', $jobPost) }}"
                       class="mt-6 inline-flex w-full items-center justify-center rounded-xl bg-cyan-600 px-5 py-3.5 text-sm font-bold text-white transition hover:bg-cyan-700">
                        {{ __('recruitment.show.apply_now') }}
                    </a>
                    <div class="mt-6 rounded-xl border border-slate-200 bg-white p-4 text-sm leading-relaxed text-slate-600">
                        <p><span class="font-bold text-slate-800">{{ __('recruitment.common.location') }}:</span> {{ $jobPost->work_location ?: __('recruitment.common.location_tbd') }}</p>
                        @if ($jobPost->recruiter)
                            <p class="mt-2"><span class="font-bold text-slate-800">{{ __('recruitment.common.recruiter') }}:</span> {{ $jobPost->recruiter->full_name }}</p>
                        @endif
                    </div>
                </div>
            </aside>
        </main>
    </div>
@endsection
