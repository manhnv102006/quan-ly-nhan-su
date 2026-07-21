<x-admin-layout title="Quản lý nhân viên">

    <div class="space-y-6">

        <div>
            <a href="{{ route('admin.employees') }}" class="inline-flex items-center gap-1 text-sm font-medium text-slate-500 hover:text-violet-600 transition">
                ← Danh sách phòng ban
            </a>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                @if (!empty($currentDepartment))
                    <h2 class="text-2xl font-bold text-slate-800">{{ $currentDepartment->department_name }}</h2>
                    <p class="text-sm text-slate-500 mt-1">Nhân viên thuộc phòng ban · {{ $currentDepartment->employees_count }}/{{ $currentDepartment->maxEmployeesLimit() }}</p>
                @elseif (!empty($unassignedOnly))
                    <h2 class="text-2xl font-bold text-slate-800">Nhân viên chưa có phòng ban</h2>
                    <p class="text-sm text-slate-500 mt-1">Danh sách nhân viên chưa được gán phòng ban</p>
                @else
                    <h2 class="text-2xl font-bold text-slate-800">Tất cả nhân viên</h2>
                    <p class="text-sm text-slate-500 mt-1">Danh sách và quản lý hồ sơ nhân viên</p>
                @endif
            </div>
            <div class="flex flex-wrap items-center gap-3">
                @if (!empty($currentDepartment))
                    <a href="{{ route('admin.employees.create', ['department_id' => $currentDepartment->id]) }}" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-violet-600 text-white font-medium hover:bg-violet-700 transition">
                        + Thêm nhân viên vào phòng này
                    </a>
                @else
                    <a href="{{ route('admin.employees.create') }}" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-violet-600 text-white font-medium hover:bg-violet-700 transition">
                        + Thêm nhân viên
                    </a>
                @endif
                <a href="{{ route('admin.employees.trash') }}" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl border border-slate-200 bg-white text-slate-600 font-medium hover:bg-slate-50 transition">
                    Nhân viên đã xóa
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="flex items-center gap-3 bg-white border border-emerald-200 shadow-sm rounded-2xl px-5 py-4">
                <p class="text-sm font-medium text-emerald-700">{{ session('success') }}</p>
            </div>
        @endif

        @if (session('error'))
            <div class="flex items-center gap-3 bg-white border border-red-200 shadow-sm rounded-2xl px-5 py-4">
                <p class="text-sm font-medium text-red-700">{{ session('error') }}</p>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm text-slate-500">Tổng nhân viên</p>
                <h3 class="text-3xl font-bold mt-2 text-slate-900">{{ $stats['total'] }}</h3>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm text-slate-500">Đang làm việc</p>
                <h3 class="text-3xl font-bold mt-2 text-emerald-600">{{ $stats['active'] }}</h3>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm text-slate-500">Tạm khóa</p>
                <h3 class="text-3xl font-bold mt-2 text-amber-600">{{ $stats['inactive'] }}</h3>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

            <div class="px-6 py-4 border-b border-slate-200">
                @php
                    $hasFilters = ($filters['search'] ?? '') !== ''
                        || !empty($filters['department_id'])
                        || !empty($filters['position_id'])
                        || ($filters['status'] ?? '') !== '';
                @endphp
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h3 class="font-semibold text-slate-800">Danh sách nhân viên</h3>
                    <span class="text-sm text-slate-500">Tìm thấy {{ $employees->total() }} nhân viên</span>
                </div>

                <form method="GET" action="{{ route('admin.employees') }}" class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-12">
                    <div class="md:col-span-4">
                        <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">Tìm kiếm</label>
                        <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                               placeholder="Mã NV, tên, email, SĐT..."
                               class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-violet-400 focus:ring-2 focus:ring-violet-100">
                    </div>

                    <div class="md:col-span-3">
                        <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">Phòng ban</label>
                        <select name="department_id"
                                class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-violet-400 focus:ring-2 focus:ring-violet-100">
                            <option value="">Tất cả phòng ban</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}" @selected((int) ($filters['department_id'] ?? 0) === $department->id)>
                                    {{ $department->department_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">Chức vụ</label>
                        <select name="position_id"
                                class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-violet-400 focus:ring-2 focus:ring-violet-100">
                            <option value="">Tất cả chức vụ</option>
                            @foreach ($positions as $position)
                                <option value="{{ $position->id }}" @selected((int) ($filters['position_id'] ?? 0) === $position->id)>
                                    {{ $position->position_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-xs font-semibold uppercase text-slate-500">Trạng thái</label>
                        <select name="status"
                                class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-violet-400 focus:ring-2 focus:ring-violet-100">
                            <option value="">Tất cả trạng thái</option>
                            <option value="active" @selected(($filters['status'] ?? '') === 'active')>Hoạt động</option>
                            <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Tạm khóa</option>
                            <option value="resigned" @selected(($filters['status'] ?? '') === 'resigned')>Đã nghỉ</option>
                        </select>
                    </div>

                    <div class="flex items-end gap-2 md:col-span-1">
                        <button type="submit"
                                class="w-full rounded-xl bg-violet-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-violet-700">
                            Lọc
                        </button>
                    </div>

                    @if ($hasFilters)
                        <div class="md:col-span-12">
                            <a href="{{ route('admin.employees') }}"
                               class="inline-flex items-center gap-1 text-sm font-medium text-slate-500 hover:text-violet-600 transition">
                                ✕ Xóa bộ lọc
                            </a>
                        </div>
                    @endif
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-500">Mã NV</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-500">Tên nhân viên</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-500">Phòng ban</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-500">Chức vụ</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-500">Email</th>
                            <th class="px-6 py-3 text-center text-xs font-bold uppercase text-slate-500">Tài khoản</th>
                            <th class="px-6 py-3 text-center text-xs font-bold uppercase text-slate-500">Trạng thái</th>
                            <th class="px-6 py-3 text-center text-xs font-bold uppercase text-slate-500">Ngày tạo</th>
                            <th class="px-6 py-3 text-center text-xs font-bold uppercase text-slate-500">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($employees as $employee)
                            <tr class="border-b border-slate-200 hover:bg-slate-50 transition">
                                <td class="px-6 py-4 text-slate-700 font-mono text-sm">{{ $employee->employee_code }}</td>
                                <td class="px-6 py-4">
                                    <a href="{{ route('admin.employees.show', $employee) }}"
                                       class="text-slate-800 font-medium hover:text-violet-600 transition">
                                        {{ $employee->full_name }}
                                    </a>
                                </td>
                                <td class="px-6 py-4 text-slate-600">{{ $employee->department?->department_name ?? '-' }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $employee->position?->position_name ?? '-' }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $employee->email }}</td>
                                <td class="px-6 py-4 text-center">
                                    @if ($employee->user)
                                        <a href="{{ route('admin.accounts.show', $employee->user) }}"
                                           class="inline-flex rounded-full bg-violet-100 px-3 py-1 text-xs font-semibold text-violet-700 hover:bg-violet-200 transition"
                                           title="{{ $employee->user->name }}">
                                            {{ $employee->user->username }}
                                        </a>
                                    @else
                                        <a href="{{ route('admin.employees.show', $employee) }}?open=link-account"
                                           class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-500 hover:bg-violet-100 hover:text-violet-700 transition">
                                            Chưa liên kết
                                        </a>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if ($employee->status === 'active')
                                        <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Hoạt động</span>
                                    @elseif ($employee->status === 'inactive')
                                        <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Tạm khóa</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-700">Đã nghỉ</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center text-slate-600">{{ $employee->created_at?->format('d/m/Y') ?? '-' }}</td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('admin.employees.show', $employee) }}" class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center hover:bg-blue-200 transition" title="Xem">👁</a>
                                        <a href="{{ route('admin.employees.edit', $employee) }}" class="w-8 h-8 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center hover:bg-amber-200 transition" title="Sửa">✏️</a>
                                        <a href="{{ route('admin.employees.show', $employee) }}?open=transfer"
                                           class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center hover:bg-blue-200 transition"
                                           title="Điều chuyển phòng ban">↔</a>
                                        <form action="{{ route('admin.employees.destroy', $employee) }}"
                                              method="POST"
                                              id="delete-form-{{ $employee->id }}"
                                              class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                    class="js-open-delete-modal w-8 h-8 rounded-lg bg-red-100 text-red-600 flex items-center justify-center hover:bg-red-200 transition"
                                                    title="Xóa"
                                                    data-form-id="delete-form-{{ $employee->id }}"
                                                    data-employee-name="{{ $employee->full_name }}">🗑</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-12 text-slate-400">Không có nhân viên nào</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($employees->hasPages())
                <div class="px-6 py-4 border-t border-slate-200">
                    {{ $employees->links() }}
                </div>
            @endif
        </div>

    </div>

    <div id="delete-confirm-modal"
         class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm">
        <div class="bg-white rounded-3xl shadow-xl w-full max-w-md mx-4 p-6">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-red-100 flex items-center justify-center">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </div>

            <h3 class="mt-5 text-lg font-bold text-slate-800 text-center">Chuyển nhân viên vào thùng rác?</h3>
            <p class="mt-2 text-sm text-slate-500 text-center">
                Bạn có chắc muốn xóa mềm nhân viên
                <span id="delete-employee-name" class="font-semibold text-slate-800"></span>?
            </p>
            <p class="mt-2 text-xs text-amber-600 text-center font-medium">
                Nhân viên sẽ được ẩn khỏi danh sách và có thể khôi phục từ mục「Nhân viên đã xóa」.
            </p>

            <div class="mt-6 flex gap-3">
                <button type="button" id="cancel-delete-btn"
                        class="flex-1 px-5 py-3 rounded-xl bg-slate-100 text-slate-700 font-medium hover:bg-slate-200 transition">
                    Hủy
                </button>
                <button type="button" id="confirm-delete-btn"
                        class="flex-1 px-5 py-3 rounded-xl bg-orange-600 text-white font-medium hover:bg-orange-700 transition">
                    Chuyển vào thùng rác
                </button>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const modal = document.getElementById('delete-confirm-modal');
            const nameEl = document.getElementById('delete-employee-name');
            const cancelBtn = document.getElementById('cancel-delete-btn');
            const confirmBtn = document.getElementById('confirm-delete-btn');
            let targetFormId = null;

            function openModal() {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }

            function closeModal() {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                targetFormId = null;
            }

            document.querySelectorAll('.js-open-delete-modal').forEach(function (button) {
                button.addEventListener('click', function () {
                    targetFormId = button.dataset.formId;
                    nameEl.textContent = button.dataset.employeeName || '';
                    openModal();
                });
            });

            cancelBtn.addEventListener('click', closeModal);

            confirmBtn.addEventListener('click', function () {
                if (!targetFormId) return;
                const form = document.getElementById(targetFormId);
                if (form) form.submit();
            });

            modal.addEventListener('click', function (event) {
                if (event.target === modal) closeModal();
            });
        })();
    </script>

</x-admin-layout>
