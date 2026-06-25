<x-admin-layout title="Them ung vien">
    @php
        $inputClass = 'w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10';
        $statuses = [
            'new' => 'Moi',
            'interview' => 'Phong van',
            'passed' => 'Dat',
            'failed' => 'Khong dat',
        ];
    @endphp

    <div class="max-w-6xl space-y-6">
        <section class="rounded-[2rem] border border-white/80 bg-white/90 p-5 shadow-sm shadow-slate-200/60 sm:p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
                        <a href="{{ route('admin.recruitment') }}" class="hover:text-cyan-600">Tuyen dung</a>
                        <span>/</span>
                        <a href="{{ route('admin.recruitment.candidates') }}" class="hover:text-cyan-600">Ung vien</a>
                        <span>/</span>
                        <span class="font-semibold text-slate-700">Them moi</span>
                    </div>
                    <h2 class="mt-3 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">Them ung vien</h2>
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                        Tao ho so ung vien, gan tin tuyen dung va tai CV vao he thong.
                    </p>
                </div>

                <a href="{{ route('admin.recruitment.candidates') }}"
                   class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:border-cyan-200 hover:text-cyan-700">
                    Quay lai danh sach
                </a>
            </div>
        </section>

        <section class="grid grid-cols-1 gap-6 xl:grid-cols-12">
            <div class="xl:col-span-8">
                <div class="overflow-hidden rounded-[2rem] border border-slate-100 bg-white shadow-sm shadow-slate-200/60">
                    <div class="border-b border-slate-100 bg-slate-50/70 px-5 py-4 sm:px-6">
                        <h3 class="text-base font-black text-slate-900">Thong tin ho so</h3>
                        <p class="mt-1 text-sm text-slate-500">Cac truong co dau * la bat buoc.</p>
                    </div>

                    <div class="p-5 sm:p-6">
                        @if (isset($errors) && $errors->any())
                            <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                                <p class="font-bold">Vui long kiem tra lai thong tin:</p>
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
                                <label for="job_post_id" class="mb-2 block text-sm font-bold text-slate-700">Tin tuyen dung</label>
                                <select id="job_post_id" name="job_post_id" class="{{ $inputClass }} @error('job_post_id') border-red-400 @enderror">
                                    <option value="">Chua gan tin tuyen dung</option>
                                    @foreach ($jobPosts as $jobPost)
                                        <option value="{{ $jobPost->id }}" @selected(old('job_post_id') == $jobPost->id)>
                                            {{ $jobPost->title }}
                                            @if ($jobPost->department)
                                                - {{ $jobPost->department->department_name }}
                                            @endif
                                            ({{ $jobPost->status === 'open' ? 'Dang mo' : 'Da dong' }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('job_post_id')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                                <div>
                                    <label for="full_name" class="mb-2 block text-sm font-bold text-slate-700">Ho va ten <span class="text-red-500">*</span></label>
                                    <input type="text" id="full_name" name="full_name" value="{{ old('full_name') }}" maxlength="100" required
                                           placeholder="Nhap ho ten ung vien" class="{{ $inputClass }} @error('full_name') border-red-400 @enderror">
                                    @error('full_name')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label for="phone" class="mb-2 block text-sm font-bold text-slate-700">So dien thoai <span class="text-red-500">*</span></label>
                                    <input type="text" id="phone" name="phone" value="{{ old('phone') }}" maxlength="20" required
                                           placeholder="Nhap so dien thoai" class="{{ $inputClass }} @error('phone') border-red-400 @enderror">
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
                                    <label for="birth_date" class="mb-2 block text-sm font-bold text-slate-700">Ngay sinh</label>
                                    <input type="date" id="birth_date" name="birth_date" value="{{ old('birth_date') }}"
                                           class="{{ $inputClass }} @error('birth_date') border-red-400 @enderror">
                                    @error('birth_date')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                                </div>
                            </div>

                            <div>
                                <label for="address" class="mb-2 block text-sm font-bold text-slate-700">Dia chi <span class="text-red-500">*</span></label>
                                <textarea id="address" name="address" rows="3" required placeholder="Nhap dia chi hien tai"
                                          class="{{ $inputClass }} resize-y @error('address') border-red-400 @enderror">{{ old('address') }}</textarea>
                                @error('address')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                                <div>
                                    <label for="cv_file" class="mb-2 block text-sm font-bold text-slate-700">CV ung vien</label>
                                    <input type="file" id="cv_file" name="cv_file" accept=".pdf,.doc,.docx"
                                           class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 file:mr-3 file:rounded-xl file:border-0 file:bg-cyan-50 file:px-3 file:py-2 file:text-sm file:font-bold file:text-cyan-700 @error('cv_file') border-red-400 @enderror">
                                    <p class="mt-2 text-xs leading-5 text-slate-500">Ho tro PDF, DOC, DOCX. Toi da 10MB.</p>
                                    @error('cv_file')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                                </div>

                                <div>
                                    <label for="status" class="mb-2 block text-sm font-bold text-slate-700">Trang thai <span class="text-red-500">*</span></label>
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
                                    Huy
                                </a>
                                <button type="submit"
                                        class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-cyan-500/20 transition hover:bg-cyan-700">
                                    Them ung vien
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <aside class="xl:col-span-4">
                <div class="sticky top-24 rounded-[2rem] border border-cyan-100 bg-cyan-50 p-5">
                    <h3 class="text-base font-black text-cyan-950">Goi y nhap lieu</h3>
                    <p class="mt-3 text-sm leading-6 text-cyan-800">
                        Nen gan ung vien vao tin tuyen dung tu dau de thong ke pipeline va phong van chinh xac hon.
                    </p>
                    <div class="mt-5 space-y-3 text-sm text-cyan-800">
                        <div class="rounded-2xl bg-white/70 p-4">CV nen dung PDF de de xem tren trinh duyet.</div>
                        <div class="rounded-2xl bg-white/70 p-4">Trang thai mac dinh nen la Moi neu ung vien chua duoc xu ly.</div>
                    </div>
                </div>
            </aside>
        </section>
    </div>
</x-admin-layout>
