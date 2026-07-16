<x-leader-layout
    title="Chi tiết hợp đồng"
    subtitle="Thông tin hợp đồng nhân viên">

    <div class="space-y-6">

        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">
                    {{ $contract->contract_code }}
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    {{ $contract->employee->full_name }}
                </p>
            </div>

            <a
                href="{{ route('leader.contracts.index') }}"
                class="admin-btn-secondary">
                Quay lại
            </a>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">

            <div class="admin-card p-6">
                <h3 class="mb-4 text-lg font-semibold">
                    Thông tin hợp đồng
                </h3>

                <dl class="space-y-3 text-sm">

                    <div class="flex justify-between">
                        <dt class="font-medium text-slate-500">
                            Mã hợp đồng
                        </dt>

                        <dd>{{ $contract->contract_code }}</dd>
                    </div>

                    <div class="flex justify-between">
                        <dt class="font-medium text-slate-500">
                            Nhân viên
                        </dt>

                        <dd>{{ $contract->employee->full_name }}</dd>
                    </div>

                    <div class="flex justify-between">
                        <dt class="font-medium text-slate-500">
                            Phòng ban
                        </dt>

                        <dd>{{ $contract->department->department_name ?? '-' }}</dd>
                    </div>

                    <div class="flex justify-between">
                        <dt class="font-medium text-slate-500">
                            Chức vụ
                        </dt>

                        <dd>{{ $contract->position->position_name ?? '-' }}</dd>
                    </div>

                    <div class="flex justify-between">
                        <dt class="font-medium text-slate-500">
                            Loại hợp đồng
                        </dt>

                        <dd>{{ $contract->contractType->contract_name ?? '-' }}</dd>
                    </div>

                    <div class="flex justify-between">
                        <dt class="font-medium text-slate-500">
                            Ngày bắt đầu
                        </dt>

                        <dd>{{ optional($contract->start_date)->format('d/m/Y') }}</dd>
                    </div>

                    <div class="flex justify-between">
                        <dt class="font-medium text-slate-500">
                            Ngày kết thúc
                        </dt>

                        <dd>
                            {{ optional($contract->end_date)->format('d/m/Y') ?? 'Không thời hạn' }}
                        </dd>
                    </div>

                    <div class="flex justify-between">
                        <dt class="font-medium text-slate-500">
                            Lương cơ bản
                        </dt>

                        <dd>
                            {{ number_format($contract->basic_salary) }} VNĐ
                        </dd>
                    </div>

                    <div class="flex justify-between">
                        <dt class="font-medium text-slate-500">
                            Trạng thái
                        </dt>

                        <dd>
                            @include('admin.contracts.partials.status-badge',[
                                'contract'=>$contract
                            ])
                        </dd>
                    </div>

                </dl>
            </div>

            <div class="admin-card p-6">

                <h3 class="mb-4 text-lg font-semibold">
                    Phụ cấp
                </h3>

                <div class="space-y-3">

                    @forelse($allowanceBreakdown as $allowance)

                        <div class="flex justify-between text-sm">

                            <span>
                                {{ $allowance['label'] }}
                            </span>

                            <span class="font-semibold">
                                {{ number_format($allowance['amount']) }} VNĐ
                            </span>

                        </div>

                    @empty

                        <p class="text-sm text-slate-500">
                            Không có phụ cấp.
                        </p>

                    @endforelse

                    <hr>

                    <div class="flex justify-between font-bold">

                        <span>Tổng phụ cấp</span>

                        <span>
                            {{ number_format($totalAllowance) }} VNĐ
                        </span>

                    </div>

                </div>

            </div>

        </div>

        <div class="admin-card p-6">

            <h3 class="mb-4 text-lg font-semibold">
                Lịch sử hợp đồng
            </h3>

            <div class="overflow-x-auto">

                <table class="w-full text-sm">

                    <thead>

                    <tr class="bg-slate-50">

                        <th class="px-4 py-3 text-left">
                            Mã HĐ
                        </th>

                        <th class="px-4 py-3 text-left">
                            Thời gian
                        </th>

                        <th class="px-4 py-3 text-left">
                            Trạng thái
                        </th>

                    </tr>

                    </thead>

                    <tbody>

                    @foreach($history as $item)

                        <tr class="border-t">

                            <td class="px-4 py-3">
                                {{ $item->contract_code }}
                            </td>

                            <td class="px-4 py-3">
                                {{ optional($item->start_date)->format('d/m/Y') }}
                                -
                                {{ optional($item->end_date)->format('d/m/Y') ?? 'Không thời hạn' }}
                            </td>

                            <td class="px-4 py-3">

                                @include('admin.contracts.partials.status-badge',[
                                    'contract'=>$item
                                ])

                            </td>

                        </tr>

                    @endforeach

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</x-leader-layout>