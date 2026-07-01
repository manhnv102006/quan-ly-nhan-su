<x-admin-layout>

    <div class="space-y-6">

        <!-- Header -->
        <div class="flex items-center justify-between">

            <div>
                <h1 class="text-2xl font-bold text-slate-800">
                    Chi tiết chấm công
                </h1>

                <p class="text-slate-500 mt-1">
                    Xem thông tin chi tiết chấm công của nhân viên
                </p>
            </div>

            <div class="flex gap-3">

                <a href="{{ route('admin.attendances.edit', $attendance->id) }}"
                    class="px-4 py-2 bg-amber-500 text-white rounded-xl hover:bg-amber-600 transition">

                    ✏️ Điều chỉnh công

                </a>

                <a href="{{ route('admin.attendances') }}"
                    class="px-4 py-2 bg-slate-600 text-white rounded-xl hover:bg-slate-700 transition">

                    ← Quay lại

                </a>

            </div>

        </div>

        <!-- Summary -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-5">

            <div class="bg-white border rounded-2xl p-5 shadow-sm">
                <p class="text-sm text-slate-500">Ngày công</p>
                <h3 class="text-xl font-bold mt-2">
                    {{ $attendance->attendance_date?->format('d/m/Y') }}
                </h3>
            </div>

            <div class="bg-white border rounded-2xl p-5 shadow-sm">
                <p class="text-sm text-slate-500">Check In</p>
                <h3 class="text-xl font-bold mt-2 text-green-600">
                    {{ $attendance->check_in?->format('H:i') ?? '--:--' }}
                </h3>
            </div>

            <div class="bg-white border rounded-2xl p-5 shadow-sm">
                <p class="text-sm text-slate-500">Check Out</p>
                <h3 class="text-xl font-bold mt-2 text-blue-600">
                    {{ $attendance->check_out?->format('H:i') ?? '--:--' }}
                </h3>
            </div>

            <div class="bg-white border rounded-2xl p-5 shadow-sm">
                <p class="text-sm text-slate-500">Giờ làm</p>
                <h3 class="text-xl font-bold mt-2 text-violet-600">
                    {{ $attendance->work_hours ?? 0 }} giờ
                </h3>
            </div>

        </div>

        <!-- Detail -->
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

            <!-- Employee -->
            <div class="bg-white rounded-2xl shadow-sm border">

                <div class="px-6 py-4 border-b">
                    <h2 class="text-lg font-semibold text-slate-800">
                        👤 Thông tin nhân viên
                    </h2>
                </div>

                <div class="p-6">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                        <div>
                            <p class="text-sm text-slate-500">
                                Mã nhân viên
                            </p>

                            <p class="font-semibold text-slate-800">
                                {{ $attendance->employee?->employee_code }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-slate-500">
                                Họ và tên
                            </p>

                            <p class="font-semibold text-slate-800">
                                {{ $attendance->employee?->full_name }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-slate-500">
                                Phòng ban
                            </p>

                            <p>
                                {{ $attendance->employee?->department?->department_name ?? 'Chưa cập nhật' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-slate-500">
                                Chức vụ
                            </p>

                            <p>
                                {{ $attendance->employee?->position?->position_name ?? 'Chưa cập nhật' }}
                            </p>
                        </div>

                    </div>

                </div>

            </div>

            <!-- Attendance -->
            <div class="bg-white rounded-2xl shadow-sm border">

                <div class="px-6 py-4 border-b">
                    <h2 class="text-lg font-semibold text-slate-800">
                        🕒 Thông tin chấm công
                    </h2>
                </div>

                <div class="p-6">

                    <div class="space-y-5">

                        <div class="flex justify-between">
                            <span class="text-slate-500">
                                Ca làm việc
                            </span>

                            <span class="font-medium">
                                {{ $attendance->employeeShift?->shift?->shift_name ?? '-' }}
                            </span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-slate-500">
                                Check In
                            </span>

                            <span class="font-medium">
                                {{ $attendance->check_in?->format('H:i:s') ?? '--:--:--' }}
                            </span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-slate-500">
                                Check Out
                            </span>

                            <span class="font-medium">
                                {{ $attendance->check_out?->format('H:i:s') ?? '--:--:--' }}
                            </span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-slate-500">
                                Số giờ làm
                            </span>

                            <span class="font-medium">
                                {{ $attendance->work_hours ?? 0 }} giờ
                            </span>
                        </div>

                        <div class="flex justify-between items-center">

                            <span class="text-slate-500">
                                Trạng thái
                            </span>

                            @switch($attendance->status)

                                @case('present')
                                    <span class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-sm font-medium">
                                        Đi làm
                                    </span>
                                @break

                                @case('late')
                                    <span class="px-3 py-1 rounded-full bg-yellow-100 text-yellow-700 text-sm font-medium">
                                        Đi muộn
                                    </span>
                                @break

                                @case('leave')
                                    <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-sm font-medium">
                                        Nghỉ phép
                                    </span>
                                @break

                                @case('absent')
                                    <span class="px-3 py-1 rounded-full bg-red-100 text-red-700 text-sm font-medium">
                                        Vắng mặt
                                    </span>
                                @break

                                @default
                                    <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-700 text-sm font-medium">
                                        Không xác định
                                    </span>

                            @endswitch

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</x-admin-layout>