<x-admin-layout>

    <div class="space-y-6">

        <div class="flex justify-between items-center">

            <div>
                <h1 class="text-2xl font-bold text-slate-800">
                    Quản lý ca làm việc
                </h1>

                <p class="text-slate-500">
                    Danh sách ca làm việc trong công ty
                </p>
            </div>

            <a href="{{ route('admin.shifts.create') }}" class="px-4 py-2 bg-violet-600 text-white rounded-xl">

                + Thêm ca làm việc

            </a>

        </div>

        <div class="bg-white rounded-2xl border shadow-sm overflow-hidden">

            <table class="w-full">

                <thead class="bg-slate-50">

                    <tr>

                        <th class="px-5 py-3 text-left">STT</th>
                        <th class="px-5 py-3 text-left">Tên ca</th>
                        <th class="px-5 py-3 text-left">Bắt đầu</th>
                        <th class="px-5 py-3 text-left">Kết thúc</th>
                        <th class="px-5 py-3 text-center">Thao tác</th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($shifts as $index => $shift)

                    <tr class="border-t">

                        <td class="px-5 py-4">
                            {{ $shifts->firstItem() + $index }}
                        </td>

                        <td class="px-5 py-4 font-medium">
                            {{ $shift->shift_name }}
                        </td>

                        <td class="px-5 py-4">
                            {{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }}
                        </td>

                        <td class="px-5 py-4">
                            {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }}
                        </td>

                        <td class="px-5 py-4">

                            <div class="flex justify-center gap-2">

                                <a href="{{ route('admin.shifts.edit', $shift) }}"
                                    class="px-3 py-1 bg-yellow-500 text-white rounded-lg">

                                    Sửa

                                </a>
                                <a href="{{ route('admin.employee-shifts.index') }}" class="px-3 py-1 bg-blue-600 text-white rounded-lg">
                                    Gán nhân viên
                                </a>


                                <form action="{{ route('admin.shifts.destroy', $shift) }}" method="POST">

                                    @csrf
                                    @method('DELETE')

                                    <button onclick="return confirm('Xóa ca làm việc này?')"
                                        class="px-3 py-1 bg-red-600 text-white rounded-lg">

                                        Xóa

                                    </button>


                                </form>

                            </div>

                        </td>

                    </tr>

                    @empty

                    <tr>

                        <td colspan="5" class="text-center py-8 text-slate-500">

                            Chưa có ca làm việc

                        </td>

                    </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        {{ $shifts->links() }}

    </div>

</x-admin-layout>