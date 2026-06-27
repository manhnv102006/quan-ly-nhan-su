<div class="rounded-3xl border border-red-100 bg-white shadow-sm overflow-hidden">
    <div class="border-b border-red-100 bg-gradient-to-r from-red-50 to-white px-6 py-5 sm:px-8">
        <div class="flex items-start gap-4">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-red-100 text-red-600">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                          d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-bold text-slate-800">Xóa tài khoản</h3>
                <p class="mt-1 text-sm text-slate-500">
                    Sau khi xóa, toàn bộ dữ liệu tài khoản sẽ bị xóa vĩnh viễn. Hãy sao lưu thông tin cần thiết trước khi thực hiện.
                </p>
            </div>
        </div>
    </div>

    <div class="p-6 sm:p-8">
        <div class="rounded-2xl border border-red-100 bg-red-50/50 px-5 py-4">
            <p class="text-sm text-red-800 font-medium">Hành động này không thể hoàn tác.</p>
            <p class="mt-1 text-xs text-red-600">Bạn sẽ bị đăng xuất và mất quyền truy cập vào hệ thống.</p>
        </div>

        <button type="button"
                id="open-delete-account-modal"
                class="mt-5 inline-flex items-center gap-2 rounded-xl bg-red-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-red-700">
            Xóa tài khoản của tôi
        </button>
    </div>
</div>

<div id="delete-account-modal"
     class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm">
    <div class="bg-white rounded-3xl shadow-xl w-full max-w-md mx-4 overflow-hidden">
        <div class="bg-gradient-to-r from-red-50 to-white px-6 py-5 border-b border-red-100">
            <h3 class="text-lg font-bold text-slate-800">Xác nhận xóa tài khoản</h3>
            <p class="mt-1 text-sm text-slate-500">Nhập mật khẩu để xác nhận bạn muốn xóa vĩnh viễn tài khoản này.</p>
        </div>

        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <label for="password" class="block text-sm font-medium text-slate-700 mb-1.5">Mật khẩu hiện tại</label>
            <input id="password" name="password" type="password" placeholder="Nhập mật khẩu để xác nhận"
                   class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 focus:border-red-400 focus:ring-2 focus:ring-red-500/30 transition">
            @if ($errors->userDeletion->has('password'))
                <p class="mt-1.5 text-xs text-red-600">{{ $errors->userDeletion->first('password') }}</p>
            @endif

            <div class="mt-6 flex gap-3">
                <button type="button" id="close-delete-account-modal"
                        class="flex-1 rounded-xl bg-slate-100 px-5 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-200">
                    Hủy
                </button>
                <button type="submit"
                        class="flex-1 rounded-xl bg-red-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-red-700">
                    Xóa vĩnh viễn
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    (function () {
        const modal = document.getElementById('delete-account-modal');
        const openBtn = document.getElementById('open-delete-account-modal');
        const closeBtn = document.getElementById('close-delete-account-modal');
        if (!modal || !openBtn) return;

        const shouldOpen = {{ $errors->userDeletion->isNotEmpty() ? 'true' : 'false' }};

        function openModal() {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeModal() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        openBtn.addEventListener('click', openModal);
        closeBtn?.addEventListener('click', closeModal);
        modal.addEventListener('click', function (e) {
            if (e.target === modal) closeModal();
        });

        if (shouldOpen) openModal();
    })();
</script>
