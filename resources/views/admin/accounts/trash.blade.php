<x-admin-layout title="Tài khoản đã xóa">

    <div class="space-y-6">

        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Tài khoản đã xóa</h2>
                <p class="text-sm text-slate-500 mt-1">
                    Các tài khoản đã bị xóa mềm — có thể khôi phục hoặc xóa vĩnh viễn
                </p>
            </div>

            <a href="{{ route('admin.accounts') }}"
               class="inline-flex items-center gap-2 px-5 py-3 rounded-xl border border-slate-200 bg-white text-slate-600 font-medium hover:bg-slate-50 transition">
                ← Quay lại danh sách
            </a>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100">
                <form action="{{ route('admin.accounts.trash') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="search" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Tìm kiếm</label>
                        <input id="search" name="search" type="text" value="{{ request('search') }}"
                               placeholder="Tên đăng nhập, họ tên hoặc email"
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 focus:border-violet-400 focus:ring-violet-100 focus:outline-none">
                    </div>
                    <div class="md:col-span-2 flex items-end justify-end">
                        <button type="submit" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-violet-600 text-white font-medium hover:bg-violet-700 transition">
                            Tìm kiếm
                        </button>
                    </div>
                </form>
            </div>

            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-semibold text-slate-800">Tài khoản trong thùng rác</h3>
                <span class="text-sm text-slate-500">{{ $users->total() }} bản ghi</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px]">
                    <thead>
                        <tr class="bg-slate-50">
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Tên đăng nhập</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Họ tên</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Email</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Vai trò</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Ngày xóa</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-500">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr class="border-t border-slate-100 hover:bg-slate-50 transition">
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-lg bg-slate-100 text-slate-700 text-sm font-medium">
                                        {{ preg_match('/::d\d+$/', $user->username) ? preg_replace('/::d\d+$/', '', $user->username) : $user->username }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-semibold text-slate-800">{{ $user->name }}</td>
                                <td class="px-6 py-4 text-slate-600">
                                    {{ str_contains($user->email, '::deleted::') ? explode('::deleted::', $user->email, 2)[0] : $user->email }}
                                </td>
                                <td class="px-6 py-4">
                                    @if ($user->role)
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">
                                            {{ $user->role->label() }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 text-sm">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-slate-500">{{ $user->deleted_at?->format('d/m/Y H:i') }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <form action="{{ route('admin.accounts.restore', $user->id) }}"
                                              method="POST"
                                              id="restore-form-{{ $user->id }}">
                                            @csrf
                                            <button type="button"
                                                    class="js-restore-account inline-flex items-center justify-center px-4 py-2 rounded-xl text-white text-sm font-medium transition"
                                                    style="background-color: #059669;"
                                                    data-user-id="{{ $user->id }}"
                                                    data-username="{{ preg_match('/::d\d+$/', $user->username) ? preg_replace('/::d\d+$/', '', $user->username) : $user->username }}">
                                                Khôi phục
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.accounts.forceDelete', $user->id) }}"
                                              method="POST"
                                              id="force-delete-form-{{ $user->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                    class="js-force-delete-account inline-flex items-center justify-center px-4 py-2 rounded-xl text-white text-sm font-medium transition"
                                                    style="background-color: #dc2626;"
                                                    data-user-id="{{ $user->id }}"
                                                    data-username="{{ preg_match('/::d\d+$/', $user->username) ? preg_replace('/::d\d+$/', '', $user->username) : $user->username }}">
                                                Xóa vĩnh viễn
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-12 text-slate-400">
                                    Không có tài khoản nào trong thùng rác.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($users->hasPages())
                <div class="px-6 py-4 border-t border-slate-100">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>

    <div id="restore-modal"
         class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm"
         style="display: none;">
        <div class="bg-white rounded-3xl shadow-xl w-full max-w-sm mx-4 p-6 text-center">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-emerald-100 flex items-center justify-center">
                <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h3 class="mt-5 text-lg font-bold text-slate-800">Khôi phục tài khoản?</h3>
            <p class="mt-2 text-sm text-slate-500">
                Bạn có chắc muốn khôi phục tài khoản
                <span id="restore-account-name" class="font-semibold text-slate-700"></span>?
            </p>
            <div class="mt-6 flex gap-3">
                <button type="button" onclick="closeRestoreModal()"
                        class="flex-1 px-5 py-3 rounded-xl bg-slate-100 text-slate-700 font-medium hover:bg-slate-200 transition">
                    Hủy
                </button>
                <button type="button" onclick="confirmRestore()"
                        class="flex-1 px-5 py-3 rounded-xl text-white font-medium transition"
                        style="background-color: #059669;">
                    Khôi phục
                </button>
            </div>
        </div>
    </div>

    <div id="force-delete-modal"
         class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm"
         style="display: none;">
        <div class="bg-white rounded-3xl shadow-xl w-full max-w-sm mx-4 p-6 text-center">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-red-100 flex items-center justify-center">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </div>
            <h3 class="mt-5 text-lg font-bold text-slate-800">Xóa vĩnh viễn?</h3>
            <p class="mt-2 text-sm text-slate-500">
                Bạn có chắc muốn xóa vĩnh viễn tài khoản
                <span id="force-delete-account-name" class="font-semibold text-slate-700"></span>?
                Hành động này không thể hoàn tác.
            </p>
            <div class="mt-6 flex gap-3">
                <button type="button" onclick="closeForceDeleteModal()"
                        class="flex-1 px-5 py-3 rounded-xl bg-slate-100 text-slate-700 font-medium hover:bg-slate-200 transition">
                    Hủy
                </button>
                <button type="button" onclick="confirmForceDelete()"
                        class="flex-1 px-5 py-3 rounded-xl text-white font-medium transition"
                        style="background-color: #dc2626;">
                    Xóa vĩnh viễn
                </button>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div id="success-toast"
             class="fixed top-6 right-6 z-50 flex items-center gap-3 bg-white border border-emerald-200 shadow-lg rounded-2xl px-5 py-4 max-w-sm">
            <div class="w-9 h-9 rounded-full bg-emerald-100 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <p class="text-sm font-medium text-slate-700">{{ session('success') }}</p>
        </div>
    @endif

    @if (session('error'))
        <div id="error-toast"
             class="fixed top-6 right-6 z-50 flex items-center gap-3 bg-white border border-red-200 shadow-lg rounded-2xl px-5 py-4 max-w-sm">
            <div class="w-9 h-9 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </div>
            <p class="text-sm font-medium text-slate-700">{{ session('error') }}</p>
        </div>
    @endif

    <script>
        let restoreTargetId = null;
        let forceDeleteTargetId = null;

        function openRestoreModal(id, username) {
            restoreTargetId = String(id);
            document.getElementById('restore-account-name').textContent = username;

            const modal = document.getElementById('restore-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            modal.style.display = 'flex';
        }

        function closeRestoreModal() {
            const modal = document.getElementById('restore-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            modal.style.display = 'none';
            restoreTargetId = null;
        }

        function confirmRestore() {
            if (!restoreTargetId) {
                return;
            }

            const form = document.getElementById('restore-form-' + restoreTargetId);
            if (form) {
                form.submit();
            } else {
                closeRestoreModal();
            }
        }

        function openForceDeleteModal(id, username) {
            forceDeleteTargetId = String(id);
            document.getElementById('force-delete-account-name').textContent = username;

            const modal = document.getElementById('force-delete-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            modal.style.display = 'flex';
        }

        function closeForceDeleteModal() {
            const modal = document.getElementById('force-delete-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            modal.style.display = 'none';
            forceDeleteTargetId = null;
        }

        function confirmForceDelete() {
            if (!forceDeleteTargetId) {
                return;
            }

            const form = document.getElementById('force-delete-form-' + forceDeleteTargetId);
            if (form) {
                form.submit();
            } else {
                closeForceDeleteModal();
            }
        }

        document.querySelectorAll('.js-restore-account').forEach(function (button) {
            button.addEventListener('click', function () {
                openRestoreModal(this.dataset.userId, this.dataset.username);
            });
        });

        document.querySelectorAll('.js-force-delete-account').forEach(function (button) {
            button.addEventListener('click', function () {
                openForceDeleteModal(this.dataset.userId, this.dataset.username);
            });
        });

        document.getElementById('restore-modal').addEventListener('click', function (e) {
            if (e.target === this) {
                closeRestoreModal();
            }
        });

        document.getElementById('force-delete-modal').addEventListener('click', function (e) {
            if (e.target === this) {
                closeForceDeleteModal();
            }
        });

        const successToast = document.getElementById('success-toast');
        if (successToast) {
            setTimeout(function () {
                successToast.style.transition = 'opacity 0.3s ease';
                successToast.style.opacity = '0';
                setTimeout(function () { successToast.remove(); }, 300);
            }, 4000);
        }

        const errorToast = document.getElementById('error-toast');
        if (errorToast) {
            setTimeout(function () {
                errorToast.style.transition = 'opacity 0.3s ease';
                errorToast.style.opacity = '0';
                setTimeout(function () { errorToast.remove(); }, 300);
            }, 4000);
        }
    </script>

</x-admin-layout>
