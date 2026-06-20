<x-admin-layout title="Quản lý tài khoản">

    <div class="space-y-6">

        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Danh sách tài khoản</h2>
                <p class="text-sm text-slate-500 mt-1">
                    Tổng cộng {{ $stats['total'] }} tài khoản đăng nhập hệ thống
                </p>
            </div>

            <a href="{{ route('admin.accounts.create') }}"
               class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-violet-600 text-white font-medium shadow-lg shadow-violet-500/20 hover:bg-violet-700 transition">
                <span>+</span>
                Thêm tài khoản
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <p class="text-slate-500 text-sm">Tổng tài khoản</p>
                <h3 class="text-3xl font-bold mt-2">{{ $stats['total'] }}</h3>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <p class="text-slate-500 text-sm">Đang hoạt động</p>
                <h3 class="text-3xl font-bold mt-2 text-emerald-600">{{ $stats['active'] }}</h3>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <p class="text-slate-500 text-sm">Đã xác thực email</p>
                <h3 class="text-3xl font-bold mt-2 text-violet-600">{{ $stats['verified'] }}</h3>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100">
                <h3 class="font-semibold text-slate-800">Danh sách tài khoản</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-slate-50">
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">ID</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Tên đăng nhập</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Họ tên</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Email</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Vai trò</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Trạng thái</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Xác thực email</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Ngày tạo</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-500">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr class="border-t border-slate-100 hover:bg-slate-50 transition">
                                <td class="px-6 py-4 text-slate-600">{{ $user->id }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-lg bg-slate-100 text-slate-700 text-sm font-medium">
                                        {{ $user->username }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-semibold text-slate-800">{{ $user->name }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $user->email }}</td>
                                <td class="px-6 py-4">
                                    @if ($user->role)
                                        @php
                                            $roleClass = match ($user->role->name) {
                                                'admin' => 'bg-violet-100 text-violet-700',
                                                'manager' => 'bg-blue-100 text-blue-700',
                                                'employee' => 'bg-cyan-100 text-cyan-700',
                                                default => 'bg-slate-100 text-slate-600',
                                            };
                                        @endphp
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold {{ $roleClass }}">
                                            {{ $user->role->label() }}
                                        </span>
                                    @else
                                        <span class="text-slate-400 text-sm">Chưa phân quyền</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if ($user->status === 'active')
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">Hoạt động</span>
                                    @else
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">Đã khóa</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if ($user->email_verified_at)
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">Đã xác thực</span>
                                    @else
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700">Chưa xác thực</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-slate-500">
                                    {{ $user->created_at?->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-center gap-2">
                                        <a href="{{ route('admin.accounts.show', $user) }}"
                                           class="w-9 h-9 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center hover:bg-blue-200"
                                           title="Xem chi tiết">👁</a>

                                        <a href="{{ route('admin.accounts.edit', $user) }}"
                                           class="w-9 h-9 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center hover:bg-amber-200"
                                           title="Sửa">✏️</a>

                                        @if ($user->id !== auth()->id())
                                            <form action="{{ route('admin.accounts.toggle-status', $user) }}"
                                                  method="POST"
                                                  id="toggle-form-{{ $user->id }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="button"
                                                        class="js-toggle-status w-9 h-9 rounded-lg flex items-center justify-center {{ $user->status === 'active' ? 'bg-orange-100 text-orange-600 hover:bg-orange-200' : 'bg-emerald-100 text-emerald-600 hover:bg-emerald-200' }}"
                                                        data-user-id="{{ $user->id }}"
                                                        data-username="{{ $user->username }}"
                                                        data-is-active="{{ $user->status === 'active' ? '1' : '0' }}"
                                                        title="{{ $user->status === 'active' ? 'Khóa tài khoản' : 'Mở khóa tài khoản' }}">
                                                    {{ $user->status === 'active' ? '🔒' : '🔓' }}
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-12 text-slate-400">
                                    Chưa có tài khoản nào
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

    <div id="toggle-modal"
         class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm"
         style="display: none;">
        <div class="bg-white rounded-3xl shadow-xl w-full max-w-sm mx-4 p-6 text-center">
            <div id="toggle-modal-icon" class="w-16 h-16 mx-auto rounded-2xl flex items-center justify-center"></div>
            <h3 id="toggle-modal-title" class="mt-5 text-lg font-bold text-slate-800"></h3>
            <p class="mt-2 text-sm text-slate-500">
                Bạn có chắc muốn
                <span id="toggle-modal-action" class="font-semibold text-slate-700"></span>
                tài khoản
                <span id="toggle-account-name" class="font-semibold text-slate-700"></span>?
            </p>
            <p id="toggle-modal-note" class="mt-2 text-xs text-slate-400"></p>
            <div class="mt-6 flex gap-3">
                <button type="button" onclick="closeToggleModal()"
                        class="flex-1 px-5 py-3 rounded-xl bg-slate-100 text-slate-700 font-medium hover:bg-slate-200 transition">
                    Hủy
                </button>
                <button type="button" onclick="confirmToggle()"
                        id="toggle-modal-confirm"
                        class="flex-1 px-5 py-3 rounded-xl text-white font-medium transition">
                    Xác nhận
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
        let toggleTargetId = null;

        function openToggleModal(id, username, isActive) {
            toggleTargetId = id;

            document.getElementById('toggle-account-name').textContent = username;
            document.getElementById('toggle-modal-action').textContent = isActive ? 'khóa' : 'mở khóa';
            document.getElementById('toggle-modal-title').textContent = isActive ? 'Khóa tài khoản?' : 'Mở khóa tài khoản?';
            document.getElementById('toggle-modal-note').textContent = isActive
                ? 'Người dùng sẽ không thể đăng nhập cho đến khi được mở khóa.'
                : 'Người dùng có thể đăng nhập lại bình thường.';

            const icon = document.getElementById('toggle-modal-icon');
            const confirmBtn = document.getElementById('toggle-modal-confirm');

            if (isActive) {
                icon.className = 'w-16 h-16 mx-auto rounded-2xl bg-orange-100 flex items-center justify-center';
                icon.textContent = '🔒';
                confirmBtn.className = 'flex-1 px-5 py-3 rounded-xl bg-orange-600 text-white font-medium hover:bg-orange-700 transition';
                confirmBtn.textContent = 'Khóa';
            } else {
                icon.className = 'w-16 h-16 mx-auto rounded-2xl bg-emerald-100 flex items-center justify-center';
                icon.textContent = '🔓';
                confirmBtn.className = 'flex-1 px-5 py-3 rounded-xl bg-emerald-600 text-white font-medium hover:bg-emerald-700 transition';
                confirmBtn.textContent = 'Mở khóa';
            }

            const modal = document.getElementById('toggle-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            modal.style.display = 'flex';
        }

        function closeToggleModal() {
            const modal = document.getElementById('toggle-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            modal.style.display = 'none';
            toggleTargetId = null;
        }

        function confirmToggle() {
            if (toggleTargetId) {
                document.getElementById('toggle-form-' + toggleTargetId).submit();
            }
        }

        document.querySelectorAll('.js-toggle-status').forEach(function (button) {
            button.addEventListener('click', function () {
                openToggleModal(
                    this.dataset.userId,
                    this.dataset.username,
                    this.dataset.isActive === '1'
                );
            });
        });

        document.getElementById('toggle-modal').addEventListener('click', function (e) {
            if (e.target === this) closeToggleModal();
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
