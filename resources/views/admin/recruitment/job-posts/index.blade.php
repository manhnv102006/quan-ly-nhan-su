<x-admin-layout title="Tin tuyen dung">
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
                        <a href="{{ route('admin.recruitment') }}" class="hover:text-cyan-600">Tuyen dung</a>
                        <span>/</span>
                        <span class="font-semibold text-slate-700">Tin tuyen dung</span>
                    </div>
                    <h2 class="mt-3 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">Quan ly tin tuyen dung</h2>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                        Tao nhu cau tuyen dung, gan phong ban, nguoi phu trach, muc luong va han nop ho so.
                    </p>
                </div>

                <a href="{{ route('admin.recruitment.job-posts.create') }}"
                   class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-cyan-500/20 transition hover:bg-cyan-700">
                    Tao tin tuyen dung
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
                <p class="font-bold">Vui long kiem tra lai thong tin:</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="rounded-[1.5rem] border border-slate-100 bg-white p-5 shadow-sm">
                <p class="text-sm font-bold text-slate-500">Tong tin</p>
                <p class="mt-2 text-3xl font-black text-slate-900">{{ $stats['total'] ?? 0 }}</p>
            </div>
            <div class="rounded-[1.5rem] border border-emerald-100 bg-emerald-50 p-5 shadow-sm">
                <p class="text-sm font-bold text-emerald-700">Dang mo</p>
                <p class="mt-2 text-3xl font-black text-emerald-800">{{ $stats['open'] ?? 0 }}</p>
            </div>
            <div class="rounded-[1.5rem] border border-slate-100 bg-slate-50 p-5 shadow-sm">
                <p class="text-sm font-bold text-slate-600">Da dong</p>
                <p class="mt-2 text-3xl font-black text-slate-900">{{ $stats['closed'] ?? 0 }}</p>
            </div>
        </section>

        @if ($showForm)
            <section class="overflow-hidden rounded-[2rem] border border-slate-100 bg-white shadow-sm shadow-slate-200/60">
                <div class="border-b border-slate-100 bg-slate-50/70 px-5 py-4 sm:px-6">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-base font-black text-slate-900">{{ ($showEditForm ?? false) ? 'Cap nhat tin tuyen dung' : 'Tao tin tuyen dung' }}</h3>
                            <p class="mt-1 text-sm text-slate-500">Nhap day du thong tin de ung vien va HR de theo doi.</p>
                        </div>
                        <a href="{{ route('admin.recruitment.job-posts') }}" class="text-sm font-bold text-slate-500 transition hover:text-cyan-700">Dong form</a>
                    </div>
                </div>

                <form action="{{ $formAction }}" method="POST" class="grid grid-cols-1 gap-5 p-5 sm:p-6 lg:grid-cols-2">
                    @csrf
                    @if (($showEditForm ?? false) && $formJobPost)
                        @method('PUT')
                    @endif

                    <div class="lg:col-span-2">
                        <label class="mb-2 block text-sm font-bold text-slate-700">Tieu de <span class="text-red-500">*</span></label>
                        <input type="text" name="title" value="{{ old('title', $formJobPost?->title) }}" required class="{{ $inputClass }}">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700">Phong ban</label>
                        <select name="department_id" class="{{ $inputClass }}">
                            <option value="">Chua gan phong ban</option>
                            @foreach (($departments ?? collect()) as $department)
                                <option value="{{ $department->id }}" @selected((string) old('department_id', $formJobPost?->department_id) === (string) $department->id)>
                                    {{ $department->department_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700">Nguoi phu trach</label>
                        <select name="recruiter_id" class="{{ $inputClass }}">
                            <option value="">Chua gan nguoi phu trach</option>
                            @foreach (($recruiters ?? collect()) as $recruiter)
                                <option value="{{ $recruiter->id }}" @selected((string) old('recruiter_id', $formJobPost?->recruiter_id) === (string) $recruiter->id)>
                                    {{ $recruiter->employee_code }} - {{ $recruiter->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700">So luong <span class="text-red-500">*</span></label>
                        <input type="number" min="1" name="quantity" value="{{ old('quantity', $formJobPost?->quantity ?? 1) }}" required class="{{ $inputClass }}">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700">Trang thai <span class="text-red-500">*</span></label>
                        <select name="status" required class="{{ $inputClass }}">
                            <option value="open" @selected(old('status', $formJobPost?->status ?? 'open') === 'open')>Dang mo</option>
                            <option value="closed" @selected(old('status', $formJobPost?->status) === 'closed')>Da dong</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700">Luong toi thieu</label>
                        <input type="number" min="0" step="100000" name="salary_min" value="{{ old('salary_min', $formJobPost?->salary_min) }}" class="{{ $inputClass }}">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700">Luong toi da</label>
                        <input type="number" min="0" step="100000" name="salary_max" value="{{ old('salary_max', $formJobPost?->salary_max) }}" class="{{ $inputClass }}">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700">Dia diem</label>
                        <input type="text" name="work_location" value="{{ old('work_location', $formJobPost?->work_location) }}" class="{{ $inputClass }}">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700">Hinh thuc</label>
                        <select name="work_type" class="{{ $inputClass }}">
                            <option value="">Chua chon</option>
                            @foreach ($workTypes as $value => $label)
                                <option value="{{ $value }}" @selected(old('work_type', $formJobPost?->work_type) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700">Han nop ho so</label>
                        <input type="date" name="application_deadline" value="{{ old('application_deadline', $formJobPost?->application_deadline?->format('Y-m-d')) }}" class="{{ $inputClass }}">
                    </div>

                    <div class="lg:col-span-2">
                        <label class="mb-2 block text-sm font-bold text-slate-700">Mo ta cong viec</label>
                        <textarea name="description" rows="4" class="{{ $inputClass }} resize-y">{{ old('description', $formJobPost?->description) }}</textarea>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700">Yeu cau</label>
                        <textarea name="requirements" rows="4" class="{{ $inputClass }} resize-y">{{ old('requirements', $formJobPost?->requirements) }}</textarea>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-slate-700">Quyen loi</label>
                        <textarea name="benefits" rows="4" class="{{ $inputClass }} resize-y">{{ old('benefits', $formJobPost?->benefits) }}</textarea>
                    </div>

                    <div class="flex flex-col-reverse gap-3 lg:col-span-2 sm:flex-row sm:items-center">
                        <a href="{{ route('admin.recruitment.job-posts') }}" class="inline-flex items-center justify-center rounded-2xl bg-slate-100 px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-200">Huy</a>
                        <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-cyan-500/20 transition hover:bg-cyan-700">
                            {{ ($showEditForm ?? false) ? 'Luu thay doi' : 'Tao tin' }}
                        </button>
                    </div>
                </form>
            </section>
        @endif

        <section class="rounded-[2rem] border border-slate-100 bg-white p-5 shadow-sm shadow-slate-200/60">
            <form method="GET" action="{{ route('admin.recruitment.job-posts') }}" class="flex flex-col gap-3 md:flex-row">
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Tim theo tieu de, phong ban, nguoi phu trach..."
                       class="min-w-0 flex-1 rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10">
                <button type="submit" class="rounded-2xl bg-slate-900 px-5 py-3 text-sm font-bold text-white transition hover:bg-slate-800">Tim kiem</button>
                <a href="{{ route('admin.recruitment.job-posts') }}" class="rounded-2xl bg-slate-100 px-5 py-3 text-center text-sm font-bold text-slate-700 transition hover:bg-slate-200">Xoa loc</a>
            </form>
        </section>

        <section class="overflow-hidden rounded-[2rem] border border-slate-100 bg-white shadow-sm shadow-slate-200/60">
            <div class="overflow-x-auto">
                <table class="min-w-[1050px] divide-y divide-slate-100">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wide text-slate-500">Vi tri</th>
                            <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wide text-slate-500">Phong ban</th>
                            <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wide text-slate-500">Phu trach</th>
                            <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wide text-slate-500">Luong / Han</th>
                            <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wide text-slate-500">Trang thai</th>
                            <th class="px-5 py-4 text-right text-xs font-black uppercase tracking-wide text-slate-500">Thao tac</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($jobPosts as $jobPost)
                            <tr class="align-top transition hover:bg-slate-50/70">
                                <td class="px-5 py-4">
                                    <p class="max-w-sm break-words font-bold text-slate-900">{{ $jobPost->title }}</p>
                                    <p class="mt-1 text-sm text-slate-500">So luong: {{ $jobPost->quantity }}</p>
                                    <p class="mt-1 max-w-sm break-words text-sm text-slate-500">
                                        {{ $jobPost->work_location ?: 'Chua co dia diem' }}
                                        @if ($jobPost->work_type)
                                            - {{ $workTypes[$jobPost->work_type] ?? $jobPost->work_type }}
                                        @endif
                                    </p>
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-600">{{ $jobPost->department?->department_name ?? 'Chua gan' }}</td>
                                <td class="px-5 py-4 text-sm text-slate-600">{{ $jobPost->recruiter?->full_name ?? 'Chua gan' }}</td>
                                <td class="px-5 py-4 text-sm text-slate-600">
                                    @if ($jobPost->salary_min || $jobPost->salary_max)
                                        <p>{{ $jobPost->salary_min ? number_format((float) $jobPost->salary_min, 0, ',', '.') : '0' }} - {{ $jobPost->salary_max ? number_format((float) $jobPost->salary_max, 0, ',', '.') : 'Thoa thuan' }}</p>
                                    @else
                                        <p>Thoa thuan</p>
                                    @endif
                                    <p class="mt-1 text-slate-500">{{ $jobPost->application_deadline?->format('d/m/Y') ?? 'Chua co han' }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    @if ($jobPost->status === 'open')
                                        <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700">Dang mo</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">Da dong</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.recruitment.job-posts.edit', $jobPost) }}" class="rounded-xl bg-amber-50 px-3 py-2 text-sm font-bold text-amber-700 transition hover:bg-amber-100">Sua</a>
                                        <form action="{{ route('admin.recruitment.job-posts.destroy', $jobPost) }}" method="POST" onsubmit="return confirm('Ban co chac muon xoa tin tuyen dung nay?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-xl bg-red-50 px-3 py-2 text-sm font-bold text-red-700 transition hover:bg-red-100">Xoa</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-14 text-center text-sm text-slate-500">Chua co tin tuyen dung nao.</td>
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
