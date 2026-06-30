<x-admin-layout>

<h1 class="text-2xl font-bold mb-6">
    Gán ca làm
</h1>

<form method="POST"
      action="{{ route('admin.employee-shifts.store') }}">

    @csrf

    <div class="mb-4">

        <label>Nhân viên</label>

        <select
            name="employee_id"
            class="w-full border rounded-lg p-2">

            @foreach($employees as $employee)

            <option value="{{ $employee->id }}">

                {{ $employee->full_name }}

            </option>

            @endforeach

        </select>

    </div>

    <div class="mb-4">

        <label>Ca làm</label>

        <select
            name="shift_id"
            class="w-full border rounded-lg p-2">

            @foreach($shifts as $shift)

            <option value="{{ $shift->id }}">

                {{ $shift->shift_name }}

            </option>

            @endforeach

        </select>

    </div>

    <div class="mb-4">

        <label>Ngày làm</label>

        <input
            type="date"
            name="work_date"
            class="w-full border rounded-lg p-2">

    </div>

    <button
        class="bg-violet-600 text-white px-5 py-2 rounded-lg">

        Lưu

    </button>

</form>

</x-admin-layout>