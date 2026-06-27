<x-admin-layout>

    <div class="space-y-6">

        <div class="flex justify-between">

            <h1 class="text-2xl font-bold">
                Chi tiết đơn tăng ca
            </h1>

            <a href="{{ route('admin.overtime-requests.index') }}"
                class="px-4 py-2 bg-slate-500 text-white rounded-lg">

                Quay lại

            </a>

        </div>

        <div class="bg-white border rounded-xl p-6">

            <div class="grid grid-cols-2 gap-5">

                <div>
                    <p class="text-slate-500">
                        Nhân viên
                    </p>

                    <h4 class="font-semibold">
                        {{ $overtimeRequest->employee->full_name }}
                    </h4>
                </div>

                <div>
                    <p class="text-slate-500">
                        Phòng ban
                    </p>

                    <h4 class="font-semibold">
                        {{ $overtimeRequest->employee->department?->department_name }}
                    </h4>
                </div>

                <div>
                    <p class="text-slate-500">
                        Ngày tăng ca
                    </p>

                    <h4 class="font-semibold">
                        {{ optional($overtimeRequest->work_date)->format('d/m/Y') }}
                    </h4>
                </div>

                <div>
                    <p class="text-slate-500">
                        Trạng thái
                    </p>

                    <h4 class="font-semibold">
                        {{ $overtimeRequest->status }}
                    </h4>
                </div>

                <div class="col-span-2">

                    <p class="text-slate-500">
                        Lý do
                    </p>

                    <p>
                        {{ $overtimeRequest->reason }}
                    </p>

                </div>

            </div>

            <div class="mt-6 p-4 rounded-lg bg-slate-50 border text-slate-600 text-sm">
                Module đang ở giai đoạn khung cấu trúc. Chưa triển khai nghiệp vụ duyệt/từ chối.
            </div>

        </div>

    </div>

</x-admin-layout>