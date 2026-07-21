<x-admin-layout title="Ung vien da phong van">
    @php
        $filters = $filters ?? [
            'search' => '',
            'status' => '',
            'job_post_id' => '',
            'interview_result' => '',
            'interview_status' => '',
        ];

        $statusLabels = [
            'interview' => 'Cho xu ly',
            'passed' => 'Dat',
            'failed' => 'Khong dat',
        ];

        $statusClasses = [
            'interview' => 'bg-amber-100 text-amber-700 ring-amber-200',
            'passed' => 'bg-emerald-100 text-emerald-700 ring-emerald-200',
            'failed' => 'bg-rose-100 text-rose-700 ring-rose-200',
        ];

        $interviewStatusLabels = [
            'scheduled' => 'Da len lich',
            'completed' => 'Da phong van',
            'cancelled' => 'Da huy',
            'no_show' => 'Khong den',
        ];

        $interviewResultLabels = [
            'pending' => 'Cho ket qua',
            'passed' => 'Dat',
            'failed' => 'Khong dat',
        ];

        $resultClasses = [
            'pending' => 'bg-amber-100 text-amber-700 ring-amber-200',
            'passed' => 'bg-emerald-100 text-emerald-700 ring-emerald-200',
            'failed' => 'bg-rose-100 text-rose-700 ring-rose-200',
        ];

        $hasFilters = collect($filters)->filter(fn ($value) => $value !== '')->isNotEmpty();
    @endphp

    @include('admin.recruitment.partials.ui-contrast')

    <div class="recruitment-ui max-w-full overflow-hidden space-y-6">
        <section class="recruitment-hero rounded-[2rem] border border-white/80 bg-white/90 p-5 shadow-sm shadow-slate-200/60 sm:p-6">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
                        <a href="{{ route('admin.recruitment') }}" class="transition hover:text-cyan-600">Tuyen dung</a>
                        <span>/</span>
                        <span class="font-semibold text-slate-700">Ung vien da phong van</span>
                    </div>
                    <h1 class="mt-3 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">Ung vien da phong van</h1>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                        Xu ly ket qua sau phong van tai day. Danh sach ung vien thuong chi dung de xem ho so.
                    </p>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row">
                    <a href="{{ route('admin.recruitment.candidates') }}"
                       class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:border-cyan-200 hover:text-cyan-700">
                        Danh sach ung vien
                    </a>
                    <a href="{{ route('admin.recruitment.interviews') }}"
                       class="recruitment-btn-primary inline-flex items-center justify-center rounded-2xl bg-cyan-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-cyan-700">
                        Lich phong van
                    </a>
                </div>
            </div>
        </section>

        @if (session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm font-semibold text-rose-700">
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

        <section class="recruitment-stats grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
            @foreach ([
                ['label' => 'Tong da phong van', 'value' => $stats['total'] ?? 0, 'tone' => 'text-slate-900'],
                ['label' => 'Cho xu ly', 'value' => $stats['interview'] ?? 0, 'tone' => 'text-amber-700'],
                ['label' => 'Dat', 'value' => $stats['passed'] ?? 0, 'tone' => 'text-emerald-700'],
                ['label' => 'Khong dat', 'value' => $stats['failed'] ?? 0, 'tone' => 'text-rose-700'],
                ['label' => 'Da nhan viec', 'value' => $stats['converted'] ?? 0, 'tone' => 'text-cyan-700'],
            ] as $item)
                <div class="rounded-[1.5rem] border border-slate-100 bg-white p-5 shadow-sm">
                    <p class="text-sm font-semibold text-slate-500">{{ $item['label'] }}</p>
                    <p class="mt-3 text-3xl font-black {{ $item['tone'] }}">{{ $item['value'] }}</p>
                </div>
            @endforeach
        </section>

        <section class="recruitment-panel rounded-[2rem] border border-slate-100 bg-white p-5 shadow-sm">
            <form method="GET" action="{{ route('admin.recruitment.interviewed-candidates') }}"
                  class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-6">
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Tim kiem</label>
                    <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Ten, email, so dien thoai..."
                           class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-800 outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20">
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Ket qua ho so</label>
                    <select name="status" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-800 outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20">
                        <option value="">Tat ca</option>
                        @foreach ($statusLabels as $value => $label)
                            <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Ket qua PV</label>
                    <select name="interview_result" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-800 outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20">
                        <option value="">Tat ca</option>
                        @foreach ($interviewResultLabels as $value => $label)
                            <option value="{{ $value }}" @selected($filters['interview_result'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Trang thai lich</label>
                    <select name="interview_status" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-800 outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20">
                        <option value="">Tat ca</option>
                        @foreach ($interviewStatusLabels as $value => $label)
                            <option value="{{ $value }}" @selected($filters['interview_status'] === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Tin tuyen dung</label>
                    <select name="job_post_id" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-800 outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20">
                        <option value="">Tat ca vi tri</option>
                        @foreach ($jobPosts as $jobPost)
                            <option value="{{ $jobPost->id }}" @selected((string) $filters['job_post_id'] === (string) $jobPost->id)>{{ $jobPost->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end gap-2 sm:col-span-2 xl:col-span-6">
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-cyan-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-cyan-700">
                        Loc ket qua
                    </button>
                    @if ($hasFilters)
                        <a href="{{ route('admin.recruitment.interviewed-candidates') }}"
                           class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                            Xoa bo loc
                        </a>
                        <span class="rounded-full bg-cyan-50 px-3 py-1 text-xs font-semibold text-cyan-700">
                            {{ $candidates->total() }} ket qua
                        </span>
                    @endif
                </div>
            </form>
        </section>

        <section class="recruitment-panel overflow-hidden rounded-[2rem] border border-slate-100 bg-white shadow-sm shadow-slate-200/60">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 sm:px-6">
                <div>
                    <h2 class="text-sm font-black text-slate-900">Danh sach ung vien</h2>
                    <p class="mt-1 text-xs text-slate-500">{{ $candidates->total() }} ung vien co lich phong van</p>
                </div>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse ($candidates as $candidate)
                    @php
                        $latestInterview = $candidate->interviews->first();
                        $statusClass = $statusClasses[$candidate->status] ?? 'bg-slate-100 text-slate-700 ring-slate-200';
                        $resultClass = $resultClasses[$latestInterview?->result] ?? 'bg-slate-100 text-slate-700 ring-slate-200';
                    @endphp

                    <article class="grid grid-cols-1 gap-5 p-5 transition hover:bg-slate-50/70 xl:grid-cols-[minmax(0,1fr)_minmax(280px,.7fr)_minmax(320px,.85fr)] sm:p-6">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1 {{ $statusClass }}">
                                    {{ $statusLabels[$candidate->status] ?? $candidate->status }}
                                </span>
                                @if ($latestInterview)
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1 {{ $resultClass }}">
                                        {{ $interviewResultLabels[$latestInterview->result] ?? $latestInterview->result }}
                                    </span>
                                @endif
                            </div>

                            <h3 class="mt-3 break-words text-lg font-black text-slate-900">{{ $candidate->full_name }}</h3>
                            <p class="mt-1 break-words text-sm text-slate-500">{{ $candidate->email }} - {{ $candidate->phone }}</p>
                            <p class="mt-2 break-words text-sm font-semibold text-slate-700">
                                {{ $candidate->jobPost?->title ?? 'Chua gan tin tuyen dung' }}
                            </p>

                            <div class="mt-4 flex flex-wrap gap-2">
                                <a href="{{ route('admin.recruitment.candidates.show', $candidate) }}"
                                   class="inline-flex items-center justify-center rounded-2xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-cyan-200 hover:text-cyan-700">
                                    Xem ho so
                                </a>
                                @if ($candidate->employee)
                                    <a href="{{ route('admin.employees.show', $candidate->employee) }}"
                                       class="inline-flex items-center justify-center rounded-2xl bg-indigo-50 px-4 py-2.5 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-100">
                                        Nhan vien {{ $candidate->employee->employee_code }}
                                    </a>
                                @endif
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-3 text-sm">
                            <div class="rounded-2xl bg-slate-50 px-4 py-3">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Phong van gan nhat</p>
                                <p class="mt-1 font-semibold text-slate-800">{{ $latestInterview?->interview_date?->format('d/m/Y H:i') ?? '-' }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 px-4 py-3">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Nguoi phong van</p>
                                <p class="mt-1 break-words font-semibold text-slate-800">{{ $latestInterview?->interviewer?->full_name ?? 'Chua gan' }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 px-4 py-3">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Trang thai lich</p>
                                <p class="mt-1 font-semibold text-slate-800">{{ $interviewStatusLabels[$latestInterview?->status] ?? ($latestInterview?->status ?? '-') }}</p>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                            <h4 class="text-sm font-black text-slate-900">Xu ly ket qua</h4>
                            <p class="mt-2 text-xs leading-5 text-slate-500">
                                Chon Dat hoac Khong dat de cap nhat ket qua phong van va trang thai ho so.
                            </p>

                            @if ($candidate->status === 'interview' || $latestInterview?->result === 'pending')
                                <form action="{{ route('admin.recruitment.interviewed-candidates.decision', $candidate) }}" method="POST" class="mt-4 space-y-3">
                                    @csrf
                                    @method('PATCH')

                                    <textarea name="note" rows="2" placeholder="Ghi chu ket qua neu co"
                                              class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20">{{ old('note') }}</textarea>

                                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                        <button type="submit" name="result" value="passed"
                                                class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700">
                                            Dat
                                        </button>
                                        <button type="submit" name="result" value="failed"
                                                class="inline-flex items-center justify-center rounded-xl border border-rose-200 bg-white px-4 py-2.5 text-sm font-semibold text-rose-700 transition hover:bg-rose-50">
                                            Khong dat
                                        </button>
                                    </div>
                                </form>
                            @else
                                <div class="mt-4 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700">
                                    Ho so da co ket qua: {{ $statusLabels[$candidate->status] ?? $candidate->status }}
                                </div>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="px-5 py-14 text-center sm:px-6">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-slate-100 text-slate-400">
                            <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M4 11h16M7 21h10a3 3 0 0 0 3-3V7a3 3 0 0 0-3-3H7a3 3 0 0 0-3 3v11a3 3 0 0 0 3 3Z"/>
                            </svg>
                        </div>
                        <h3 class="mt-5 text-lg font-black text-slate-900">Chua co ung vien da phong van</h3>
                        <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500">
                            Khi ung vien co lich phong van, ho se xuat hien tai day de bo phan tuyen dung xu ly ket qua.
                        </p>
                    </div>
                @endforelse
            </div>
        </section>

        <div class="overflow-x-auto">
            {{ $candidates->links() }}
        </div>
    </div>
</x-admin-layout>
