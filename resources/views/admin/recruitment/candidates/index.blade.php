<x-admin-layout title="Ứng viên">

    <div class="space-y-6">

        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-sm text-slate-500">
                    <a href="{{ route('admin.recruitment') }}" class="hover:text-violet-600 transition">Tuyển dụng</a>
                    <span>/</span>
                    <span class="text-slate-700 font-medium">Ứng viên</span>
                </div>

                <h2 class="mt-2 text-2xl font-bold text-slate-800">Danh sách ứng viên</h2>
                <p class="text-sm text-slate-500 mt-1">
                    Theo dõi hồ sơ ứng viên và trạng thái tuyển dụng hiện tại trong hệ thống.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('admin.recruitment.candidates.create') }}"
                   class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-cyan-600 text-white font-medium hover:bg-cyan-700 transition shadow-lg shadow-cyan-500/20">
                    + Thêm ứng viên
                </a>

                <a href="{{ route('admin.recruitment') }}"
                   class="inline-flex items-center gap-2 px-5 py-3 rounded-xl border border-slate-200 bg-white text-slate-600 font-medium hover:bg-slate-50 transition">
                    Quay lại tuyển dụng
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="flex items-center gap-3 bg-white border border-emerald-200 shadow-sm rounded-2xl px-5 py-4">
                <p class="text-sm font-medium text-emerald-700">{{ session('success') }}</p>
            </div>
        @endif

        @if (session('error'))
            <div class="flex items-center gap-3 bg-white border border-red-200 shadow-sm rounded-2xl px-5 py-4">
                <p class="text-sm font-medium text-red-700">{{ session('error') }}</p>
            </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm text-slate-500">Tổng ứng viên</p>
                <h3 class="text-3xl font-bold mt-2 text-slate-900">{{ $stats['total'] }}</h3>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm text-slate-500">Mới tiếp nhận</p>
                <h3 class="text-3xl font-bold mt-2 text-sky-600">{{ $stats['new'] }}</h3>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm text-slate-500">Đang phỏng vấn</p>
                <h3 class="text-3xl font-bold mt-2 text-amber-600">{{ $stats['interview'] }}</h3>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm text-slate-500">Đạt</p>
                <h3 class="text-3xl font-bold mt-2 text-emerald-600">{{ $stats['passed'] }}</h3>
            </div>
        </div>

        <div class="admin-card p-6">
            <form method="GET" action="{{ route('admin.recruitment.candidates') }}" class="flex flex-col gap-4 lg:flex-row lg:items-end">
                <div class="flex-1">
                    <label for="search" class="block text-sm font-medium text-slate-700 mb-2">
                        Tìm kiếm ứng viên
                    </label>
                    <input type="text" id="search" name="search" value="{{ $search }}"
                        placeholder="Nhập họ tên, điện thoại, email, địa chỉ hoặc tin tuyển dụng"
                        class="w-full rounded-xl border-slate-200 focus:border-violet-500 focus:ring-violet-500">
                </div>

                <div class="flex flex-wrap gap-3">
                    <button type="submit"
                        class="inline-flex items-center justify-center px-5 py-3 rounded-xl bg-violet-600 text-white font-medium hover:bg-violet-700 transition">
                        Tìm kiếm
                    </button>

                    @if ($search !== '')
                        <a href="{{ route('admin.recruitment.candidates') }}"
                            class="inline-flex items-center justify-center px-5 py-3 rounded-xl border border-slate-200 bg-white text-slate-600 font-medium hover:bg-slate-50 transition">
                            Xóa bộ lọc
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <h3 class="font-semibold text-slate-800">Danh sách ứng viên</h3>
                <p class="text-sm text-slate-500">Hiển thị {{ $candidates->count() }} / {{ $candidates->total() }} bản ghi</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-500">Họ tên</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-500">Tin tuyển dụng</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-500">Điện thoại</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-500">Email</th>
                            <th class="px-6 py-3 text-center text-xs font-bold uppercase text-slate-500">CV</th>
                            <th class="px-6 py-3 text-center text-xs font-bold uppercase text-slate-500">Trạng thái</th>
                            <th class="px-6 py-3 text-center text-xs font-bold uppercase text-slate-500">Ngày tạo</th>
                            <th class="px-6 py-3 text-center text-xs font-bold uppercase text-slate-500">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($candidates as $candidate)
                            <tr class="border-b border-slate-200 hover:bg-slate-50 transition">
                                <td class="px-6 py-4">
                                    <a href="{{ route('admin.recruitment.candidates.show', $candidate) }}" class="font-medium text-slate-800 hover:text-cyan-700 transition">
                                        {{ $candidate->full_name }}
                                    </a>
                                    <p class="mt-1 text-sm text-slate-500">{{ $candidate->address }}</p>
                                </td>
                                <td class="px-6 py-4 text-slate-600">{{ $candidate->jobPost?->title ?? 'Chưa gắn tin tuyển dụng' }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $candidate->phone }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $candidate->email }}</td>
                                <td class="px-6 py-4 text-center">
                                    @if ($candidate->cv_file)
                                        <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Đã có CV</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">Chưa có CV</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if ($candidate->status === 'new')
                                        <span class="inline-flex rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-700">Mới</span>
                                    @elseif ($candidate->status === 'interview')
                                        <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Phỏng vấn</span>
                                    @elseif ($candidate->status === 'passed')
                                        <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Đạt</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-700">Không đạt</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center text-slate-600">{{ $candidate->created_at?->format('d/m/Y') ?? '-' }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('admin.recruitment.candidates.show', $candidate) }}" class="w-9 h-9 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center hover:bg-blue-200" title="Chi tiết">👁</a>
                                        <a href="{{ route('admin.recruitment.candidates.edit', $candidate) }}" class="w-9 h-9 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center hover:bg-amber-200" title="Sửa">✏️</a>
                                        <form action="{{ route('admin.recruitment.candidates.destroy', $candidate) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa ứng viên này? Các lịch phỏng vấn liên quan cũng sẽ bị xóa.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="w-9 h-9 rounded-lg bg-red-100 text-red-600 flex items-center justify-center hover:bg-red-200" title="Xóa">🗑</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-12 text-slate-400">
                                    {{ $search !== '' ? 'Không tìm thấy ứng viên phù hợp.' : 'Chưa có ứng viên nào trong hệ thống.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($candidates->hasPages())
                <div class="px-6 py-4 border-t border-slate-200">{{ $candidates->links() }}</div>
            @endif
        </div>

    </div>

</x-admin-layout>