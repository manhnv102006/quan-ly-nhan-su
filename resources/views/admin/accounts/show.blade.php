<x-admin-layout title="Chi tiết tài khoản">

    <div class="space-y-6">

        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Chi tiết tài khoản</h2>
                <p class="text-slate-500 mt-1">Xem thông tin chi tiết của tài khoản</p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button type="button"
                        class="js-reset-password px-5 py-3 rounded-xl bg-violet-100 text-violet-700 font-medium hover:bg-violet-200 transition"
                        data-reset-url="{{ route('admin.accounts.reset-password', $user) }}"
                        data-username="{{ $user->username }}">
                    🔑 Đặt lại mật khẩu
                </button>

                @if ($user->id !== auth()->id())
                    <form action="{{ route('admin.accounts.toggle-status', $user) }}" method="POST"
                          onsubmit="return confirm(@json($user->status === 'active' ? 'Bạn có chắc muốn khóa tài khoản này? Người dùng sẽ không thể đăng nhập.' : 'Bạn có chắc muốn mở khóa tài khoản này?'))">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                                class="px-5 py-3 rounded-xl font-medium transition {{ $user->status === 'active' ? 'bg-orange-100 text-orange-700 hover:bg-orange-200' : 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' }}">
                            {{ $user->status === 'active' ? '🔒 Khóa tài khoản' : '🔓 Mở khóa tài khoản' }}
                        </button>
                    </form>
                @endif

                <a href="{{ route('admin.accounts.edit', $user) }}"
                   class="px-5 py-3 rounded-xl bg-violet-600 text-white font-medium hover:bg-violet-700 transition">
                    Sửa tài khoản
                </a>

                <a href="{{ route('admin.accounts') }}"
                   class="px-5 py-3 rounded-xl bg-slate-200 text-slate-700 font-medium hover:bg-slate-300 transition">
                    ← Quay lại
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <div class="lg:col-span-2">
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100">
                    <div class="px-6 py-5 border-b border-slate-100">
                        <h3 class="text-lg font-semibold text-slate-800">Thông tin tài khoản</h3>
                    </div>

                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-medium text-slate-500 mb-2">Tên đăng nhập</label>
                                <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 font-semibold text-slate-800">
                                    {{ $user->username }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-500 mb-2">Họ và tên</label>
                                <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 font-semibold text-slate-800">
                                    {{ $user->name }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-500 mb-2">Email</label>
                                <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-700">
                                    {{ $user->email }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-500 mb-2">Vai trò</label>
                                <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200">
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
                                        <span class="text-slate-400">Chưa phân quyền</span>
                                    @endif
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-500 mb-2">Trạng thái</label>
                                <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200">
                                    @if ($user->status === 'active')
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">Hoạt động</span>
                                    @else
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">Đã khóa</span>
                                    @endif
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-500 mb-2">Xác thực email</label>
                                <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200">
                                    @if ($user->email_verified_at)
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">Đã xác thực</span>
                                        <p class="text-xs text-slate-500 mt-2">{{ $user->email_verified_at->format('d/m/Y H:i') }}</p>
                                    @else
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700">Chưa xác thực</span>
                                    @endif
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-500 mb-2">Ngày tạo</label>
                                <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200">
                                    {{ $user->created_at?->format('d/m/Y H:i') }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-500 mb-2">Cập nhật lần cuối</label>
                                <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200">
                                    {{ $user->updated_at?->format('d/m/Y H:i') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8 text-center">
                    <div class="w-24 h-24 mx-auto rounded-3xl bg-violet-100 flex items-center justify-center">
                        <svg class="w-12 h-12 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>

                    <h3 class="mt-5 text-xl font-bold text-slate-800">{{ $user->name }}</h3>
                    <p class="text-slate-500 mt-2">{{ '@'.$user->username }}</p>

                    <div class="mt-4">
                        @if ($user->status === 'active')
                            <span class="inline-flex items-center px-4 py-2 rounded-full bg-emerald-100 text-emerald-700 text-sm font-semibold">Hoạt động</span>
                        @else
                            <span class="inline-flex items-center px-4 py-2 rounded-full bg-red-100 text-red-700 text-sm font-semibold">Đã khóa</span>
                        @endif
                    </div>

                    @if ($user->role)
                        <p class="mt-4 text-sm text-slate-500">
                            Vai trò: <span class="font-semibold text-slate-800">{{ $user->role->label() }}</span>
                        </p>
                    @endif
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100">
            <div class="px-6 py-5 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
                <h3 class="text-lg font-semibold text-slate-800">Hồ sơ nhân viên liên kết</h3>
                @if ($user->employee)
                    <form action="{{ route('admin.accounts.unlink-employee', $user) }}"
                          method="POST"
                          id="unlink-employee-form">
                        @csrf
                        @method('PATCH')
                        <button type="button"
                                id="open-unlink-employee-modal"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-orange-100 text-orange-700 text-sm font-medium hover:bg-orange-200 transition">
                            Gỡ liên kết
                        </button>
                    </form>
                @elseif ($availableEmployees->isNotEmpty())
                    <button type="button"
                            id="open-link-employee-modal"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-violet-600 text-white text-sm font-medium hover:bg-violet-700 transition">
                        + Liên kết nhân viên
                    </button>
                @endif
            </div>

            <div class="p-6">
                @if ($user->employee)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-slate-500 mb-2">Mã nhân viên</label>
                            <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 font-medium text-slate-800">
                                {{ $user->employee->employee_code }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-500 mb-2">Phòng ban</label>
                            <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200">
                                {{ $user->employee->department?->department_name ?? '—' }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-500 mb-2">Chức vụ</label>
                            <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200">
                                {{ $user->employee->position?->position_name ?? '—' }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-500 mb-2">Số điện thoại</label>
                            <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200">
                                {{ $user->employee->phone }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-500 mb-2">Ngày vào làm</label>
                            <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200">
                                {{ $user->employee->hire_date ? \Illuminate\Support\Carbon::parse($user->employee->hire_date)->format('d/m/Y') : '—' }}
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-500 mb-2">Trạng thái nhân viên</label>
                            <div class="px-4 py-3 rounded-xl bg-slate-50 border border-slate-200">
                                @if ($user->employee->status === 'active')
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">Đang làm</span>
                                @elseif ($user->employee->status === 'resigned')
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">Đã nghỉ</span>
                                @else
                                    <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">Không hoạt động</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mt-5">
                        <a href="{{ route('admin.employees.show', $user->employee) }}"
                           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-violet-100 text-violet-700 text-sm font-medium hover:bg-violet-200 transition">
                            Xem chi tiết nhân viên →
                        </a>
                    </div>
                @else
                    <div class="py-12 text-center">
                        <div class="w-16 h-16 mx-auto rounded-2xl bg-slate-100 flex items-center justify-center">
                            <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <p class="mt-4 text-slate-500 font-medium">Chưa liên kết hồ sơ nhân viên</p>
                        <p class="mt-1 text-sm text-slate-400">Tài khoản này chưa được gán với nhân viên trong hệ thống</p>
                        @if ($availableEmployees->isNotEmpty())
                            <button type="button"
                                    onclick="document.getElementById('open-link-employee-modal')?.click()"
                                    class="mt-5 inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-violet-600 text-white text-sm font-medium hover:bg-violet-700 transition">
                                Liên kết nhân viên ngay
                            </button>
                        @else
                            <p class="mt-4 text-sm text-amber-600">Tất cả nhân viên đã có tài khoản liên kết.</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>

    </div>

    @include('admin.accounts.partials.reset-password-modal')
    @include('admin.accounts.partials.link-employee-modal')

    <div id="unlink-employee-modal"
         class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm"
         style="display: none;">
        <div class="bg-white rounded-3xl shadow-xl w-full max-w-sm mx-4 p-6 text-center">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-orange-100 flex items-center justify-center">
                <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                </svg>
            </div>
            <h3 class="mt-5 text-lg font-bold text-slate-800">Gỡ liên kết nhân viên?</h3>
            <p class="mt-2 text-sm text-slate-500">
                Hồ sơ <span class="font-semibold text-slate-700">{{ $user->employee?->full_name }}</span>
                sẽ không còn liên kết với tài khoản này.
            </p>
            <div class="mt-6 flex gap-3">
                <button type="button" id="close-unlink-employee-modal"
                        class="flex-1 px-5 py-3 rounded-xl bg-slate-100 text-slate-700 font-medium hover:bg-slate-200 transition">
                    Hủy
                </button>
                <button type="button" id="confirm-unlink-employee"
                        class="flex-1 px-5 py-3 rounded-xl text-white font-medium transition"
                        style="background-color: #ea580c;">
                    Gỡ liên kết
                </button>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div id="success-toast"
             class="fixed top-6 right-6 z-50 flex items-center gap-3 bg-white border border-emerald-200 shadow-lg rounded-2xl px-5 py-4 max-w-sm">
            <p class="text-sm font-medium text-slate-700">{{ session('success') }}</p>
        </div>
    @endif

    @if (session('error'))
        <div id="error-toast"
             class="fixed top-6 right-6 z-50 flex items-center gap-3 bg-white border border-red-200 shadow-lg rounded-2xl px-5 py-4 max-w-sm">
            <p class="text-sm font-medium text-slate-700">{{ session('error') }}</p>
        </div>
    @endif

    <script>
        (function () {
            const unlinkModal = document.getElementById('unlink-employee-modal');
            const openUnlinkBtn = document.getElementById('open-unlink-employee-modal');
            const closeUnlinkBtn = document.getElementById('close-unlink-employee-modal');
            const confirmUnlinkBtn = document.getElementById('confirm-unlink-employee');
            const unlinkForm = document.getElementById('unlink-employee-form');

            if (!openUnlinkBtn || !unlinkModal) return;

            function openUnlinkModal() {
                unlinkModal.classList.remove('hidden');
                unlinkModal.classList.add('flex');
                unlinkModal.style.display = 'flex';
            }

            function closeUnlinkModal() {
                unlinkModal.classList.add('hidden');
                unlinkModal.classList.remove('flex');
                unlinkModal.style.display = 'none';
            }

            openUnlinkBtn.addEventListener('click', openUnlinkModal);
            closeUnlinkBtn?.addEventListener('click', closeUnlinkModal);
            confirmUnlinkBtn?.addEventListener('click', function () {
                if (unlinkForm) unlinkForm.submit();
            });
            unlinkModal.addEventListener('click', function (event) {
                if (event.target === unlinkModal) closeUnlinkModal();
            });
        })();
    </script>

</x-admin-layout>
