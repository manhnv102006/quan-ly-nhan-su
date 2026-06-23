<x-admin-layout title="Thêm ứng viên mới">

    <div class="space-y-6">

        <div class="flex items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-sm text-slate-500">
                    <a href="{{ route('admin.recruitment') }}" class="hover:text-cyan-600 transition">Tuyển dụng</a>
                    <span>/</span>
                    <a href="{{ route('admin.recruitment.candidates') }}" class="hover:text-cyan-600 transition">Ứng viên</a>
                    <span>/</span>
                    <span class="text-slate-700 font-medium">Thêm mới</span>
                </div>

                <h2 class="mt-2 text-2xl font-bold text-slate-800">Thêm ứng viên mới</h2>
                <p class="text-sm text-slate-500 mt-1">
                    Tạo hồ sơ ứng viên cơ bản và tải CV ngay trong bước khởi tạo hồ sơ.
                </p>
            </div>

            <a href="{{ route('admin.recruitment.candidates') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 font-medium hover:bg-slate-50 transition">
                ← Quay lại
            </a>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 sm:p-8 max-w-4xl">

            @if ($errors->any())
                <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <p class="font-semibold mb-1">Vui lòng kiểm tra lại thông tin:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.recruitment.candidates.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf

                <div>
                    <label for="job_post_id" class="block text-sm font-semibold text-slate-700 mb-2">
                        Tin tuyển dụng
                    </label>
                    <select id="job_post_id" name="job_post_id"
                            class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 outline-none transition @error('job_post_id') border-red-400 @enderror">
                        <option value="">-- Chưa gắn tin tuyển dụng --</option>
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
                    @error('job_post_id')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="full_name" class="block text-sm font-semibold text-slate-700 mb-2">
                            Họ và tên <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="full_name" name="full_name"
                               value="{{ old('full_name') }}"
                               placeholder="Nhập họ và tên ứng viên" maxlength="100" required
                               class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 placeholder:text-slate-400 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 outline-none transition @error('full_name') border-red-400 @enderror">
                        @error('full_name')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-semibold text-slate-700 mb-2">
                            Số điện thoại <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="phone" name="phone"
                               value="{{ old('phone') }}"
                               placeholder="Nhập số điện thoại" maxlength="20" required
                               class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 placeholder:text-slate-400 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 outline-none transition @error('phone') border-red-400 @enderror">
                        @error('phone')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="email" class="block text-sm font-semibold text-slate-700 mb-2">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email" id="email" name="email"
                               value="{{ old('email') }}"
                               placeholder="email@example.com" maxlength="100" required
                               class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 placeholder:text-slate-400 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 outline-none transition @error('email') border-red-400 @enderror">
                        @error('email')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="birth_date" class="block text-sm font-semibold text-slate-700 mb-2">
                            Ngày sinh
                        </label>
                        <input type="date" id="birth_date" name="birth_date"
                               value="{{ old('birth_date') }}"
                               class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 outline-none transition @error('birth_date') border-red-400 @enderror">
                        @error('birth_date')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="address" class="block text-sm font-semibold text-slate-700 mb-2">
                        Địa chỉ <span class="text-red-500">*</span>
                    </label>
                    <textarea id="address" name="address" rows="3" required
                              placeholder="Nhập địa chỉ hiện tại"
                              class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 placeholder:text-slate-400 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 outline-none transition @error('address') border-red-400 @enderror">{{ old('address') }}</textarea>
                    @error('address')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="cv_file" class="block text-sm font-semibold text-slate-700 mb-2">
                        CV ứng viên
                    </label>
                    <input type="file" id="cv_file" name="cv_file" accept=".pdf,.doc,.docx"
                           class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-slate-800 text-sm file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-cyan-100 file:text-cyan-700 file:text-sm file:font-medium @error('cv_file') border-red-400 @enderror">
                    <p class="mt-2 text-sm text-slate-500">Hỗ trợ PDF, DOC, DOCX. Dung lượng tối đa 10MB.</p>
                    @error('cv_file')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="status" class="block text-sm font-semibold text-slate-700 mb-2">
                        Trạng thái <span class="text-red-500">*</span>
                    </label>
                    <select id="status" name="status" required
                            class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 outline-none transition @error('status') border-red-400 @enderror">
                        <option value="">-- Chọn trạng thái --</option>
                        <option value="new" @selected(old('status', 'new') === 'new')>Mới</option>
                        <option value="interview" @selected(old('status') === 'interview')>Phỏng vấn</option>
                        <option value="passed" @selected(old('status') === 'passed')>Đạt</option>
                        <option value="failed" @selected(old('status') === 'failed')>Không đạt</option>
                    </select>
                    @error('status')
                        <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex flex-wrap gap-3 pt-2">
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-cyan-600 text-white font-medium shadow-lg shadow-cyan-500/20 hover:bg-cyan-700 transition">
                        + Thêm ứng viên
                    </button>
                    <a href="{{ route('admin.recruitment.candidates') }}"
                       class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-slate-100 text-slate-700 font-medium hover:bg-slate-200 transition">
                        Hủy
                    </a>
                </div>
            </form>
        </div>

    </div>

</x-admin-layout>