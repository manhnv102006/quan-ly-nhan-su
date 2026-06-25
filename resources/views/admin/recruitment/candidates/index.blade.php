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
        $statsCards = [
            ['label' => 'Tong', 'value' => $stats['total'] ?? 0, 'class' => 'text-slate-900 bg-slate-50'],
            ['label' => 'Moi', 'value' => $stats['new'] ?? 0, 'class' => 'text-sky-700 bg-sky-50'],
            ['label' => 'Phong van', 'value' => $stats['interview'] ?? 0, 'class' => 'text-amber-700 bg-amber-50'],
            ['label' => 'Dat', 'value' => $stats['passed'] ?? 0, 'class' => 'text-emerald-700 bg-emerald-50'],
            ['label' => 'Khong dat', 'value' => $stats['failed'] ?? 0, 'class' => 'text-rose-700 bg-rose-50'],
            ['label' => 'Da nhan viec', 'value' => $stats['converted'] ?? 0, 'class' => 'text-indigo-700 bg-indigo-50'],
        ];
    @endphp

    <div class="max-w-full overflow-hidden space-y-6">
        <section class="rounded-[2rem] border border-white/80 bg-white/90 p-5 shadow-sm shadow-slate-200/60 sm:p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
                        <a href="{{ route('admin.recruitment') }}" class="hover:text-cyan-600">Tuyen dung</a>
                        <span>/</span>
                        <span class="font-semibold text-slate-700">Ung vien</span>
                    </div>
                    <h2 class="mt-3 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">Quan ly ung vien</h2>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                        Loc ho so, theo doi trang thai phong van, CV va tinh trang chuyen thanh nhan vien.
                    </p>
                </div>

                <a href="{{ route('admin.recruitment.candidates.create') }}"
                   class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-cyan-500/20 transition hover:bg-cyan-700">
                    Them ung vien
                </a>
            </div>
        </section>

        @if (session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-700">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-bold text-red-700">{{ session('error') }}</div>
        @endif

        <section class="grid grid-cols-2 gap-4 md:grid-cols-3 xl:grid-cols-6">
            @foreach ($statsCards as $card)
                <div class="rounded-[1.5rem] border border-white/80 {{ $card['class'] }} p-4 shadow-sm">
                    <p class="truncate text-xs font-bold uppercase tracking-wide opacity-80">{{ $card['label'] }}</p>
                    <p class="mt-2 text-2xl font-black">{{ $card['value'] }}</p>
                </div>
            @endforeach
        </section>

        <section class="rounded-[2rem] border border-slate-100 bg-white p-5 shadow-sm shadow-slate-200/60">
            <form method="GET" action="{{ route('admin.recruitment.candidates') }}" class="grid grid-cols-1 gap-4 lg:grid-cols-12">
                <div class="lg:col-span-4">
                    <label class="mb-2 block text-sm font-bold text-slate-700">Tim kiem</label>
                    <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Ten, email, so dien thoai, vi tri..."
                           class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10">
                </div>

                <div class="lg:col-span-2">
                    <label class="mb-2 block text-sm font-bold text-slate-700">Trang thai</label>
                    <select name="status" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10">
                        <option value="">Tat ca</option>
                        @foreach ($statusLabels as $value => $label)
                            <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="lg:col-span-3">
                    <label class="mb-2 block text-sm font-bold text-slate-700">Tin tuyen dung</label>
                    <select name="job_post_id" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10">
                        <option value="">Tat ca</option>
                        @foreach ($jobPosts as $jobPost)
                            <option value="{{ $jobPost->id }}" @selected((string) $filters['job_post_id'] === (string) $jobPost->id)>{{ $jobPost->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="lg:col-span-3">
                    <label class="mb-2 block text-sm font-bold text-slate-700">Tinh trang</label>
                    <div class="grid grid-cols-2 gap-3">
                        <select name="cv_status" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10">
                            <option value="">CV</option>
                            <option value="has_cv" @selected($filters['cv_status'] === 'has_cv')>Co CV</option>
                            <option value="missing_cv" @selected($filters['cv_status'] === 'missing_cv')>Thieu CV</option>
                        </select>
                        <select name="converted" class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10">
                            <option value="">Nhan vien</option>
                            <option value="yes" @selected($filters['converted'] === 'yes')>Da chuyen</option>
                            <option value="no" @selected($filters['converted'] === 'no')>Chua chuyen</option>
                        </select>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <label class="mb-2 block text-sm font-bold text-slate-700">Tu ngay</label>
                    <input type="date" name="created_from" value="{{ $filters['created_from'] }}"
                           class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10">
                </div>

                <div class="lg:col-span-2">
                    <label class="mb-2 block text-sm font-bold text-slate-700">Den ngay</label>
                    <input type="date" name="created_to" value="{{ $filters['created_to'] }}"
                           class="w-full rounded-2xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10">
                </div>

                <div class="flex flex-col gap-3 lg:col-span-8 lg:flex-row lg:items-end">
                    <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-slate-900 px-5 py-3 text-sm font-bold text-white transition hover:bg-slate-800">Loc ung vien</button>
                    <a href="{{ route('admin.recruitment.candidates') }}" class="inline-flex items-center justify-center rounded-2xl bg-slate-100 px-5 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-200">Xoa loc</a>
                </div>
            </form>
        </section>

        <section class="overflow-hidden rounded-[2rem] border border-slate-100 bg-white shadow-sm shadow-slate-200/60">
            <div class="overflow-x-auto">
                <table class="min-w-[980px] divide-y divide-slate-100">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wide text-slate-500">Ung vien</th>
                            <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wide text-slate-500">Tin tuyen dung</th>
                            <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wide text-slate-500">Lien he</th>
                            <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wide text-slate-500">CV</th>
                            <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wide text-slate-500">Trang thai</th>
                            <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wide text-slate-500">Nhan vien</th>
                            <th class="px-5 py-4 text-right text-xs font-black uppercase tracking-wide text-slate-500">Thao tac</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($candidates as $candidate)
                            <tr class="align-top transition hover:bg-slate-50/70">
                                <td class="px-5 py-4">
                                    <p class="break-words font-bold text-slate-900">{{ $candidate->full_name }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $candidate->created_at?->format('d/m/Y H:i') ?? '-' }}</p>
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-600">
                                    <p class="max-w-xs break-words">{{ $candidate->jobPost?->title ?? 'Chua gan tin' }}</p>
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-600">
                                    <p class="break-words">{{ $candidate->phone }}</p>
                                    <p class="mt-1 break-words">{{ $candidate->email }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    @if ($candidate->cv_file)
                                        <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700">Co CV</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">Thieu CV</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold {{ $statusClasses[$candidate->status] ?? 'bg-slate-100 text-slate-700' }}">
                                        {{ $statusLabels[$candidate->status] ?? $candidate->status }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-sm">
                                    @if ($candidate->employee)
                                        <a href="{{ route('admin.employees.show', $candidate->employee) }}" class="font-bold text-cyan-700 hover:text-cyan-800">
                                            {{ $candidate->employee->employee_code }}
                                        </a>
                                    @else
                                        <span class="text-slate-400">Chua chuyen</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.recruitment.candidates.show', $candidate) }}" class="rounded-xl bg-cyan-50 px-3 py-2 text-sm font-bold text-cyan-700 transition hover:bg-cyan-100">Xem</a>
                                        <a href="{{ route('admin.recruitment.candidates.edit', $candidate) }}" class="rounded-xl bg-amber-50 px-3 py-2 text-sm font-bold text-amber-700 transition hover:bg-amber-100">Sua</a>
                                        <form action="{{ route('admin.recruitment.candidates.destroy', $candidate) }}" method="POST" onsubmit="return confirm('Ban co chac muon xoa ung vien nay?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-xl bg-red-50 px-3 py-2 text-sm font-bold text-red-700 transition hover:bg-red-100">Xoa</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-14 text-center text-sm text-slate-500">Chua co ung vien phu hop.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-100 p-5">
                {{ $candidates->links() }}
            </div>
        </section>
    </div>
</x-admin-layout>
