<div id="reset-modal"
     class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm"
     style="display: none;">
    <div class="bg-white rounded-3xl shadow-xl w-full max-w-md mx-4 p-6">
        <div class="text-center">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-violet-100 flex items-center justify-center text-3xl">🔑</div>
            <h3 class="mt-5 text-lg font-bold text-slate-800">Đặt lại mật khẩu</h3>
            <p class="mt-2 text-sm text-slate-500">
                Nhập mật khẩu mới cho tài khoản
                <span id="reset-account-name" class="font-semibold text-slate-700"></span>
            </p>
        </div>

        <form id="reset-password-form" method="POST" action="" class="mt-6 space-y-4">
            @csrf
            @method('PATCH')

            <div>
                <label for="reset-password" class="block text-sm font-semibold text-slate-700 mb-2">
                    Mật khẩu mới <span class="text-red-500">*</span>
                </label>
                <input type="password" id="reset-password" name="password" required autocomplete="new-password"
                       value=""
                       class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition @error('password') border-red-400 @enderror">
                @error('password')
                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="reset-password-confirmation" class="block text-sm font-semibold text-slate-700 mb-2">
                    Xác nhận mật khẩu <span class="text-red-500">*</span>
                </label>
                <input type="password" id="reset-password-confirmation" name="password_confirmation" required autocomplete="new-password"
                       class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition">
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeResetModal()"
                        class="flex-1 px-5 py-3 rounded-xl bg-slate-100 text-slate-700 font-medium hover:bg-slate-200 transition">
                    Hủy
                </button>
                <button type="submit"
                        class="flex-1 px-5 py-3 rounded-xl text-white font-medium transition"
                        style="background-color: #7c3aed;">
                    Xác nhận
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openResetModal(resetUrl, username) {
        document.getElementById('reset-account-name').textContent = username;
        document.getElementById('reset-password-form').action = resetUrl;
        document.getElementById('reset-password').value = '';
        document.getElementById('reset-password-confirmation').value = '';

        const modal = document.getElementById('reset-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        modal.style.display = 'flex';
        document.getElementById('reset-password').focus();
    }

    function closeResetModal() {
        const modal = document.getElementById('reset-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        modal.style.display = 'none';
    }

    document.querySelectorAll('.js-reset-password').forEach(function (button) {
        button.addEventListener('click', function () {
            openResetModal(this.dataset.resetUrl, this.dataset.username);
        });
    });

    document.getElementById('reset-modal').addEventListener('click', function (e) {
        if (e.target === this) closeResetModal();
    });

    @if (session('open_reset_modal'))
        openResetModal(
            @json(route('admin.accounts.reset-password', session('open_reset_modal'))),
            @json(session('reset_username'))
        );
    @endif
</script>
