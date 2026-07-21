<x-guest-layout wide="true">
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12 lg:items-start">
        <section class="lg:col-span-5">
            <div class="rounded-xl bg-white p-4 sm:p-6">
                <div class="mb-6 text-center">
                    <h1 class="text-2xl font-bold text-gray-900">Đăng nhập</h1>
                    <p class="mt-1 text-sm text-gray-500">Chào mừng trở lại! Vui lòng đăng nhập để tiếp tục.</p>
                </div>

                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <div>
                        <x-input-label for="login" value="Email hoặc tên đăng nhập" class="text-gray-700 font-medium" />
                        <x-text-input
                            id="login"
                            class="block mt-1.5 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                            type="text"
                            name="login"
                            :value="old('login')"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder="Nhập email hoặc username"
                        />
                        <x-input-error :messages="$errors->get('login')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="password" value="Mật khẩu" class="text-gray-700 font-medium" />
                        <x-text-input
                            id="password"
                            class="block mt-1.5 w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            placeholder="Nhập mật khẩu"
                        />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-between gap-3">
                        <label for="remember_me" class="inline-flex items-center cursor-pointer">
                            <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                            <span class="ms-2 text-sm text-gray-600">Ghi nhớ đăng nhập</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a class="text-sm text-indigo-600 hover:text-indigo-800 font-medium transition" href="{{ route('password.request') }}">
                                Quên mật khẩu?
                            </a>
                        @endif
                    </div>

                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-3 bg-gradient-to-r from-indigo-600 to-blue-600 border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-wide hover:from-indigo-700 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition shadow-md hover:shadow-lg">
                        Đăng nhập
                    </button>

                    <p class="text-center text-sm text-gray-600 pt-2">
                        Chưa có tài khoản?
                        <a href="{{ route('register') }}" class="text-indigo-600 hover:text-indigo-800 font-semibold transition">
                            Đăng ký ngay
                        </a>
                    </p>
                </form>
            </div>
        </section>

        <section class="lg:col-span-7">
            <div class="h-full rounded-xl bg-slate-50 p-4 sm:p-6">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-sm font-bold uppercase tracking-wide text-cyan-700">Tin tuyển dụng</p>
                        <h2 class="mt-2 text-2xl font-black text-slate-950">Cơ hội đang mở</h2>
                    </div>
                    <a href="{{ route('public.recruitment.index') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:border-cyan-300 hover:text-cyan-700">
                        Xem tất cả
                    </a>
                </div>

                <div class="mt-5 space-y-3">
                    @forelse (($publicJobPosts ?? collect()) as $jobPost)
                        <article class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                <div class="min-w-0">
                                    <p class="text-sm font-bold text-cyan-700">{{ $jobPost->department?->department_name ?? 'Chưa gắn phòng ban' }}</p>
                                    <h3 class="mt-1 break-words text-lg font-black text-slate-950">{{ $jobPost->title }}</h3>
                                    <p class="mt-2 text-sm text-slate-500">
                                        Hạn nộp:
                                        <span class="font-bold text-slate-700">{{ $jobPost->application_deadline?->format('d/m/Y') ?? 'Không giới hạn' }}</span>
                                    </p>
                                </div>
                                <span class="inline-flex w-fit shrink-0 rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700">Đang tuyển</span>
                            </div>

                            <div class="mt-4 flex flex-col gap-2 sm:flex-row">
                                <a href="{{ route('public.recruitment.show', $jobPost) }}" class="inline-flex items-center justify-center rounded-lg bg-cyan-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-cyan-700">
                                    Xem chi tiết
                                </a>
                                <a href="{{ route('public.recruitment.apply', $jobPost) }}" class="inline-flex items-center justify-center rounded-lg bg-slate-100 px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-200">
                                    Ứng tuyển ngay
                                </a>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-lg border border-dashed border-slate-300 bg-white p-6 text-center">
                            <h3 class="text-base font-black text-slate-900">Chưa có tin tuyển dụng đang mở</h3>
                            <p class="mt-2 text-sm text-slate-500">Các vị trí mới sẽ được hiển thị tại đây khi được mở tuyển.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
</x-guest-layout>
