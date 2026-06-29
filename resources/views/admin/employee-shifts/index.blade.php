<x-admin-layout>

<div class="flex justify-between items-center mb-6">

    <h1 class="text-2xl font-bold">
        Gán ca làm
    </h1>

    <a href="{{ route('admin.employee-shifts.create') }}"
       class="bg-violet-600 text-white px-4 py-2 rounded-lg">

        + Gán ca

    </a>

</div>

<table class="w-full bg-white rounded-xl">

    <thead>

        <tr class="border-b">

            <th class="p-3">Nhân viên</th>

            <th>Ca</th>

            <th>Ngày</th>

        </tr>

    </thead>

    <tbody>

        @foreach($employeeShifts as $item)

        <tr class="border-b">

            <td class="p-3">

                {{ $item->employee->full_name }}

            </td>

            <td>

                {{ $item->shift->shift_name }}

            </td>

            <td>

                {{ $item->work_date->format('d/m/Y') }}

            </td>

        </tr>

        @endforeach

    </tbody>

</table>

{{ $employeeShifts->links() }}

</x-admin-layout>