<x-admin-layout>

    <div class="space-y-6">

        <div class="flex items-center justify-between">

            <div>
                <h1 class="text-2xl font-bold text-slate-800">
                    Chi tiết chấm công
                </h1>

                <p class="text-slate-500">
                    Thông tin chấm công của nhân viên
                </p>
            </div>

            <a href="{{ route('admin.attendances') }}"
                class="px-4 py-2 bg-slate-600 text-white rounded-xl hover:bg-slate-700">

                Quay lại

            </a>

        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- Thông tin nhân viên --}}
            <div class="bg-white rounded-2xl shadow-sm border p-6">

                <h2 class="text-lg font-semibold text-slate-800 mb-5">
                    Thông tin nhân viên
                </h2>

                <div class="space-y-4">

                    <div>
                        <p class="text-sm text-slate-500">
                            Mã nhân viên
                        </p>

                        <p class="font-semibold">
                            {{ $attendance->employee->employee_code }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500">
                            Họ và tên
                        </p>

                        <p class="font-semibold">
                            {{ $attendance->employee->full_name }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500">
                            Phòng ban
                        </p>

                        <p class="font-semibold">
                            {{ $attendance->employee->department?->department_name }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500">
                            Chức vụ
                        </p>

                        <p class="font-semibold">
                            {{ $attendance->employee->position?->position_name }}
                        </p>
                    </div>

                </div>

            </div>

            {{-- Thông tin chấm công --}}
            <div class="bg-white rounded-2xl shadow-sm border p-6">

                <h2 class="text-lg font-semibold text-slate-800 mb-5">
                    Thông tin chấm công
                </h2>

                <div class="space-y-4">

                    <div>
                        <p class="text-sm text-slate-500">
                            Ngày công
                        </p>

                        <p class="font-semibold">
                            {{ $attendance->attendance_date->format('d/m/Y') }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500">
                            Ca làm
                        </p>

                        <p class="font-semibold">
                            {{ $attendance->shift?->shift_name }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500">
                            Check In
                        </p>

                        <p class="font-semibold">
                            {{ $attendance->check_in?->format('H:i:s') ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500">
                            Check Out
                        </p>

                        <p class="font-semibold">
                            {{ $attendance->check_out?->format('H:i:s') ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500">
                            Giờ làm
                        </p>

                        <p class="font-semibold">
                            {{ $attendance->work_hours }} giờ
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-slate-500">
                            Trạng thái
                        </p>

                        <p class="font-semibold">

                            @switch($attendance->status)

                                @case('present')
                                    <span class="badge bg-success">
                                        Đi làm
                                    </span>
                                @break

                                @case('late')
                                    <span class="badge bg-warning">
                                        Đi muộn
                                    </span>
                                @break

                                @case('leave')
                                    <span class="badge bg-info">
                                        Nghỉ phép
                                    </span>
                                @break

                                @case('absent')
                                    <span class="badge bg-danger">
                                        Vắng mặt
                                    </span>
                                @break

                            @endswitch

                        </p>
                    </div>

                </div>

            </div>

        </div>

    </div>

</x-admin-layout>