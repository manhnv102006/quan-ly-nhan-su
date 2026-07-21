<x-guest-layout dark="true" wide="true" flush="true">
    <div class="relative min-h-screen overflow-hidden bg-[#030712] text-white">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_18%_12%,rgba(249,115,22,.18),transparent_30%),radial-gradient(circle_at_82%_20%,rgba(34,211,238,.22),transparent_30%),radial-gradient(circle_at_50%_92%,rgba(16,185,129,.14),transparent_34%)]"></div>
        <div class="absolute inset-0 opacity-20" style="background-image: linear-gradient(to right, rgba(255,255,255,.08) 1px, transparent 1px), linear-gradient(to bottom, rgba(255,255,255,.08) 1px, transparent 1px); background-size: 72px 72px;"></div>

        <header class="relative z-10 border-b border-white/10 bg-[#030712]/80 backdrop-blur">
            <div class="mx-auto flex max-w-[1500px] items-center justify-between gap-4 px-5 py-5 sm:px-8 lg:px-12">
                <a href="{{ route('public.recruitment.index') }}" class="flex min-w-0 items-center gap-3">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white shadow-lg shadow-cyan-950/30">
                        <x-application-logo class="h-12 w-12 object-contain" />
                    </div>
                    <div class="min-w-0">
                        <p class="truncate text-xs font-black uppercase tracking-[0.22em] text-cyan-300">HRM System</p>
                        <p class="truncate text-lg font-black text-white">{{ config('app.name', 'Laravel') }}</p>
                    </div>
                </a>

                <div class="flex items-center gap-2">
                    <a href="{{ route('public.recruitment.index') }}" class="hidden rounded-xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm font-black text-white transition hover:border-cyan-300/50 hover:bg-cyan-300/10 sm:inline-flex">Trang tuyển dụng</a>
                    <a href="{{ route('login') }}" class="rounded-xl bg-white px-4 py-2.5 text-sm font-black text-slate-950 shadow-lg shadow-cyan-950/20 transition hover:bg-slate-100">Đăng nhập</a>
                </div>
            </div>
        </header>

        <main class="relative z-10 mx-auto grid max-w-[1500px] grid-cols-1 gap-6 px-5 py-10 sm:px-8 lg:grid-cols-[minmax(0,1fr)_minmax(420px,560px)] lg:px-12 lg:py-16">
            <section class="order-2 min-w-0 lg:order-1">
                <div class="grid h-full grid-cols-1 gap-5">
                    <div class="relative overflow-hidden rounded-[2rem] border border-white/10 bg-white/[0.07] p-6 shadow-2xl shadow-cyan-950/30 backdrop-blur sm:p-8">
                        <div class="absolute -right-24 -top-24 h-72 w-72 rounded-full bg-orange-400/20 blur-3xl"></div>
                        <div class="absolute -bottom-24 left-20 h-72 w-72 rounded-full bg-cyan-400/15 blur-3xl"></div>

                        <div class="relative">
                            <p class="text-sm font-black uppercase tracking-[0.25em] text-orange-300">Tạo tài khoản nội bộ</p>
                            <h1 class="mt-4 max-w-3xl text-5xl font-black leading-tight tracking-tight sm:text-7xl">Bắt đầu quản trị nhân sự theo cách gọn hơn</h1>
                            <p class="mt-5 max-w-2xl text-base leading-8 text-slate-300">
                                Tài khoản được tạo cho người dùng nội bộ theo quyền đã chọn. Sau khi đăng ký thành công, hệ thống sẽ đưa bạn về đúng trang làm việc theo vai trò.
                            </p>
                        </div>

                        <div class="relative mt-10 grid grid-cols-1 gap-4 md:grid-cols-3">
                            <div class="rounded-3xl border border-cyan-300/20 bg-cyan-300/10 p-5">
                                <p class="text-3xl font-black text-cyan-200">Role</p>
                                <p class="mt-2 text-sm font-bold leading-6 text-slate-300">Chọn quyền tài khoản ngay khi tạo.</p>
                            </div>
                            <div class="rounded-3xl border border-orange-300/20 bg-orange-300/10 p-5">
                                <p class="text-3xl font-black text-orange-300">Secure</p>
                                <p class="mt-2 text-sm font-bold leading-6 text-slate-300">Mật khẩu xác nhận theo chuẩn hệ thống.</p>
                            </div>
                            <div class="rounded-3xl border border-emerald-300/20 bg-emerald-300/10 p-5">
                                <p class="text-3xl font-black text-emerald-300">Fast</p>
                                <p class="mt-2 text-sm font-bold leading-6 text-slate-300">Tạo xong là vào dashboard phù hợp.</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <a href="{{ route('public.recruitment.jobs') }}" class="rounded-3xl border border-white/10 bg-white/[0.06] p-5 transition hover:-translate-y-1 hover:border-cyan-300/50">
                            <p class="text-sm font-black uppercase tracking-[0.22em] text-cyan-300">Public careers</p>
                            <h2 class="mt-4 text-2xl font-black text-white">Xem cơ hội tuyển dụng</h2>
                            <p class="mt-3 text-sm leading-7 text-slate-300">Ứng viên bên ngoài có thể ứng tuyển mà không cần tài khoản.</p>
                        </a>
                        <a href="{{ route('public.recruitment.about') }}" class="rounded-3xl border border-white/10 bg-white/[0.06] p-5 transition hover:-translate-y-1 hover:border-orange-300/50">
                            <p class="text-sm font-black uppercase tracking-[0.22em] text-orange-300">HRM culture</p>
                            <h2 class="mt-4 text-2xl font-black text-white">Tìm hiểu về HRM</h2>
                            <p class="mt-3 text-sm leading-7 text-slate-300">Khám phá cách hệ thống kết nối tuyển dụng và quản trị nhân sự.</p>
                        </a>
                    </div>
                </div>
            </section>

            <section class="order-1 min-w-0 lg:order-2">
                <div class="rounded-[2rem] border border-white/10 bg-white/[0.08] p-5 shadow-2xl shadow-black/30 backdrop-blur sm:p-8">
                    <div class="mb-8">
                        <p class="inline-flex rounded-full border border-cyan-300/20 bg-cyan-300/10 px-4 py-2 text-xs font-black uppercase tracking-[0.25em] text-cyan-200">Đăng ký</p>
                        <h2 class="mt-5 text-4xl font-black tracking-tight text-white">Tạo tài khoản</h2>
                        <p class="mt-3 text-sm leading-7 text-slate-300">Điền thông tin tài khoản để bắt đầu sử dụng hệ thống.</p>
                    </div>

                    <form method="POST" action="{{ route('register') }}" class="space-y-5">
                        @csrf

                        <div>
                            <label for="role_id" class="mb-2 block text-sm font-bold text-slate-200">Quyền tài khoản</label>
                            <select
                                id="role_id"
                                name="role_id"
                                required
                                class="block w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3.5 text-base text-white outline-none transition focus:border-cyan-300 focus:ring-4 focus:ring-cyan-300/10"
                            >
                                <option value="" disabled {{ old('role_id') ? '' : 'selected' }}>Chọn quyền</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}" {{ (string) old('role_id') === (string) $role->id ? 'selected' : '' }}>
                                        {{ $role->label() }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('role_id')" class="mt-2 text-red-300" />
                        </div>

                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div>
                                <label for="username" class="mb-2 block text-sm font-bold text-slate-200">Tên đăng nhập</label>
                                <input
                                    id="username"
                                    class="block w-full rounded-2xl border border-white/10 bg-white/[0.06] px-4 py-3.5 text-base text-white outline-none transition placeholder:text-slate-500 focus:border-cyan-300 focus:ring-4 focus:ring-cyan-300/10"
                                    type="text"
                                    name="username"
                                    value="{{ old('username') }}"
                                    required
                                    autofocus
                                    autocomplete="username"
                                    placeholder="nguyenvana"
                                >
                                <x-input-error :messages="$errors->get('username')" class="mt-2 text-red-300" />
                            </div>

                            <div>
                                <label for="name" class="mb-2 block text-sm font-bold text-slate-200">Họ và tên</label>
                                <input
                                    id="name"
                                    class="block w-full rounded-2xl border border-white/10 bg-white/[0.06] px-4 py-3.5 text-base text-white outline-none transition placeholder:text-slate-500 focus:border-cyan-300 focus:ring-4 focus:ring-cyan-300/10"
                                    type="text"
                                    name="name"
                                    value="{{ old('name') }}"
                                    required
                                    autocomplete="name"
                                    placeholder="Nguyễn Văn A"
                                >
                                <x-input-error :messages="$errors->get('name')" class="mt-2 text-red-300" />
                            </div>
                        </div>

                        <div>
                            <label for="email" class="mb-2 block text-sm font-bold text-slate-200">Email</label>
                            <input
                                id="email"
                                class="block w-full rounded-2xl border border-white/10 bg-white/[0.06] px-4 py-3.5 text-base text-white outline-none transition placeholder:text-slate-500 focus:border-cyan-300 focus:ring-4 focus:ring-cyan-300/10"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                autocomplete="email"
                                placeholder="email@example.com"
                            >
                            <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-300" />
                        </div>

                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div>
                                <label for="password" class="mb-2 block text-sm font-bold text-slate-200">Mật khẩu</label>
                                <input
                                    id="password"
                                    class="block w-full rounded-2xl border border-white/10 bg-white/[0.06] px-4 py-3.5 text-base text-white outline-none transition placeholder:text-slate-500 focus:border-cyan-300 focus:ring-4 focus:ring-cyan-300/10"
                                    type="password"
                                    name="password"
                                    required
                                    autocomplete="new-password"
                                    placeholder="Tối thiểu 8 ký tự"
                                >
                                <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-300" />
                            </div>

                            <div>
                                <label for="password_confirmation" class="mb-2 block text-sm font-bold text-slate-200">Xác nhận mật khẩu</label>
                                <input
                                    id="password_confirmation"
                                    class="block w-full rounded-2xl border border-white/10 bg-white/[0.06] px-4 py-3.5 text-base text-white outline-none transition placeholder:text-slate-500 focus:border-cyan-300 focus:ring-4 focus:ring-cyan-300/10"
                                    type="password"
                                    name="password_confirmation"
                                    required
                                    autocomplete="new-password"
                                    placeholder="Nhập lại mật khẩu"
                                >
                                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-red-300" />
                            </div>
                        </div>

                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-gradient-to-r from-orange-500 to-cyan-500 px-5 py-4 text-sm font-black uppercase tracking-wide text-white shadow-xl shadow-cyan-950/30 transition hover:-translate-y-0.5 hover:from-orange-600 hover:to-cyan-600 focus:outline-none focus:ring-4 focus:ring-cyan-300/20">
                            Đăng ký
                        </button>

                        <div class="rounded-2xl border border-white/10 bg-black/20 p-4 text-center text-sm text-slate-300">
                            Đã có tài khoản?
                            <a href="{{ route('login') }}" class="font-black text-cyan-300 transition hover:text-cyan-200">Đăng nhập</a>
                        </div>
                    </form>
                </div>
            </section>
        </main>
    </div>
</x-guest-layout>
