@extends('layouts.recruitment')

@section('title', 'Ứng tuyển '.$jobPost->title)
@section('header_theme', 'dark')

@section('content')
    @php
        $inputClass = 'w-full rounded-2xl border border-white/10 bg-white/[0.06] px-4 py-3 text-sm text-white outline-none transition placeholder:text-slate-500 focus:border-cyan-300 focus:ring-4 focus:ring-cyan-300/10';
    @endphp

    <div class="bg-[#030712] text-white">
        <section class="relative border-b border-white/10">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_18%_22%,rgba(34,211,238,.20),transparent_32%),radial-gradient(circle_at_82%_10%,rgba(249,115,22,.16),transparent_30%)]"></div>
            <div class="relative mx-auto max-w-[1500px] px-5 py-16 sm:px-8 lg:px-12 lg:py-24">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                    <div class="min-w-0">
                        <p class="inline-flex rounded-full border border-cyan-300/20 bg-cyan-300/10 px-4 py-2 text-xs font-black uppercase tracking-[0.25em] text-cyan-200">{{ $jobPost->department?->department_name ?? 'Tin tuyển dụng' }}</p>
                        <h1 class="mt-6 max-w-5xl break-words text-5xl font-black leading-tight tracking-tight sm:text-7xl">Ứng tuyển {{ $jobPost->title }}</h1>
                    </div>
                    <a href="{{ route('public.recruitment.show', $jobPost) }}" class="inline-flex w-full items-center justify-center rounded-2xl border border-white/15 bg-white/5 px-5 py-3 text-sm font-black uppercase text-white transition hover:border-cyan-300/60 hover:bg-cyan-300/10 sm:w-auto">Quay lại tin</a>
                </div>
            </div>
        </section>

        <main class="mx-auto flex max-w-[1500px] flex-col gap-6 px-5 py-12 sm:px-8 lg:flex-row lg:items-start lg:px-12">
            <section class="w-full min-w-0 lg:flex-1">
                <div class="overflow-hidden rounded-[2rem] border border-white/10 bg-white/[0.06] shadow-2xl shadow-black/20 backdrop-blur">
                    <div class="border-b border-white/10 bg-white/[0.04] px-5 py-5 sm:px-8">
                        <h2 class="text-2xl font-black">Thông tin ứng viên</h2>
                        <p class="mt-2 text-sm leading-7 text-slate-300">Vui lòng điền đầy đủ thông tin để bộ phận tuyển dụng liên hệ.</p>
                    </div>

                    <div class="p-5 sm:p-8">
                        @if (isset($errors) && $errors->any())
                            <div class="mb-6 rounded-2xl border border-red-300/25 bg-red-400/10 px-4 py-3 text-sm text-red-200">
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
                                    <label for="full_name" class="mb-2 block text-sm font-bold text-slate-200">Họ và tên <span class="text-orange-300">*</span></label>
                                    <input id="full_name" type="text" name="full_name" value="{{ old('full_name') }}" maxlength="100" required placeholder="Nhập họ tên" class="{{ $inputClass }} @error('full_name') border-red-400 @enderror">
                                    @error('full_name')<p class="mt-2 text-sm font-semibold text-red-300">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label for="phone" class="mb-2 block text-sm font-bold text-slate-200">Số điện thoại <span class="text-orange-300">*</span></label>
                                    <input id="phone" type="text" name="phone" value="{{ old('phone') }}" inputmode="numeric" maxlength="10" pattern="[0-9]{10}" required placeholder="0900000000" class="{{ $inputClass }} @error('phone') border-red-400 @enderror">
                                    @error('phone')<p class="mt-2 text-sm font-semibold text-red-300">{{ $message }}</p>@enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                                <div>
                                    <label for="email" class="mb-2 block text-sm font-bold text-slate-200">Email <span class="text-orange-300">*</span></label>
                                    <input id="email" type="email" name="email" value="{{ old('email') }}" maxlength="100" required placeholder="email@example.com" class="{{ $inputClass }} @error('email') border-red-400 @enderror">
                                    @error('email')<p class="mt-2 text-sm font-semibold text-red-300">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label for="birth_date" class="mb-2 block text-sm font-bold text-slate-200">Ngày sinh <span class="text-orange-300">*</span></label>
                                    <input id="birth_date" type="date" name="birth_date" value="{{ old('birth_date') }}" required class="{{ $inputClass }} @error('birth_date') border-red-400 @enderror">
                                    @error('birth_date')<p class="mt-2 text-sm font-semibold text-red-300">{{ $message }}</p>@enderror
                                </div>
                            </div>

                            <div>
                                <label for="address" class="mb-2 block text-sm font-bold text-slate-200">Địa chỉ <span class="text-orange-300">*</span></label>
                                <textarea id="address" name="address" rows="3" required placeholder="Nhập địa chỉ hiện tại" class="{{ $inputClass }} resize-y @error('address') border-red-400 @enderror">{{ old('address') }}</textarea>
                                @error('address')<p class="mt-2 text-sm font-semibold text-red-300">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="cv_file" class="mb-2 block text-sm font-bold text-slate-200">CV ứng viên</label>
                                <input id="cv_file" type="file" name="cv_file" accept=".pdf,.doc,.docx" class="w-full rounded-2xl border border-white/10 bg-white/[0.06] px-4 py-2.5 text-sm text-slate-200 file:mr-3 file:rounded-xl file:border-0 file:bg-cyan-300 file:px-3 file:py-2 file:text-sm file:font-black file:text-slate-950 @error('cv_file') border-red-400 @enderror">
                                <p class="mt-2 text-xs leading-5 text-slate-400">Không bắt buộc. Hỗ trợ PDF, DOC, DOCX, tối đa 10MB.</p>
                                @error('cv_file')<p class="mt-2 text-sm font-semibold text-red-300">{{ $message }}</p>@enderror
                            </div>

                            <div class="flex flex-col-reverse gap-3 pt-2 sm:flex-row sm:items-center">
                                <a href="{{ route('public.recruitment.show', $jobPost) }}" class="inline-flex w-full items-center justify-center rounded-2xl border border-white/10 bg-white/5 px-5 py-3 text-sm font-black text-white transition hover:bg-white/10 sm:w-auto">Hủy</a>
                                <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-orange-500 px-5 py-3 text-sm font-black uppercase text-white transition hover:bg-orange-600 sm:w-auto">Gửi hồ sơ</button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>

            <aside class="w-full min-w-0 lg:w-96 lg:shrink-0">
                <div class="rounded-[2rem] border border-cyan-300/20 bg-cyan-300/10 p-6 shadow-2xl shadow-cyan-950/30 lg:sticky lg:top-32">
                    <span class="inline-flex rounded-full bg-emerald-400/10 px-3 py-1 text-xs font-black text-emerald-300">Đang tuyển</span>
                    <h2 class="mt-4 break-words text-2xl font-black">{{ $jobPost->title }}</h2>
                    <div class="mt-5 space-y-3 text-sm leading-7 text-slate-300">
                        <p><span class="font-black text-white">Phòng ban:</span> {{ $jobPost->department?->department_name ?? 'Chưa cập nhật' }}</p>
                        <p><span class="font-black text-white">Địa điểm:</span> {{ $jobPost->work_location ?: 'Trao đổi khi phỏng vấn' }}</p>
                        <p><span class="font-black text-white">Hạn nộp:</span> {{ $jobPost->application_deadline?->format('d/m/Y') ?? 'Không giới hạn' }}</p>
                    </div>
                </div>
            </aside>
        </main>
    </div>
@endsection
