<x-guest-layout wide="true" flush="true">
    <div class="min-h-screen bg-white text-slate-900">
        <header class="sticky top-0 z-10 border-b border-slate-100 bg-white/95 backdrop-blur">
            <div class="mx-auto flex w-[83%] items-center justify-between gap-4 py-5">
                <a href="{{ route('public.recruitment.index') }}" class="flex min-w-0 items-center gap-3">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <x-application-logo class="h-12 w-12 object-contain" />
                    </div>
                    <div class="min-w-0">
                        <p class="truncate text-xs font-bold uppercase tracking-wider text-cyan-700">HRM Careers</p>
                        <p class="truncate text-lg font-black text-slate-900">{{ config('app.name', 'Laravel') }}</p>
                    </div>
                </a>

                <a href="{{ route('public.recruitment.index') }}" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-800 transition hover:border-cyan-300 hover:text-cyan-800">
                    Trang tuyển dụng
                </a>
            </div>
        </header>

        <main class="mx-auto grid w-[83%] grid-cols-1 gap-8 py-10 lg:grid-cols-[minmax(360px,440px)_minmax(0,1fr)] lg:py-14">
            <section class="min-w-0">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-lg shadow-slate-200/40 sm:p-8">
                    <div class="mb-8">
                        <p class="text-xs font-bold uppercase tracking-wider text-cyan-700">Đăng nhập hệ thống</p>
                        <h1 class="mt-3 text-3xl font-black tracking-tight text-slate-900">Chào mừng trở lại</h1>
                        <p class="mt-3 text-sm leading-relaxed text-slate-600">Đăng nhập để vào trang quản trị nhân sự, tuyển dụng và các nghiệp vụ nội bộ.</p>
                        <p class="mt-2 text-xs text-slate-500">Tài khoản mới chỉ do quản trị viên tạo trong mục Quản lý tài khoản.</p>
                    </div>

                    <x-auth-session-status class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800" :status="session('status')" />

                    <form method="POST" action="{{ route('login') }}" class="space-y-5">
                        @csrf

                        <div>
                            <label for="login" class="mb-2 block text-sm font-semibold text-slate-700">Email hoặc tên đăng nhập</label>
                            <input
                                id="login"
                                class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-base text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20"
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
                                class="block w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-base text-slate-900 outline-none transition placeholder:text-slate-400 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20"
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
                                <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-cyan-600 shadow-sm focus:ring-cyan-500" name="remember">
                                <span class="ms-2 text-sm font-medium text-slate-600">Ghi nhớ đăng nhập</span>
                            </label>

                            @if (Route::has('password.request'))
                                <a class="text-sm font-semibold text-cyan-700 transition hover:text-cyan-900" href="{{ route('password.request') }}">
                                    Quên mật khẩu?
                                </a>
                            @endif
                        </div>

                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-cyan-600 px-5 py-3.5 text-sm font-bold text-white shadow-md shadow-cyan-600/20 transition hover:bg-cyan-700 focus:outline-none focus:ring-2 focus:ring-cyan-500/30">
                            Đăng nhập
                        </button>
                    </form>
                </div>
            </section>

            <section class="min-w-0">
                <div class="grid h-full grid-cols-1 gap-5">
                    <div class="rounded-2xl border border-slate-200 bg-gradient-to-br from-slate-50 to-white p-6 shadow-sm sm:p-8">
                        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                            <div>
                                <p class="text-sm font-bold uppercase tracking-wider text-cyan-700">Tin tuyển dụng</p>
                                <h2 class="mt-2 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl">Cơ hội đang mở</h2>
                                <p class="mt-3 max-w-2xl text-sm leading-relaxed text-slate-600">Ứng viên có thể xem tin và gửi hồ sơ mà không cần đăng nhập hệ thống.</p>
                            </div>
                            <a href="{{ route('public.recruitment.jobs') }}" class="inline-flex w-full items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-bold text-slate-800 transition hover:border-cyan-300 md:w-auto">Xem tất cả</a>
                        </div>

                        <div class="mt-8 grid grid-cols-1 gap-4 xl:grid-cols-3">
                            @forelse (($publicJobPosts ?? collect()) as $jobPost)
                                <article class="flex min-w-0 flex-col rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-cyan-200 hover:shadow-md">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="text-sm font-bold text-cyan-700">{{ $jobPost->department?->department_name ?? 'Chưa gắn phòng ban' }}</p>
                                            <h3 class="mt-2 break-words text-lg font-bold leading-snug text-slate-900">{{ $jobPost->title }}</h3>
                                        </div>
                                        <span class="shrink-0 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-bold text-emerald-800">Mở</span>
                                    </div>

                                    <p class="mt-4 text-sm text-slate-500">Hạn nộp: <span class="font-semibold text-slate-700">{{ $jobPost->application_deadline?->format('d/m/Y') ?? 'Không giới hạn' }}</span></p>

                                    <div class="mt-auto flex flex-col gap-2 pt-4 sm:flex-row xl:flex-col 2xl:flex-row">
                                        <a href="{{ route('public.recruitment.show', $jobPost) }}" class="inline-flex w-full items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-800 transition hover:border-cyan-300">Chi tiết</a>
                                        <a href="{{ route('public.recruitment.apply', $jobPost) }}" class="inline-flex w-full items-center justify-center rounded-xl bg-cyan-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-cyan-700">Ứng tuyển</a>
                                    </div>
                                </article>
                            @empty
                                <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 p-8 text-center xl:col-span-3">
                                    <h3 class="text-lg font-bold text-slate-900">Chưa có tin tuyển dụng đang mở</h3>
                                    <p class="mx-auto mt-2 max-w-md text-sm text-slate-500">Các vị trí mới sẽ được cập nhật sau khi bộ phận nhân sự mở tuyển.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div class="rounded-xl border border-cyan-100 bg-cyan-50/50 p-5">
                            <p class="text-lg font-black text-cyan-800">Public</p>
                            <p class="mt-2 text-sm leading-relaxed text-slate-600">Xem tin tuyển dụng không cần tài khoản.</p>
                        </div>
                        <div class="rounded-xl border border-orange-100 bg-orange-50/50 p-5">
                            <p class="text-lg font-black text-orange-800">Fast</p>
                            <p class="mt-2 text-sm leading-relaxed text-slate-600">Gửi hồ sơ trực tiếp chỉ với một form.</p>
                        </div>
                        <div class="rounded-xl border border-emerald-100 bg-emerald-50/50 p-5">
                            <p class="text-lg font-black text-emerald-800">Admin</p>
                            <p class="mt-2 text-sm leading-relaxed text-slate-600">Hồ sơ tự động vào trang quản trị ứng viên.</p>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</x-guest-layout>
