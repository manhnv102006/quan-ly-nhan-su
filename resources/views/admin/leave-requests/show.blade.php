<x-admin-layout title="Chi tiết đơn nghỉ phép">

    <div class="space-y-6">

        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Chi tiết đơn nghỉ phép</h1>
                <p class="text-slate-500">Xem thông tin đơn nghỉ phép (chỉ xem, không duyệt).</p>
            </div>

            <a href="{{ route('admin.leave-requests') }}" class="px-4 py-2 bg-slate-600 text-white rounded-xl">
                Quay lại
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <div class="bg-white rounded-2xl border shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-5">Thông tin nhân viên</h2>
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

            <div class="bg-white rounded-2xl border shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-5">Thông tin nghỉ phép</h2>
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-slate-500">Loại nghỉ</p>
                        <p class="font-semibold">{{ $leaveRequest->leave_type }}</p>
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
                    @if($leaveRequest->approver)
                        <div>
                            <p class="text-sm text-slate-500">Người duyệt</p>
                            <p class="font-semibold">{{ $leaveRequest->approver->name }}</p>
                            <p class="text-xs text-slate-400">{{ optional($leaveRequest->approved_at)->format('d/m/Y H:i') }}</p>
                        </div>
                    @endif
                    @if($leaveRequest->reject_reason)
                        <div>
                            <p class="text-sm text-slate-500">Lý do từ chối</p>
                            <p class="font-semibold text-rose-600">{{ $leaveRequest->reject_reason }}</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        <div class="bg-white rounded-2xl border shadow-sm p-6">
            <h2 class="text-lg font-semibold mb-4">Lý do nghỉ phép</h2>
            <p class="text-slate-700">{{ $leaveRequest->reason }}</p>
        </div>

        @if($leaveRequest->histories->isNotEmpty())
            <div class="bg-white rounded-2xl border shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-4">Lịch sử xử lý</h2>
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
                                    <td>{{ $history->actor?->name ?? '—' }}</td>
                                    <td><x-approval-action-badge :action="$history->action" /></td>
                                    <td>{{ optional($history->created_at)->format('d/m/Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if($leaveRequest->isPending())
            <div class="alert alert-info mb-0">
                Đơn đang chờ quản lý phê duyệt. Admin chỉ được xem, không được duyệt/từ chối.
            </div>
        @endif

    </div>

</x-admin-layout>
