<x-admin-layout title="Quản lý phòng ban">

    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex items-center justify-between">

            <div>
                <h2 class="text-2xl font-bold text-slate-800">
                    Danh sách phòng ban
                </h2>

                <p class="text-sm text-slate-500 mt-1">
                    Tổng cộng {{ $departments->count() }} phòng ban
                </p>
            </div>

            <a href="#"
               class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-violet-600 text-white font-medium shadow-lg shadow-violet-500/20 hover:bg-violet-700 transition">

                <span>+</span>
                Thêm phòng ban

            </a>

        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">

                <p class="text-slate-500 text-sm">
                    Tổng phòng ban
                </p>

                <h3 class="text-3xl font-bold mt-2">
                    {{ $departments->count() }}
                </h3>

            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">

                <p class="text-slate-500 text-sm">
                    Đang hoạt động
                </p>

                <h3 class="text-3xl font-bold mt-2 text-emerald-600">
                    {{ $departments->count() }}
                </h3>

            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">

                <p class="text-slate-500 text-sm">
                    Quản lý
                </p>

                <h3 class="text-3xl font-bold mt-2 text-violet-600">
                    {{ $departments->whereNotNull('manager_id')->count() }}
                </h3>

            </div>

        </div>

        {{-- Table --}}
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">

            <div class="px-6 py-5 border-b border-slate-100">

                <h3 class="font-semibold text-slate-800">
                    Danh sách phòng ban
                </h3>

            </div>

            <div class="overflow-x-auto">

                <table class="w-full">

                    <thead>

                        <tr class="bg-slate-50">

                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">
                                ID
                            </th>

                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">
                                Mã PB
                            </th>

                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">
                                Tên phòng ban
                            </th>

                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">
                                Mô tả
                            </th>

                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">
                                Quản lý
                            </th>

                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">
                                Trạng thái
                            </th>

                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">
                                Ngày tạo
                            </th>

                            <th class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-500">
                                Hành động
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($departments as $dept)

                        <tr class="border-t border-slate-100 hover:bg-slate-50 transition">

                            <td class="px-6 py-4">
                                {{ $dept->id }}
                            </td>

                            <td class="px-6 py-4">

                                <span class="px-3 py-1 rounded-lg bg-slate-100 text-slate-700 text-sm">
                                    {{ $dept->department_code }}
                                </span>

                            </td>

                            <td class="px-6 py-4 font-semibold text-slate-800">
                                {{ $dept->department_name }}
                            </td>

                            <td class="px-6 py-4 text-slate-500">
                                {{ $dept->description }}
                            </td>

                            <td class="px-6 py-4">
                                {{ $dept->manager_id ?? 'Chưa chỉ định' }}
                            </td>

                            <td class="px-6 py-4">

                                <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
                                    Active
                                </span>

                            </td>

                            <td class="px-6 py-4 text-slate-500">
                                {{ $dept->created_at?->format('d/m/Y') }}
                            </td>

                            <td class="px-6 py-4">

                                <div class="flex justify-center gap-2">

                                    <a href="{{ route('admin.departments.detail',$dept->id) }}"
                                       class="w-9 h-9 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center hover:bg-blue-200">

                                        👁

                                    </a>

                                    <form action="{{ route('admin.departments.delete',$dept->id) }}"
                                          method="POST">

                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            onclick="return confirm('Bạn có chắc muốn xóa?')"
                                            class="w-9 h-9 rounded-lg bg-red-100 text-red-600 hover:bg-red-200">

                                            🗑

                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                        @empty

                        <tr>

                            <td colspan="8"
                                class="text-center py-12 text-slate-400">

                                Chưa có phòng ban nào

                            </td>

                        </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</x-admin-layout>