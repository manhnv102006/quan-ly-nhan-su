<x-admin-layout title="Quản lý chấm công">

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

        <div class="bg-white rounded-2xl border shadow-sm p-5">

            <form method="GET">
        
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        
                    <div>
                        <input
                            type="text"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Tìm nhân viên..."
                            list="employees-list"
                            autocomplete="off"
                            class="w-full border rounded-xl px-4 py-2">
                        <datalist id="employees-list">
                            @foreach($employees as $emp)
                                <option value="{{ $emp->full_name }}">{{ $emp->employee_code }}</option>
                            @endforeach
                        </datalist>
                    </div>
        
                    <div>
                        <input
                            type="date"
                            name="date"
                            value="{{ request('date') }}"
                            class="w-full border rounded-xl px-4 py-2">
                    </div>
        
                    <div>
                        <select
                            name="status"
                            class="w-full border rounded-xl px-4 py-2">
        
                            <option value="">
                                Tất cả trạng thái
                            </option>
        
                            <option value="present"
                                @selected(request('status')=='present')>
                                Đi làm
                            </option>
        
                            <option value="late"
                                @selected(request('status')=='late')>
                                Đi muộn
                            </option>
        
                            <option value="leave"
                                @selected(request('status')=='leave')>
                                Nghỉ phép
                            </option>
        
                            <option value="absent"
                                @selected(request('status')=='absent')>
                                Vắng mặt
                            </option>
        
                        </select>
                    </div>
        
                    <div class="flex gap-2">
        
                        <button
                            class="px-4 py-2 bg-violet-600 text-white rounded-xl">
        
                            Tìm kiếm
        
                        </button>
        
                        <a
                            href="{{ route('admin.attendances') }}"
                            class="px-4 py-2 bg-slate-500 text-white rounded-xl">
        
                            Làm mới
        
                        </a>
        
                    </div>
        
                </div>
        
            </form>
        
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

                                    <a href="{{ route('admin.attendances.show', $attendance) }}"
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

        <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-violet-600">Quản lý chấm công</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-800">Quản lý chấm công</h1>
            <p class="mt-1 text-sm text-slate-500">
                Chọn phòng ban để xem và quản lý dữ liệu chấm công
            </p>

        </div>

        @include('admin.partials.department-cards', [
            'departmentSummaries' => $departmentSummaries,
            'routeName' => 'admin.attendances.department',
            'statLabels' => ['NV chấm công', 'Đi làm', 'Đi muộn'],
            'statKeys' => ['employee_count', 'work_days', 'late'],
            'statTones' => ['slate', 'emerald', 'amber'],
        ])
    </div>

</x-admin-layout>
