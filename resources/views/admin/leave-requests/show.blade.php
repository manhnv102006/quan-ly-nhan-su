<x-admin-layout>

    <div class="space-y-6">

        <div class="flex items-center justify-between">

            <div>
                <h1 class="text-2xl font-bold text-slate-800">
                    Chi tiết đơn nghỉ phép
                </h1>

                <p class="text-slate-500">
                    Thông tin đơn nghỉ phép của nhân viên
                </p>
            </div>

            <a
                href="{{ route('admin.leave-requests.index') }}"
                class="px-4 py-2 bg-slate-600 text-white rounded-xl">

                Quay lại

            </a>

        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Thông tin nhân viên --}}
            <div class="bg-white rounded-2xl border shadow-sm p-6">

                <h2 class="text-lg font-semibold mb-5">
                    Thông tin nhân viên
                </h2>

                <div class="space-y-4">

                    <div>
                        <p class="text-sm text-slate-500">
                            Mã nhân viên
                        </p>

                        <p class="font-semibold">
                            {{ $leaveRequest->employee->employee_code }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500">
                            Họ và tên
                        </p>

                        <p class="font-semibold">
                            {{ $leaveRequest->employee->full_name }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500">
                            Phòng ban
                        </p>

                        <p class="font-semibold">
                            {{ $leaveRequest->employee->department?->department_name }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500">
                            Chức vụ
                        </p>

                        <p class="font-semibold">
                            {{ $leaveRequest->employee->position?->position_name }}
                        </p>
                    </div>

                </div>

            </div>

            {{-- Thông tin đơn --}}
            <div class="bg-white rounded-2xl border shadow-sm p-6">

                <h2 class="text-lg font-semibold mb-5">
                    Thông tin nghỉ phép
                </h2>

                <div class="space-y-4">

                    <div>
                        <p class="text-sm text-slate-500">
                            Loại nghỉ
                        </p>

                        <p class="font-semibold">
                            {{ $leaveRequest->leave_type }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500">
                            Từ ngày
                        </p>

                        <p class="font-semibold">
                            {{ $leaveRequest->start_date->format('d/m/Y') }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500">
                            Đến ngày
                        </p>

                        <p class="font-semibold">
                            {{ $leaveRequest->end_date->format('d/m/Y') }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500">
                            Trạng thái
                        </p>

                        <p>

                            @if($leaveRequest->status == 'pending')
                                <span class="badge bg-warning">
                                    Chờ duyệt
                                </span>
                            @elseif($leaveRequest->status == 'approved')
                                <span class="badge bg-success">
                                    Đã duyệt
                                </span>
                            @else
                                <span class="badge bg-danger">
                                    Từ chối
                                </span>
                            @endif

                        </p>
                    </div>

                </div>

            </div>

        </div>

        {{-- Lý do nghỉ --}}
        <div class="bg-white rounded-2xl border shadow-sm p-6">

            <h2 class="text-lg font-semibold mb-4">
                Lý do nghỉ phép
            </h2>

            <p class="text-slate-700">
                {{ $leaveRequest->reason }}
            </p>

        </div>

        {{-- Duyệt đơn --}}
        @if($leaveRequest->status == 'pending')

            <div class="flex gap-3">

                <form
                    action="{{ route('admin.leave-requests.approve',$leaveRequest) }}"
                    method="POST">

                    @csrf
                    @method('PATCH')

                    <button
                        type="submit"
                        class="px-5 py-2 bg-green-600 text-white rounded-xl">

                        Duyệt đơn

                    </button>

                </form>

                <form
                    action="{{ route('admin.leave-requests.reject',$leaveRequest) }}"
                    method="POST">

                    @csrf
                    @method('PATCH')

                    <button
                        type="submit"
                        class="px-5 py-2 bg-red-600 text-white rounded-xl">

                        Từ chối

                    </button>

                </form>

            </div>

        @endif

    </div>

</x-admin-layout>