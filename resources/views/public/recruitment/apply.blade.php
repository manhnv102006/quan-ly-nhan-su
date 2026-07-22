@extends('layouts.recruitment')

@section('title', 'Ứng tuyển '.$jobPost->title)

@section('content')
    @php
        $inputClass = 'w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20';
    @endphp

    <div class="bg-white text-slate-900">
        <section class="border-b border-slate-100 bg-gradient-to-b from-slate-50 to-white">
            <div class="mx-auto flex w-[83%] flex-col gap-6 py-12 lg:flex-row lg:items-end lg:justify-between lg:py-16">
                <div class="min-w-0">
                    <p class="text-sm font-bold text-cyan-700">{{ $jobPost->department?->department_name ?? 'Tin tuyển dụng' }}</p>
                    <h1 class="mt-3 break-words text-3xl font-black leading-tight text-slate-900 sm:text-4xl">Ứng tuyển {{ $jobPost->title }}</h1>
                </div>
                <a href="{{ route('public.recruitment.show', $jobPost) }}"
                   class="inline-flex w-full items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-800 transition hover:border-cyan-300 sm:w-auto">
                    Quay lại tin
                </a>
            </div>
        </section>

        <main class="mx-auto flex w-[83%] flex-col gap-8 py-10 lg:flex-row lg:items-start lg:py-12">
            <section class="w-full min-w-0 lg:flex-1">
                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-5 sm:px-8">
                        <h2 class="text-xl font-bold text-slate-900">Thông tin ứng viên</h2>
                        <p class="mt-2 text-sm text-slate-600">Vui lòng điền đầy đủ thông tin để bộ phận tuyển dụng liên hệ.</p>
                    </div>

                    <div class="p-5 sm:p-8">
                        @if (isset($errors) && $errors->any())
                            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                                <p class="font-semibold">Vui lòng kiểm tra lại thông tin:</p>
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
                                    <label for="full_name" class="mb-2 block text-sm font-semibold text-slate-700">Họ và tên <span class="text-red-500">*</span></label>
                                    <input id="full_name" type="text" name="full_name" value="{{ old('full_name') }}" maxlength="100" required placeholder="Nhập họ tên" class="{{ $inputClass }} @error('full_name') border-red-400 @enderror">
                                    @error('full_name')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label for="phone" class="mb-2 block text-sm font-semibold text-slate-700">Số điện thoại <span class="text-red-500">*</span></label>
                                    <input id="phone" type="text" name="phone" value="{{ old('phone') }}" inputmode="numeric" maxlength="10" pattern="[0-9]{10}" required placeholder="0900000000" class="{{ $inputClass }} @error('phone') border-red-400 @enderror">
                                    @error('phone')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                                <div>
                                    <label for="email" class="mb-2 block text-sm font-semibold text-slate-700">Email <span class="text-red-500">*</span></label>
                                    <input id="email" type="email" name="email" value="{{ old('email') }}" maxlength="100" required placeholder="email@example.com" class="{{ $inputClass }} @error('email') border-red-400 @enderror">
                                    @error('email')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label for="birth_date" class="mb-2 block text-sm font-semibold text-slate-700">Ngày sinh <span class="text-red-500">*</span></label>
                                    <input id="birth_date" type="date" name="birth_date" value="{{ old('birth_date') }}" required class="{{ $inputClass }} @error('birth_date') border-red-400 @enderror">
                                    @error('birth_date')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                                </div>
                            </div>

                            <div>
                                <label for="address" class="mb-2 block text-sm font-semibold text-slate-700">Địa chỉ <span class="text-red-500">*</span></label>
                                <textarea id="address" name="address" rows="3" required placeholder="Nhập địa chỉ hiện tại" class="{{ $inputClass }} resize-y @error('address') border-red-400 @enderror">{{ old('address') }}</textarea>
                                @error('address')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label for="cv_file" class="mb-2 block text-sm font-semibold text-slate-700">CV ứng viên</label>
                                <input id="cv_file" type="file" name="cv_file" accept=".pdf,.doc,.docx" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm text-slate-700 file:mr-3 file:rounded-lg file:border-0 file:bg-cyan-600 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-white @error('cv_file') border-red-400 @enderror">
                                <p class="mt-2 text-xs text-slate-500">Không bắt buộc. Hỗ trợ PDF, DOC, DOCX, tối đa 10MB.</p>
                                @error('cv_file')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div class="flex flex-col-reverse gap-3 pt-2 sm:flex-row sm:items-center">
                                <a href="{{ route('public.recruitment.show', $jobPost) }}" class="inline-flex w-full items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-800 transition hover:border-slate-300 sm:w-auto">Hủy</a>
                                <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-cyan-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-cyan-700 sm:w-auto">Gửi hồ sơ</button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>

            <aside class="w-full min-w-0 lg:w-96 lg:shrink-0">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-6 lg:sticky lg:top-32">
                    <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-800">Đang tuyển</span>
                    <h2 class="mt-4 break-words text-xl font-bold text-slate-900">{{ $jobPost->title }}</h2>
                    <div class="mt-5 space-y-2 text-sm text-slate-600">
                        <p><span class="font-semibold text-slate-800">Phòng ban:</span> {{ $jobPost->department?->department_name ?? 'Chưa cập nhật' }}</p>
                        <p><span class="font-semibold text-slate-800">Địa điểm:</span> {{ $jobPost->work_location ?: 'Trao đổi khi phỏng vấn' }}</p>
                        <p><span class="font-semibold text-slate-800">Hạn nộp:</span> {{ $jobPost->application_deadline?->format('d/m/Y') ?? 'Không giới hạn' }}</p>
                    </div>
                </div>
            </aside>
        </main>
    </div>
@endsection
