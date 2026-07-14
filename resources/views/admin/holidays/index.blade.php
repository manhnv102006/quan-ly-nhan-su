<x-admin-layout title="Quản lý Ngày Lễ">

    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">
                    Danh sách Ngày Lễ / Sự kiện
                </h2>
                <p class="text-sm text-slate-500 mt-1">
                    Cấu hình ngày nghỉ có lương cho toàn công ty
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('admin.holidays.create') }}"
                   class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-violet-600 text-white font-medium shadow-lg shadow-violet-500/20 hover:bg-violet-700 transition">
                    <span>+</span>
                    Thêm Sự Kiện
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-emerald-50 text-emerald-600 p-4 rounded-xl border border-emerald-100 flex items-center gap-3">
                <i class="bi bi-check-circle-fill"></i>
                {{ session('success') }}
            </div>
        @endif

        {{-- Table --}}
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-slate-50">
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Tên sự kiện</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Loại</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Thời gian</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Mô tả</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-500">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($holidays as $holiday)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-slate-800">{{ $holiday->name }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($holiday->type === 'public_holiday')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-emerald-50 text-emerald-700 text-xs font-medium border border-emerald-200/60">
                                            Nghỉ Lễ, Tết
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-blue-50 text-blue-700 text-xs font-medium border border-blue-200/60">
                                            Du lịch / Sự kiện CT
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600">
                                    {{ $holiday->start_date->format('d/m/Y') }} 
                                    @if($holiday->start_date != $holiday->end_date)
                                        - {{ $holiday->end_date->format('d/m/Y') }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-slate-600 max-w-xs truncate">
                                    {{ $holiday->description ?: '--' }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <form action="{{ route('admin.holidays.destroy', $holiday) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa sự kiện này?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 text-rose-500 hover:bg-rose-50 rounded-lg transition" title="Xóa">
                                                <i class="bi bi-trash3"></i> Xóa
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-slate-500">
                                    Chưa có sự kiện / ngày lễ nào.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($holidays->hasPages())
                <div class="px-6 py-4 border-t border-slate-100">
                    {{ $holidays->links() }}
                </div>
            @endif
        </div>

    </div>

</x-admin-layout>
