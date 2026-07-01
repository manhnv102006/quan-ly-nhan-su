<div class="rounded-3xl border border-slate-100 bg-white shadow-sm overflow-hidden">
    <div class="border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white px-6 py-5 sm:px-8">
        <div class="flex items-start gap-4">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-violet-100 text-violet-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                          d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-bold text-slate-800">Thông tin cá nhân</h3>
                <p class="mt-1 text-sm text-slate-500">Cập nhật họ tên và địa chỉ email của tài khoản.</p>
            </div>
        </div>
    </div>

    <div class="p-6 sm:p-8">
        <form id="send-verification" method="post" action="{{ route('verification.send') }}">
            @csrf
        </form>

        <form method="post" action="{{ route('profile.update') }}" class="space-y-5">
            @csrf
            @method('patch')

            <div>
                <label for="name" class="block text-sm font-medium text-slate-700 mb-1.5">Họ và tên</label>
                <input id="name" name="name" type="text"
                       value="{{ old('name', $user->name) }}" required autofocus autocomplete="name"
                       class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-violet-400 focus:ring-2 focus:ring-violet-500/30 transition">
                @error('name')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-slate-700 mb-1.5">Email</label>
                <input id="email" name="email" type="email"
                       value="{{ old('email', $user->email) }}" required autocomplete="username"
                       class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-violet-400 focus:ring-2 focus:ring-violet-500/30 transition">
                @error('email')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <div class="mt-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3">
                        <p class="text-sm text-amber-800">
                            Email chưa được xác minh.
                            <button form="send-verification" type="submit"
                                    class="font-semibold text-amber-900 underline hover:no-underline">
                                Gửi lại email xác minh
                            </button>
                        </p>
                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-2 text-sm font-medium text-emerald-700">Đã gửi link xác minh mới tới email của bạn.</p>
                        @endif
                    </div>
                @endif
            </div>

            <div class="flex flex-wrap items-center gap-3 pt-2">
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-violet-600 px-5 py-3 text-sm font-semibold text-white shadow-sm shadow-violet-500/20 transition hover:bg-violet-700">
                    Lưu thay đổi
                </button>
            </div>
        </form>
    </div>
</div>
