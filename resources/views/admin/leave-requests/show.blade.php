@php
    $isFromManager = $leaveRequest->employee?->user?->isManager() ?? false;
    $canDecide = $isFromManager && $leaveRequest->isPending();
@endphp

<x-admin-layout title="Chi tiết đơn nghỉ phép">

    <div class="space-y-6">

        <div class="flex items-center justify-between">
            <div>
                <div class="flex flex-wrap items-center gap-3 mb-1">
                    <h1 class="text-2xl font-bold text-slate-800">Chi tiết đơn nghỉ phép</h1>
                    @if(!$isFromManager)
                        <x-view-only-badge />
                    @endif
                </div>
                <p class="text-slate-500">
                    @if($isFromManager)
                        Đơn nghỉ phép của quản lý — Admin là người phê duyệt.
                    @else
                        Xem thông tin đơn nghỉ phép. Đơn của nhân viên do quản lý phụ trách duyệt.
                    @endif
                </p>
            </div>

            <div class="flex items-center gap-2">
                @if($canDecide)
                    <form action="{{ route('admin.leave-requests.approve', $leaveRequest) }}" method="POST" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-xl" onclick="return confirm('Duyệt đơn nghỉ phép này?')">Duyệt</button>
                    </form>
                    <button type="button" class="px-4 py-2 bg-rose-600 text-white rounded-xl" onclick="document.getElementById('reject-form').classList.toggle('hidden')">Từ chối</button>
                @endif
                <a href="{{ route('admin.leave-requests') }}" class="px-4 py-2 bg-slate-600 text-white rounded-xl">
                    Quay lại
                </a>
            </div>
        </div>

        @if($canDecide)
            <div id="reject-form" class="hidden bg-white rounded-2xl border border-rose-200 shadow-sm p-6">
                <form action="{{ route('admin.leave-requests.reject', $leaveRequest) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <label class="block text-sm font-medium text-slate-600 mb-2">Lý do từ chối</label>
                    <textarea name="reject_reason" rows="3" class="w-full rounded-xl border-slate-300 mb-3" required>{{ old('reject_reason') }}</textarea>
                    @error('reject_reason')<div class="text-rose-600 text-sm mb-2">{{ $message }}</div>@enderror
                    <button type="submit" class="px-4 py-2 bg-rose-600 text-white rounded-xl">Xác nhận từ chối</button>
                </form>
            </div>
        @endif

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
                    @include('leave-requests.partials.approval-info', [
                        'leaveRequest' => $leaveRequest,
                        'variant' => 'tailwind',
                    ])
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

</x-admin-layout>
