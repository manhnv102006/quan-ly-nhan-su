<x-guest-layout wide="true" flush="true">
    <div class="relative min-h-screen overflow-hidden bg-slate-950 text-white">
        {{-- Nền gradient + ánh sáng mềm --}}
        <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-slate-900 via-[#1a2744] to-indigo-950"></div>
        <div class="pointer-events-none absolute -left-32 top-0 h-96 w-96 rounded-full bg-blue-500/10 blur-3xl"></div>
        <div class="pointer-events-none absolute -right-24 bottom-0 h-80 w-80 rounded-full bg-indigo-400/10 blur-3xl"></div>
        <div class="pointer-events-none absolute left-1/2 top-1/3 h-64 w-64 -translate-x-1/2 rounded-full bg-sky-500/5 blur-3xl"></div>

        <header class="relative z-10 border-b border-white/5 bg-slate-900/30 backdrop-blur-md">
            <div class="mx-auto flex w-[83%] max-w-5xl items-center justify-between gap-4 py-5">
                <a href="{{ route('public.recruitment.index') }}" class="flex min-w-0 items-center gap-3">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl border border-white/10 bg-white/95 shadow-lg shadow-black/20">
                        <x-application-logo class="h-12 w-12 object-contain" />
                    </div>
                    <div class="min-w-0">
                        <p class="truncate text-xs font-bold uppercase tracking-wider text-sky-300/80">HRM Careers</p>
                        <p class="truncate text-lg font-black text-white">{{ config('app.name', 'Laravel') }}</p>
                    </div>
                </a>

                <a href="{{ route('public.recruitment.index') }}" class="rounded-xl border border-white/15 bg-white/5 px-4 py-2.5 text-sm font-semibold text-slate-200 transition hover:border-white/25 hover:bg-white/10 hover:text-white">
                    Trang tuyển dụng
                </a>
            </div>
        </header>

        <main class="relative z-10 mx-auto flex w-full max-w-md items-center justify-center px-6 py-12 sm:py-16">
            <section class="w-full">
                <div class="rounded-2xl border border-white/10 bg-white/95 p-6 text-slate-900 shadow-2xl shadow-black/40 backdrop-blur-sm sm:p-8">
                    <div class="mb-8">
                        <p class="text-xs font-bold uppercase tracking-wider text-indigo-600">Đăng nhập hệ thống</p>
                        <h1 class="mt-3 text-3xl font-black tracking-tight text-slate-900">Chào mừng trở lại</h1>
                        <p class="mt-3 text-sm leading-relaxed text-slate-600">Đăng nhập để vào trang quản trị nhân sự và các nghiệp vụ nội bộ.</p>
                        <p class="mt-2 text-xs text-slate-500">Tài khoản mới chỉ do quản trị viên tạo trong mục Quản lý tài khoản.</p>
                    </div>

                    <x-auth-session-status class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800" :status="session('status')" />

                    <form method="POST" action="{{ route('login') }}" class="space-y-5">
                        @csrf

                        <div>
                            <label for="login" class="mb-2 block text-sm font-semibold text-slate-700">Email hoặc tên đăng nhập</label>
                            <input
                                id="login"
                                class="block w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-3 text-base text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-400/20"
                                type="text"
                                name="login"
                                value="{{ old('login') }}"
                                required
                                autofocus
                                autocomplete="username"
                                placeholder="admin@example.com"
                            >
                            <x-input-error :messages="$errors->get('login')" class="mt-2 text-red-600" />
                        </div>

                        <div>
                            <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">Mật khẩu</label>
                            <input
                                id="password"
                                class="block w-full rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-3 text-base text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-400/20"
                                type="password"
                                name="password"
                                required
                                autocomplete="current-password"
                                placeholder="Nhập mật khẩu"
                            >
                            <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-600" />
                        </div>

                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <label for="remember_me" class="inline-flex cursor-pointer items-center">
                                <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                                <span class="ms-2 text-sm font-medium text-slate-600">Ghi nhớ đăng nhập</span>
                            </label>

                            @if (Route::has('password.request'))
                                <a class="text-sm font-semibold text-indigo-600 transition hover:text-indigo-800" href="{{ route('password.request') }}">
                                    Quên mật khẩu?
                                </a>
                            @endif
                        </div>

                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-gradient-to-r from-indigo-600 to-blue-600 px-5 py-3.5 text-sm font-bold text-white shadow-lg shadow-indigo-900/30 transition hover:from-indigo-500 hover:to-blue-500 focus:outline-none focus:ring-2 focus:ring-indigo-400/40">
                            Đăng nhập
                        </button>
                    </form>
                </div>
            </section>
        </main>
    </div>
</x-guest-layout>
