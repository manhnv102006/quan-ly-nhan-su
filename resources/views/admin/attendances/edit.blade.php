<x-admin-layout>

    <div class="space-y-6">

        <div class="flex items-center justify-between">

            <div>
                <h1 class="text-2xl font-bold text-slate-800">
                    Điều chỉnh chấm công
                </h1>

                <p class="text-slate-500">
                    Cập nhật dữ liệu chấm công nhân viên
                </p>
            </div>

            <a href="{{ route('admin.attendances.show', $attendance) }}"
                class="px-4 py-2 bg-slate-600 text-white rounded-xl hover:bg-slate-700">
                Quay lại
            </a>

        </div>

        <div class="bg-white rounded-2xl border shadow-sm p-6">

            <form
                action="{{ route('admin.attendances.update', $attendance) }}"
                method="POST">

                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

                    <div>
                        <label class="block mb-2 font-medium">
                            Nhân viên
                        </label>

                        <input
                            type="text"
                            value="{{ $attendance->employee->full_name }}"
                            readonly
                            class="w-full rounded-xl border px-4 py-2 bg-slate-100">
                    </div>

                    <div>
                        <label class="block mb-2 font-medium">
                            Trạng thái
                        </label>

                        <select
                            name="status"
                            class="w-full rounded-xl border px-4 py-2">

                            <option value="present"
                                @selected($attendance->status == 'present')>
                                Đi làm
                            </option>

                            <option value="late"
                                @selected($attendance->status == 'late')>
                                Đi muộn
                            </option>

                            <option value="leave"
                                @selected($attendance->status == 'leave')>
                                Nghỉ phép
                            </option>

                            <option value="absent"
                                @selected($attendance->status == 'absent')>
                                Vắng mặt
                            </option>

                        </select>
                    </div>

                    <div>
                        <label class="block mb-2 font-medium">
                            Check In
                        </label>

                        <input
                            type="datetime-local"
                            name="check_in"
                            value="{{ optional($attendance->check_in)->format('Y-m-d\TH:i') }}"
                            class="w-full rounded-xl border px-4 py-2">
                    </div>

                    <div>
                        <label class="block mb-2 font-medium">
                            Check Out
                        </label>

                        <input
                            type="datetime-local"
                            name="check_out"
                            value="{{ optional($attendance->check_out)->format('Y-m-d\TH:i') }}"
                            class="w-full rounded-xl border px-4 py-2">
                    </div>

                    <div>
                        <label class="block mb-2 font-medium">
                            Giờ làm
                        </label>

                        <input
                            type="number"
                            step="0.5"
                            name="work_hours"
                            value="{{ $attendance->work_hours }}"
                            class="w-full rounded-xl border px-4 py-2">
                    </div>

                </div>

                <div class="mt-6 flex gap-3">

                    <button
                        type="submit"
                        class="px-5 py-2 bg-violet-600 text-white rounded-xl hover:bg-violet-700">

                        Lưu thay đổi

                    </button>

                </div>

            </form>

        </div>

    </div>

</x-admin-layout>