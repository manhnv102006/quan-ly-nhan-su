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
    @endphp

    <div class="max-w-full overflow-hidden space-y-6">
        <section class="rounded-[2rem] border border-white/80 bg-white/90 p-5 shadow-sm shadow-slate-200/60 sm:p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
                        <a href="{{ route('admin.recruitment') }}" class="hover:text-cyan-600">Tuyển dụng</a>
                        <span>/</span>
                        <span class="font-semibold text-slate-700">Tin tuyển dụng</span>
                    </div>
                    <h2 class="mt-3 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">Quản lý tin tuyển dụng</h2>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                        Tạo nhu cầu tuyển dụng, gắn phòng ban, người phụ trách, mức lương và hạn nộp hồ sơ.
                    </p>
                </div>

                <a href="{{ route('admin.recruitment.job-posts.create') }}"
                   class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-cyan-500/20 transition hover:bg-cyan-700">
                    Tạo tin tuyển dụng
                </a>
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

        <section class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="rounded-[1.5rem] border border-slate-100 bg-white p-5 shadow-sm">
                <p class="text-sm font-bold text-slate-500">Tổng tin</p>
                <p class="mt-2 text-3xl font-black text-slate-900">{{ $stats['total'] ?? 0 }}</p>
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

        @if ($showForm)
            <section class="overflow-hidden rounded-[2rem] border border-slate-100 bg-white shadow-sm shadow-slate-200/60">
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

                    <div class="lg:col-span-2">
                        <label class="mb-2 block text-sm font-bold text-slate-700">Tiêu đề <span class="text-red-500">*</span></label>
                        <input type="text" name="title" value="{{ old('title', $formJobPost?->title) }}" required class="{{ $inputClass }}">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700">Phòng ban</label>
                        <select name="department_id" class="{{ $inputClass }}">
                            <option value="">Chưa gắn phòng ban</option>
                            @foreach (($departments ?? collect()) as $department)
                                <option value="{{ $department->id }}" @selected((string) old('department_id', $formJobPost?->department_id) === (string) $department->id)>
                                    {{ $department->department_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700">Người phụ trách</label>
                        <select name="recruiter_id" class="{{ $inputClass }}">
                            <option value="">Chưa gắn người phụ trách</option>
                            @foreach (($recruiters ?? collect()) as $recruiter)
                                <option value="{{ $recruiter->id }}" @selected((string) old('recruiter_id', $formJobPost?->recruiter_id) === (string) $recruiter->id)>
                                    {{ $recruiter->employee_code }} - {{ $recruiter->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700">Số lượng <span class="text-red-500">*</span></label>
                        <input type="number" min="1" name="quantity" value="{{ old('quantity', $formJobPost?->quantity ?? 1) }}" required class="{{ $inputClass }}">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700">Trạng thái <span class="text-red-500">*</span></label>
                        <select name="status" required class="{{ $inputClass }}">
                            <option value="open" @selected(old('status', $formJobPost?->status ?? 'open') === 'open')>Đang mở</option>
                            <option value="closed" @selected(old('status', $formJobPost?->status) === 'closed')>Đã đóng</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700">Lương tối thiểu</label>
                        <input type="number" min="0" step="100000" name="salary_min" value="{{ old('salary_min', $formJobPost?->salary_min) }}" class="{{ $inputClass }}">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700">Lương tối đa</label>
                        <input type="number" min="0" step="100000" name="salary_max" value="{{ old('salary_max', $formJobPost?->salary_max) }}" class="{{ $inputClass }}">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700">Địa điểm</label>
                        <input type="text" name="work_location" value="{{ old('work_location', $formJobPost?->work_location) }}" class="{{ $inputClass }}">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700">Hình thức</label>
                        <select name="work_type" class="{{ $inputClass }}">
                            <option value="">Chưa chọn</option>
                            @foreach ($workTypes as $value => $label)
                                <option value="{{ $value }}" @selected(old('work_type', $formJobPost?->work_type) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700">Hạn nộp hồ sơ</label>
                        <input type="date" name="application_deadline" value="{{ old('application_deadline', $formJobPost?->application_deadline?->format('Y-m-d')) }}" class="{{ $inputClass }}">
                    </div>

                    <div class="lg:col-span-2">
                        <label class="mb-2 block text-sm font-bold text-slate-700">Mô tả công việc</label>
                        <textarea name="description" rows="4" class="{{ $inputClass }} resize-y">{{ old('description', $formJobPost?->description) }}</textarea>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700">Yêu cầu</label>
                        <textarea name="requirements" rows="4" class="{{ $inputClass }} resize-y">{{ old('requirements', $formJobPost?->requirements) }}</textarea>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700">Quyền lợi</label>
                        <textarea name="benefits" rows="4" class="{{ $inputClass }} resize-y">{{ old('benefits', $formJobPost?->benefits) }}</textarea>
                    </div>

                    <div class="flex flex-col-reverse gap-3 lg:col-span-2 sm:flex-row sm:items-center">
                        <a href="{{ route('admin.recruitment.job-posts') }}" class="inline-flex items-center justify-center rounded-2xl bg-slate-100 px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-200">Hủy</a>
                        <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-cyan-500/20 transition hover:bg-cyan-700">
                            {{ ($showEditForm ?? false) ? 'Lưu thay đổi' : 'Tạo tin' }}
                        </button>
                    </div>
                </form>
            </section>
        @endif

        <section class="rounded-[2rem] border border-slate-100 bg-white p-5 shadow-sm shadow-slate-200/60">
            <form method="GET" action="{{ route('admin.recruitment.job-posts') }}" class="flex flex-col gap-3 md:flex-row">
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Tìm theo tiêu đề, phòng ban, người phụ trách..."
                       class="min-w-0 flex-1 rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10">
                <button type="submit" class="rounded-2xl bg-slate-900 px-5 py-3 text-sm font-bold text-white transition hover:bg-slate-800">Tìm kiếm</button>
                <a href="{{ route('admin.recruitment.job-posts') }}" class="rounded-2xl bg-slate-100 px-5 py-3 text-center text-sm font-bold text-slate-700 transition hover:bg-slate-200">Xóa lọc</a>
            </form>
        </section>

        <section class="overflow-hidden rounded-[2rem] border border-slate-100 bg-white shadow-sm shadow-slate-200/60">
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
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-600">{{ $jobPost->department?->department_name ?? 'Chưa gắn' }}</td>
                                <td class="px-5 py-4 text-sm text-slate-600">{{ $jobPost->recruiter?->full_name ?? 'Chưa gắn' }}</td>
                                <td class="px-5 py-4 text-sm text-slate-600">
                                    @if ($jobPost->salary_min || $jobPost->salary_max)
                                        <p>{{ $jobPost->salary_min ? number_format((float) $jobPost->salary_min, 0, ',', '.') : '0' }} - {{ $jobPost->salary_max ? number_format((float) $jobPost->salary_max, 0, ',', '.') : 'Thỏa thuận' }}</p>
                                    @else
                                        <p>Thỏa thuận</p>
                                    @endif
                                    <p class="mt-1 text-slate-500">{{ $jobPost->application_deadline?->format('d/m/Y') ?? 'Chưa có hạn' }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    @if ($jobPost->status === 'open')
                                        <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700">Đang mở</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">Đã đóng</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
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
    </div>
</x-admin-layout>
