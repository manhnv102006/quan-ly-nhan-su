<x-admin-layout title="Nhân viên đã xóa">

    <div class="space-y-6">

        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Nhân viên đã xóa</h2>
                <p class="text-sm text-slate-500 mt-1">
                    Danh sách nhân viên đã bị xóa mềm — chỉ xem trong thùng rác
                </p>
            </div>

            <a href="{{ route('admin.employees') }}"
               class="inline-flex items-center gap-2 px-5 py-3 rounded-xl border border-slate-200 bg-white text-slate-600 font-medium hover:bg-slate-50 transition">
                ← Quay lại danh sách
            </a>
        </div>

        <div class="flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4">
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-700 text-sm font-bold">i</span>
            <p class="text-sm text-amber-800">
                Đây là <strong>bước 1</strong> của tính năng xóa mềm: trang thùng rác để xem nhân viên đã xóa.
                Chức năng <strong>xóa mềm từ danh sách</strong>, <strong>khôi phục</strong> và <strong>xóa vĩnh viễn</strong> sẽ được thêm ở bước tiếp theo.
            </p>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100">
                <form action="{{ route('admin.employees.trash') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="md:col-span-2">
                        <label for="search" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Tìm kiếm</label>
                        <input id="search" name="search" type="text" value="{{ $search }}"
                               placeholder="Mã NV, họ tên, email hoặc số điện thoại"
                               class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 focus:border-violet-400 focus:ring-violet-100 focus:outline-none">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="inline-flex w-full items-center justify-center gap-2 px-5 py-3 rounded-xl bg-violet-600 text-white font-medium hover:bg-violet-700 transition">
                            Tìm kiếm
                        </button>
                    </div>
                </form>
            </div>

            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-semibold text-slate-800">Nhân viên trong thùng rác</h3>
                <span class="text-sm text-slate-500">{{ $employees->total() }} bản ghi</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px]">
                    <thead>
                        <tr class="bg-slate-50">
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Mã NV</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Họ tên</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Phòng ban</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Chức vụ</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Email</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Trạng thái</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Ngày xóa</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($employees as $employee)
                            <tr class="border-t border-slate-100 hover:bg-slate-50 transition">
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-lg bg-slate-100 text-slate-700 text-sm font-mono">
                                        {{ $employee->employee_code }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-semibold text-slate-800">{{ $employee->full_name }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $employee->department?->department_name ?? '—' }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $employee->position?->position_name ?? '—' }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $employee->email }}</td>
                                <td class="px-6 py-4">
                                    @if ($employee->status === 'active')
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">Hoạt động</span>
                                    @elseif ($employee->status === 'inactive')
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">Tạm khóa</span>
                                    @else
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-700">Đã nghỉ</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-slate-500">{{ $employee->deleted_at?->format('d/m/Y H:i') ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-16 text-center">
                                    <div class="mx-auto w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center">
                                        <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </div>
                                    <p class="mt-4 text-slate-500 font-medium">Thùng rác đang trống</p>
                                    <p class="mt-1 text-sm text-slate-400">Chưa có nhân viên nào bị xóa mềm</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($employees->hasPages())
                <div class="px-6 py-4 border-t border-slate-100">
                    {{ $employees->links() }}
                </div>
            @endif
        </div>

    </div>

</x-admin-layout>
