@php
    $statusClasses = \App\Models\OvertimeRequest::STATUS_TAILWIND_CLASSES;
    $statusLabels = \App\Models\OvertimeRequest::STATUS_LABELS;
@endphp

<x-manager-layout
    title="Duyệt tăng ca"
    subtitle="Chỉ hiển thị đơn của nhân viên thường trong phòng ban bạn quản lý. Đơn tăng ca của quản lý do Admin phê duyệt."
>
    <div class="manager-page">
        @if(!($managerLinked ?? true))
            <div class="manager-card border border-amber-100 bg-amber-50/90 p-5">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h3 class="text-base font-bold text-amber-800">Chưa liên kết hồ sơ nhân viên</h3>
                        <p class="mt-2 text-sm leading-6 text-amber-700">
                            Tài khoản quản lý của bạn chưa được liên kết với hồ sơ nhân viên trong hệ thống,
                            nên không thể tải danh sách đơn tăng ca cần duyệt.
                        </p>
                        <p class="mt-2 text-xs text-amber-600/90">
                            Vui lòng liên hệ quản trị viên để gán <code class="rounded bg-amber-100 px-1">user_id</code>
                            trên bảng nhân viên. Sau khi liên kết, hệ thống sẽ lấy đơn cấp dưới theo
                            <code class="rounded bg-amber-100 px-1">manager_id</code> hoặc phòng ban được giao quản lý.
                        </p>
                    </div>
                    <a href="{{ route('profile.edit') }}" class="inline-flex shrink-0 items-center justify-center rounded-2xl bg-amber-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-amber-700">
                        Xem hồ sơ
                    </a>
                </div>
            </div>
        @else
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
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <span class="rounded-full bg-amber-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-[0.24em] text-amber-700">Chờ</span>
                    </div>
                    <p class="mt-5 text-3xl font-extrabold tracking-tight text-slate-800">{{ number_format($stats['pending']) }}</p>
                    <p class="mt-1 text-sm font-medium text-slate-500">Đơn chờ duyệt</p>
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
                    <h3 class="mt-2 text-xl font-bold tracking-tight text-slate-800">Tìm kiếm đơn tăng ca</h3>
                </div>
                <div class="px-6 py-5 sm:px-7">
                    <form method="GET" action="{{ route('manager.overtime-requests.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
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
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Trạng thái</label>
                            <select name="status" class="w-full rounded-xl border-0 bg-slate-100/90 px-4 py-2.5 text-sm text-slate-700 focus:bg-white focus:ring-2 focus:ring-teal-500/30">
                                <option value="">Tất cả</option>
                                @foreach($statusLabels as $value => $label)
                                    <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Tăng ca từ ngày</label>
                            <input type="date" name="work_date_from" value="{{ $filters['work_date_from'] ?? '' }}"
                                   class="w-full rounded-xl border-0 bg-slate-100/90 px-4 py-2.5 text-sm text-slate-700 focus:bg-white focus:ring-2 focus:ring-teal-500/30">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500">Tăng ca đến ngày</label>
                            <input type="date" name="work_date_to" value="{{ $filters['work_date_to'] ?? '' }}"
                                   class="w-full rounded-xl border-0 bg-slate-100/90 px-4 py-2.5 text-sm text-slate-700 focus:bg-white focus:ring-2 focus:ring-teal-500/30">
                        </div>
                        <div class="flex flex-wrap items-end justify-end gap-2 md:col-span-2 xl:col-span-3">
                            <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-teal-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-teal-900/20 transition hover:bg-teal-700">
                                Tìm kiếm
                            </button>
                            @if(collect($filters)->filter()->isNotEmpty())
                                <a href="{{ route('manager.overtime-requests.index') }}" class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
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
                         showRejectModal: {{ ($errors->has('reject_reason') || old('overtime_request_ids')) ? 'true' : 'false' }},
                         syncSelection() {
                             const boxes = this.$refs.tableForm.querySelectorAll('input[name=\'overtime_request_ids[]\']');
                             const checked = this.$refs.tableForm.querySelectorAll('input[name=\'overtime_request_ids[]\']:checked');
                             this.selectedCount = checked.length;
                             this.allSelected = boxes.length > 0 && checked.length === boxes.length;
                         },
                         toggleAll(event) {
                             const checked = event.target.checked;
                             this.$refs.tableForm.querySelectorAll('input[name=\'overtime_request_ids[]\']').forEach(box => {
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

                             if (! confirm(`Duyệt ${this.selectedCount} đơn tăng ca đã chọn?`)) {
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

                             this.$refs.tableForm.querySelectorAll('input[name=\'overtime_request_ids[]\']:checked').forEach(box => {
                                 const input = document.createElement('input');
                                 input.type = 'hidden';
                                 input.name = 'overtime_request_ids[]';
                                 input.value = box.value;
                                 container.appendChild(input);
                             });

                             this.showRejectModal = true;
                         },
                         restoreOldSelection() {
                             @json(array_map('intval', old('overtime_request_ids', []))).forEach(id => {
                                 const box = this.$refs.tableForm.querySelector(`input[name='overtime_request_ids[]'][value='${id}']`);
                                 if (box) {
                                     box.checked = true;
                                 }
                             });
                             this.syncSelection();
                         }
                     }"
                     x-init="restoreOldSelection()">
                <div class="border-b border-slate-100 px-6 py-5 sm:px-7">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-emerald-600">Danh sách</p>
                            <h3 class="mt-2 text-xl font-bold tracking-tight text-slate-800">Đơn tăng ca nhân viên phòng ban</h3>
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
                                        form="bulk-overtime-approve-form"
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

                <form id="bulk-overtime-approve-form"
                      x-ref="tableForm"
                      method="POST"
                      action="{{ route('manager.overtime-requests.bulk-approve') }}"
                      @submit="confirmApprove($event)">
                    @csrf
                    @method('PATCH')

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50">
                                <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-400">#</th>
                                <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-400">Nhân viên</th>
                                <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-400">Ngày tăng ca</th>
                                <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-400">Khung giờ</th>
                                <th class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-400">Tổng giờ</th>
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
                            @forelse($overtimeRequests as $index => $item)
                                @php
                                    $canBulkApprove = auth()->user()->can('approve', $item) && $item->isAwaitingManagerApproval();
                                @endphp
                                <tr class="transition hover:bg-slate-50/50">
                                    <td class="px-6 py-4 text-xs font-medium text-slate-500">{{ ($overtimeRequests->firstItem() ?? 0) + $index }}</td>
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-semibold text-slate-800">{{ $item->employee?->full_name ?? '—' }}</p>
                                        <p class="text-xs text-slate-500">{{ $item->employee?->employee_code ?? '—' }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium text-slate-700">{{ optional($item->work_date)->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 text-xs font-medium text-slate-700">
                                        {{ $item->start_time }} → {{ $item->end_time }}
                                    </td>
                                    <td class="px-6 py-4 text-center text-sm font-bold text-slate-800">{{ $item->total_hours }}h</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-bold {{ $statusClasses[$item->status] ?? 'bg-slate-100 text-slate-600 border-slate-200' }}">
                                            {{ $statusLabels[$item->status] ?? $item->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <a href="{{ route('manager.overtime-requests.show', $item) }}"
                                           class="inline-flex items-center rounded-lg border border-teal-100 bg-teal-50 px-3 py-1.5 text-xs font-semibold text-teal-700 transition hover:bg-teal-100">
                                            Chi tiết
                                        </a>
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        @if($canBulkApprove)
                                            <input type="checkbox"
                                                   name="overtime_request_ids[]"
                                                   value="{{ $item->id }}"
                                                   class="rounded border-slate-300 text-teal-600 focus:ring-teal-500/30"
                                                   @change="syncSelection()"
                                                   aria-label="Chọn đơn tăng ca của {{ $item->employee?->full_name ?? 'nhân viên' }}">
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-12 text-center text-sm text-slate-400">
                                        Không có đơn tăng ca phù hợp.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                </form>

                @if($overtimeRequests->hasPages())
                    <div class="border-t border-slate-100 px-6 py-4">
                        {{ $overtimeRequests->links() }}
                    </div>
                @endif

                <div x-show="showRejectModal"
                     x-cloak
                     class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 px-4"
                     @keydown.escape.window="showRejectModal = false">
                    <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl" @click.outside="showRejectModal = false">
                        <form method="POST" action="{{ route('manager.overtime-requests.bulk-reject') }}">
                            @csrf
                            @method('PATCH')
                            <div x-ref="rejectIds"></div>

                            <h3 class="text-lg font-bold text-slate-800">Từ chối đơn tăng ca hàng loạt</h3>
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

            @include('manager.overtime-requests.partials.history-table', [
                'histories' => $recentHistories,
                'showEmployee' => true,
                'showOvertimeRequestLink' => true,
            ])
        @endif
    </div>
</x-manager-layout>
