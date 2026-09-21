@php
    $requiresAdminApproval = $leaveRequest->employee?->requiresAdminApproval() ?? false;
    $canAdminDecide = $leaveRequest->status === 'pending' && $requiresAdminApproval;
@endphp

<x-admin-layout title="Chi tiết đơn nghỉ phép">

    <div class="space-y-6">

        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <div class="mb-1 flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl font-bold text-slate-800">Chi tiết đơn nghỉ phép</h1>

                    @if (!$canAdminDecide)
                        <x-view-only-badge />
                    @endif
                </div>
                <p class="text-slate-500">
                    @if ($canAdminDecide)
                        Đây là đơn của quản lý/kế toán — Admin được duyệt hoặc từ chối.
                    @elseif ($requiresAdminApproval)
                        Đơn nghỉ phép của quản lý/kế toán — Admin là người phê duyệt.
                    @else
                        Xem thông tin đơn nghỉ phép. Đơn của nhân viên thường do quản lý phê duyệt.
                    @endif
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @if ($canAdminDecide)
                    <form action="{{ route('admin.leave-requests.approve', $leaveRequest) }}" method="POST"
                          onsubmit="return confirm('Duyệt đơn nghỉ phép của quản lý {{ $leaveRequest->employee?->full_name }}?')">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                            Duyệt đơn
                        </button>
                    </form>
                    @if (($capacityContext['blocked'] ?? false) && ($capacityEnforcement ?? 'override') === 'override')
                        <button type="button" onclick="openLeaveCapacityOverrideModal()"
                                class="rounded-xl border border-amber-300 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-900 transition hover:bg-amber-100">
                            Duyệt vượt giới hạn
                        </button>
                    @endif
                    <button type="button" onclick="openLeaveRejectModal()"
                            class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 transition hover:bg-rose-100">
                        Từ chối
                    </button>
                @endif
                <a href="{{ route('admin.leave-requests') }}" class="rounded-xl bg-slate-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-700">
                    Quay lại
                </a>
            </div>
        </div>

        @if (session('error'))
            <div class="flex items-center gap-3 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 shadow-sm">
                <p class="text-sm font-medium text-rose-800">{{ session('error') }}</p>
            </div>
        @endif

        <x-leave-capacity-alert field="capacity" />

        @include('shared.leave-capacity-approval-notice', [
            'capacityContext' => $capacityContext ?? null,
            'capacityEnforcement' => $capacityEnforcement ?? config('leave.department_capacity_enforcement', 'override'),
            'canDecide' => $canAdminDecide,
            'approveRoute' => route('admin.leave-requests.approve', $leaveRequest),
        ])

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

            <div class="rounded-2xl border bg-white p-6 shadow-sm">
                <h2 class="mb-5 text-lg font-semibold">Thông tin nhân viên</h2>
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-slate-500">Mã nhân viên</p>
                        <p class="font-semibold">{{ $leaveRequest->employee?->employee_code ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Họ và tên</p>
                        <p class="font-semibold">{{ $leaveRequest->employee?->full_name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Phòng ban</p>
                        <p class="font-semibold">{{ $leaveRequest->employee?->department?->department_name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Chức vụ</p>
                        <p class="font-semibold">{{ $leaveRequest->employee?->position?->position_name ?? '—' }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border bg-white p-6 shadow-sm">
                <h2 class="mb-5 text-lg font-semibold">Thông tin nghỉ phép</h2>
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-slate-500">Loại nghỉ</p>
                        <p class="font-semibold">{{ $leaveRequest->leaveTypeLabel() }}</p>
                        @include('shared.leave-type-policy', ['leaveRequest' => $leaveRequest])
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Từ ngày</p>
                        <p class="font-semibold">{{ $leaveRequest->start_date->format('d/m/Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Đến ngày</p>
                        <p class="font-semibold">{{ $leaveRequest->end_date->format('d/m/Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500">Trạng thái</p>
                        <p><x-status-badge :model="$leaveRequest" /></p>
                    </div>
                    @include('leave-requests.partials.approval-info', [
                        'leaveRequest' => $leaveRequest,
                        'variant' => 'tailwind',
                    ])
                </div>
            </div>

        </div>

        <div class="rounded-2xl border bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-lg font-semibold">Lý do nghỉ phép</h2>
            <p class="text-slate-700">{{ $leaveRequest->reason }}</p>
        </div>

        @if($leaveRequest->document)
            <div class="rounded-2xl border bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-lg font-semibold">Giấy tờ minh chứng</h2>
                <a href="{{ route('employee.leave-requests.document', $leaveRequest) }}"
                   class="inline-flex items-center rounded-xl border border-sky-200 bg-sky-50 px-4 py-2 text-sm font-semibold text-sky-800 transition hover:bg-sky-100">
                    Tải xuống: {{ $leaveRequest->document->original_name }}
                </a>
            </div>
        @endif

        @if($leaveRequest->histories->isNotEmpty())
            <div class="rounded-2xl border bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-lg font-semibold">Lịch sử xử lý</h2>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Người xử lý</th>
                                <th>Hành động</th>
                                <th>Thời gian</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($leaveRequest->histories as $history)
                                <tr>
                                    <td>{{ $history->actor?->employee?->full_name ?? $history->actor?->name ?? '—' }}</td>
                                    <td><x-approval-action-badge :action="$history->action" /></td>
                                    <td>{{ optional($history->created_at)->format('d/m/Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

    </div>

    @if ($canAdminDecide)
        <div id="leave-reject-modal"
             class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm">
            <div class="mx-4 w-full max-w-md rounded-3xl bg-white p-6 shadow-xl">
                <h3 class="mb-2 text-lg font-bold text-slate-800">Từ chối đơn nghỉ phép</h3>
                <p class="mb-4 text-sm text-slate-500">
                    Nhập lý do từ chối cho quản lý <strong class="text-slate-800">{{ $leaveRequest->employee?->full_name }}</strong>:
                </p>
                <form action="{{ route('admin.leave-requests.reject', $leaveRequest) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="mb-5">
                        <label for="reject_reason" class="mb-2 block text-sm font-semibold text-slate-700">Lý do từ chối</label>
                        <textarea id="reject_reason" name="reject_reason" required rows="3"
                                  placeholder="Nhập lý do từ chối..."
                                  class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20">{{ old('reject_reason') }}</textarea>
                        @error('reject_reason')
                            <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex gap-3">
                        <button type="button" onclick="closeLeaveRejectModal()"
                                class="flex-1 rounded-xl bg-slate-100 px-5 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-200">
                            Hủy
                        </button>
                        <button type="submit"
                                class="flex-1 rounded-xl bg-rose-600 px-5 py-3 text-sm font-medium text-white transition hover:bg-rose-700">
                            Xác nhận từ chối
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @if ($canAdminDecide && ($capacityContext['blocked'] ?? false) && ($capacityEnforcement ?? 'override') === 'override')
            <div id="leave-capacity-override-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 p-4">
                <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl">
                    <h3 class="text-lg font-bold text-slate-800">Duyệt vượt giới hạn phòng ban</h3>
                    <p class="mt-1 text-sm text-slate-500">Ghi rõ lý do nghiệp vụ để hệ thống lưu vào lịch sử đơn.</p>
                    <form action="{{ route('admin.leave-requests.approve', $leaveRequest) }}" method="POST" class="mt-5">
                        @csrf
                        @method('PATCH')
                        <label for="capacity_override_reason" class="mb-2 block text-sm font-semibold text-slate-700">Lý do duyệt vượt giới hạn</label>
                        <textarea id="capacity_override_reason" name="capacity_override_reason" required rows="4"
                                  class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20">{{ old('capacity_override_reason') }}</textarea>
                        @error('capacity_override_reason')
                            <p class="mt-2 text-sm text-rose-600">{{ $message }}</p>
                        @enderror
                        <div class="mt-6 flex gap-3">
                            <button type="button" onclick="closeLeaveCapacityOverrideModal()"
                                    class="flex-1 rounded-xl bg-slate-100 px-5 py-3 text-sm font-medium text-slate-700 transition hover:bg-slate-200">
                                Hủy
                            </button>
                            <button type="submit"
                                    class="flex-1 rounded-xl bg-amber-600 px-5 py-3 text-sm font-medium text-white transition hover:bg-amber-700">
                                Xác nhận duyệt vượt
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <script>
            function openLeaveRejectModal() {
                const modal = document.getElementById('leave-reject-modal');
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }

            function closeLeaveRejectModal() {
                const modal = document.getElementById('leave-reject-modal');
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }

            function openLeaveCapacityOverrideModal() {
                const modal = document.getElementById('leave-capacity-override-modal');
                if (!modal) return;
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }

            function closeLeaveCapacityOverrideModal() {
                const modal = document.getElementById('leave-capacity-override-modal');
                if (!modal) return;
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        </script>
    @endif

    @include('request-approvals.partials.processing-history', [
        'requestModel' => $leaveRequest,
        'title' => 'Lịch sử xử lý đơn nghỉ phép',
    ])

</x-admin-layout>
