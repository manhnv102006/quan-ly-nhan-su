<x-guest-layout dark="true" wide="true" flush="true">
    <div class="relative min-h-screen overflow-hidden bg-[#030712] text-white">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_15%_15%,rgba(34,211,238,.22),transparent_30%),radial-gradient(circle_at_85%_20%,rgba(249,115,22,.16),transparent_28%),radial-gradient(circle_at_50%_90%,rgba(59,130,246,.18),transparent_35%)]"></div>
        <div class="absolute inset-0 opacity-20" style="background-image: linear-gradient(to right, rgba(255,255,255,.08) 1px, transparent 1px), linear-gradient(to bottom, rgba(255,255,255,.08) 1px, transparent 1px); background-size: 72px 72px;"></div>

        <header class="relative z-10 border-b border-white/10 bg-[#030712]/80 backdrop-blur">
            <div class="mx-auto flex max-w-[1500px] items-center justify-between gap-4 px-5 py-5 sm:px-8 lg:px-12">
                <a href="{{ route('public.recruitment.index') }}" class="flex min-w-0 items-center gap-3">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white shadow-lg shadow-cyan-950/30">
                        <x-application-logo class="h-12 w-12 object-contain" />
                    </div>
                    <div class="min-w-0">
                        <p class="truncate text-xs font-black uppercase tracking-[0.22em] text-cyan-300">HRM Careers</p>
                        <p class="truncate text-lg font-black text-white">{{ config('app.name', 'Laravel') }}</p>
                    </div>
                </a>

                <div class="flex items-center gap-2">
                    <a href="{{ route('public.recruitment.index') }}" class="hidden rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm font-black text-white transition hover:border-cyan-300/50 hover:bg-cyan-300/10 sm:inline-flex">Trang tuyển dụng</a>
                    <a href="{{ route('register') }}" class="rounded-xl bg-orange-500 px-4 py-2.5 text-sm font-black text-white shadow-lg shadow-orange-950/30 transition hover:bg-orange-600">Đăng ký</a>
                </div>
            </div>
        </header>

        <main class="relative z-10 mx-auto grid max-w-[1500px] grid-cols-1 gap-6 px-5 py-10 sm:px-8 lg:grid-cols-[minmax(380px,500px)_minmax(0,1fr)] lg:px-12 lg:py-16">
            <section class="min-w-0">
                <div class="rounded-[2rem] border border-white/10 bg-white/[0.08] p-5 shadow-2xl shadow-black/30 backdrop-blur sm:p-8">
                    <div class="mb-8">
                        <p class="inline-flex rounded-full border border-cyan-300/20 bg-cyan-300/10 px-4 py-2 text-xs font-black uppercase tracking-[0.25em] text-cyan-200">Đăng nhập hệ thống</p>
                        <h1 class="mt-5 text-4xl font-black tracking-tight text-white">Chào mừng trở lại</h1>
                        <p class="mt-3 text-sm leading-7 text-slate-300">Đăng nhập để vào trang quản trị nhân sự, tuyển dụng và các nghiệp vụ nội bộ.</p>
                    </div>

                    <x-auth-session-status class="mb-4 rounded-2xl border border-emerald-300/25 bg-emerald-300/10 px-4 py-3 text-sm font-bold text-emerald-200" :status="session('status')" />

                    <form method="POST" action="{{ route('login') }}" class="space-y-5">
                        @csrf

                        <div>
                            <label for="login" class="mb-2 block text-sm font-bold text-slate-200">Email hoặc tên đăng nhập</label>
                            <input
                                id="login"
                                class="block w-full rounded-2xl border border-white/10 bg-white/[0.06] px-4 py-3.5 text-base text-white outline-none transition placeholder:text-slate-500 focus:border-cyan-300 focus:ring-4 focus:ring-cyan-300/10"
                                type="text"
                                name="login"
                                value="{{ old('login') }}"
                                required
                                autofocus
                                autocomplete="username"
                                placeholder="admin@example.com"
                            >
                            <x-input-error :messages="$errors->get('login')" class="mt-2 text-red-300" />
                        </div>

                        <div>
                            <label for="password" class="mb-2 block text-sm font-bold text-slate-200">Mật khẩu</label>
                            <input
                                id="password"
                                class="block w-full rounded-2xl border border-white/10 bg-white/[0.06] px-4 py-3.5 text-base text-white outline-none transition placeholder:text-slate-500 focus:border-cyan-300 focus:ring-4 focus:ring-cyan-300/10"
                                type="password"
                                name="password"
                                required
                                autocomplete="current-password"
                                placeholder="Nhập mật khẩu"
                            >
                            <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-300" />
                        </div>

                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <label for="remember_me" class="inline-flex cursor-pointer items-center">
                                <input id="remember_me" type="checkbox" class="rounded border-white/20 bg-white/10 text-cyan-400 shadow-sm focus:ring-cyan-300" name="remember">
                                <span class="ms-2 text-sm font-semibold text-slate-300">Ghi nhớ đăng nhập</span>
                            </label>

                            @if (Route::has('password.request'))
                                <a class="text-sm font-black text-cyan-300 transition hover:text-cyan-200" href="{{ route('password.request') }}">
                                    Quên mật khẩu?
                                </a>
                            @endif
                        </div>

                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-gradient-to-r from-orange-500 to-cyan-500 px-5 py-4 text-sm font-black uppercase tracking-wide text-white shadow-xl shadow-cyan-950/30 transition hover:-translate-y-0.5 hover:from-orange-600 hover:to-cyan-600 focus:outline-none focus:ring-4 focus:ring-cyan-300/20">
                            Đăng nhập
                        </button>

                        <div class="rounded-2xl border border-white/10 bg-black/20 p-4 text-center text-sm text-slate-300">
                            Chưa có tài khoản?
                            <a href="{{ route('register') }}" class="font-black text-orange-300 transition hover:text-orange-200">Đăng ký ngay</a>
                        </div>
                    </form>
                </div>
            </section>

            <section class="min-w-0">
                <div class="grid h-full grid-cols-1 gap-5">
                    <div class="relative overflow-hidden rounded-[2rem] border border-white/10 bg-white/[0.07] p-6 shadow-2xl shadow-cyan-950/30 backdrop-blur sm:p-8">
                        <div class="absolute -right-24 -top-24 h-72 w-72 rounded-full bg-cyan-400/20 blur-3xl"></div>
                        <div class="absolute -bottom-24 left-20 h-72 w-72 rounded-full bg-orange-500/15 blur-3xl"></div>

                        <div class="relative flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                            <div>
                                <p class="text-sm font-black uppercase tracking-[0.25em] text-cyan-300">Tin tuyển dụng</p>
                                <h2 class="mt-3 text-4xl font-black tracking-tight text-white sm:text-5xl">Cơ hội đang mở</h2>
                                <p class="mt-3 max-w-2xl text-sm leading-7 text-slate-300">Ứng viên có thể xem tin và gửi hồ sơ mà không cần đăng nhập hệ thống.</p>
                            </div>
                            <a href="{{ route('public.recruitment.jobs') }}" class="inline-flex w-full items-center justify-center rounded-2xl bg-white px-5 py-3 text-sm font-black text-slate-950 transition hover:bg-slate-100 md:w-auto">Xem tất cả</a>
                        </div>

                        <div class="relative mt-8 grid grid-cols-1 gap-4 xl:grid-cols-3">
                            @forelse (($publicJobPosts ?? collect()) as $jobPost)
                                <article class="flex min-w-0 flex-col rounded-3xl border border-white/10 bg-black/25 p-5 transition hover:-translate-y-1 hover:border-cyan-300/50 hover:bg-white/[0.08]">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="text-sm font-black text-cyan-300">{{ $jobPost->department?->department_name ?? 'Chưa gắn phòng ban' }}</p>
                                            <h3 class="mt-3 break-words text-xl font-black leading-7 text-white">{{ $jobPost->title }}</h3>
                                        </div>
                                        <span class="shrink-0 rounded-full bg-emerald-400/10 px-3 py-1 text-xs font-black text-emerald-300">Mở</span>
                                    </div>

                                    <p class="mt-5 text-sm text-slate-400">Hạn nộp: <span class="font-black text-slate-200">{{ $jobPost->application_deadline?->format('d/m/Y') ?? 'Không giới hạn' }}</span></p>

                                    <div class="mt-auto flex flex-col gap-2 pt-5 sm:flex-row xl:flex-col 2xl:flex-row">
                                        <a href="{{ route('public.recruitment.show', $jobPost) }}" class="inline-flex w-full items-center justify-center rounded-2xl bg-cyan-500 px-4 py-3 text-sm font-black text-white transition hover:bg-cyan-600">Chi tiết</a>
                                        <a href="{{ route('public.recruitment.apply', $jobPost) }}" class="inline-flex w-full items-center justify-center rounded-2xl bg-white px-4 py-3 text-sm font-black text-slate-950 transition hover:bg-slate-100">Ứng tuyển</a>
                                    </div>
                                </article>
                            @empty
                                <div class="rounded-3xl border border-dashed border-white/15 bg-black/20 p-8 text-center xl:col-span-3">
                                    <h3 class="text-xl font-black text-white">Chưa có tin tuyển dụng đang mở</h3>
                                    <p class="mx-auto mt-3 max-w-md text-sm leading-7 text-slate-400">Các vị trí mới sẽ được cập nhật tại đây sau khi bộ phận nhân sự mở tuyển.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div class="rounded-3xl border border-cyan-300/20 bg-cyan-300/10 p-5">
                            <p class="text-3xl font-black text-cyan-200">Public</p>
                            <p class="mt-2 text-sm font-bold leading-6 text-slate-300">Xem tin tuyển dụng không cần tài khoản.</p>
                        </div>
                        <div class="rounded-3xl border border-orange-300/20 bg-orange-300/10 p-5">
                            <p class="text-3xl font-black text-orange-300">Fast</p>
                            <p class="mt-2 text-sm font-bold leading-6 text-slate-300">Gửi hồ sơ trực tiếp chỉ với một form.</p>
                        </div>
                        <div class="rounded-3xl border border-emerald-300/20 bg-emerald-300/10 p-5">
                            <p class="text-3xl font-black text-emerald-300">Admin</p>
                            <p class="mt-2 text-sm font-bold leading-6 text-slate-300">Hồ sơ tự động vào trang quản trị ứng viên.</p>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</x-guest-layout>
