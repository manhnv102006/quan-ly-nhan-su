<x-admin-layout title="Tin tuyển dụng">
    @php
        $showForm = ($showCreateForm ?? false) || ($showEditForm ?? false);
        $formJobPost = ($showEditForm ?? false) ? ($editingJobPost ?? null) : null;
        $formAction = ($showEditForm ?? false) && $formJobPost
            ? route('admin.recruitment.job-posts.update', $formJobPost)
            : route('admin.recruitment.job-posts.store');
        $inputClass = 'w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10';
        $workTypes = [
            'full_time' => 'Full-time',
            'part_time' => 'Part-time',
            'remote' => 'Remote',
            'hybrid' => 'Hybrid',
            'contract' => 'Contract',
        ];

        $departmentManagers = ($departments ?? collect())->mapWithKeys(function ($department) {
            $manager = $department->manager;
            $label = $manager
                ? trim(($manager->employee_code ? $manager->employee_code.' - ' : '').$manager->full_name)
                : null;

            return [(string) $department->id => $label];
        })->all();

        $selectedDepartmentId = (string) old('department_id', $formJobPost?->department_id ?? '');
        $initialRecruiterLabel = $selectedDepartmentId !== '' && array_key_exists($selectedDepartmentId, $departmentManagers)
            ? ($departmentManagers[$selectedDepartmentId] ?? null)
            : null;
    @endphp

    @include('admin.recruitment.partials.ui-contrast')

    <div class="recruitment-ui max-w-full overflow-hidden space-y-6">
        <section class="recruitment-hero rounded-[2rem] border border-white/80 bg-white/90 p-5 shadow-sm shadow-slate-200/60 sm:p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
                        <a href="{{ route('admin.recruitment') }}" class="hover:text-cyan-600">Tuyển dụng</a>
                        <span>/</span>
                        <span class="font-semibold text-slate-700">Tin tuyển dụng</span>
                    </div>
                    <h2 class="mt-3 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">Quản lý tin tuyển dụng</h2>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                        Tạo nhu cầu tuyển dụng, gắn phòng ban, mức lương và hạn nộp hồ sơ. Người phụ trách là quản lý phòng ban.
                    </p>
                </div>

                @if (!$showForm)
                <a href="{{ route('admin.recruitment.job-posts.create') }}"
                   class="recruitment-btn-primary inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-cyan-500/20 transition hover:bg-cyan-700">
                    Tạo tin tuyển dụng
                </a>
                @else
                <a href="{{ route('admin.recruitment.job-posts') }}"
                   class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-700 transition hover:border-cyan-300 hover:text-cyan-800">
                    ← Danh sách tin
                </a>
                @endif
            </div>
        </section>

        @if (session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-700">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-bold text-red-700">{{ session('error') }}</div>
        @endif

        @if (isset($errors) && $errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
                <p class="font-bold">Vui lòng kiểm tra lại thông tin:</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @unless ($showForm)
        <section class="recruitment-stats grid grid-cols-1 gap-4 md:grid-cols-4">
            <div class="rounded-[1.5rem] border border-slate-100 bg-white p-5 shadow-sm">
                <p class="text-sm font-bold text-slate-500">Tổng tin</p>
                <p class="mt-2 text-3xl font-black text-slate-900">{{ $stats['total'] ?? 0 }}</p>
            </div>
            <div class="rounded-[1.5rem] border border-amber-100 bg-amber-50 p-5 shadow-sm">
                <p class="text-sm font-bold text-amber-800">Chờ duyệt</p>
                <p class="mt-2 text-3xl font-black text-amber-900">{{ $stats['pending_approval'] ?? 0 }}</p>
            </div>
            <div class="rounded-[1.5rem] border border-emerald-100 bg-emerald-50 p-5 shadow-sm">
                <p class="text-sm font-bold text-emerald-700">Đang mở</p>
                <p class="mt-2 text-3xl font-black text-emerald-800">{{ $stats['open'] ?? 0 }}</p>
            </div>
            <div class="rounded-[1.5rem] border border-slate-100 bg-slate-50 p-5 shadow-sm">
                <p class="text-sm font-bold text-slate-600">Đã đóng</p>
                <p class="mt-2 text-3xl font-black text-slate-900">{{ $stats['closed'] ?? 0 }}</p>
            </div>
        </section>
        @endunless

        @if ($showForm)
            <section class="recruitment-panel overflow-hidden rounded-[2rem] border border-slate-100 bg-white shadow-sm shadow-slate-200/60">
                <div class="border-b border-slate-100 bg-slate-50/70 px-5 py-4 sm:px-6">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-base font-black text-slate-900">{{ ($showEditForm ?? false) ? 'Cập nhật tin tuyển dụng' : 'Tạo tin tuyển dụng' }}</h3>
                            <p class="mt-1 text-sm text-slate-500">Nhập đầy đủ thông tin để ứng viên và HR dễ theo dõi.</p>
                        </div>
                        <a href="{{ route('admin.recruitment.job-posts') }}" class="text-sm font-bold text-slate-500 transition hover:text-cyan-700">Đóng form</a>
                    </div>
                </div>

                <form action="{{ $formAction }}" method="POST" class="grid grid-cols-1 gap-5 p-5 sm:p-6 lg:grid-cols-2">
                    @csrf
                    @if (($showEditForm ?? false) && $formJobPost)
                        @method('PUT')
                    @endif

                    <div class="rounded-2xl bg-cyan-50 px-4 py-3 lg:col-span-2">
                        <h4 class="text-sm font-black text-cyan-950">Thông tin công việc</h4>
                        <p class="mt-1 text-sm text-cyan-700">Nhập vị trí, phòng ban và số lượng cần tuyển. Quản lý phòng ban sẽ phụ trách tin.</p>
                    </div>

                    <div class="lg:col-span-2">
                        <label class="mb-2 block text-sm font-bold text-slate-700">Tiêu đề <span class="text-red-500">*</span></label>
                        <input type="text" name="title" value="{{ old('title', $formJobPost?->title) }}" required class="{{ $inputClass }}">
                        @error('title')<p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700">Phòng ban</label>
                        <select id="job-post-department-id" name="department_id" class="{{ $inputClass }}">
                            <option value="">Chưa gắn phòng ban</option>
                            @foreach (($departments ?? collect()) as $department)
                                <option value="{{ $department->id }}" @selected((string) old('department_id', $formJobPost?->department_id) === (string) $department->id)>
                                    {{ $department->department_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('department_id')<p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700">Người phụ trách</label>
                        <div id="job-post-recruiter-display"
                             class="{{ $inputClass }} bg-slate-50 text-slate-600"
                             data-empty="Chọn phòng ban để hiển thị quản lý phòng ban"
                             data-no-manager="Phòng ban chưa có quản lý">
                            @if ($initialRecruiterLabel)
                                {{ $initialRecruiterLabel }}
                            @elseif ($selectedDepartmentId !== '')
                                Phòng ban chưa có quản lý
                            @else
                                Chọn phòng ban để hiển thị quản lý phòng ban
                            @endif
                        </div>
                        <p class="mt-2 text-xs text-slate-500">Tự động theo quản lý của phòng ban đã chọn.</p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700">Số lượng <span class="text-red-500">*</span></label>
                        <input type="number" min="1" name="quantity" value="{{ old('quantity', $formJobPost?->quantity ?? 1) }}" required class="{{ $inputClass }}">
                        @error('quantity')<p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700">Trạng thái <span class="text-red-500">*</span></label>
                        <select name="status" required class="{{ $inputClass }}">
                            <option value="open" @selected(old('status', $formJobPost?->status ?? 'open') === 'open')>Đang tuyển</option>
                            <option value="closed" @selected(old('status', $formJobPost?->status) === 'closed')>Đã đóng</option>
                        </select>
                        @error('status')<p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="rounded-2xl bg-slate-50 px-4 py-3 lg:col-span-2">
                        <h4 class="text-sm font-black text-slate-900">Lương, hình thức và thời hạn</h4>
                        <p class="mt-1 text-sm text-slate-500">Các thông tin này giúp ứng viên hiểu nhanh điều kiện tuyển dụng.</p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700">Lương tối thiểu</label>
                        <input type="number" min="0" step="100000" name="salary_min" value="{{ old('salary_min', $formJobPost?->salary_min) }}" class="{{ $inputClass }}">
                        @error('salary_min')<p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700">Lương tối đa</label>
                        <input type="number" min="0" step="100000" name="salary_max" value="{{ old('salary_max', $formJobPost?->salary_max) }}" class="{{ $inputClass }}">
                        @error('salary_max')<p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700">Địa điểm</label>
                        <input type="text" name="work_location" value="{{ old('work_location', $formJobPost?->work_location) }}" class="{{ $inputClass }}">
                        @error('work_location')<p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700">Hình thức</label>
                        <select name="work_type" class="{{ $inputClass }}">
                            <option value="">Chưa chọn</option>
                            @foreach ($workTypes as $value => $label)
                                <option value="{{ $value }}" @selected(old('work_type', $formJobPost?->work_type) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('work_type')<p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700">Hạn nộp hồ sơ</label>
                        <input type="date" name="application_deadline" value="{{ old('application_deadline', $formJobPost?->application_deadline?->format('Y-m-d')) }}" class="{{ $inputClass }}">
                        @error('application_deadline')<p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="rounded-2xl bg-amber-50 px-4 py-3 lg:col-span-2">
                        <h4 class="text-sm font-black text-amber-950">Nội dung tuyển dụng</h4>
                        <p class="mt-1 text-sm text-amber-700">Tách rõ mô tả, yêu cầu và quyền lợi để tin tuyển dụng dễ đọc hơn.</p>
                    </div>

                    <div class="lg:col-span-2">
                        <label class="mb-2 block text-sm font-bold text-slate-700">Mô tả công việc</label>
                        <textarea name="description" rows="4" class="{{ $inputClass }} resize-y">{{ old('description', $formJobPost?->description) }}</textarea>
                        @error('description')<p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700">Yêu cầu</label>
                        <textarea name="requirements" rows="4" class="{{ $inputClass }} resize-y">{{ old('requirements', $formJobPost?->requirements) }}</textarea>
                        @error('requirements')<p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700">Quyền lợi</label>
                        <textarea name="benefits" rows="4" class="{{ $inputClass }} resize-y">{{ old('benefits', $formJobPost?->benefits) }}</textarea>
                        @error('benefits')<p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex flex-col-reverse gap-3 lg:col-span-2 sm:flex-row sm:items-center">
                        <a href="{{ route('admin.recruitment.job-posts') }}" class="inline-flex items-center justify-center rounded-2xl bg-slate-100 px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-200">Hủy</a>
                        <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-cyan-500/20 transition hover:bg-cyan-700">
                            {{ ($showEditForm ?? false) ? 'Lưu thay đổi' : 'Tạo tin' }}
                        </button>
                    </div>
                </form>
            </section>

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const departmentSelect = document.getElementById('job-post-department-id');
                    const recruiterDisplay = document.getElementById('job-post-recruiter-display');
                    if (!departmentSelect || !recruiterDisplay) {
                        return;
                    }

                    const managers = @json($departmentManagers);

                    function refreshRecruiterLabel() {
                        const departmentId = departmentSelect.value;
                        if (!departmentId) {
                            recruiterDisplay.textContent = recruiterDisplay.dataset.empty;
                            return;
                        }

                        const managerName = managers[departmentId] ?? null;
                        recruiterDisplay.textContent = managerName || recruiterDisplay.dataset.noManager;
                    }

                    departmentSelect.addEventListener('change', refreshRecruiterLabel);
                });
            </script>
        @endif

        @unless ($showForm)
        <section class="recruitment-panel rounded-[2rem] border border-slate-100 bg-white p-5 shadow-sm shadow-slate-200/60">
            <form method="GET" action="{{ route('admin.recruitment.job-posts') }}" class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                <div class="lg:col-span-5">
                    <label class="mb-2 block text-sm font-bold text-slate-700">Tìm kiếm</label>
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Tìm theo tiêu đề, phòng ban, người phụ trách..."
                           class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10">
                </div>

                <div class="lg:col-span-3">
                    <label class="mb-2 block text-sm font-bold text-slate-700">Trạng thái</label>
                    <select name="status" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10">
                        <option value="">Tất cả trạng thái</option>
                        <option value="open" @selected(($filters['status'] ?? '') === 'open')>Đang tuyển</option>
                        <option value="pending_approval" @selected(($filters['status'] ?? '') === 'pending_approval')>Chờ duyệt</option>
                        <option value="closed" @selected(($filters['status'] ?? '') === 'closed')>Đã đóng</option>
                        <option value="rejected" @selected(($filters['status'] ?? '') === 'rejected')>Đã từ chối</option>
                    </select>
                </div>

                <div class="lg:col-span-3">
                    <label class="mb-2 block text-sm font-bold text-slate-700">Phòng ban</label>
                    <select name="department_id" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10">
                        <option value="">Tất cả phòng ban</option>
                        @foreach (($departments ?? collect()) as $department)
                            <option value="{{ $department->id }}" @selected((string) ($filters['department_id'] ?? '') === (string) $department->id)>
                                {{ $department->department_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col gap-3 lg:col-span-1 lg:justify-end">
                    <button type="submit" class="rounded-2xl bg-slate-900 px-5 py-3 text-sm font-bold text-white transition hover:bg-slate-800">Lọc</button>
                </div>

                <div class="lg:col-span-12">
                    <a href="{{ route('admin.recruitment.job-posts') }}" class="inline-flex rounded-2xl bg-slate-100 px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-200">Xóa bộ lọc</a>
                </div>
            </form>
        </section>

        <section class="recruitment-panel overflow-hidden rounded-[2rem] border border-slate-100 bg-white shadow-sm shadow-slate-200/60">
            <div class="overflow-x-auto">
                <table class="min-w-[1050px] divide-y divide-slate-100">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wide text-slate-500">Vị trí</th>
                            <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wide text-slate-500">Phòng ban</th>
                            <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wide text-slate-500">Phụ trách</th>
                            <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wide text-slate-500">Lương / Hạn</th>
                            <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wide text-slate-500">Trạng thái</th>
                            <th class="px-5 py-4 text-right text-xs font-black uppercase tracking-wide text-slate-500">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($jobPosts as $jobPost)
                            <tr class="align-top transition hover:bg-slate-50/70">
                                <td class="px-5 py-4">
                                    <p class="max-w-sm break-words font-bold text-slate-900">{{ $jobPost->title }}</p>
                                    <p class="mt-1 text-sm text-slate-500">Số lượng: {{ $jobPost->quantity }}</p>
                                    <p class="mt-1 max-w-sm break-words text-sm text-slate-500">
                                        {{ $jobPost->work_location ?: 'Chưa có địa điểm' }}
                                        @if ($jobPost->work_type)
                                            - {{ $workTypes[$jobPost->work_type] ?? $jobPost->work_type }}
                                        @endif
                                    </p>
                                    <details class="mt-3 max-w-md rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                                        <summary class="cursor-pointer text-sm font-bold text-cyan-700">Xem chi tiết nhanh</summary>
                                        <div class="mt-3 space-y-3 text-sm leading-6 text-slate-600">
                                            <div>
                                                <p class="font-bold text-slate-800">Mô tả</p>
                                                <p class="break-words">{{ $jobPost->description ?: 'Chưa có mô tả.' }}</p>
                                            </div>
                                            <div>
                                                <p class="font-bold text-slate-800">Yêu cầu</p>
                                                <p class="break-words">{{ $jobPost->requirements ?: 'Chưa có yêu cầu.' }}</p>
                                            </div>
                                            <div>
                                                <p class="font-bold text-slate-800">Quyền lợi</p>
                                                <p class="break-words">{{ $jobPost->benefits ?: 'Chưa có quyền lợi.' }}</p>
                                            </div>
                                        </div>
                                    </details>
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-600">{{ $jobPost->department?->department_name ?? 'Chưa gắn' }}</td>
                                <td class="px-5 py-4 text-sm text-slate-600">
                                    {{ $jobPost->recruiter?->full_name ?? 'Chưa gắn' }}
                                    @if ($jobPost->submittedBy)
                                        <p class="mt-1 text-xs text-amber-700">Gửi bởi: {{ $jobPost->submittedBy->full_name }}</p>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-600">
                                    @if ($jobPost->salary_min || $jobPost->salary_max)
                                        <p>{{ $jobPost->salary_min ? number_format((float) $jobPost->salary_min, 0, ',', '.') : '0' }} - {{ $jobPost->salary_max ? number_format((float) $jobPost->salary_max, 0, ',', '.') : 'Thỏa thuận' }}</p>
                                    @else
                                        <p>Thỏa thuận</p>
                                    @endif
                                    <p class="mt-1 text-slate-500">{{ $jobPost->application_deadline?->format('d/m/Y') ?? 'Chưa có hạn' }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    @if ($jobPost->status === 'pending_approval')
                                        <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-800">Chờ duyệt</span>
                                    @elseif ($jobPost->status === 'rejected')
                                        <span class="inline-flex rounded-full bg-rose-100 px-3 py-1 text-xs font-bold text-rose-800">Đã từ chối</span>
                                    @else
                                        <form action="{{ route('admin.recruitment.job-posts.update-status', $jobPost) }}" method="POST" class="inline-block">
                                            @csrf
                                            @method('PATCH')
                                            <select name="status"
                                                    class="min-w-[8.5rem] rounded-full border-0 py-1.5 pl-3 pr-8 text-xs font-bold shadow-sm ring-1 ring-inset transition focus:ring-2 focus:ring-cyan-500/30 {{ $jobPost->status === 'open' ? 'bg-emerald-100 text-emerald-800 ring-emerald-200' : 'bg-slate-100 text-slate-700 ring-slate-200' }}"
                                                    onchange="this.form.submit()">
                                                <option value="open" @selected($jobPost->status === 'open')>Đang tuyển</option>
                                                <option value="closed" @selected($jobPost->status === 'closed')>Đã đóng</option>
                                            </select>
                                        </form>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap justify-end gap-2">
                                        @if ($jobPost->status === 'pending_approval')
                                            <form action="{{ route('admin.recruitment.job-posts.approve', $jobPost) }}" method="POST">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="rounded-xl bg-emerald-600 px-3 py-2 text-sm font-bold text-white hover:bg-emerald-700">Duyệt</button>
                                            </form>
                                            <form action="{{ route('admin.recruitment.job-posts.reject', $jobPost) }}" method="POST" onsubmit="return confirm('Từ chối tin tuyển dụng này?')">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="rounded-xl bg-rose-50 px-3 py-2 text-sm font-bold text-rose-700 hover:bg-rose-100">Từ chối</button>
                                            </form>
                                        @endif
                                        <a href="{{ route('admin.recruitment.job-posts.edit', $jobPost) }}" class="rounded-xl bg-amber-50 px-3 py-2 text-sm font-bold text-amber-700 transition hover:bg-amber-100">Sửa</a>
                                        <form action="{{ route('admin.recruitment.job-posts.destroy', $jobPost) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa tin tuyển dụng này?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-xl bg-red-50 px-3 py-2 text-sm font-bold text-red-700 transition hover:bg-red-100">Xóa</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-14 text-center text-sm text-slate-500">Chưa có tin tuyển dụng nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-100 p-5">
                {{ $jobPosts->links() }}
            </div>
        </section>
        @endunless
    </div>
</x-admin-layout>
