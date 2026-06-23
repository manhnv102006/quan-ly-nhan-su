<x-admin-layout>

    <div class="bg-white rounded-2xl p-6 shadow-sm">

        <h1 class="text-2xl font-bold mb-6">
            Thêm ca làm việc
        </h1>

        <form
        action="{{ route('admin.shifts.store') }}"
        method="POST">

            @csrf

            <div class="space-y-5">

                <div>
                    <label>Tên ca</label>

                    <input
                        type="text"
                        name="shift_name"
                        class="w-full border rounded-xl p-3">
                </div>

                <div>
                    <label>Giờ bắt đầu</label>

                    <input
                        type="time"
                        name="start_time"
                        class="w-full border rounded-xl p-3">
                </div>

                <div>
                    <label>Giờ kết thúc</label>

                    <input
                        type="time"
                        name="end_time"
                        class="w-full border rounded-xl p-3">
                </div>

                <button
                    class="px-5 py-2 bg-violet-600 text-white rounded-xl">

                    Lưu

                </button>

            </div>

        </form>

    </div>

</x-admin-layout>