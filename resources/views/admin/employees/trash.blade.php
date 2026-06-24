<x-admin-layout title="Nhân viên đã xóa">

    <div class="space-y-6">

        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Nhân viên đã xóa</h2>
                <p class="text-sm text-slate-500 mt-1">
                    Các nhân viên đã bị xóa mềm — có thể khôi phục hoặc xóa vĩnh viễn
                </p>
            </div>

            <a href="{{ route('admin.employees') }}"
               class="inline-flex items-center gap-2 px-5 py-3 rounded-xl border border-slate-200 bg-white text-slate-600 font-medium hover:bg-slate-50 transition">
                ← Quay lại danh sách
            </a>
        </div>

        @if (session('success'))
            <div id="success-toast"
                 class="flex items-center gap-3 bg-white border border-emerald-200 shadow-sm rounded-2xl px-5 py-4">
                <div class="w-9 h-9 rounded-full bg-emerald-100 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <p class="text-sm font-medium text-emerald-700">{{ session('success') }}</p>
            </div>
        @endif

        @if (session('error'))
            <div id="error-toast"
                 class="flex items-center gap-3 bg-white border border-red-200 shadow-sm rounded-2xl px-5 py-4">
                <p class="text-sm font-medium text-red-700">{{ session('error') }}</p>
            </div>
        @endif

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100">
                <form action="{{ route('admin.employees.trash') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2">
                        <label for="search" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Tìm kiếm</label>
                        <input id="search" name="search" type="text" value="{{ $search }}"
                               placeholder="Mã NV, họ tên, email hoặc số điện thoại"
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 focus:border-violet-400 focus:ring-violet-100 focus:outline-none">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="inline-flex w-full items-center justify-center gap-2 px-5 py-3 rounded-xl bg-violet-600 text-white font-medium hover:bg-violet-700 transition">
                            Tìm kiếm
                        </button>
                    </div>
                </form>
            </div>

            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-semibold text-slate-800">Nhân viên trong thùng rác</h3>
                <span class="text-sm text-slate-500">{{ $employees->total() }} bản ghi</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[1000px]">
                    <thead>
                        <tr class="bg-slate-50">
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Mã NV</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Họ tên</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Phòng ban</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Chức vụ</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Email</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Trạng thái</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Ngày xóa</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-500">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($employees as $employee)
                            <tr class="border-t border-slate-100 hover:bg-slate-50 transition">
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-lg bg-slate-100 text-slate-700 text-sm font-mono">
                                        {{ $employee->employee_code }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-semibold text-slate-800">{{ $employee->full_name }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $employee->department?->department_name ?? '—' }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $employee->position?->position_name ?? '—' }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $employee->email }}</td>
                                <td class="px-6 py-4">
                                    @if ($employee->status === 'active')
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">Hoạt động</span>
                                    @elseif ($employee->status === 'inactive')
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">Tạm khóa</span>
                                    @else
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-700">Đã nghỉ</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-slate-500">{{ $employee->deleted_at?->format('d/m/Y H:i') ?? '—' }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <form action="{{ route('admin.employees.restore', $employee->id) }}"
                                              method="POST"
                                              id="restore-form-{{ $employee->id }}">
                                            @csrf
                                            <button type="button"
                                                    class="js-restore-employee inline-flex items-center justify-center px-4 py-2 rounded-xl text-white text-sm font-medium transition hover:opacity-90"
                                                    style="background-color: #059669;"
                                                    data-employee-id="{{ $employee->id }}"
                                                    data-employee-name="{{ $employee->full_name }}">
                                                Khôi phục
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.employees.forceDelete', $employee->id) }}"
                                              method="POST"
                                              id="force-delete-form-{{ $employee->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                    class="js-force-delete-employee inline-flex items-center justify-center px-4 py-2 rounded-xl text-white text-sm font-medium transition hover:opacity-90"
                                                    style="background-color: #dc2626;"
                                                    data-employee-id="{{ $employee->id }}"
                                                    data-employee-name="{{ $employee->full_name }}">
                                                Xóa vĩnh viễn
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-16 text-center">
                                    <div class="mx-auto w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center">
                                        <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </div>
                                    <p class="mt-4 text-slate-500 font-medium">Thùng rác đang trống</p>
                                    <p class="mt-1 text-sm text-slate-400">Chưa có nhân viên nào bị xóa mềm</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($employees->hasPages())
                <div class="px-6 py-4 border-t border-slate-100">
                    {{ $employees->links() }}
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
            <h3 class="mt-5 text-lg font-bold text-slate-800">Khôi phục nhân viên?</h3>
            <p class="mt-2 text-sm text-slate-500">
                Bạn có chắc muốn khôi phục nhân viên
                <span id="restore-employee-name" class="font-semibold text-slate-700"></span>?
            </p>
            <div class="mt-6 flex gap-3">
                <button type="button" id="close-restore-modal"
                        class="flex-1 px-5 py-3 rounded-xl bg-slate-100 text-slate-700 font-medium hover:bg-slate-200 transition">
                    Hủy
                </button>
                <button type="button" id="confirm-restore-btn"
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
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </div>
            <h3 class="mt-5 text-lg font-bold text-slate-800">Xóa vĩnh viễn?</h3>
            <p class="mt-2 text-sm text-slate-500">
                Bạn có chắc muốn xóa vĩnh viễn nhân viên
                <span id="force-delete-employee-name" class="font-semibold text-slate-700"></span>?
                Toàn bộ dữ liệu liên quan sẽ bị xóa và không thể hoàn tác.
            </p>
            <div class="mt-6 flex gap-3">
                <button type="button" id="close-force-delete-modal"
                        class="flex-1 px-5 py-3 rounded-xl bg-slate-100 text-slate-700 font-medium hover:bg-slate-200 transition">
                    Hủy
                </button>
                <button type="button" id="confirm-force-delete-btn"
                        class="flex-1 px-5 py-3 rounded-xl text-white font-medium transition"
                        style="background-color: #dc2626;">
                    Xóa vĩnh viễn
                </button>
            </div>
        </div>
    </div>

    <script>
        (function () {
            let restoreTargetId = null;
            let forceDeleteTargetId = null;

            const restoreModal = document.getElementById('restore-modal');
            const forceDeleteModal = document.getElementById('force-delete-modal');

            function openRestoreModal(id, name) {
                restoreTargetId = id;
                document.getElementById('restore-employee-name').textContent = name;
                restoreModal.classList.remove('hidden');
                restoreModal.classList.add('flex');
                restoreModal.style.display = 'flex';
            }

            function closeRestoreModal() {
                restoreTargetId = null;
                restoreModal.classList.add('hidden');
                restoreModal.classList.remove('flex');
                restoreModal.style.display = 'none';
            }

            function openForceDeleteModal(id, name) {
                forceDeleteTargetId = id;
                document.getElementById('force-delete-employee-name').textContent = name;
                forceDeleteModal.classList.remove('hidden');
                forceDeleteModal.classList.add('flex');
                forceDeleteModal.style.display = 'flex';
            }

            function closeForceDeleteModal() {
                forceDeleteTargetId = null;
                forceDeleteModal.classList.add('hidden');
                forceDeleteModal.classList.remove('flex');
                forceDeleteModal.style.display = 'none';
            }

            document.querySelectorAll('.js-restore-employee').forEach(function (button) {
                button.addEventListener('click', function () {
                    openRestoreModal(button.dataset.employeeId, button.dataset.employeeName);
                });
            });

            document.querySelectorAll('.js-force-delete-employee').forEach(function (button) {
                button.addEventListener('click', function () {
                    openForceDeleteModal(button.dataset.employeeId, button.dataset.employeeName);
                });
            });

            document.getElementById('close-restore-modal')?.addEventListener('click', closeRestoreModal);
            document.getElementById('close-force-delete-modal')?.addEventListener('click', closeForceDeleteModal);

            document.getElementById('confirm-restore-btn')?.addEventListener('click', function () {
                if (!restoreTargetId) return;
                const form = document.getElementById('restore-form-' + restoreTargetId);
                if (form) form.submit();
            });

            document.getElementById('confirm-force-delete-btn')?.addEventListener('click', function () {
                if (!forceDeleteTargetId) return;
                const form = document.getElementById('force-delete-form-' + forceDeleteTargetId);
                if (form) form.submit();
            });

            restoreModal?.addEventListener('click', function (e) {
                if (e.target === restoreModal) closeRestoreModal();
            });

            forceDeleteModal?.addEventListener('click', function (e) {
                if (e.target === forceDeleteModal) closeForceDeleteModal();
            });

            ['success-toast', 'error-toast'].forEach(function (id) {
                const toast = document.getElementById(id);
                if (!toast) return;
                setTimeout(function () {
                    toast.style.transition = 'opacity 0.3s ease';
                    toast.style.opacity = '0';
                    setTimeout(function () { toast.remove(); }, 300);
                }, 4000);
            });
        })();
    </script>

</x-admin-layout>
