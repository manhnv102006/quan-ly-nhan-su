<x-admin-layout title="Sửa ứng viên">
    @php
        $inputClass = 'w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10';
        $statuses = [
            'new' => 'Mới',
            'interview' => 'Phỏng vấn',
            'passed' => 'Đạt',
            'failed' => 'Không đạt',
        ];
    @endphp

    @include('admin.recruitment.partials.ui-contrast')

    <div class="recruitment-ui max-w-6xl space-y-6">
        <section class="recruitment-hero rounded-[2rem] border border-white/80 bg-white/90 p-5 shadow-sm shadow-slate-200/60 sm:p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
                        <a href="{{ route('admin.recruitment') }}" class="hover:text-cyan-600">Tuyển dụng</a>
                        <span>/</span>
                        <a href="{{ route('admin.recruitment.candidates') }}" class="hover:text-cyan-600">Ứng viên</a>
                        <span>/</span>
                        <span class="break-words font-semibold text-slate-700">{{ $candidate->full_name }}</span>
                    </div>
                    <h2 class="mt-3 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">Sửa ứng viên</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                        Cập nhật thông tin hồ sơ, trạng thái xử lý và thay CV mới nếu cần.
                    </p>
                </div>

                <a href="{{ route('admin.recruitment.candidates.show', $candidate) }}"
                   class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:border-cyan-200 hover:text-cyan-700">
                    Quay lại chi tiết
                </a>
            </div>
        </section>

        <section class="recruitment-panel overflow-hidden rounded-[2rem] border border-slate-100 bg-white shadow-sm shadow-slate-200/60">
            <div class="border-b border-slate-100 bg-slate-50/70 px-5 py-4 sm:px-6">
                <h3 class="text-base font-black text-slate-900">Thông tin hồ sơ</h3>
                <p class="mt-1 text-sm text-slate-500">Các trường có dấu * là bắt buộc.</p>
            </div>

            <div class="p-5 sm:p-6">
                @if (isset($errors) && $errors->any())
                    <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        <p class="font-bold">Vui lòng kiểm tra lại thông tin:</p>
                        <ul class="mt-2 list-disc space-y-1 pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.recruitment.candidates.update', $candidate) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="job_post_id" class="mb-2 block text-sm font-bold text-slate-700">Tin tuyển dụng</label>
                        <select id="job_post_id" name="job_post_id" class="{{ $inputClass }} @error('job_post_id') border-red-400 @enderror">
                            <option value="">Chưa gắn tin tuyển dụng</option>
                            @foreach ($jobPosts as $jobPost)
                                <option value="{{ $jobPost->id }}" @selected(old('job_post_id', $candidate->job_post_id) == $jobPost->id)>
                                    {{ $jobPost->title }}
                                    @if ($jobPost->department)
                                        - {{ $jobPost->department->department_name }}
                                    @endif
                                    ({{ $jobPost->status === 'open' ? 'Đang mở' : 'Đã đóng' }})
                                </option>
                            @endforeach
                        </select>
                        @error('job_post_id')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div>
                            <label for="full_name" class="mb-2 block text-sm font-bold text-slate-700">Họ và tên <span class="text-red-500">*</span></label>
                            <input type="text" id="full_name" name="full_name" value="{{ old('full_name', $candidate->full_name) }}" maxlength="100" required
                                   class="{{ $inputClass }} @error('full_name') border-red-400 @enderror">
                            @error('full_name')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="phone" class="mb-2 block text-sm font-bold text-slate-700">Số điện thoại <span class="text-red-500">*</span></label>
                            <input type="text" id="phone" name="phone" value="{{ old('phone', $candidate->phone) }}" maxlength="20" required
                                   class="{{ $inputClass }} @error('phone') border-red-400 @enderror">
                            @error('phone')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div>
                            <label for="email" class="mb-2 block text-sm font-bold text-slate-700">Email <span class="text-red-500">*</span></label>
                            <input type="email" id="email" name="email" value="{{ old('email', $candidate->email) }}" maxlength="100" required
                                   class="{{ $inputClass }} @error('email') border-red-400 @enderror">
                            @error('email')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="birth_date" class="mb-2 block text-sm font-bold text-slate-700">Ngày sinh</label>
                            <input type="date" id="birth_date" name="birth_date" value="{{ old('birth_date', $candidate->birth_date?->format('Y-m-d')) }}"
                                   class="{{ $inputClass }} @error('birth_date') border-red-400 @enderror">
                            @error('birth_date')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div>
                        <label for="address" class="mb-2 block text-sm font-bold text-slate-700">Địa chỉ <span class="text-red-500">*</span></label>
                        <textarea id="address" name="address" rows="3" required
                                  class="{{ $inputClass }} resize-y @error('address') border-red-400 @enderror">{{ old('address', $candidate->address) }}</textarea>
                        @error('address')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="rounded-[1.5rem] border border-slate-100 bg-slate-50 p-5">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div class="min-w-0">
                                <h3 class="text-sm font-black text-slate-900">CV hiện tại</h3>
                                @if ($cvUrl)
                                    <p class="mt-1 break-all text-sm text-slate-500">{{ $candidate->cv_file }}</p>
                                    <a href="{{ $cvUrl }}" target="_blank" rel="noopener"
                                       class="mt-3 inline-flex rounded-xl bg-white px-4 py-2 text-sm font-bold text-cyan-700 ring-1 ring-cyan-100 transition hover:bg-cyan-50">
                                        Mở CV
                                    </a>
                                @elseif ($candidate->cv_file)
                                    <p class="mt-1 break-all text-sm text-amber-700">Có đường dẫn CV nhưng file không còn trên storage: {{ $candidate->cv_file }}</p>
                                @else
                                    <p class="mt-1 text-sm text-slate-500">Ứng viên chưa có CV.</p>
                                @endif
                            </div>

                            <div class="w-full lg:max-w-sm">
                                <label for="cv_file" class="mb-2 block text-sm font-bold text-slate-700">Tải CV mới</label>
                                <input type="file" id="cv_file" name="cv_file" accept=".pdf,.doc,.docx"
                                       class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 file:mr-3 file:rounded-xl file:border-0 file:bg-cyan-50 file:px-3 file:py-2 file:text-sm file:font-bold file:text-cyan-700 @error('cv_file') border-red-400 @enderror">
                                @error('cv_file')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="status" class="mb-2 block text-sm font-bold text-slate-700">Trạng thái <span class="text-red-500">*</span></label>
                        <select id="status" name="status" required class="{{ $inputClass }} @error('status') border-red-400 @enderror">
                            @foreach ($statuses as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', $candidate->status) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex flex-col-reverse gap-3 pt-2 sm:flex-row sm:items-center">
                        <a href="{{ route('admin.recruitment.candidates.show', $candidate) }}"
                           class="inline-flex items-center justify-center rounded-2xl bg-slate-100 px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-200">
                            Hủy
                        </a>
                        <button type="submit"
                                class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-cyan-500/20 transition hover:bg-cyan-700">
                            Lưu thay đổi
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </div>
</x-admin-layout>
