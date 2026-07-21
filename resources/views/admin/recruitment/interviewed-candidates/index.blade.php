<x-admin-layout title="Ung vien da dat phong van">
    @php
        $filters = $filters ?? [
            'search' => '',
            'job_post_id' => '',
            'converted' => '',
        ];

        $interviewStatusLabels = [
            'scheduled' => 'Da len lich',
            'completed' => 'Da phong van',
            'cancelled' => 'Da huy',
            'no_show' => 'Khong den',
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
                        <span class="font-semibold text-slate-700">Ung vien da dat phong van</span>
                    </div>
                    <h1 class="mt-3 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">Ung vien da dat phong van</h1>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                        Xu ly buoc cuoi: xac nhan ung vien da dat phong van len lam nhan vien cua phong ban trong tin tuyen dung va gan quan ly cua phong ban do.
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

        <section class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            @foreach ([
                ['label' => 'Da dat phong van', 'value' => $stats['total'] ?? 0, 'tone' => 'text-emerald-700'],
                ['label' => 'Cho xac nhan nhan viec', 'value' => $stats['pending_conversion'] ?? 0, 'tone' => 'text-amber-700'],
                ['label' => 'Da len nhan vien', 'value' => $stats['converted'] ?? 0, 'tone' => 'text-cyan-700'],
            ] as $item)
                <div class="rounded-[1.5rem] border border-slate-100 bg-white p-5 shadow-sm">
                    <p class="text-sm font-semibold text-slate-500">{{ $item['label'] }}</p>
                    <p class="mt-3 text-3xl font-black {{ $item['tone'] }}">{{ $item['value'] }}</p>
                </div>
            @endforeach
        </section>

        <section class="recruitment-panel rounded-[2rem] border border-slate-100 bg-white p-5 shadow-sm">
            <form method="GET" action="{{ route('admin.recruitment.interviewed-candidates') }}"
                  class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-5">
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Tim kiem</label>
                    <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Ten, email, so dien thoai..."
                           class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-slate-800 outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20">
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

                <div>
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Nhan vien</label>
                    <select name="converted" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-800 outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20">
                        <option value="">Tat ca</option>
                        <option value="no" @selected($filters['converted'] === 'no')>Cho xac nhan</option>
                        <option value="yes" @selected($filters['converted'] === 'yes')>Da len nhan vien</option>
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-cyan-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-cyan-700">
                        Loc
                    </button>
                </div>

                @if ($hasFilters)
                    <div class="flex items-center gap-2 sm:col-span-2 xl:col-span-5">
                        <a href="{{ route('admin.recruitment.interviewed-candidates') }}"
                           class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                            Xoa bo loc
                        </a>
                        <span class="rounded-full bg-cyan-50 px-3 py-1 text-xs font-semibold text-cyan-700">
                            {{ $candidates->total() }} ket qua
                        </span>
                    </div>
                @endif
            </form>
        </section>

        <section class="recruitment-panel overflow-hidden rounded-[2rem] border border-slate-100 bg-white shadow-sm shadow-slate-200/60">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 sm:px-6">
                <div>
                    <h2 class="text-sm font-black text-slate-900">Danh sach dat phong van</h2>
                    <p class="mt-1 text-xs text-slate-500">{{ $candidates->total() }} ho so san sang xac nhan nhan viec</p>
                </div>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse ($candidates as $candidate)
                    @php
                        $latestInterview = $candidate->interviews->first();
                        $department = $candidate->jobPost?->department;
                        $manager = $department?->manager;
                        $canConvert = $candidate->employee_id === null && $department !== null;
                    @endphp

                    <article class="grid grid-cols-1 gap-5 p-5 transition hover:bg-slate-50/70 xl:grid-cols-[minmax(0,1fr)_minmax(280px,.75fr)_minmax(360px,.95fr)] sm:p-6">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-200">
                                    Da dat phong van
                                </span>
                                @if ($candidate->employee)
                                    <span class="inline-flex rounded-full bg-cyan-100 px-3 py-1 text-xs font-semibold text-cyan-700 ring-1 ring-cyan-200">
                                        Da len nhan vien
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700 ring-1 ring-amber-200">
                                        Cho xac nhan
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
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Phong ban</p>
                                <p class="mt-1 break-words font-semibold text-slate-800">{{ $department?->department_name ?? 'Chua gan phong ban' }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 px-4 py-3">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Quan ly phong ban</p>
                                <p class="mt-1 break-words font-semibold text-slate-800">{{ $manager?->full_name ?? 'Chua gan quan ly' }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 px-4 py-3">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Phong van gan nhat</p>
                                <p class="mt-1 font-semibold text-slate-800">{{ $latestInterview?->interview_date?->format('d/m/Y H:i') ?? '-' }}</p>
                                <p class="mt-1 text-xs text-slate-500">{{ $interviewStatusLabels[$latestInterview?->status] ?? ($latestInterview?->status ?? '-') }}</p>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4">
                            <h4 class="text-sm font-black text-slate-900">Xac nhan len nhan vien</h4>
                            <p class="mt-2 text-xs leading-5 text-slate-500">
                                He thong se tao ho so nhan vien voi phong ban cua tin tuyen dung va gan quan ly cua phong ban do.
                            </p>

                            @if ($candidate->employee)
                                <div class="mt-4 rounded-xl border border-cyan-100 bg-cyan-50 px-4 py-3 text-sm font-semibold text-cyan-700">
                                    Da tao nhan vien: {{ $candidate->employee->employee_code }}
                                </div>
                            @elseif (! $department)
                                <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-700">
                                    Tin tuyen dung chua gan phong ban, chua the xac nhan len nhan vien.
                                </div>
                            @else
                                <form action="{{ route('admin.recruitment.candidates.convert-to-employee', $candidate) }}" method="POST" class="mt-4 space-y-3">
                                    @csrf
                                    <input type="hidden" name="department_id" value="{{ $department->id }}">
                                    <input type="hidden" name="status" value="active">

                                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                        <div>
                                            <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Ma nhan vien</label>
                                            <input type="text" name="employee_code" value="{{ old('employee_code', $candidate->suggested_employee_code) }}" required
                                                   class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-800 outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20">
                                        </div>
                                        <div>
                                            <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Ngay vao lam</label>
                                            <input type="date" name="hire_date" value="{{ old('hire_date', now()->format('Y-m-d')) }}" required
                                                   class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-800 outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20">
                                        </div>
                                        <div>
                                            <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Gioi tinh</label>
                                            <select name="gender" required class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-800 outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20">
                                                <option value="male" @selected(old('gender') === 'male')>Nam</option>
                                                <option value="female" @selected(old('gender') === 'female')>Nu</option>
                                                <option value="other" @selected(old('gender') === 'other')>Khac</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Ngay sinh</label>
                                            <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $candidate->birth_date?->format('Y-m-d')) }}" required
                                                   class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-800 outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Chuc vu</label>
                                        <select name="position_id" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-800 outline-none transition focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20">
                                            <option value="">Chua gan</option>
                                            @foreach ($positions as $position)
                                                <option value="{{ $position->id }}" @selected(old('position_id') == $position->id)>{{ $position->position_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <button type="submit"
                                            class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-700">
                                        Xac nhan len nhan vien
                                    </button>
                                </form>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="px-5 py-14 text-center sm:px-6">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-3xl bg-emerald-50 text-xl font-black text-emerald-600">
                            OK
                        </div>
                        <h3 class="mt-5 text-lg font-black text-slate-900">Chua co ung vien dat phong van</h3>
                        <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500">
                            Khi ung vien duoc cap nhat ket qua Dat sau phong van, ho se xuat hien tai day de xac nhan len nhan vien.
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
