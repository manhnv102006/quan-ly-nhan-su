<x-admin-layout>

    <div class="space-y-6">
    
        <div>
            <h1 class="text-2xl font-bold text-slate-800">
                Quản lý chấm công
            </h1>
    
            <p class="text-slate-500 mt-1">
                Danh sách chấm công nhân viên
            </p>
        </div>
    
        <div class="bg-white rounded-2xl shadow border overflow-hidden">
    
            <div class="px-6 py-4 border-b">
                <h2 class="font-semibold">
                    Danh sách chấm công
                </h2>
            </div>
    
            <table class="w-full">
    
                <thead class="bg-slate-50">
    
                <tr>
    
                    <th class="px-6 py-3 text-left">
                        Nhân viên
                    </th>
    
                    <th class="px-6 py-3 text-left">
                        Phòng ban
                    </th>
    
                    <th class="px-6 py-3 text-left">
                        Ngày
                    </th>
    
                    <th class="px-6 py-3 text-left">
                        Ca làm
                    </th>
    
                    <th class="px-6 py-3 text-left">
                        Check In
                    </th>
    
                    <th class="px-6 py-3 text-left">
                        Check Out
                    </th>
    
                </tr>
    
                </thead>
    
                <tbody>
    
                @forelse($attendances as $attendance)
    
                    <tr class="border-t">
    
                        <td class="px-6 py-4">
                            {{ $attendance->employee?->full_name }}
                        </td>
    
                        <td class="px-6 py-4">
                            {{ $attendance->employee?->department?->department_name }}
                        </td>
    
                        <td class="px-6 py-4">
                            {{ $attendance->attendance_date?->format('d/m/Y') }}
                        </td>
    
                        <td class="px-6 py-4">
                            {{ $attendance->shift?->shift_name }}
                        </td>
    
                        <td class="px-6 py-4">
                            {{ $attendance->check_in?->format('H:i') }}
                        </td>
    
                        <td class="px-6 py-4">
                            {{ $attendance->check_out?->format('H:i') }}
                        </td>
    
                    </tr>
    
                @empty
    
                    <tr>
    
                        <td colspan="6"
                            class="text-center py-8 text-slate-400">
    
                            Chưa có dữ liệu
    
                        </td>
    
                    </tr>
    
                @endforelse
    
                </tbody>
    
            </table>
    
            <div class="p-5 border-t">
    
                {{ $attendances->links() }}
    
            </div>
    
        </div>
    
    </div>
    
    </x-admin-layout>