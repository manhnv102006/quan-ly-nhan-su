@php
    $firstName = collect(explode(' ', trim($user->name)))->filter()->first() ?? $user->name;
    $initial = strtoupper(mb_substr($user->name, 0, 1));
    $roleLabel = $user->role?->label() ?? 'Người dùng';
    $roleBadgeClass = match ($user->role?->name) {
        'admin' => 'bg-violet-100 text-violet-700',
        'manager' => 'bg-emerald-100 text-emerald-700',
        'employee' => 'bg-sky-100 text-sky-700',
        default => 'bg-slate-100 text-slate-600',
    };
@endphp

<div class="space-y-6">

    {{-- Hero --}}
    <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br {{ $heroTheme }} p-6 sm:p-8 text-white shadow-xl">
        <div class="absolute -right-16 top-0 h-48 w-48 rounded-full bg-white/10 blur-3xl"></div>
        <div class="absolute bottom-0 left-0 h-40 w-40 -translate-x-1/4 translate-y-1/4 rounded-full bg-white/10 blur-3xl"></div>

        <div class="relative flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-5">
                <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-3xl bg-white/20 text-3xl font-bold backdrop-blur border border-white/30 shadow-lg">
                    {{ $initial }}
                </div>
                <div>
                    <span class="inline-flex items-center gap-2 rounded-full bg-white/15 px-3 py-1 text-xs font-semibold backdrop-blur">
                        Tài khoản {{ strtolower($roleLabel) }}
                    </span>
                    <h2 class="mt-2 text-2xl sm:text-3xl font-extrabold tracking-tight">{{ $user->name }}</h2>
                    <p class="mt-1 text-sm text-white/80 font-mono">{{ $user->username ?? $user->email }}</p>
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold {{ $roleBadgeClass }} bg-white">
                            {{ $roleLabel }}
                        </span>
                        @if ($user->status === 'active')
                            <span class="inline-flex px-3 py-1 rounded-full bg-emerald-400/20 border border-emerald-300/40 text-emerald-50 text-xs font-semibold">Đang hoạt động</span>
                        @else
                            <span class="inline-flex px-3 py-1 rounded-full bg-rose-400/20 border border-rose-300/40 text-rose-50 text-xs font-semibold">Đã khóa</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap gap-3 shrink-0">
                <a href="{{ route($dashboardRoute) }}"
                   class="inline-flex items-center gap-2 rounded-2xl border border-white/25 bg-white/10 px-4 py-3 text-sm font-semibold text-white backdrop-blur transition hover:bg-white/20">
                    ← Về Dashboard
                </a>
            </div>
        </div>
    </section>

    @if (session('status') === 'profile-updated')
        <div class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-white px-5 py-4 shadow-sm">
            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">✓</span>
            <p class="text-sm font-medium text-emerald-700">Đã cập nhật thông tin hồ sơ thành công.</p>
        </div>
    @endif

    @if (session('status') === 'password-updated')
        <div class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-white px-5 py-4 shadow-sm">
            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">✓</span>
            <p class="text-sm font-medium text-emerald-700">Đã đổi mật khẩu thành công.</p>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

        {{-- Sidebar summary --}}
        <div class="space-y-4 xl:col-span-1">
            <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400">Thông tin tài khoản</p>
                <dl class="mt-4 space-y-4">
                    <div>
                        <dt class="text-xs font-medium text-slate-500">Tên đăng nhập</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-800">{{ $user->username ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-slate-500">Email</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-800 break-all">{{ $user->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-slate-500">Ngày tham gia</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-800">{{ $user->created_at?->format('d/m/Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-slate-500">Cập nhật lần cuối</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-800">{{ $user->updated_at?->format('d/m/Y H:i') ?? '—' }}</dd>
                    </div>
                </dl>
            </div>

            @if ($employeeProfile)
                <div class="rounded-3xl border border-violet-100 bg-gradient-to-br from-violet-50 to-indigo-50 p-6">
                    <p class="text-xs font-bold uppercase tracking-widest text-violet-500">Hồ sơ nhân sự liên kết</p>
                    <div class="mt-4 space-y-3">
                        <div>
                            <p class="text-xs text-slate-500">Mã nhân viên</p>
                            <p class="font-mono text-sm font-bold text-slate-800">{{ $employeeProfile->employee_code }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500">Phòng ban</p>
                            <p class="text-sm font-semibold text-slate-800">{{ $employeeProfile->department?->department_name ?? 'Chưa gán' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500">Chức vụ</p>
                            <p class="text-sm font-semibold text-slate-800">{{ $employeeProfile->position?->position_name ?? 'Chưa gán' }}</p>
                        </div>
                        @if ($user->isAdmin())
                            <a href="{{ route('admin.employees.show', $employeeProfile) }}"
                               class="mt-2 inline-flex items-center gap-1 text-sm font-medium text-violet-600 hover:text-violet-700">
                                Xem hồ sơ HR →
                            </a>
                        @endif
                    </div>
                </div>
            @else
                <div class="rounded-3xl border border-amber-100 bg-amber-50/80 p-6">
                    <p class="text-sm font-semibold text-amber-800">Chưa liên kết hồ sơ nhân sự</p>
                    <p class="mt-1 text-xs text-amber-700 leading-relaxed">Tài khoản này chưa được gán với bản ghi nhân viên trong hệ thống HR.</p>
                </div>
            @endif

            <nav class="rounded-3xl border border-slate-100 bg-white p-4 shadow-sm">
                <p class="px-2 text-xs font-bold uppercase tracking-widest text-slate-400 mb-2">Mục cài đặt</p>
                <a href="#thong-tin-ca-nhan" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-600 hover:bg-violet-50 hover:text-violet-700 transition">
                    <span class="w-8 h-8 rounded-lg bg-violet-100 text-violet-600 flex items-center justify-center text-sm">👤</span>
                    Thông tin cá nhân
                </a>
                <a href="#doi-mat-khau" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-600 hover:bg-violet-50 hover:text-violet-700 transition">
                    <span class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center text-sm">🔒</span>
                    Đổi mật khẩu
                </a>
                <a href="#xoa-tai-khoan" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-600 hover:bg-red-50 hover:text-red-600 transition">
                    <span class="w-8 h-8 rounded-lg bg-red-100 text-red-600 flex items-center justify-center text-sm">⚠</span>
                    Xóa tài khoản
                </a>
            </nav>
        </div>

        {{-- Forms --}}
        <div class="space-y-6 xl:col-span-2">
            <div id="thong-tin-ca-nhan">
                @include('profile.partials.update-profile-information-form')
            </div>
            <div id="doi-mat-khau">
                @include('profile.partials.update-password-form')
            </div>
            <div id="xoa-tai-khoan">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</div>
