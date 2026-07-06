<x-admin-layout title="Thêm ứng viên">
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
                        <span class="font-semibold text-slate-700">Thêm mới</span>
                    </div>
                    <h2 class="mt-3 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">Thêm ứng viên</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                        Tạo hồ sơ ứng viên, gắn tin tuyển dụng và tải CV vào hệ thống.
                    </p>
                </div>

                <a href="{{ route('admin.recruitment.candidates') }}"
                   class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:border-cyan-200 hover:text-cyan-700">
                    Quay lại danh sách
                </a>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-6 xl:grid-cols-12">
            <div class="xl:col-span-8">
                <div class="recruitment-panel overflow-hidden rounded-[2rem] border border-slate-100 bg-white shadow-sm shadow-slate-200/60">
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

                        <form action="{{ route('admin.recruitment.candidates.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                            @csrf

                            <div>
                                <label for="job_post_id" class="mb-2 block text-sm font-bold text-slate-700">Tin tuyển dụng <span class="text-red-500">*</span></label>
                                <select id="job_post_id" name="job_post_id" required class="{{ $inputClass }} @error('job_post_id') border-red-400 @enderror">
                                    <option value="">Chọn tin tuyển dụng</option>
                                    @foreach ($jobPosts as $jobPost)
                                        <option value="{{ $jobPost->id }}" @selected(old('job_post_id') == $jobPost->id)>
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
                                    <input type="text" id="full_name" name="full_name" value="{{ old('full_name') }}" maxlength="100" required
                                           placeholder="Nhập họ tên ứng viên" class="{{ $inputClass }} @error('full_name') border-red-400 @enderror">
                                    @error('full_name')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label for="phone" class="mb-2 block text-sm font-bold text-slate-700">Số điện thoại <span class="text-red-500">*</span></label>
                                    <input type="text" id="phone" name="phone" value="{{ old('phone') }}" inputmode="numeric" maxlength="10" pattern="[0-9]{10}" required
                                           placeholder="Nhập số điện thoại" class="{{ $inputClass }} @error('phone') border-red-400 @enderror">
                                    @error('phone')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                                <div>
                                    <label for="email" class="mb-2 block text-sm font-bold text-slate-700">Email <span class="text-red-500">*</span></label>
                                    <input type="email" id="email" name="email" value="{{ old('email') }}" maxlength="100" required
                                           placeholder="email@example.com" class="{{ $inputClass }} @error('email') border-red-400 @enderror">
                                    @error('email')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label for="birth_date" class="mb-2 block text-sm font-bold text-slate-700">Ngày sinh <span class="text-red-500">*</span></label>
                                    <input type="date" id="birth_date" name="birth_date" value="{{ old('birth_date') }}" required
                                           class="{{ $inputClass }} @error('birth_date') border-red-400 @enderror">
                                    @error('birth_date')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                                </div>
                            </div>

                            <div>
                                <label for="address" class="mb-2 block text-sm font-bold text-slate-700">Địa chỉ <span class="text-red-500">*</span></label>
                                <textarea id="address" name="address" rows="3" required placeholder="Nhập địa chỉ hiện tại"
                                          class="{{ $inputClass }} resize-y @error('address') border-red-400 @enderror">{{ old('address') }}</textarea>
                                @error('address')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                                <div>
                                    <label for="cv_file" class="mb-2 block text-sm font-bold text-slate-700">CV ứng viên</label>
                                    <input type="file" id="cv_file" name="cv_file" accept=".pdf,.doc,.docx"
                                           class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 file:mr-3 file:rounded-xl file:border-0 file:bg-cyan-50 file:px-3 file:py-2 file:text-sm file:font-bold file:text-cyan-700 @error('cv_file') border-red-400 @enderror">
                                    <p class="mt-2 text-xs leading-5 text-slate-500">Hỗ trợ PDF, DOC, DOCX. Tối đa 10MB.</p>
                                    @error('cv_file')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label for="status" class="mb-2 block text-sm font-bold text-slate-700">Trạng thái <span class="text-red-500">*</span></label>
                                    <select id="status" name="status" required class="{{ $inputClass }} @error('status') border-red-400 @enderror">
                                        @foreach ($statuses as $value => $label)
                                            <option value="{{ $value }}" @selected(old('status', 'new') === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('status')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                                </div>
                            </div>

                            <div class="flex flex-col-reverse gap-3 pt-2 sm:flex-row sm:items-center">
                                <a href="{{ route('admin.recruitment.candidates') }}"
                                   class="inline-flex items-center justify-center rounded-2xl bg-slate-100 px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-200">
                                    Hủy
                                </a>
                                <button type="submit"
                                        class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-cyan-500/20 transition hover:bg-cyan-700">
                                    Thêm ứng viên
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <aside class="xl:col-span-4">
                <div class="sticky top-24 rounded-[2rem] border border-cyan-100 bg-cyan-50 p-5">
                    <h3 class="text-base font-black text-cyan-950">Gợi ý nhập liệu</h3>
                    <p class="mt-3 text-sm leading-6 text-cyan-800">
                        Nên gắn ứng viên vào tin tuyển dụng từ đầu để thống kê pipeline và phỏng vấn chính xác hơn.
                    </p>
                    <div class="mt-5 space-y-3 text-sm text-cyan-800">
                        <div class="rounded-2xl bg-white/70 p-4">CV nên dùng PDF để dễ xem trên trình duyệt.</div>
                        <div class="rounded-2xl bg-white/70 p-4">Trạng thái mặc định nên là Mới nếu ứng viên chưa được xử lý.</div>
                    </div>
                </div>
            </aside>
        </section>
    </div>
</x-admin-layout>
