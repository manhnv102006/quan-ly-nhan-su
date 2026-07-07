<x-admin-layout title="Quản lý tài khoản">
@php
    $filters = $filters ?? ['search' => '', 'role_id' => '', 'status' => '', 'verified' => ''];
    $hasFilters = collect($filters)->filter(fn($v) => $v !== '')->isNotEmpty();

    $roleConfig = [
        'admin'    => ['label' => 'Quản trị viên', 'badge' => 'bg-violet-100 text-violet-700', 'avatar' => 'bg-violet-100 text-violet-700'],
        'manager'  => ['label' => 'Quản lý',       'badge' => 'bg-blue-100 text-blue-700',     'avatar' => 'bg-blue-100 text-blue-700'],
        'employee' => ['label' => 'Nhân viên',     'badge' => 'bg-cyan-100 text-cyan-700',     'avatar' => 'bg-cyan-100 text-cyan-700'],
    ];
@endphp

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-violet-600">Quản trị hệ thống</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-800">Quản lý tài khoản</h1>
            <p class="mt-1 text-sm text-slate-500">Tổng cộng {{ $stats['total'] }} tài khoản đăng nhập hệ thống</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('admin.accounts.trash') }}"
               class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5
                      text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                </svg>
                Thùng rác
                @if ($stats['trashed'] > 0)
                    <span class="inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-rose-100 px-1.5 text-xs font-bold text-rose-700">
                        {{ $stats['trashed'] }}
                    </span>
                @endif
            </a>
            <a href="{{ route('admin.accounts.create') }}"
               class="inline-flex items-center gap-2 rounded-xl bg-violet-600 px-5 py-2.5 text-sm font-semibold
                      text-white shadow-md shadow-violet-500/20 transition hover:bg-violet-700">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Thêm tài khoản
            </a>
        </div>
    </div>

    {{-- Thống kê --}}
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        @foreach ([
            ['Tổng tài khoản',   $stats['total'],    'text-slate-800',   'bg-white',        ''],
            ['Đang hoạt động',   $stats['active'],   'text-emerald-700', 'bg-emerald-50',   'active'],
            ['Đã xác thực email',$stats['verified'], 'text-violet-700',  'bg-violet-50',    'yes'],
            ['Trong thùng rác',  $stats['trashed'],  'text-rose-700',    'bg-rose-50',      'trash'],
        ] as [$label, $value, $textClass, $bgClass, $filterKey])
            @if($filterKey === 'trash')
                <a href="{{ route('admin.accounts.trash') }}"
                   class="group rounded-xl border border-slate-100 {{ $bgClass }} p-4 shadow-sm transition hover:shadow-md">
            @else
                <a href="{{ $filterKey ? route('admin.accounts', ['status' => $filterKey === 'active' ? 'active' : '', 'verified' => $filterKey === 'yes' ? 'yes' : '']) : route('admin.accounts') }}"
                   class="group rounded-xl border border-slate-100 {{ $bgClass }} p-4 shadow-sm transition hover:shadow-md">
            @endif
                <p class="text-xs font-semibold text-slate-500 group-hover:text-slate-700 transition">{{ $label }}</p>
                <p class="mt-2 text-2xl font-black {{ $textClass }}">{{ $value }}</p>
            </a>
        @endforeach
    </div>

    {{-- Bộ lọc --}}
    <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
        <form method="GET" action="{{ route('admin.accounts') }}"
              class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5">

            <div class="sm:col-span-2">
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Tìm kiếm</label>
                <div class="relative">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
                         fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                    </svg>
                    <input type="text" name="search" value="{{ $filters['search'] }}"
                           placeholder="Tên đăng nhập, họ tên, email…"
                           class="w-full rounded-xl border border-slate-200 py-2.5 pl-9 pr-4 text-sm text-slate-800 outline-none
                                  transition placeholder:text-slate-400 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20">
                </div>
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Vai trò</label>
                <select name="role_id"
                        class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-800 outline-none
                               transition focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20">
                    <option value="">Tất cả vai trò</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}" @selected((string)$filters['role_id'] === (string)$role->id)>
                            {{ $role->label() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Trạng thái</label>
                <select name="status"
                        class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-800 outline-none
                               transition focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20">
                    <option value="">Tất cả</option>
                    <option value="active"   @selected($filters['status'] === 'active')>Hoạt động</option>
                    <option value="inactive" @selected($filters['status'] === 'inactive')>Đã khóa</option>
                </select>
            </div>

            <div>
                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wide text-slate-500">Xác thực email</label>
                <select name="verified"
                        class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-slate-800 outline-none
                               transition focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20">
                    <option value="">Tất cả</option>
                    <option value="yes" @selected($filters['verified'] === 'yes')>Đã xác thực</option>
                    <option value="no"  @selected($filters['verified'] === 'no')>Chưa xác thực</option>
                </select>
            </div>

            <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-5">
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-violet-600 px-5 py-2.5 text-sm font-semibold
                               text-white shadow-md shadow-violet-500/20 transition hover:bg-violet-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 0 1-.659 1.591l-5.432 5.432a2.25 2.25 0 0 0-.659 1.591v2.927a2.25 2.25 0 0 1-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 0 0-.659-1.591L3.659 7.409A2.25 2.25 0 0 1 3 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0 1 12 3Z"/>
                    </svg>
                    Lọc kết quả
                </button>
                @if($hasFilters)
                    <a href="{{ route('admin.accounts') }}"
                       class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-5 py-2.5
                              text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                        Xóa bộ lọc
                    </a>
                    <span class="rounded-full bg-violet-50 px-3 py-1 text-xs font-semibold text-violet-700">
                        {{ $users->total() }} kết quả
                    </span>
                @endif
            </div>
        </form>
    </div>

    {{-- Danh sách tài khoản --}}
    <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Danh sách tài khoản</h3>
                <p class="text-xs text-slate-500">{{ $users->total() }} tài khoản</p>
            </div>
        </div>

        @forelse ($users as $user)
            @php
                $roleName   = $user->role?->name ?? '';
                $roleCfg    = $roleConfig[$roleName] ?? ['label' => 'Chưa phân quyền', 'badge' => 'bg-slate-100 text-slate-600', 'avatar' => 'bg-slate-100 text-slate-600'];
                $initials   = collect(explode(' ', $user->name))->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->join('');
                $isSelf     = $user->id === auth()->id();
            @endphp

            <div class="group border-b border-slate-100 px-6 py-4 transition hover:bg-slate-50/60 last:border-b-0">
                <div class="flex flex-wrap items-center gap-4">

                    {{-- Avatar + thông tin chính --}}
                    <div class="flex min-w-0 flex-1 items-center gap-4">
                        <div class="relative flex h-11 w-11 shrink-0 items-center justify-center rounded-xl text-sm font-bold {{ $roleCfg['avatar'] }}">
                            {{ $initials }}
                            @if($user->status === 'active')
                                <span class="absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full border-2 border-white bg-emerald-500"></span>
                            @else
                                <span class="absolute -bottom-0.5 -right-0.5 h-3 w-3 rounded-full border-2 border-white bg-slate-400"></span>
                            @endif
                        </div>

                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <a href="{{ route('admin.accounts.show', $user) }}"
                                   class="font-bold text-slate-800 hover:text-violet-700 transition">
                                    {{ $user->name }}
                                </a>
                                @if($isSelf)
                                    <span class="rounded-full bg-violet-100 px-2 py-0.5 text-xs font-semibold text-violet-700">Bạn</span>
                                @endif
                                <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $roleCfg['badge'] }}">
                                    {{ $roleCfg['label'] }}
                                </span>
                                @if($user->status === 'active')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                        Hoạt động
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-semibold text-rose-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                                        Đã khóa
                                    </span>
                                @endif
                                @if($user->email_verified_at)
                                    <span class="rounded-full bg-sky-100 px-2 py-0.5 text-xs font-semibold text-sky-700">Email đã xác thực</span>
                                @else
                                    <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700">Chưa xác thực</span>
                                @endif
                            </div>

                            <div class="mt-1.5 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500">
                                <span class="flex items-center gap-1">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                                    </svg>
                                    <span class="font-mono">{{ $user->username }}</span>
                                </span>
                                <span class="flex items-center gap-1">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/>
                                    </svg>
                                    {{ $user->email }}
                                </span>
                                <span class="text-slate-400">Tạo {{ $user->created_at?->format('d/m/Y') }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex shrink-0 flex-wrap items-center gap-2">
                        <a href="{{ route('admin.accounts.show', $user) }}"
                           class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2
                                  text-xs font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50"
                           title="Xem chi tiết">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                            </svg>
                            Chi tiết
                        </a>

                        <a href="{{ route('admin.accounts.edit', $user) }}"
                           class="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2
                                  text-xs font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50"
                           title="Sửa">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/>
                            </svg>
                            Sửa
                        </a>

                        <button type="button"
                                class="js-reset-password inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2
                                       text-xs font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50"
                                data-reset-url="{{ route('admin.accounts.reset-password', $user) }}"
                                data-username="{{ $user->username }}"
                                title="Đặt lại mật khẩu">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z"/>
                            </svg>
                            Mật khẩu
                        </button>

                        @if (!$isSelf)
                            <form action="{{ route('admin.accounts.toggle-status', $user) }}"
                                  method="POST" id="toggle-form-{{ $user->id }}">
                                @csrf @method('PATCH')
                                <button type="button"
                                        class="js-toggle-status inline-flex items-center gap-1.5 rounded-xl px-3.5 py-2
                                               text-xs font-semibold shadow-sm transition
                                               {{ $user->status === 'active'
                                                   ? 'border border-orange-200 bg-white text-orange-600 hover:bg-orange-50'
                                                   : 'border border-emerald-200 bg-white text-emerald-600 hover:bg-emerald-50' }}"
                                        data-user-id="{{ $user->id }}"
                                        data-username="{{ $user->username }}"
                                        data-is-active="{{ $user->status === 'active' ? '1' : '0' }}"
                                        title="{{ $user->status === 'active' ? 'Khóa tài khoản' : 'Mở khóa' }}">
                                    @if($user->status === 'active')
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/>
                                        </svg>
                                        Khóa
                                    @else
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75M7.5 15.75H3.375c-.621 0-1.125.504-1.125 1.125v3.375c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125V16.875c0-.621-.504-1.125-1.125-1.125H16.5v-1.875a2.625 2.625 0 0 0-2.625-2.625h-1.5a2.625 2.625 0 0 0-2.625 2.625V15.75Z"/>
                                        </svg>
                                        Mở khóa
                                    @endif
                                </button>
                            </form>

                            <form action="{{ route('admin.accounts.destroy', $user) }}"
                                  method="POST" id="delete-form-{{ $user->id }}">
                                @csrf @method('DELETE')
                                <button type="button"
                                        class="js-delete-account inline-flex h-9 w-9 items-center justify-center rounded-xl
                                               border border-slate-200 text-slate-400 transition hover:border-rose-200
                                               hover:bg-rose-50 hover:text-rose-600"
                                        data-user-id="{{ $user->id }}"
                                        data-username="{{ $user->username }}"
                                        title="Xóa">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                                    </svg>
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
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128A12.318 12.318 0 0 1 8.624 21a12.318 12.318 0 0 1-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.965-3.07M12 7.875a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Z"/>
                    </svg>
                </div>
                <h3 class="mt-4 text-sm font-bold text-slate-700">Chưa có tài khoản nào</h3>
                <p class="mt-1 text-sm text-slate-500">
                    @if($hasFilters)
                        Không tìm thấy tài khoản phù hợp với bộ lọc.
                    @else
                        Hãy thêm tài khoản mới để bắt đầu.
                    @endif
                </p>
                <div class="mt-4 flex justify-center gap-3">
                    @if($hasFilters)
                        <a href="{{ route('admin.accounts') }}"
                           class="inline-flex rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                            Xóa bộ lọc
                        </a>
                    @endif
                    <a href="{{ route('admin.accounts.create') }}"
                       class="inline-flex rounded-xl bg-violet-600 px-4 py-2 text-sm font-semibold text-white hover:bg-violet-700">
                        Thêm tài khoản
                    </a>
                </div>
            </div>
        @endforelse

        @if ($users->hasPages())
            <div class="border-t border-slate-100 px-6 py-4">
                {{ $users->links() }}
            </div>
        @endif
    </div>

</div>

{{-- Modal khóa/mở khóa --}}
<div id="toggle-modal"
     class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm"
     style="display: none;">
    <div class="mx-4 w-full max-w-sm rounded-2xl bg-white p-6 text-center shadow-xl">
        <div id="toggle-modal-icon-lock" style="display: none;"
             class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-orange-100">
            <svg class="h-7 w-7 text-orange-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/>
            </svg>
        </div>
        <div id="toggle-modal-icon-unlock" style="display: none;"
             class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-100">
            <svg class="h-7 w-7 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75M7.5 15.75H3.375c-.621 0-1.125.504-1.125 1.125v3.375c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125V16.875c0-.621-.504-1.125-1.125-1.125H16.5v-1.875a2.625 2.625 0 0 0-2.625-2.625h-1.5a2.625 2.625 0 0 0-2.625 2.625V15.75Z"/>
            </svg>
        </div>
        <h3 id="toggle-modal-title" class="mt-5 text-lg font-bold text-slate-800"></h3>
        <p class="mt-2 text-sm text-slate-500">
            Bạn có chắc muốn <span id="toggle-modal-action" class="font-semibold text-slate-700"></span>
            tài khoản <span id="toggle-account-name" class="font-semibold text-slate-700"></span>?
        </p>
        <p id="toggle-modal-note" class="mt-2 text-xs text-slate-400"></p>
        <div class="mt-6 flex gap-3">
            <button type="button" onclick="closeToggleModal()"
                    class="flex-1 rounded-xl bg-slate-100 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-200">
                Hủy
            </button>
            <button type="button" onclick="confirmToggle()" id="toggle-modal-confirm"
                    class="flex-1 rounded-xl px-5 py-3 text-sm font-semibold text-white transition"
                    style="background-color: #ea580c;">
                Khóa
            </button>
        </div>
    </div>
</div>

{{-- Modal xóa --}}
<div id="delete-modal"
     class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm"
     style="display: none;">
    <div class="mx-4 w-full max-w-sm rounded-2xl bg-white p-6 text-center shadow-xl">
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-rose-100">
            <svg class="h-7 w-7 text-rose-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
            </svg>
        </div>
        <h3 class="mt-5 text-lg font-bold text-slate-800">Xóa tài khoản?</h3>
        <p class="mt-2 text-sm text-slate-500">
            Bạn có chắc muốn xóa tài khoản
            <span id="delete-account-name" class="font-semibold text-slate-700"></span>?
            Tài khoản sẽ được chuyển vào thùng rác.
        </p>
        <div class="mt-6 flex gap-3">
            <button type="button" onclick="closeDeleteModal()"
                    class="flex-1 rounded-xl bg-slate-100 px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-200">
                Hủy
            </button>
            <button type="button" onclick="confirmDelete()"
                    class="flex-1 rounded-xl bg-rose-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-rose-700">
                Xóa
            </button>
        </div>
    </div>
</div>

@include('admin.accounts.partials.reset-password-modal')

@if (session('success'))
    <div id="success-toast"
         class="fixed right-6 top-6 z-50 flex max-w-sm items-center gap-3 rounded-2xl border border-emerald-200 bg-white px-5 py-4 shadow-lg">
        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-emerald-100">
            <svg class="h-5 w-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
        </div>
        <p class="text-sm font-medium text-slate-700">{{ session('success') }}</p>
    </div>
@endif

@if (session('error'))
    <div id="error-toast"
         class="fixed right-6 top-6 z-50 flex max-w-sm items-center gap-3 rounded-2xl border border-rose-200 bg-white px-5 py-4 shadow-lg">
        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-rose-100">
            <svg class="h-5 w-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </div>
        <p class="text-sm font-medium text-slate-700">{{ session('error') }}</p>
    </div>
@endif

<script>
    let toggleTargetId = null;
    let deleteTargetId = null;

    function openDeleteModal(id, username) {
        deleteTargetId = String(id);
        document.getElementById('delete-account-name').textContent = username;
        const modal = document.getElementById('delete-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        modal.style.display = 'flex';
    }

    function closeDeleteModal() {
        const modal = document.getElementById('delete-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        modal.style.display = 'none';
        deleteTargetId = null;
    }

    function confirmDelete() {
        if (!deleteTargetId) return;
        const form = document.getElementById('delete-form-' + deleteTargetId);
        if (form) form.submit();
        else closeDeleteModal();
    }

    function openToggleModal(id, username, isActive) {
        toggleTargetId = id;
        document.getElementById('toggle-account-name').textContent = username;
        document.getElementById('toggle-modal-action').textContent = isActive ? 'khóa' : 'mở khóa';
        document.getElementById('toggle-modal-title').textContent = isActive ? 'Khóa tài khoản?' : 'Mở khóa tài khoản?';
        document.getElementById('toggle-modal-note').textContent = isActive
            ? 'Người dùng sẽ không thể đăng nhập cho đến khi được mở khóa.'
            : 'Người dùng có thể đăng nhập lại bình thường.';
        document.getElementById('toggle-modal-icon-lock').style.display = isActive ? 'flex' : 'none';
        document.getElementById('toggle-modal-icon-unlock').style.display = isActive ? 'none' : 'flex';
        const confirmBtn = document.getElementById('toggle-modal-confirm');
        confirmBtn.textContent = isActive ? 'Khóa' : 'Mở khóa';
        confirmBtn.style.backgroundColor = isActive ? '#ea580c' : '#059669';
        const modal = document.getElementById('toggle-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        modal.style.display = 'flex';
    }

    function closeToggleModal() {
        const modal = document.getElementById('toggle-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        modal.style.display = 'none';
        toggleTargetId = null;
    }

    function confirmToggle() {
        if (toggleTargetId) {
            document.getElementById('toggle-form-' + toggleTargetId).submit();
        }
    }

    document.querySelectorAll('.js-delete-account').forEach(function (button) {
        button.addEventListener('click', function () {
            openDeleteModal(this.dataset.userId, this.dataset.username);
        });
    });

    document.getElementById('delete-modal').addEventListener('click', function (e) {
        if (e.target === this) closeDeleteModal();
    });

    document.querySelectorAll('.js-toggle-status').forEach(function (button) {
        button.addEventListener('click', function () {
            openToggleModal(this.dataset.userId, this.dataset.username, this.dataset.isActive === '1');
        });
    });

    document.getElementById('toggle-modal').addEventListener('click', function (e) {
        if (e.target === this) closeToggleModal();
    });

    ['success-toast', 'error-toast'].forEach(function (id) {
        const toast = document.getElementById(id);
        if (toast) {
            setTimeout(function () {
                toast.style.transition = 'opacity 0.3s ease';
                toast.style.opacity = '0';
                setTimeout(function () { toast.remove(); }, 300);
            }, 4000);
        }
    });
</script>

</x-admin-layout>
