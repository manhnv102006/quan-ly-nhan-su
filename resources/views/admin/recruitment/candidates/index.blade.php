<x-admin-layout title="Ứng viên">
@php
    $filters = $filters ?? [
        'search' => '', 'status' => '', 'job_post_id' => '',
        'cv_status' => '', 'converted' => '', 'created_from' => '', 'created_to' => '',
    ];

    $statusConfig = [
        'new'       => ['label' => 'Mới',        'dot' => 'bg-sky-500',     'badge' => 'bg-sky-100 text-sky-700'],
        'interview' => ['label' => 'Phỏng vấn',  'dot' => 'bg-amber-500',   'badge' => 'bg-amber-100 text-amber-700'],
        'passed'    => ['label' => 'Đạt',         'dot' => 'bg-emerald-500', 'badge' => 'bg-emerald-100 text-emerald-700'],
        'failed'    => ['label' => 'Không đạt',  'dot' => 'bg-rose-500',    'badge' => 'bg-rose-100 text-rose-700'],
    ];

    $hasFilters = collect($filters)->filter(fn($v) => $v !== '')->isNotEmpty();
@endphp

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <nav class="flex items-center gap-2 text-sm text-slate-500 mb-2">
                <a href="{{ route('admin.recruitment') }}" class="hover:text-violet-600 transition">Tuyển dụng</a>
                <span>/</span>
                <span class="font-semibold text-slate-700">Ứng viên</span>
            </nav>
            <h1 class="text-2xl font-bold text-slate-800">Quản lý ứng viên</h1>
            <p class="mt-1 text-sm text-slate-500">Theo dõi hồ sơ, trạng thái phỏng vấn và chuyển đổi nhân viên</p>
        </div>
    </div>

    @if(session('success'))
        <div class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            <svg class="h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="flex items-center gap-3 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">
            <svg class="h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd"/></svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- Thống kê --}}
    <div class="grid grid-cols-3 gap-3 lg:grid-cols-6">
        @foreach ([
            ['Tổng ứng viên', $stats['total'] ?? 0,     'text-slate-800',   'bg-white',        ''],
            ['Mới',           $stats['new'] ?? 0,        'text-sky-700',     'bg-sky-50',       'new'],
            ['Phỏng vấn',     $stats['interview'] ?? 0,  'text-amber-700',   'bg-amber-50',     'interview'],
            ['Đạt',           $stats['passed'] ?? 0,     'text-emerald-700', 'bg-emerald-50',   'passed'],
            ['Không đạt',     $stats['failed'] ?? 0,     'text-rose-700',    'bg-rose-50',      'failed'],
            ['Đã nhận việc',  $stats['converted'] ?? 0,  'text-violet-700',  'bg-violet-50',    ''],
        ] as [$label, $value, $textClass, $bgClass, $statusFilter])
            <a href="{{ $statusFilter ? route('admin.recruitment.candidates', ['status' => $statusFilter]) : route('admin.recruitment.candidates') }}"
               class="group rounded-xl border border-slate-100 {{ $bgClass }} p-4 shadow-sm transition hover:shadow-md">
                <p class="text-xs font-semibold text-slate-500 group-hover:text-slate-700 transition">{{ $label }}</p>
                <p class="mt-2 text-2xl font-black {{ $textClass }}">{{ $value }}</p>
            </a>
        @endforeach
    </div>

    {{-- Bộ lọc --}}
    <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
        <form method="GET" action="{{ route('admin.recruitment.candidates') }}"
              class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6">

            {{-- Tìm kiếm --}}
            <div class="sm:col-span-2">
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Tìm kiếm</label>
                <div class="relative">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                         fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                    </svg>
                    <input type="text" name="search" value="{{ $filters['search'] }}"
                           placeholder="Tên, email, SĐT…"
                           class="w-full rounded-xl border border-slate-200 py-2.5 pl-9 pr-4 text-sm text-slate-800 outline-none
                                  transition placeholder:text-slate-400 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20">
                </div>
            </div>

            {{-- Trạng thái --}}
            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Trạng thái</label>
                <select name="status"
                        class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-800 outline-none
                               transition focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20">
                    <option value="">Tất cả</option>
                    @foreach ($statusConfig as $val => $cfg)
                        <option value="{{ $val }}" @selected($filters['status'] === $val)>{{ $cfg['label'] }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Tin tuyển dụng --}}
            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Tin tuyển dụng</label>
                <select name="job_post_id"
                        class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-800 outline-none
                               transition focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20">
                    <option value="">Tất cả vị trí</option>
                    @foreach ($jobPosts as $jp)
                        <option value="{{ $jp->id }}" @selected((string)$filters['job_post_id'] === (string)$jp->id)>
                            {{ $jp->title }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- CV --}}
            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">CV</label>
                <select name="cv_status"
                        class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-800 outline-none
                               transition focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20">
                    <option value="">Tất cả</option>
                    <option value="has_cv"     @selected($filters['cv_status'] === 'has_cv')>Có CV</option>
                    <option value="missing_cv" @selected($filters['cv_status'] === 'missing_cv')>Thiếu CV</option>
                </select>
            </div>

            {{-- Nhân viên --}}
            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Nhân viên</label>
                <select name="converted"
                        class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-800 outline-none
                               transition focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20">
                    <option value="">Tất cả</option>
                    <option value="yes" @selected($filters['converted'] === 'yes')>Đã chuyển</option>
                    <option value="no"  @selected($filters['converted'] === 'no')>Chưa chuyển</option>
                </select>
            </div>

            {{-- Nút --}}
            <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-4 xl:col-span-6">
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-violet-600 px-5 py-2.5 text-sm font-semibold
                               text-white shadow-md shadow-violet-500/20 transition hover:bg-violet-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z"/>
                    </svg>
                    Lọc kết quả
                </button>
                @if($hasFilters)
                    <a href="{{ route('admin.recruitment.candidates') }}"
                       class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-5 py-2.5
                              text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                        Xóa bộ lọc
                    </a>
                    <span class="rounded-full bg-violet-50 px-3 py-1 text-xs font-semibold text-violet-700">
                        {{ $candidates->total() }} kết quả
                    </span>
                @endif
            </div>
        </form>
    </div>

    {{-- Danh sách ứng viên --}}
    <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Danh sách ứng viên</h3>
                <p class="text-xs text-slate-500">{{ $candidates->total() }} ứng viên</p>
            </div>
        </div>

        @forelse ($candidates as $candidate)
            @php
                $cfg     = $statusConfig[$candidate->status] ?? ['label' => $candidate->status, 'dot' => 'bg-slate-400', 'badge' => 'bg-slate-100 text-slate-700'];
                $initials = collect(explode(' ', $candidate->full_name))->map(fn($w) => strtoupper(substr($w, 0, 1)))->last(null, '?');
                $colors  = ['bg-violet-100 text-violet-700','bg-sky-100 text-sky-700','bg-emerald-100 text-emerald-700','bg-amber-100 text-amber-700','bg-rose-100 text-rose-700','bg-indigo-100 text-indigo-700'];
                $colorIdx = crc32($candidate->full_name) % count($colors);
                $avatarClass = $colors[abs($colorIdx)];
            @endphp

            <div class="group border-b border-slate-100 px-6 py-4 transition hover:bg-slate-50/60 last:border-b-0">
                <div class="flex flex-wrap items-start gap-4">

                    {{-- Avatar + Tên --}}
                    <div class="flex min-w-0 flex-1 items-start gap-4">
                        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-base font-bold {{ $avatarClass }}">
                            {{ $initials }}
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <a href="{{ route('admin.recruitment.candidates.show', $candidate) }}"
                                   class="font-bold text-slate-800 hover:text-violet-700 transition">
                                    {{ $candidate->full_name }}
                                </a>
                                {{-- Trạng thái --}}
                                <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $cfg['badge'] }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $cfg['dot'] }}"></span>
                                    {{ $cfg['label'] }}
                                </span>
                                {{-- CV --}}
                                @if($candidate->cv_file)
                                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700">Có CV</span>
                                @else
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-500">Thiếu CV</span>
                                @endif
                                {{-- Đã chuyển --}}
                                @if($candidate->employee)
                                    <a href="{{ route('admin.employees.show', $candidate->employee) }}"
                                       class="rounded-full bg-violet-100 px-2 py-0.5 text-xs font-semibold text-violet-700 hover:bg-violet-200 transition">
                                        Nhân viên {{ $candidate->employee->employee_code }}
                                    </a>
                                @endif
                            </div>

                            {{-- Thông tin phụ --}}
                            <div class="mt-1.5 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500">
                                <span class="flex items-center gap-1">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/>
                                    </svg>
                                    {{ $candidate->phone }}
                                </span>
                                <span class="flex items-center gap-1">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/>
                                    </svg>
                                    {{ $candidate->email }}
                                </span>
                                @if($candidate->jobPost)
                                    <span class="flex items-center gap-1">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006-3.75 3.75m0 0-3.75-3.75m3.75 3.75V10.5"/>
                                        </svg>
                                        {{ $candidate->jobPost->title }}
                                    </span>
                                @endif
                                <span class="text-slate-400">{{ $candidate->created_at?->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex shrink-0 flex-wrap items-center gap-2">
                        <a href="{{ route('admin.recruitment.candidates.show', $candidate) }}"
                           class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2
                                  text-xs font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                            </svg>
                            Hồ sơ
                        </a>

                        @if($candidate->status !== 'failed')
                            <form action="{{ route('admin.recruitment.candidates.update', $candidate) }}" method="POST"
                                  onsubmit="return confirm('Từ chối ứng viên {{ addslashes($candidate->full_name) }}?')">
                                @csrf @method('PUT')
                                @foreach(['job_post_id','full_name','phone','email','address'] as $f)
                                    <input type="hidden" name="{{ $f }}" value="{{ $candidate->$f }}">
                                @endforeach
                                <input type="hidden" name="birth_date" value="{{ $candidate->birth_date?->format('Y-m-d') }}">
                                <input type="hidden" name="status" value="failed">
                                <button type="submit"
                                        class="inline-flex items-center gap-1.5 rounded-xl border border-rose-200 bg-white px-3.5 py-2
                                               text-xs font-semibold text-rose-600 shadow-sm transition hover:bg-rose-50">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                                    </svg>
                                    Từ chối
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="py-16 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100">
                    <svg class="h-7 w-7 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21a12.318 12.318 0 0 1-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.965-3.07M12 7.875a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Z"/>
                    </svg>
                </div>
                <h3 class="mt-4 text-sm font-bold text-slate-700">Chưa có ứng viên nào</h3>
                <p class="mt-1 text-sm text-slate-500">
                    @if($hasFilters)
                        Không tìm thấy ứng viên phù hợp với bộ lọc hiện tại.
                    @else
                        Ứng viên sẽ xuất hiện tại đây khi có người nộp hồ sơ từ trang tuyển dụng.
                    @endif
                </p>
                @if($hasFilters)
                    <div class="mt-4 flex justify-center">
                        <a href="{{ route('admin.recruitment.candidates') }}"
                           class="inline-flex rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                            Xóa bộ lọc
                        </a>
                    </div>
                @endif
            </div>
        @endforelse

        @if($candidates->hasPages())
            <div class="border-t border-slate-100 px-6 py-4">
                {{ $candidates->links() }}
            </div>
        @endif
    </div>

</div>
</x-admin-layout>
