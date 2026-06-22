<x-admin-layout>

    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">
                    Quản lý chấm công
                </h1>

                <p class="text-slate-500 mt-1">
                    Theo dõi và quản lý dữ liệu chấm công của nhân viên
                </p>
            </div>

            <a href="#"
                class="px-4 py-2 bg-violet-600 text-white rounded-xl hover:bg-violet-700">
                + Thêm chấm công
            </a>
        </div>

        {{-- Thống kê --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-5">

            <div class="bg-white rounded-2xl border shadow-sm p-5">
                <p class="text-sm text-slate-500">
                    Tổng bản ghi
                </p>

                <h3 class="text-3xl font-bold mt-2">
                    {{ $stats['total'] }}
                </h3>
            </div>

            <div class="bg-white rounded-2xl border shadow-sm p-5">
                <p class="text-sm text-slate-500">
                    Đi làm
                </p>

                <h3 class="text-3xl font-bold text-green-600 mt-2">
                    {{ $stats['present'] }}
                </h3>
            </div>

            <div class="bg-white rounded-2xl border shadow-sm p-5">
                <p class="text-sm text-slate-500">
                    Đi muộn
                </p>

                <h3 class="text-3xl font-bold text-yellow-600 mt-2">
                    {{ $stats['late'] }}
                </h3>
            </div>

            <div class="bg-white rounded-2xl border shadow-sm p-5">
                <p class="text-sm text-slate-500">
                    Nghỉ phép
                </p>

                <h3 class="text-3xl font-bold text-red-600 mt-2">
                    {{ $stats['leave'] }}
                </h3>
            </div>

        </div>

        {{-- Danh sách --}}
        <div class="bg-white rounded-2xl border shadow-sm overflow-hidden">

            <div class="px-6 py-4 border-b flex items-center justify-between">

                <h2 class="font-semibold text-slate-800">
                    Danh sách chấm công
                </h2>

                <span class="text-sm text-slate-500">
                    Tổng: {{ $attendances->total() }} bản ghi
                </span>

            </div>

            <div class="overflow-x-auto">

                <table class="min-w-full">

                    <thead class="bg-slate-50">

                        <tr>

                            <th class="px-6 py-3 text-left text-sm font-semibold text-slate-600">
                                STT
                            </th>

                            <th class="px-6 py-3 text-left text-sm font-semibold text-slate-600">
                                Nhân viên
                            </th>

                            <th class="px-6 py-3 text-left text-sm font-semibold text-slate-600">
                                Phòng ban
                            </th>

                            <th class="px-6 py-3 text-left text-sm font-semibold text-slate-600">
                                Ngày
                            </th>

                            <th class="px-6 py-3 text-left text-sm font-semibold text-slate-600">
                                Ca làm
                            </th>

                            <th class="px-6 py-3 text-left text-sm font-semibold text-slate-600">
                                Check In
                            </th>

                            <th class="px-6 py-3 text-left text-sm font-semibold text-slate-600">
                                Check Out
                            </th>

                            <th class="px-6 py-3 text-left text-sm font-semibold text-slate-600">
                                Trạng thái
                            </th>

                            <th class="px-6 py-3 text-left text-sm font-semibold text-slate-600">
                                Giờ làm
                            </th>

                            <th class="px-6 py-3 text-center text-sm font-semibold text-slate-600">
                                Thao tác
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse ($attendances as $index => $attendance)

                            <tr class="border-t hover:bg-slate-50 transition">

                                <td class="px-6 py-4">
                                    {{ $attendances->firstItem() + $index }}
                                </td>

                                <td class="px-6 py-4 font-medium text-slate-800">
                                    {{ $attendance->employee?->full_name }}
                                </td>

                                <td class="px-6 py-4">
                                    {{ $attendance->employee?->department?->department_name ?? '-' }}
                                </td>

                                <td class="px-6 py-4">
                                    {{ $attendance->attendance_date?->format('d/m/Y') }}
                                </td>

                                <td class="px-6 py-4">
                                    {{ $attendance->shift?->shift_name ?? '-' }}
                                </td>

                                <td class="px-6 py-4">
                                    {{ $attendance->check_in?->format('H:i') ?? '-' }}
                                </td>

                                <td class="px-6 py-4">
                                    {{ $attendance->check_out?->format('H:i') ?? '-' }}
                                </td>

                                <td class="px-6 py-4">

                                    @switch($attendance->status)

                                        @case('present')
                                            <span
                                                class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs font-semibold">
                                                Đi làm
                                            </span>
                                        @break

                                        @case('late')
                                            <span
                                                class="px-3 py-1 rounded-full bg-yellow-100 text-yellow-700 text-xs font-semibold">
                                                Đi muộn
                                            </span>
                                        @break

                                        @case('leave')
                                            <span
                                                class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-semibold">
                                                Nghỉ phép
                                            </span>
                                        @break

                                        @case('absent')
                                            <span
                                                class="px-3 py-1 rounded-full bg-red-100 text-red-700 text-xs font-semibold">
                                                Vắng mặt
                                            </span>
                                        @break

                                        @default
                                            <span
                                                class="px-3 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-semibold">
                                                Không xác định
                                            </span>
                                    @endswitch

                                </td>

                                <td class="px-6 py-4 font-medium">
                                    {{ $attendance->work_hours ?? 0 }} giờ
                                </td>

                                <td class="px-6 py-4 text-center">

                                    <a href="#"
                                        class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">

                                        Chi tiết

                                    </a>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="10"
                                    class="py-10 text-center text-slate-400">

                                    Chưa có dữ liệu chấm công

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            <div class="px-6 py-4 border-t bg-slate-50">
                {{ $attendances->links() }}
            </div>

        </div>

    </div>

</x-admin-layout>