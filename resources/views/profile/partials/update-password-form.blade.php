<div class="rounded-3xl border border-slate-100 bg-white shadow-sm overflow-hidden">
    <div class="border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white px-6 py-5 sm:px-8">
        <div class="flex items-start gap-4">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-blue-100 text-blue-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                          d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-bold text-slate-800">Đổi mật khẩu</h3>
                <p class="mt-1 text-sm text-slate-500">Dùng mật khẩu mạnh, dài và khó đoán để bảo vệ tài khoản.</p>
            </div>
        </div>
    </div>

    <div class="p-6 sm:p-8">
        <form method="post" action="{{ route('password.update') }}" class="space-y-5">
            @csrf
            @method('put')

            <div>
                <label for="update_password_current_password" class="block text-sm font-medium text-slate-700 mb-1.5">Mật khẩu hiện tại</label>
                <input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password"
                       class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-violet-400 focus:ring-2 focus:ring-violet-500/30 transition">
                @if ($errors->updatePassword->has('current_password'))
                    <p class="mt-1.5 text-xs text-red-600">{{ $errors->updatePassword->first('current_password') }}</p>
                @endif
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="update_password_password" class="block text-sm font-medium text-slate-700 mb-1.5">Mật khẩu mới</label>
                    <input id="update_password_password" name="password" type="password" autocomplete="new-password"
                           class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-violet-400 focus:ring-2 focus:ring-violet-500/30 transition">
                    @if ($errors->updatePassword->has('password'))
                        <p class="mt-1.5 text-xs text-red-600">{{ $errors->updatePassword->first('password') }}</p>
                    @endif
                </div>
                <div>
                    <label for="update_password_password_confirmation" class="block text-sm font-medium text-slate-700 mb-1.5">Xác nhận mật khẩu</label>
                    <input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
                           class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-violet-400 focus:ring-2 focus:ring-violet-500/30 transition">
                    @if ($errors->updatePassword->has('password_confirmation'))
                        <p class="mt-1.5 text-xs text-red-600">{{ $errors->updatePassword->first('password_confirmation') }}</p>
                    @endif
                </div>
            </div>

            <div class="rounded-xl border border-slate-100 bg-slate-50 px-4 py-3">
                <p class="text-xs text-slate-500 leading-relaxed">
                    Mật khẩu nên có ít nhất 8 ký tự, kết hợp chữ hoa, chữ thường, số và ký tự đặc biệt.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3 pt-2">
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-violet-600 px-5 py-3 text-sm font-semibold text-white shadow-sm shadow-violet-500/20 transition hover:bg-violet-700">
                    Cập nhật mật khẩu
                </button>
            </div>
        </form>
    </div>
</div>
