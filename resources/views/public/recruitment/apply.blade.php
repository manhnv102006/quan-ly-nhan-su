<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Ứng tuyển {{ $jobPost->title }} - {{ config('app.name', 'Quản lý nhân sự') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-slate-50 font-sans text-slate-900 antialiased">
        @php
            $inputClass = 'w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10';
        @endphp

        <div class="min-h-screen">
            <header class="border-b border-slate-200 bg-white">
                <div class="mx-auto flex max-w-7xl flex-col gap-4 px-4 py-5 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
                    <a href="{{ route('public.recruitment.show', $jobPost) }}" class="flex min-w-0 items-center gap-3">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
                            <x-application-logo class="h-10 w-10 object-contain" />
                        </div>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-bold text-slate-500">{{ config('app.name', 'Quản lý nhân sự') }}</p>
                            <h1 class="truncate text-xl font-black text-slate-950">Ứng tuyển</h1>
                        </div>
                    </a>

                    <a href="{{ route('public.recruitment.show', $jobPost) }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:border-cyan-300 hover:text-cyan-700">
                        Quay lại tin tuyển dụng
                    </a>
                </div>
            </header>

            <main class="mx-auto grid max-w-7xl grid-cols-1 gap-6 px-4 py-8 sm:px-6 lg:grid-cols-12 lg:px-8">
                <section class="lg:col-span-8">
                    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                        <div class="border-b border-slate-100 bg-slate-50 px-5 py-4 sm:px-6">
                            <p class="text-sm font-bold text-cyan-700">{{ $jobPost->department?->department_name ?? 'Tin tuyển dụng' }}</p>
                            <h2 class="mt-2 break-words text-2xl font-black text-slate-950">{{ $jobPost->title }}</h2>
                            <p class="mt-2 text-sm leading-6 text-slate-600">Vui lòng điền đầy đủ thông tin để bộ phận tuyển dụng liên hệ.</p>
                        </div>

                        <div class="p-5 sm:p-6">
                            @if (isset($errors) && $errors->any())
                                <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                                    <p class="font-bold">Vui lòng kiểm tra lại thông tin:</p>
                                    <ul class="mt-2 list-disc space-y-1 pl-5">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form action="{{ route('public.recruitment.apply.store', $jobPost) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                                @csrf

                                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                                    <div>
                                        <label for="full_name" class="mb-2 block text-sm font-bold text-slate-700">Họ và tên <span class="text-red-500">*</span></label>
                                        <input id="full_name" type="text" name="full_name" value="{{ old('full_name') }}" maxlength="100" required placeholder="Nhập họ tên" class="{{ $inputClass }} @error('full_name') border-red-400 @enderror">
                                        @error('full_name')<p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>@enderror
                                    </div>

                                    <div>
                                        <label for="phone" class="mb-2 block text-sm font-bold text-slate-700">Số điện thoại <span class="text-red-500">*</span></label>
                                        <input id="phone" type="text" name="phone" value="{{ old('phone') }}" inputmode="numeric" maxlength="10" pattern="[0-9]{10}" required placeholder="0900000000" class="{{ $inputClass }} @error('phone') border-red-400 @enderror">
                                        @error('phone')<p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>@enderror
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                                    <div>
                                        <label for="email" class="mb-2 block text-sm font-bold text-slate-700">Email <span class="text-red-500">*</span></label>
                                        <input id="email" type="email" name="email" value="{{ old('email') }}" maxlength="100" required placeholder="email@example.com" class="{{ $inputClass }} @error('email') border-red-400 @enderror">
                                        @error('email')<p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>@enderror
                                    </div>

                                    <div>
                                        <label for="birth_date" class="mb-2 block text-sm font-bold text-slate-700">Ngày sinh <span class="text-red-500">*</span></label>
                                        <input id="birth_date" type="date" name="birth_date" value="{{ old('birth_date') }}" required class="{{ $inputClass }} @error('birth_date') border-red-400 @enderror">
                                        @error('birth_date')<p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>@enderror
                                    </div>
                                </div>

                                <div>
                                    <label for="address" class="mb-2 block text-sm font-bold text-slate-700">Địa chỉ <span class="text-red-500">*</span></label>
                                    <textarea id="address" name="address" rows="3" required placeholder="Nhập địa chỉ hiện tại" class="{{ $inputClass }} resize-y @error('address') border-red-400 @enderror">{{ old('address') }}</textarea>
                                    @error('address')<p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label for="cv_file" class="mb-2 block text-sm font-bold text-slate-700">CV ứng viên</label>
                                    <input id="cv_file" type="file" name="cv_file" accept=".pdf,.doc,.docx" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 file:mr-3 file:rounded-lg file:border-0 file:bg-cyan-50 file:px-3 file:py-2 file:text-sm file:font-bold file:text-cyan-700 @error('cv_file') border-red-400 @enderror">
                                    <p class="mt-2 text-xs leading-5 text-slate-500">Không bắt buộc. Hỗ trợ PDF, DOC, DOCX, tối đa 10MB.</p>
                                    @error('cv_file')<p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>@enderror
                                </div>

                                <div class="flex flex-col-reverse gap-3 pt-2 sm:flex-row sm:items-center">
                                    <a href="{{ route('public.recruitment.show', $jobPost) }}" class="inline-flex items-center justify-center rounded-xl bg-slate-100 px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-200">
                                        Hủy
                                    </a>
                                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-cyan-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-cyan-700">
                                        Gửi hồ sơ
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>

                <aside class="lg:col-span-4">
                    <div class="sticky top-6 rounded-lg border border-cyan-100 bg-white p-5 shadow-sm">
                        <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">Đang tuyển</span>
                        <h3 class="mt-4 break-words text-xl font-black text-slate-950">{{ $jobPost->title }}</h3>
                        <div class="mt-4 space-y-3 text-sm text-slate-600">
                            <p><span class="font-bold text-slate-800">Phòng ban:</span> {{ $jobPost->department?->department_name ?? 'Chưa cập nhật' }}</p>
                            <p><span class="font-bold text-slate-800">Địa điểm:</span> {{ $jobPost->work_location ?: 'Trao đổi khi phỏng vấn' }}</p>
                            <p><span class="font-bold text-slate-800">Hạn nộp:</span> {{ $jobPost->application_deadline?->format('d/m/Y') ?? 'Không giới hạn' }}</p>
                        </div>
                    </div>
                </aside>
            </main>
        </div>
    </body>
</html>
