<x-admin-layout title="Chi tiết đơn nghỉ phép">

    <div class="space-y-6">

        @php
            $canAdminDecide = $leaveRequest->status === 'pending' && $leaveRequest->employee?->hasManagerRole();
        @endphp

        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <div class="flex flex-wrap items-center gap-3 mb-1">
                    <h1 class="text-2xl font-bold text-slate-800">Chi tiết đơn nghỉ phép</h1>
                    @unless ($canAdminDecide)
                        <x-view-only-badge />
                    @endunless
                </div>
                <p class="text-slate-500">
                    @if ($canAdminDecide)
                        Đây là đơn của quản lý — Admin được duyệt hoặc từ chối.
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
                    <button type="button" onclick="openLeaveRejectModal()"
                            class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-rose-700">
                        Từ chối
                    </button>
                @endif
                <a href="{{ route('admin.leave-requests') }}" class="px-4 py-2 bg-slate-600 text-white rounded-xl">
                    Quay lại
                </a>
            </div>
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
                                  class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-800 outline-none transition focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20"></textarea>
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
        </script>
    @endif

</x-admin-layout>
