<x-admin-layout>

    <div class="space-y-6">

        <div>
            <h1 class="text-2xl font-bold">
                Duyệt đơn tăng ca
            </h1>

            <p class="text-slate-500">
                Quản lý các đơn đăng ký tăng ca
            </p>
        </div>

        <div class="grid grid-cols-4 gap-4">

            <div class="bg-white p-5 rounded-xl border">
                <p>Tổng đơn</p>
                <h3 class="text-3xl font-bold">
                    {{ $stats['total'] }}
                </h3>
            </div>

            <div class="bg-yellow-50 p-5 rounded-xl border">
                <p>Chờ duyệt</p>
                <h3 class="text-3xl font-bold">
                    {{ $stats['pending'] }}
                </h3>
            </div>

            <div class="bg-green-50 p-5 rounded-xl border">
                <p>Đã duyệt</p>
                <h3 class="text-3xl font-bold">
                    {{ $stats['approved'] }}
                </h3>
            </div>

            <div class="bg-red-50 p-5 rounded-xl border">
                <p>Từ chối</p>
                <h3 class="text-3xl font-bold">
                    {{ $stats['rejected'] }}
                </h3>
            </div>

        </div>

        <div class="bg-white rounded-xl border overflow-hidden">

            <table class="w-full">

                <thead class="bg-slate-50">
                    <tr>
                        <th class="p-3 text-left">Nhân viên</th>
                        <th class="p-3 text-left">Ngày</th>
                        <th class="p-3 text-left">Giờ bắt đầu</th>
                        <th class="p-3 text-left">Giờ kết thúc</th>
                        <th class="p-3 text-left">Trạng thái</th>
                        <th class="p-3 text-center">Thao tác</th>
                    </tr>
                </thead>

                <tbody>

                    @foreach($overtimeRequests as $item)

                    <tr class="border-t">

                        <td class="p-3">
                            {{ $item->employee?->full_name }}
                        </td>

                        <td class="p-3">
                            {{ $item->work_date?->format('d/m/Y') }}
                        </td>

                        <td class="p-3">
                            {{ $item->start_time }}
                        </td>

                        <td class="p-3">
                            {{ $item->end_time }}
                        </td>

                        <td class="p-3">
                            {{ $item->status }}
                        </td>

                        <td class="p-3 text-center">

                            <a href="{{ route('admin.overtime-requests.show',$item) }}"
                                class="text-blue-600">

                                Chi tiết

                            </a>

                        </td>

                    </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

        {{ $overtimeRequests->links() }}

    </div>

</x-admin-layout>