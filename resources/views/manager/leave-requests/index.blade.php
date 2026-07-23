@php
    $statusClasses = [
        'pending' => 'bg-amber-50 text-amber-700 border-amber-100',
        'approved' => 'bg-blue-50 text-blue-700 border-blue-100',
        'rejected' => 'bg-rose-50 text-rose-700 border-rose-100',
    ];
    $statusLabels = [
        'pending' => 'Chờ duyệt',
        'approved' => 'Đã duyệt',
        'rejected' => 'Từ chối',
    ];
    $leaveTypes = [
        'annual' => ['label' => 'Nghỉ phép', 'class' => 'bg-sky-50 text-sky-700 border-sky-100'],
        'sick' => ['label' => 'Nghỉ ốm', 'class' => 'bg-amber-50 text-amber-700 border-amber-100'],
        'unpaid' => ['label' => 'Không lương', 'class' => 'bg-slate-100 text-slate-700 border-slate-200'],
    ];
@endphp

<x-manager-layout
    title="Quản lý nghỉ phép"
    subtitle="Duyệt đơn nhân viên cấp dưới và quản lý đơn nghỉ phép cá nhân của bạn."
>
    <div class="manager-page">
        <div class="manager-page-header">
            <div>
                <p class="manager-kicker">Nghỉ phép</p>
                <h2 class="manager-title">Quản lý nghỉ phép</h2>
                <p class="manager-subtitle">
                    Duyệt đơn của <span class="font-semibold text-slate-700">nhân viên</span>.
                    Đơn của bạn do <span class="font-semibold text-slate-700">Admin</span> phê duyệt.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('employee.leave-requests') }}" class="manager-btn-secondary text-xs">Đơn của tôi</a>
                <a href="{{ route('employee.leave-requests.create') }}" class="manager-btn-primary text-xs">Tạo đơn nghỉ phép</a>
            </div>
        </div>

        @if(!($managerLinked ?? true))
            <div class="manager-card border border-amber-100 bg-amber-50/90 p-5">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h3 class="text-base font-bold text-amber-800">Chưa liên kết hồ sơ nhân viên</h3>
                        <p class="mt-2 text-sm leading-6 text-amber-700">
                            Tài khoản quản lý của bạn chưa được liên kết với hồ sơ nhân viên trong hệ thống,
                            nên không thể tải danh sách đơn nghỉ phép cần duyệt.
                        </p>
                        <p class="mt-2 text-xs text-amber-600/90">
                            Vui lòng liên hệ quản trị viên để gán <code class="rounded bg-amber-100 px-1">Trưởng phòng ban</code>
                            trong mục Quản lý phòng ban. Chỉ quản lý được gán trưởng phòng mới nhận đơn nghỉ phép của nhân viên cùng phòng ban.
                        </p>
                    </div>
                    <a href="{{ route('profile.edit') }}" class="inline-flex shrink-0 items-center justify-center rounded-2xl bg-amber-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-amber-700">
                        Xem hồ sơ
                    </a>
                </div>
            </div>
        @else
            @if ($myLeaveStats)
                <section class="manager-card border border-sky-100/80 bg-sky-50/40 p-5 sm:p-6">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-sky-600">Đơn cá nhân</p>
                            <h3 class="mt-1 text-lg font-bold text-slate-800">Nghỉ phép của bạn</h3>
                            <p class="mt-1 text-sm text-slate-600">
                                Tổng {{ number_format($myLeaveStats['total']) }} đơn
                                @if ($myLeaveStats['pending'] > 0)
                                    · <span class="font-semibold text-amber-700">{{ number_format($myLeaveStats['pending']) }} đơn đang chờ Admin duyệt</span>
                                @endif
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('employee.leave-requests') }}"
                               class="inline-flex items-center rounded-xl border border-sky-200 bg-white px-4 py-2.5 text-sm font-semibold text-sky-700 transition hover:bg-sky-50">
                                Xem đơn của tôi
                            </a>
                            <a href="{{ route('employee.leave-requests.create') }}"
                               class="inline-flex items-center rounded-xl bg-sky-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-sky-700">
                                Tạo đơn mới
                            </a>
                        </div>
                    </div>
                </section>
            @endif

            @if (session('success'))
                <div class="flex items-center gap-3 rounded-2xl border border-teal-200 bg-teal-50 px-5 py-4 shadow-sm">
                    <p class="text-sm font-medium text-teal-800">{{ session('success') }}</p>
                </div>
            @endif

            @if (session('error'))
                <div class="flex items-center gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 shadow-sm">
                    <p class="text-sm font-medium text-rose-800">{{ session('error') }}</p>
                </div>
            @endif

            <section class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div class="manager-stat-card border border-amber-100/80 bg-white/90">
                    <div class="flex items-start justify-between">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 text-white shadow-lg shadow-amber-200">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zM12 16.5h.008v.008H12V16.5z" />
                            </svg>
                        </div>
                        <span class="rounded-full bg-amber-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.24em] text-amber-700">Chờ</span>
                    </div>
                    <p class="mt-5 text-3xl font-extrabold tracking-tight text-slate-800">{{ number_format($stats['pending']) }}</p>
                    <p class="mt-1 text-sm font-medium text-slate-500">Đơn nhân viên chờ duyệt</p>
                </div>

                <div class="manager-stat-card border border-teal-100/80 bg-white/90">
                    <div class="flex items-start justify-between">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-teal-500 to-emerald-600 text-white shadow-lg shadow-teal-200">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span class="rounded-full bg-teal-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.24em] text-teal-700">OK</span>
                    </div>
                    <p class="mt-5 text-3xl font-extrabold tracking-tight text-slate-800">{{ number_format($stats['approved']) }}</p>
                    <p class="mt-1 text-sm font-medium text-slate-500">Đã duyệt</p>
                </div>

                <div class="manager-stat-card border border-rose-100/80 bg-white/90">
                    <div class="flex items-start justify-between">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-rose-500 to-red-600 text-white shadow-lg shadow-rose-200">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </div>
                        <span class="rounded-full bg-rose-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.24em] text-rose-700">Từ chối</span>
                    </div>
                    <p class="mt-5 text-3xl font-extrabold tracking-tight text-slate-800">{{ number_format($stats['rejected']) }}</p>
                    <p class="mt-1 text-sm font-medium text-slate-500">Đã từ chối</p>
                </div>
            </section>

            <section class="manager-card overflow-hidden">
                <div class="border-b border-slate-100 px-6 py-5 sm:px-7">
                    <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-teal-600">Bộ lọc</p>
                    <h3 class="mt-2 text-xl font-bold tracking-tight text-slate-800">Tìm kiếm đơn nghỉ phép</h3>
                </div>
                <div class="px-6 py-5 sm:px-7">
                    <form method="GET" action="{{ route('manager.leave-requests.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Tên nhân viên</label>
                            <input type="text" name="employee_name" value="{{ $filters['employee_name'] ?? '' }}"
                                   placeholder="Nhập tên nhân viên"
                                   class="w-full rounded-xl border-0 bg-slate-100/90 px-4 py-2.5 text-sm text-slate-700 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-teal-500/30">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Mã nhân viên</label>
                            <input type="text" name="employee_code" value="{{ $filters['employee_code'] ?? '' }}"
                                   placeholder="Nhập mã nhân viên"
                                   class="w-full rounded-xl border-0 bg-slate-100/90 px-4 py-2.5 text-sm text-slate-700 placeholder:text-slate-400 focus:bg-white focus:ring-2 focus:ring-teal-500/30">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Loại nghỉ</label>
                            <select name="leave_type" class="w-full rounded-xl border-0 bg-slate-100/90 px-4 py-2.5 text-sm text-slate-700 focus:bg-white focus:ring-2 focus:ring-teal-500/30">
                                <option value="">Tất cả</option>
                                @foreach(\App\Models\LeaveRequest::LEAVE_TYPE_LABELS as $value => $label)
                                    <option value="{{ $value }}" @selected(($filters['leave_type'] ?? '') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Trạng thái</label>
                            <select name="status" class="w-full rounded-xl border-0 bg-slate-100/90 px-4 py-2.5 text-sm text-slate-700 focus:bg-white focus:ring-2 focus:ring-teal-500/30">
                                <option value="">Tất cả</option>
                                <option value="pending" @selected(($filters['status'] ?? '') === 'pending')>Chờ duyệt</option>
                                <option value="approved" @selected(($filters['status'] ?? '') === 'approved')>Đã duyệt</option>
                                <option value="rejected" @selected(($filters['status'] ?? '') === 'rejected')>Từ chối</option>
                            </select>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Nghỉ từ ngày</label>
                            <input type="date" name="start_from" value="{{ $filters['start_from'] ?? '' }}"
                                   class="w-full rounded-xl border-0 bg-slate-100/90 px-4 py-2.5 text-sm text-slate-700 focus:bg-white focus:ring-2 focus:ring-teal-500/30">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Nghỉ đến ngày</label>
                            <input type="date" name="start_to" value="{{ $filters['start_to'] ?? '' }}"
                                   class="w-full rounded-xl border-0 bg-slate-100/90 px-4 py-2.5 text-sm text-slate-700 focus:bg-white focus:ring-2 focus:ring-teal-500/30">
                        </div>
                        <div class="flex flex-wrap items-end justify-end gap-2 md:col-span-2 xl:col-span-3">
                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-teal-900/20 transition hover:bg-teal-700">
                                Tìm kiếm
                            </button>
                            @if(collect($filters)->filter()->isNotEmpty())
                                <a href="{{ route('manager.leave-requests.index') }}" class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                                    Xóa lọc
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </section>

            <section class="manager-card overflow-hidden"
                     x-data="{
                         selectedCount: 0,
                         allSelected: false,
                         showRejectModal: {{ ($errors->has('reject_reason') || old('leave_request_ids')) ? 'true' : 'false' }},
                         syncSelection() {
                             const boxes = this.$refs.tableForm.querySelectorAll('input[name=\'leave_request_ids[]\']');
                             const checked = this.$refs.tableForm.querySelectorAll('input[name=\'leave_request_ids[]\']:checked');
                             this.selectedCount = checked.length;
                             this.allSelected = boxes.length > 0 && checked.length === boxes.length;
                         },
                         toggleAll(event) {
                             const checked = event.target.checked;
                             this.$refs.tableForm.querySelectorAll('input[name=\'leave_request_ids[]\']').forEach(box => {
                                 box.checked = checked;
                             });
                             this.syncSelection();
                         },
                         confirmApprove(event) {
                             if (this.selectedCount === 0) {
                                 event.preventDefault();
                                 alert('Vui lòng chọn ít nhất một đơn chờ duyệt.');
                                 return;
                             }

                             if (! confirm(`Duyệt ${this.selectedCount} đơn nghỉ phép đã chọn?`)) {
                                 event.preventDefault();
                             }
                         },
                         openRejectModal() {
                             if (this.selectedCount === 0) {
                                 alert('Vui lòng chọn ít nhất một đơn chờ duyệt.');
                                 return;
                             }

                             const container = this.$refs.rejectIds;
                             container.innerHTML = '';

                             this.$refs.tableForm.querySelectorAll('input[name=\'leave_request_ids[]\']:checked').forEach(box => {
                                 const input = document.createElement('input');
                                 input.type = 'hidden';
                                 input.name = 'leave_request_ids[]';
                                 input.value = box.value;
                                 container.appendChild(input);
                             });

                             this.showRejectModal = true;
                         },
                         restoreOldSelection() {
                             @json(array_map('intval', old('leave_request_ids', []))).forEach(id => {
                                 const box = this.$refs.tableForm.querySelector(`input[name='leave_request_ids[]'][value='${id}']`);
                                 if (box) {
                                     box.checked = true;
                                 }
                             });
                             this.syncSelection();
                         }
                     }"
                     x-init="restoreOldSelection()">
                <form id="bulk-leave-approve-form"
                      x-ref="tableForm"
                      method="POST"
                      action="{{ route('manager.leave-requests.bulk-approve') }}"
                      @submit="confirmApprove($event)">
                    @csrf
                    @method('PATCH')

                <div class="border-b border-slate-100 px-6 py-5 sm:px-7">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-emerald-600">Danh sách</p>
                            <h3 class="mt-2 text-xl font-bold tracking-tight text-slate-800">Đơn nghỉ phép nhân viên</h3>
                            <p class="mt-1 text-xs text-slate-500">Chọn các đơn chờ duyệt ở cột bên phải, sau đó dùng nút Duyệt hoặc Từ chối phía trên bảng.</p>
                        </div>
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                            <p class="text-sm font-medium text-slate-600">
                                <span x-show="selectedCount === 0">Chưa chọn đơn nào</span>
                                <span x-show="selectedCount > 0" x-cloak>
                                    Đã chọn <span x-text="selectedCount" class="font-bold text-teal-700"></span> đơn
                                </span>
                            </p>
                            <div class="flex flex-wrap gap-2">
                                <button type="submit"
                                        :disabled="selectedCount === 0"
                                        class="inline-flex items-center justify-center rounded-xl bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-teal-900/20 transition hover:bg-teal-700 disabled:cursor-not-allowed disabled:opacity-50">
                                    Duyệt
                                </button>
                                <button type="button"
                                        @click="openRejectModal()"
                                        :disabled="selectedCount === 0"
                                        class="inline-flex items-center justify-center rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-rose-500/20 transition hover:bg-rose-700 disabled:cursor-not-allowed disabled:opacity-50">
                                    Từ chối
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50">
                                <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-400">#</th>
                                <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-400">Nhân viên</th>
                                <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-400">Loại</th>
                                <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-400">Thời gian</th>
                                <th class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-400">Số ngày</th>
                                <th class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-400">Trạng thái</th>
                                <th class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-400">Thao tác</th>
                                <th class="px-4 py-4 text-center text-xs font-bold uppercase text-slate-400">
                                    <input type="checkbox"
                                           class="rounded border-slate-300 text-teal-600 focus:ring-teal-500/30"
                                           :checked="allSelected"
                                           @change="toggleAll($event)"
                                           aria-label="Chọn tất cả đơn chờ duyệt trên trang">
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($leaveRequests as $index => $item)
                                @php
                                    $canBulkApprove = auth()->user()->can('approve', $item) && $item->isAwaitingManagerApproval();
                                @endphp
                                <tr class="transition hover:bg-slate-50/50">
                                    <td class="px-6 py-4 text-xs font-medium text-slate-500">{{ ($leaveRequests->firstItem() ?? 0) + $index }}</td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-semibold text-slate-800">{{ $item->employee?->full_name ?? '—' }}</p>
                                        <p class="text-xs text-slate-500">{{ $item->employee?->employee_code ?? '—' }}</p>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold {{ $leaveTypes[$item->leave_type]['class'] ?? 'bg-slate-100 text-slate-600 border-slate-200' }}">
                                            {{ \App\Models\LeaveRequest::LEAVE_TYPE_LABELS[$item->leave_type] ?? $item->leave_type }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-xs font-medium text-slate-700">
                                        {{ optional($item->start_date)->format('d/m/Y') }} → {{ optional($item->end_date)->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 text-center text-sm font-bold text-slate-800">{{ $item->total_days }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-bold {{ $statusClasses[$item->status] ?? 'bg-slate-100 text-slate-600 border-slate-200' }}">
                                            {{ $statusLabels[$item->status] ?? $item->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <a href="{{ route('manager.leave-requests.show', $item) }}"
                                           class="inline-flex items-center rounded-lg border border-teal-100 bg-teal-50 px-3 py-1.5 text-xs font-semibold text-teal-700 transition hover:bg-teal-100">
                                            Chi tiết
                                        </a>
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        @if($canBulkApprove)
                                            <input type="checkbox"
                                                   name="leave_request_ids[]"
                                                   value="{{ $item->id }}"
                                                   class="rounded border-slate-300 text-teal-600 focus:ring-teal-500/30"
                                                   @change="syncSelection()"
                                                   aria-label="Chọn đơn nghỉ phép của {{ $item->employee?->full_name ?? 'nhân viên' }}">
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center text-sm text-slate-400">
                                        Không có đơn nghỉ phép phù hợp.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                </form>

                @if($leaveRequests->hasPages())
                    <div class="border-t border-slate-100 px-6 py-4">
                        {{ $leaveRequests->links() }}
                    </div>
                @endif

                <div x-show="showRejectModal"
                     x-cloak
                     class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 px-4"
                     @keydown.escape.window="showRejectModal = false">
                    <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl" @click.outside="showRejectModal = false">
                        <form method="POST" action="{{ route('manager.leave-requests.bulk-reject') }}">
                            @csrf
                            @method('PATCH')
                            <div x-ref="rejectIds"></div>

                            <h3 class="text-lg font-bold text-slate-800">Từ chối đơn nghỉ phép hàng loạt</h3>
                            <p class="mt-1 text-sm text-slate-500">
                                Bạn đang từ chối <span x-text="selectedCount" class="font-semibold text-rose-600"></span> đơn. Vui lòng ghi rõ lý do.
                            </p>

                            <div class="mt-5">
                                <label class="mb-1.5 block text-sm font-semibold text-slate-700">
                                    Lý do từ chối <span class="text-rose-500">*</span>
                                </label>
                                <textarea name="reject_reason" rows="4" required minlength="1"
                                          class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-700 focus:border-rose-300 focus:ring-2 focus:ring-rose-500/20 @error('reject_reason') border-rose-400 @enderror">{{ old('reject_reason') }}</textarea>
                                @error('reject_reason')
                                    <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mt-6 flex justify-end gap-2">
                                <button type="button"
                                        @click="showRejectModal = false"
                                        class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                                    Hủy
                                </button>
                                <button type="submit"
                                        class="rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-rose-700">
                                    Xác nhận từ chối
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>

            @include('manager.leave-requests.partials.history-table', [
                'histories' => $recentHistories,
                'showEmployee' => true,
                'showLeaveRequestLink' => true,
            ])
        @endif
    </div>
</x-manager-layout>
