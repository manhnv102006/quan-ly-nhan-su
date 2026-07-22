@extends('layouts.recruitment')

@section('title', __('recruitment.apply.title', ['title' => $jobPost->title]))

@section('content')
    @php
        $inputClass = 'w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20';
    @endphp

    <div class="bg-white text-slate-900">
        <section class="border-b border-slate-100 bg-gradient-to-b from-slate-50 to-white">
            <div class="mx-auto flex w-[83%] flex-col gap-6 py-12 lg:flex-row lg:items-end lg:justify-between lg:py-16">
                <div class="min-w-0">
                    <p class="text-sm font-bold text-cyan-700">{{ $jobPost->department?->department_name ?? __('recruitment.common.job_post') }}</p>
                    <h1 class="mt-3 break-words text-3xl font-black leading-tight text-slate-900 sm:text-4xl">{{ __('recruitment.apply.title', ['title' => $jobPost->title]) }}</h1>
                </div>
                <a href="{{ route('public.recruitment.show', $jobPost) }}"
                   class="inline-flex w-full items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-800 transition hover:border-cyan-300 sm:w-auto">
                    {{ __('recruitment.apply.back_to_job') }}
                </a>
            </div>
        </section>

        <main class="mx-auto flex w-[83%] flex-col gap-8 py-10 lg:flex-row lg:items-start lg:py-12">
            <section class="w-full min-w-0 lg:flex-1">
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-5 sm:px-8">
                        <h2 class="text-xl font-bold text-slate-900">{{ __('recruitment.apply.form_title') }}</h2>
                        <p class="mt-2 text-sm text-slate-600">{{ __('recruitment.apply.form_hint') }}</p>
                    </div>

                    <div class="p-5 sm:p-8">
                        @if (isset($errors) && $errors->any())
                            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                                <p class="font-semibold">{{ __('recruitment.common.check_form') }}</p>
                                <ul class="mt-2 list-disc space-y-1 pl-5">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('public.recruitment.apply.store', $jobPost) }}" method="POST" enctype="multipart/form-data" data-vi-html5-validation class="space-y-5">
                            @csrf

                            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                                <div>
                                    <label for="full_name" class="mb-2 block text-sm font-semibold text-slate-700">{{ __('recruitment.apply.full_name') }} <span class="text-red-500">*</span></label>
                                    <input id="full_name" type="text" name="full_name" value="{{ old('full_name') }}" maxlength="100" required placeholder="{{ __('recruitment.apply.full_name_placeholder') }}" class="{{ $inputClass }} @error('full_name') border-red-400 @enderror">
                                    @error('full_name')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label for="phone" class="mb-2 block text-sm font-semibold text-slate-700">{{ __('recruitment.apply.phone') }} <span class="text-red-500">*</span></label>
                                    <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" inputmode="tel" maxlength="12" required placeholder="{{ __('recruitment.apply.phone_placeholder') }}" class="{{ $inputClass }} @error('phone') border-red-400 @enderror">
                                    <p class="mt-1 text-xs text-slate-500">{{ __('recruitment.apply.phone_hint') }}</p>
                                    @error('phone')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                                </div>
                            </div>

                            <div>
                                <label for="email" class="mb-2 block text-sm font-semibold text-slate-700">{{ __('recruitment.apply.email') }} <span class="text-red-500">*</span></label>
                                <input id="email" type="email" name="email" value="{{ old('email') }}" maxlength="100" required placeholder="{{ __('recruitment.apply.email_placeholder') }}" class="{{ $inputClass }} @error('email') border-red-400 @enderror">
                                @error('email')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="cv_file" class="mb-2 block text-sm font-semibold text-slate-700">{{ __('recruitment.apply.cv') }} <span class="text-red-500">*</span></label>
                                <input id="cv_file" type="file" name="cv_file" accept=".pdf,.doc,.docx" required class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 file:mr-3 file:rounded-lg file:border-0 file:bg-cyan-600 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white @error('cv_file') border-red-400 @enderror">
                                <p class="mt-2 text-xs text-slate-500">{{ __('recruitment.apply.cv_hint') }}</p>
                                @error('cv_file')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div class="flex flex-col-reverse gap-3 pt-2 sm:flex-row sm:items-center">
                                <a href="{{ route('public.recruitment.show', $jobPost) }}" class="inline-flex w-full items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-800 transition hover:border-slate-300 sm:w-auto">{{ __('recruitment.apply.cancel') }}</a>
                                <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-cyan-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-cyan-700 sm:w-auto">{{ __('recruitment.apply.submit') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>

            <aside class="w-full min-w-0 lg:w-96 lg:shrink-0">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-6 lg:sticky lg:top-32">
                    <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">{{ __('recruitment.common.hiring') }}</span>
                    <h2 class="mt-4 break-words text-xl font-bold text-slate-900">{{ $jobPost->title }}</h2>
                    <div class="mt-5 space-y-2 text-sm text-slate-600">
                        <p><span class="font-semibold text-slate-800">{{ __('recruitment.common.department') }}:</span> {{ $jobPost->department?->department_name ?? __('recruitment.common.not_updated') }}</p>
                        <p><span class="font-semibold text-slate-800">{{ __('recruitment.common.location') }}:</span> {{ $jobPost->work_location ?: __('recruitment.common.location_tbd') }}</p>
                        <p><span class="font-semibold text-slate-800">{{ __('recruitment.common.deadline') }}:</span> {{ $jobPost->application_deadline?->format('d/m/Y') ?? __('recruitment.common.no_deadline') }}</p>
                    </div>
                </div>
            </aside>
        </main>
    </div>

    @include('public.recruitment.partials.vi-html5-validation')
@endsection
