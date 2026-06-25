<x-admin-layout title="Ung vien">
    @php
        $filters = $filters ?? [
            'search' => '',
            'status' => '',
            'job_post_id' => '',
            'cv_status' => '',
            'converted' => '',
            'created_from' => '',
            'created_to' => '',
        ];
        $statusLabels = [
            'new' => 'Moi',
            'interview' => 'Phong van',
            'passed' => 'Dat',
            'failed' => 'Khong dat',
        ];
        $statusClasses = [
            'new' => 'bg-sky-100 text-sky-700',
            'interview' => 'bg-amber-100 text-amber-700',
            'passed' => 'bg-emerald-100 text-emerald-700',
            'failed' => 'bg-rose-100 text-rose-700',
        ];
    @endphp

    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-sm text-slate-500">
                    <a href="{{ route('admin.recruitment') }}" class="hover:text-cyan-600 transition">Tuyen dung</a>
                    <span>/</span>
                    <span class="font-medium text-slate-700">Ung vien</span>
                </div>
                <h2 class="mt-2 text-2xl font-bold text-slate-800">Quan ly ung vien</h2>
                <p class="mt-1 text-sm text-slate-500">Loc ho so, theo doi trang thai phong van va chuyen ung vien dat thanh nhan vien.</p>
            </div>

            <a href="{{ route('admin.recruitment.candidates.create') }}"
               class="inline-flex items-center rounded-xl bg-cyan-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-cyan-500/20 transition hover:bg-cyan-700">
                Them ung vien
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

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-6">
            <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-slate-500">Tong</p>
                <p class="mt-2 text-2xl font-bold text-slate-800">{{ $stats['total'] ?? 0 }}</p>
            </div>
            <div class="rounded-3xl border border-sky-100 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-sky-600">Moi</p>
                <p class="mt-2 text-2xl font-bold text-sky-700">{{ $stats['new'] ?? 0 }}</p>
            </div>
            <div class="rounded-3xl border border-amber-100 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-amber-600">Phong van</p>
                <p class="mt-2 text-2xl font-bold text-amber-700">{{ $stats['interview'] ?? 0 }}</p>
            </div>
            <div class="rounded-3xl border border-emerald-100 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-emerald-600">Dat</p>
                <p class="mt-2 text-2xl font-bold text-emerald-700">{{ $stats['passed'] ?? 0 }}</p>
            </div>
            <div class="rounded-3xl border border-rose-100 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-rose-600">Khong dat</p>
                <p class="mt-2 text-2xl font-bold text-rose-700">{{ $stats['failed'] ?? 0 }}</p>
            </div>
            <div class="rounded-3xl border border-indigo-100 bg-white p-5 shadow-sm">
                <p class="text-sm font-medium text-indigo-600">Da nhan viec</p>
                <p class="mt-2 text-2xl font-bold text-indigo-700">{{ $stats['converted'] ?? 0 }}</p>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-100 bg-white p-5 shadow-sm">
            <form method="GET" action="{{ route('admin.recruitment.candidates') }}" class="grid grid-cols-1 gap-4 lg:grid-cols-6">
                <div class="lg:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-slate-700">Tim kiem</label>
                    <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Ten, email, so dien thoai, vi tri..."
                           class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-cyan-500 focus:ring-cyan-500">
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Trang thai</label>
                    <select name="status" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-cyan-500 focus:ring-cyan-500">
                        <option value="">Tat ca</option>
                        @foreach ($statusLabels as $value => $label)
                            <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Tin tuyen dung</label>
                    <select name="job_post_id" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-cyan-500 focus:ring-cyan-500">
                        <option value="">Tat ca</option>
                        @foreach ($jobPosts as $jobPost)
                            <option value="{{ $jobPost->id }}" @selected((string) $filters['job_post_id'] === (string) $jobPost->id)>{{ $jobPost->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">CV</label>
                    <select name="cv_status" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-cyan-500 focus:ring-cyan-500">
                        <option value="">Tat ca</option>
                        <option value="has_cv" @selected($filters['cv_status'] === 'has_cv')>Co CV</option>
                        <option value="missing_cv" @selected($filters['cv_status'] === 'missing_cv')>Thieu CV</option>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Nhan vien</label>
                    <select name="converted" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-cyan-500 focus:ring-cyan-500">
                        <option value="">Tat ca</option>
                        <option value="yes" @selected($filters['converted'] === 'yes')>Da chuyen</option>
                        <option value="no" @selected($filters['converted'] === 'no')>Chua chuyen</option>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Tu ngay</label>
                    <input type="date" name="created_from" value="{{ $filters['created_from'] }}" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-cyan-500 focus:ring-cyan-500">
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Den ngay</label>
                    <input type="date" name="created_to" value="{{ $filters['created_to'] }}" class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm focus:border-cyan-500 focus:ring-cyan-500">
                </div>

                <div class="flex items-end gap-3 lg:col-span-4">
                    <button type="submit" class="rounded-xl bg-slate-800 px-5 py-3 text-sm font-semibold text-white transition hover:bg-slate-900">Loc ung vien</button>
                    <a href="{{ route('admin.recruitment.candidates') }}" class="rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">Xoa loc</a>
                </div>
            </form>
        </div>

        <div class="rounded-3xl border border-slate-100 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Ung vien</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Tin tuyen dung</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Lien he</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">CV</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Trang thai</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">Nhan vien</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">Thao tac</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($candidates as $candidate)
                            <tr class="align-top">
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-slate-800">{{ $candidate->full_name }}</p>
                                    <p class="mt-1 text-sm text-slate-500">Tao luc {{ $candidate->created_at?->format('d/m/Y H:i') ?? '-' }}</p>
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-600">
                                    {{ $candidate->jobPost?->title ?? 'Chua gan tin' }}
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-600">
                                    <p>{{ $candidate->phone }}</p>
                                    <p class="mt-1">{{ $candidate->email }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    @if ($candidate->cv_file)
                                        <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Co CV</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Thieu CV</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusClasses[$candidate->status] ?? 'bg-slate-100 text-slate-700' }}">
                                        {{ $statusLabels[$candidate->status] ?? $candidate->status }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-600">
                                    @if ($candidate->employee)
                                        <a href="{{ route('admin.employees.show', $candidate->employee) }}" class="font-medium text-cyan-700 hover:text-cyan-800">
                                            {{ $candidate->employee->employee_code }}
                                        </a>
                                    @else
                                        <span class="text-slate-400">Chua chuyen</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.recruitment.candidates.show', $candidate) }}" class="rounded-lg bg-cyan-100 px-3 py-2 text-sm font-medium text-cyan-700 transition hover:bg-cyan-200">Xem</a>
                                        <a href="{{ route('admin.recruitment.candidates.edit', $candidate) }}" class="rounded-lg bg-amber-100 px-3 py-2 text-sm font-medium text-amber-700 transition hover:bg-amber-200">Sua</a>
                                        <form action="{{ route('admin.recruitment.candidates.destroy', $candidate) }}" method="POST" onsubmit="return confirm('Ban co chac muon xoa ung vien nay?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg bg-red-100 px-3 py-2 text-sm font-medium text-red-700 transition hover:bg-red-200">Xoa</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center text-sm text-slate-500">Chua co ung vien phu hop.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-100 p-5">
                {{ $candidates->links() }}
            </div>
        </div>
    </div>
</x-admin-layout>
