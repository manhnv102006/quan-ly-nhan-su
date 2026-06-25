<x-admin-layout title="Tin tuyen dung">
    @php
        $showForm = ($showCreateForm ?? false) || ($showEditForm ?? false);
        $formJobPost = ($showEditForm ?? false) ? ($editingJobPost ?? null) : null;
        $formAction = ($showEditForm ?? false) && $formJobPost
            ? route('admin.recruitment.job-posts.update', $formJobPost)
            : route('admin.recruitment.job-posts.store');
        $workTypes = [
            'full_time' => 'Full-time',
            'part_time' => 'Part-time',
            'remote' => 'Remote',
            'hybrid' => 'Hybrid',
            'contract' => 'Contract',
        ];
    @endphp

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-sm text-slate-500">
                    <a href="{{ route('admin.recruitment') }}" class="hover:text-cyan-600 transition">Tuyen dung</a>
                    <span>/</span>
                    <span class="font-medium text-slate-700">Tin tuyen dung</span>
                </div>
                <h2 class="mt-2 text-2xl font-bold text-slate-800">Quan ly tin tuyen dung</h2>
                <p class="mt-1 text-sm text-slate-500">Theo doi nhu cau tuyen, phong ban, nguoi phu trach va han nop ho so.</p>
            </div>

            <a href="{{ route('admin.recruitment.job-posts.create') }}"
               class="inline-flex items-center rounded-xl bg-cyan-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-cyan-500/20 transition hover:bg-cyan-700">
                Tao tin tuyen dung
            </a>
        </div>

        @if (session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-medium text-red-700">
                {{ session('error') }}
            </div>
        @endif

        @if (isset($errors) && $errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
                <p class="font-semibold">Vui long kiem tra lai thong tin:</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Tong tin</p>
                <p class="mt-2 text-3xl font-bold text-slate-800">{{ $stats['total'] ?? 0 }}</p>
            </div>
            <div class="rounded-3xl border border-emerald-100 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-emerald-600">Dang mo</p>
                <p class="mt-2 text-3xl font-bold text-emerald-700">{{ $stats['open'] ?? 0 }}</p>
            </div>
            <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Da dong</p>
                <p class="mt-2 text-3xl font-bold text-slate-700">{{ $stats['closed'] ?? 0 }}</p>
            </div>
        </div>

        @if ($showForm)
            <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
                <div class="mb-5 flex items-center justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-semibold text-slate-800">
                            {{ ($showEditForm ?? false) ? 'Cap nhat tin tuyen dung' : 'Tao tin tuyen dung moi' }}
                        </h3>
                        <p class="mt-1 text-sm text-slate-500">Nhap ro vi tri, so luong, muc luong va thong tin ung vien can biet.</p>
                    </div>
                    <a href="{{ route('admin.recruitment.job-posts') }}" class="text-sm font-medium text-slate-500 hover:text-cyan-600">Dong form</a>
                </div>

                <form action="{{ $formAction }}" method="POST" class="grid grid-cols-1 gap-5 lg:grid-cols-2">
                    @csrf
                    @if (($showEditForm ?? false) && $formJobPost)
                        @method('PUT')
                    @endif

                    <div class="lg:col-span-2">
                        <label class="mb-2 block text-sm font-medium text-slate-700">Tieu de tin tuyen dung</label>
                        <input type="text" name="title" value="{{ old('title', $formJobPost?->title) }}" required
                               class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-cyan-500 focus:ring-cyan-500">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Phong ban</label>
                        <select name="department_id" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-cyan-500 focus:ring-cyan-500">
                            <option value="">Chua gan phong ban</option>
                            @foreach (($departments ?? collect()) as $department)
                                <option value="{{ $department->id }}" @selected((string) old('department_id', $formJobPost?->department_id) === (string) $department->id)>
                                    {{ $department->department_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Nguoi phu trach</label>
                        <select name="recruiter_id" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-cyan-500 focus:ring-cyan-500">
                            <option value="">Chua gan nguoi phu trach</option>
                            @foreach (($recruiters ?? collect()) as $recruiter)
                                <option value="{{ $recruiter->id }}" @selected((string) old('recruiter_id', $formJobPost?->recruiter_id) === (string) $recruiter->id)>
                                    {{ $recruiter->employee_code }} - {{ $recruiter->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">So luong tuyen</label>
                        <input type="number" min="1" name="quantity" value="{{ old('quantity', $formJobPost?->quantity ?? 1) }}" required
                               class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-cyan-500 focus:ring-cyan-500">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Trang thai</label>
                        <select name="status" required class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-cyan-500 focus:ring-cyan-500">
                            <option value="open" @selected(old('status', $formJobPost?->status ?? 'open') === 'open')>Dang mo</option>
                            <option value="closed" @selected(old('status', $formJobPost?->status) === 'closed')>Da dong</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Luong toi thieu</label>
                        <input type="number" min="0" step="100000" name="salary_min" value="{{ old('salary_min', $formJobPost?->salary_min) }}"
                               class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-cyan-500 focus:ring-cyan-500">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Luong toi da</label>
                        <input type="number" min="0" step="100000" name="salary_max" value="{{ old('salary_max', $formJobPost?->salary_max) }}"
                               class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-cyan-500 focus:ring-cyan-500">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Dia diem lam viec</label>
                        <input type="text" name="work_location" value="{{ old('work_location', $formJobPost?->work_location) }}"
                               class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-cyan-500 focus:ring-cyan-500">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Hinh thuc lam viec</label>
                        <select name="work_type" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-cyan-500 focus:ring-cyan-500">
                            <option value="">Chua chon</option>
                            @foreach ($workTypes as $value => $label)
                                <option value="{{ $value }}" @selected(old('work_type', $formJobPost?->work_type) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Han nop ho so</label>
                        <input type="date" name="application_deadline" value="{{ old('application_deadline', $formJobPost?->application_deadline?->format('Y-m-d')) }}"
                               class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-cyan-500 focus:ring-cyan-500">
                    </div>

                    <div class="lg:col-span-2">
                        <label class="mb-2 block text-sm font-medium text-slate-700">Mo ta cong viec</label>
                        <textarea name="description" rows="4" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-cyan-500 focus:ring-cyan-500">{{ old('description', $formJobPost?->description) }}</textarea>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Yeu cau ung vien</label>
                        <textarea name="requirements" rows="4" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-cyan-500 focus:ring-cyan-500">{{ old('requirements', $formJobPost?->requirements) }}</textarea>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Quyen loi</label>
                        <textarea name="benefits" rows="4" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-cyan-500 focus:ring-cyan-500">{{ old('benefits', $formJobPost?->benefits) }}</textarea>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 lg:col-span-2">
                        <button type="submit" class="rounded-xl bg-cyan-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-cyan-700">
                            {{ ($showEditForm ?? false) ? 'Luu thay doi' : 'Tao tin' }}
                        </button>
                        <a href="{{ route('admin.recruitment.job-posts') }}" class="rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">Huy</a>
                    </div>
                </form>
            </div>
        @endif

        <div class="rounded-3xl border border-slate-100 bg-white shadow-sm">
            <div class="border-b border-slate-100 p-5">
                <form method="GET" action="{{ route('admin.recruitment.job-posts') }}" class="flex flex-col gap-3 md:flex-row">
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Tim theo tieu de, phong ban, dia diem..."
                           class="flex-1 rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-cyan-500 focus:ring-cyan-500">
                    <button type="submit" class="rounded-xl bg-slate-800 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-900">Tim kiem</button>
                    @if (($search ?? '') !== '')
                        <a href="{{ route('admin.recruitment.job-posts') }}" class="rounded-xl border border-slate-200 px-5 py-3 text-center text-sm font-semibold text-slate-600 transition hover:bg-slate-50">Xoa loc</a>
                    @endif
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Vi tri</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Phong ban</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Nguoi phu trach</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Luong / Han nop</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Trang thai</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Thao tac</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($jobPosts as $jobPost)
                            <tr class="align-top">
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-slate-800">{{ $jobPost->title }}</p>
                                    <p class="mt-1 text-sm text-slate-500">So luong: {{ $jobPost->quantity }}</p>
                                    @if ($jobPost->work_location || $jobPost->work_type)
                                        <p class="mt-1 text-sm text-slate-500">
                                            {{ $jobPost->work_location ?: 'Chua co dia diem' }}
                                            @if ($jobPost->work_type)
                                                - {{ $workTypes[$jobPost->work_type] ?? $jobPost->work_type }}
                                            @endif
                                        </p>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-600">{{ $jobPost->department?->department_name ?? 'Chua gan' }}</td>
                                <td class="px-5 py-4 text-sm text-slate-600">{{ $jobPost->recruiter?->full_name ?? 'Chua gan' }}</td>
                                <td class="px-5 py-4 text-sm text-slate-600">
                                    @if ($jobPost->salary_min || $jobPost->salary_max)
                                        <p>{{ $jobPost->salary_min ? number_format((float) $jobPost->salary_min, 0, ',', '.') : '0' }} - {{ $jobPost->salary_max ? number_format((float) $jobPost->salary_max, 0, ',', '.') : 'Thoa thuan' }}</p>
                                    @else
                                        <p>Thoa thuan</p>
                                    @endif
                                    <p class="mt-1 text-slate-500">Han: {{ $jobPost->application_deadline?->format('d/m/Y') ?? 'Chua co' }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    @if ($jobPost->status === 'open')
                                        <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Dang mo</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-slate-200 px-3 py-1 text-xs font-semibold text-slate-700">Da dong</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.recruitment.job-posts.edit', $jobPost) }}" class="rounded-lg bg-amber-100 px-3 py-2 text-sm font-medium text-amber-700 transition hover:bg-amber-200">Sua</a>
                                        <form action="{{ route('admin.recruitment.job-posts.destroy', $jobPost) }}" method="POST" onsubmit="return confirm('Ban co chac muon xoa tin tuyen dung nay?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg bg-red-100 px-3 py-2 text-sm font-medium text-red-700 transition hover:bg-red-200">Xoa</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center text-sm text-slate-500">Chua co tin tuyen dung nao.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-100 p-5">
                {{ $jobPosts->links() }}
            </div>
        </div>
    </div>
</x-admin-layout>
