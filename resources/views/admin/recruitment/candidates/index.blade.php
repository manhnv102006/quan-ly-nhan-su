<x-admin-layout title="Ung vien">
@php
    $filters = $filters ?? [
        'search' => '', 'status' => '', 'job_post_id' => '',
        'cv_status' => '', 'converted' => '', 'created_from' => '', 'created_to' => '',
    ];

    $statusConfig = [
        'new' => ['label' => 'Moi', 'dot' => 'bg-sky-500', 'badge' => 'bg-sky-100 text-sky-700'],
        'interview' => ['label' => 'Phong van', 'dot' => 'bg-amber-500', 'badge' => 'bg-amber-100 text-amber-700'],
        'passed' => ['label' => 'Dat', 'dot' => 'bg-emerald-500', 'badge' => 'bg-emerald-100 text-emerald-700'],
        'failed' => ['label' => 'Khong dat', 'dot' => 'bg-rose-500', 'badge' => 'bg-rose-100 text-rose-700'],
    ];

    $hasFilters = collect($filters)->filter(fn ($value) => $value !== '')->isNotEmpty();
@endphp

<div class="space-y-6">
    <section class="rounded-[2rem] border border-white/80 bg-white p-5 shadow-sm shadow-slate-200/60 sm:p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <nav class="mb-2 flex items-center gap-2 text-sm text-slate-500">
                    <a href="{{ route('admin.recruitment') }}" class="transition hover:text-violet-600">Tuyen dung</a>
                    <span>/</span>
                    <span class="font-semibold text-slate-700">Ung vien</span>
                </nav>
                <h1 class="text-2xl font-black text-slate-900">Ho so ung vien</h1>
                <p class="mt-1 max-w-3xl text-sm leading-6 text-slate-500">
                    Khu vuc nay dung de xem va tra cuu ho so. Ung vien da dat phong van se duoc xac nhan len nhan vien o trang rieng.
                </p>
            </div>

            <a href="{{ route('admin.recruitment.interviewed-candidates') }}"
               class="inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-cyan-500/20 transition hover:bg-cyan-700">
                Ung vien da dat phong van
            </a>
        </div>
    </section>

    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">
            {{ session('error') }}
        </div>
    @endif

    <section class="grid grid-cols-2 gap-3 lg:grid-cols-6">
        @foreach ([
            ['Tong ung vien', $stats['total'] ?? 0, 'text-slate-800', 'bg-white', ''],
            ['Moi', $stats['new'] ?? 0, 'text-sky-700', 'bg-sky-50', 'new'],
            ['Phong van', $stats['interview'] ?? 0, 'text-amber-700', 'bg-amber-50', 'interview'],
            ['Dat', $stats['passed'] ?? 0, 'text-emerald-700', 'bg-emerald-50', 'passed'],
            ['Khong dat', $stats['failed'] ?? 0, 'text-rose-700', 'bg-rose-50', 'failed'],
            ['Da nhan viec', $stats['converted'] ?? 0, 'text-violet-700', 'bg-violet-50', ''],
        ] as [$label, $value, $textClass, $bgClass, $statusFilter])
            <a href="{{ $statusFilter ? route('admin.recruitment.candidates', ['status' => $statusFilter]) : route('admin.recruitment.candidates') }}"
               class="rounded-xl border border-slate-100 {{ $bgClass }} p-4 shadow-sm transition hover:shadow-md">
                <p class="text-xs font-semibold text-slate-500">{{ $label }}</p>
                <p class="mt-2 text-2xl font-black {{ $textClass }}">{{ $value }}</p>
            </a>
        @endforeach
    </section>

    <section class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
        <form method="GET" action="{{ route('admin.recruitment.candidates') }}"
              class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6">
            <div class="sm:col-span-2">
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Tim kiem</label>
                <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Ten, email, so dien thoai..."
                       class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20">
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Trang thai</label>
                <select name="status" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-800 outline-none transition focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20">
                    <option value="">Tat ca</option>
                    @foreach ($statusConfig as $value => $config)
                        <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $config['label'] }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Tin tuyen dung</label>
                <select name="job_post_id" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-800 outline-none transition focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20">
                    <option value="">Tat ca vi tri</option>
                    @foreach ($jobPosts as $jobPost)
                        <option value="{{ $jobPost->id }}" @selected((string) $filters['job_post_id'] === (string) $jobPost->id)>{{ $jobPost->title }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">CV</label>
                <select name="cv_status" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-800 outline-none transition focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20">
                    <option value="">Tat ca</option>
                    <option value="has_cv" @selected($filters['cv_status'] === 'has_cv')>Co CV</option>
                    <option value="missing_cv" @selected($filters['cv_status'] === 'missing_cv')>Thieu CV</option>
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Nhan vien</label>
                <select name="converted" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-800 outline-none transition focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20">
                    <option value="">Tat ca</option>
                    <option value="yes" @selected($filters['converted'] === 'yes')>Da chuyen</option>
                    <option value="no" @selected($filters['converted'] === 'no')>Chua chuyen</option>
                </select>
            </div>

            <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-4 xl:col-span-6">
                <button type="submit" class="inline-flex items-center rounded-xl bg-violet-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-violet-700">
                    Loc ket qua
                </button>
                @if($hasFilters)
                    <a href="{{ route('admin.recruitment.candidates') }}"
                       class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                        Xoa bo loc
                    </a>
                    <span class="rounded-full bg-violet-50 px-3 py-1 text-xs font-semibold text-violet-700">
                        {{ $candidates->total() }} ket qua
                    </span>
                @endif
            </div>
        </form>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <div>
                <h2 class="text-sm font-bold text-slate-800">Danh sach ho so</h2>
                <p class="text-xs text-slate-500">{{ $candidates->total() }} ung vien</p>
            </div>
        </div>

        @forelse ($candidates as $candidate)
            @php
                $config = $statusConfig[$candidate->status] ?? ['label' => $candidate->status, 'dot' => 'bg-slate-400', 'badge' => 'bg-slate-100 text-slate-700'];
                $initials = collect(explode(' ', $candidate->full_name))->map(fn ($word) => strtoupper(substr($word, 0, 1)))->last(null, '?');
            @endphp

            <article class="border-b border-slate-100 px-6 py-4 transition hover:bg-slate-50/60 last:border-b-0">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div class="flex min-w-0 flex-1 items-start gap-4">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-violet-100 text-base font-bold text-violet-700">
                            {{ $initials }}
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <a href="{{ route('admin.recruitment.candidates.show', $candidate) }}"
                                   class="font-bold text-slate-800 transition hover:text-violet-700">
                                    {{ $candidate->full_name }}
                                </a>
                                <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $config['badge'] }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $config['dot'] }}"></span>
                                    {{ $config['label'] }}
                                </span>
                                <span class="rounded-full {{ $candidate->cv_file ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }} px-2 py-0.5 text-xs font-semibold">
                                    {{ $candidate->cv_file ? 'Co CV' : 'Thieu CV' }}
                                </span>
                                @if($candidate->employee)
                                    <a href="{{ route('admin.employees.show', $candidate->employee) }}"
                                       class="rounded-full bg-violet-100 px-2 py-0.5 text-xs font-semibold text-violet-700 transition hover:bg-violet-200">
                                        Nhan vien {{ $candidate->employee->employee_code }}
                                    </a>
                                @endif
                            </div>

                            <div class="mt-1.5 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500">
                                <span>{{ $candidate->phone }}</span>
                                <span>{{ $candidate->email }}</span>
                                @if($candidate->jobPost)
                                    <span>{{ $candidate->jobPost->title }}</span>
                                @endif
                                <span class="text-slate-400">{{ $candidate->created_at?->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex shrink-0 flex-wrap items-center gap-2">
                        <a href="{{ route('admin.recruitment.candidates.show', $candidate) }}"
                           class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                            Xem ho so
                        </a>
                    </div>
                </div>
            </article>
        @empty
            <div class="py-16 text-center">
                <h3 class="text-sm font-bold text-slate-700">Chua co ung vien nao</h3>
                <p class="mt-1 text-sm text-slate-500">
                    @if($hasFilters)
                        Khong tim thay ung vien phu hop voi bo loc hien tai.
                    @else
                        Ho so ung vien se xuat hien tai day sau khi duoc tao hoac ung tuyen tu trang public.
                    @endif
                </p>
                @if($hasFilters)
                    <a href="{{ route('admin.recruitment.candidates') }}"
                       class="mt-4 inline-flex rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        Xoa bo loc
                    </a>
                @endif
            </div>
        @endforelse

        @if($candidates->hasPages())
            <div class="border-t border-slate-100 px-6 py-4">
                {{ $candidates->links() }}
            </div>
        @endif
    </section>
</div>
</x-admin-layout>
