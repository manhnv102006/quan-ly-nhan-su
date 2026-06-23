<x-admin-layout>

    <div class="space-y-6">

        <div>
            <h1 class="text-2xl font-bold">
                Quản lý nghỉ phép
            </h1>

            <p class="text-slate-500">
                Duyệt và quản lý đơn nghỉ phép
            </p>
        </div>

        <div class="grid grid-cols-4 gap-4">

            <div class="bg-white p-5 rounded-xl border">
                <p>Tổng đơn</p>
                <h3 class="text-3xl font-bold">
                    {{ $stats['total'] }}
                </h3>
            </div>

            <div class="bg-white p-5 rounded-xl border">
                <p>Chờ duyệt</p>
                <h3 class="text-3xl font-bold text-yellow-500">
                    {{ $stats['pending'] }}
                </h3>
            </div>

            <div class="bg-white p-5 rounded-xl border">
                <p>Đã duyệt</p>
                <h3 class="text-3xl font-bold text-green-600">
                    {{ $stats['approved'] }}
                </h3>
            </div>

            <div class="bg-white p-5 rounded-xl border">
                <p>Từ chối</p>
                <h3 class="text-3xl font-bold text-red-600">
                    {{ $stats['rejected'] }}
                </h3>
            </div>

        </div>

        <div class="bg-white rounded-xl border overflow-hidden">

            <table class="w-full">

                <thead class="bg-slate-100">

                    <tr>
                        <th class="p-3 text-left">Nhân viên</th>
                        <th class="p-3 text-left">Phòng ban</th>
                        <th class="p-3 text-left">Loại nghỉ</th>
                        <th class="p-3 text-left">Từ ngày</th>
                        <th class="p-3 text-left">Đến ngày</th>
                        <th class="p-3 text-left">Trạng thái</th>
                        <th class="p-3 text-center">Thao tác</th>
                    </tr>

                </thead>

                <tbody>

                    @foreach($leaveRequests as $request)

                    <tr class="border-t">

                        <td class="p-3">
                            {{ $request->employee?->full_name }}
                        </td>

                        <td class="p-3">
                            {{ $request->employee?->department?->department_name }}
                        </td>

                        <td class="p-3">
                            {{ $request->leave_type }}
                        </td>

                        <td class="p-3">
                            {{ $request->start_date->format('d/m/Y') }}
                        </td>

                        <td class="p-3">
                            {{ $request->end_date->format('d/m/Y') }}
                        </td>

                        <td class="p-3">

                            @if($request->status == 'pending')
                                <span class="badge bg-warning">
                                    Chờ duyệt
                                </span>
                            @elseif($request->status == 'approved')
                                <span class="badge bg-success">
                                    Đã duyệt
                                </span>
                            @else
                                <span class="badge bg-danger">
                                    Từ chối
                                </span>
                            @endif

                        </td>

                        <td class="p-3 text-center">

                            <a
                                href="{{ route('admin.leave-requests.show',$request) }}"
                                class="btn btn-primary btn-sm">

                                Chi tiết

                            </a>

                        </td>

                    </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

        {{ $leaveRequests->links() }}

    </div>

</x-admin-layout>