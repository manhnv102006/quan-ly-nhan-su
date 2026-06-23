<x-admin-layout>

    ```
    <div class="space-y-6">
    
        <div>
            <h1 class="text-2xl font-bold text-gray-800">
                Báo cáo chấm công
            </h1>
    
            <p class="text-gray-500">
                Thống kê chấm công tháng {{ $month }}/{{ $year }}
            </p>
        </div>
    
        <form
            method="GET"
            class="bg-white border rounded-xl p-4 flex gap-3 items-center">
    
            <select
                name="month"
                class="border rounded-lg px-3 py-2">
    
                @for($i = 1; $i <= 12; $i++)
                    <option
                        value="{{ $i }}"
                        @selected($month == $i)>
                        Tháng {{ $i }}
                    </option>
                @endfor
    
            </select>
    
            <input
                type="number"
                name="year"
                value="{{ $year }}"
                class="border rounded-lg px-3 py-2">
    
            <button
                type="submit"
                class="px-4 py-2 bg-violet-600 text-white rounded-lg">
    
                Lọc
    
            </button>
    
        </form>
    
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
    
            <div class="bg-green-50 border rounded-xl p-5">
                <p class="text-gray-500">
                    Đi làm
                </p>
    
                <h2 class="text-3xl font-bold text-green-600">
                    {{ $stats['present'] }}
                </h2>
            </div>
    
            <div class="bg-yellow-50 border rounded-xl p-5">
                <p class="text-gray-500">
                    Đi muộn
                </p>
    
                <h2 class="text-3xl font-bold text-yellow-600">
                    {{ $stats['late'] }}
                </h2>
            </div>
    
            <div class="bg-blue-50 border rounded-xl p-5">
                <p class="text-gray-500">
                    Nghỉ phép
                </p>
    
                <h2 class="text-3xl font-bold text-blue-600">
                    {{ $stats['leave'] }}
                </h2>
            </div>
    
            <div class="bg-red-50 border rounded-xl p-5">
                <p class="text-gray-500">
                    Vắng mặt
                </p>
    
                <h2 class="text-3xl font-bold text-red-600">
                    {{ $stats['absent'] }}
                </h2>
            </div>
    
        </div>
    
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    
            <div class="bg-white border rounded-xl p-5">
                <p class="text-gray-500 mb-2">
                    Tổng giờ làm
                </p>
    
                <h2 class="text-3xl font-bold text-violet-600">
                    {{ number_format($stats['total_hours'], 2) }} giờ
                </h2>
            </div>
    
            <div class="bg-white border rounded-xl p-5">
                <p class="text-gray-500 mb-2">
                    Tổng phút đi muộn
                </p>
    
                <h2 class="text-3xl font-bold text-orange-600">
                    {{ $stats['late_minutes'] }} phút
                </h2>
            </div>
    
            <div class="bg-white border rounded-xl p-5">
                <p class="text-gray-500 mb-2">
                    Đi muộn nhiều nhất
                </p>
    
                <h2 class="text-xl font-bold text-red-600">
                    {{ $stats['top_late_employee'] ?? 'Không có' }}
                </h2>
            </div>
    
        </div>
    
        <div class="bg-white border rounded-xl overflow-hidden">
    
            <div class="p-4 border-b">
                <h3 class="font-semibold">
                    Chi tiết chấm công
                </h3>
            </div>
    
            <div class="overflow-x-auto">
    
                <table class="w-full">
    
                    <thead class="bg-gray-100">
    
                        <tr>
    
                            <th class="p-3 text-left">
                                Nhân viên
                            </th>
    
                            <th class="p-3 text-left">
                                Phòng ban
                            </th>
    
                            <th class="p-3 text-center">
                                Ngày
                            </th>
    
                            <th class="p-3 text-center">
                                Ca làm
                            </th>
    
                            <th class="p-3 text-center">
                                Giờ vào
                            </th>
    
                            <th class="p-3 text-center">
                                Giờ ra
                            </th>
    
                            <th class="p-3 text-center">
                                Đi muộn
                            </th>
    
                            <th class="p-3 text-center">
                                Giờ làm
                            </th>
    
                            <th class="p-3 text-center">
                                Trạng thái
                            </th>
    
                        </tr>
    
                    </thead>
    
                    <tbody>
    
                        @forelse($attendances as $attendance)
    
                            <tr class="border-b hover:bg-gray-50">
    
                                <td class="p-3">
                                    {{ $attendance->employee->full_name }}
                                </td>
    
                                <td class="p-3">
                                    {{ $attendance->employee->department->department_name ?? '-' }}
                                </td>
    
                                <td class="p-3 text-center">
                                    {{ \Carbon\Carbon::parse($attendance->attendance_date)->format('d/m/Y') }}
                                </td>
    
                                <td class="p-3 text-center">
                                    {{ $attendance->shift->shift_name ?? '-' }}
                                </td>
    
                                <td class="p-3 text-center">
                                    {{ $attendance->check_in ? \Carbon\Carbon::parse($attendance->check_in)->format('H:i') : '-' }}
                                </td>
    
                                <td class="p-3 text-center">
                                    {{ $attendance->check_out ? \Carbon\Carbon::parse($attendance->check_out)->format('H:i') : '-' }}
                                </td>
    
                                <td class="p-3 text-center font-bold text-orange-600">
                                    {{ $attendance->late_minutes ?? 0 }} phút
                                </td>
    
                                <td class="p-3 text-center">
                                    {{ $attendance->work_hours ?? 0 }} giờ
                                </td>
    
                                <td class="p-3 text-center">
    
                                    @if($attendance->status == 'present')
    
                                        <span class="px-3 py-1 rounded-full bg-green-100 text-green-700">
                                            Đi làm
                                        </span>
    
                                    @elseif($attendance->status == 'late')
    
                                        <span class="px-3 py-1 rounded-full bg-yellow-100 text-yellow-700">
                                            Đi muộn
                                        </span>
    
                                    @elseif($attendance->status == 'leave')
    
                                        <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-700">
                                            Nghỉ phép
                                        </span>
    
                                    @else
    
                                        <span class="px-3 py-1 rounded-full bg-red-100 text-red-700">
                                            Vắng mặt
                                        </span>
    
                                    @endif
    
                                </td>
    
                            </tr>
    
                        @empty
    
                            <tr>
    
                                <td
                                    colspan="9"
                                    class="text-center p-6 text-gray-500">
    
                                    Không có dữ liệu
    
                                </td>
    
                            </tr>
    
                        @endforelse
    
                    </tbody>
    
                </table>
    
            </div>
    
        </div>
    
    </div>
    ```
    
    </x-admin-layout>
    