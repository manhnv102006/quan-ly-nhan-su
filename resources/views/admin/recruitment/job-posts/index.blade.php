<x-admin-layout title="Tin tuyển dụng">

    <div class="space-y-6">

        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-sm text-slate-500">
                    <a href="{{ route('admin.recruitment') }}" class="hover:text-violet-600 transition">Tuyển dụng</a>
                    <span>/</span>
                    <span class="text-slate-700 font-medium">Tin tuyển dụng</span>
                </div>

                <h2 class="mt-2 text-2xl font-bold text-slate-800">Danh sách tin tuyển dụng</h2>
                <p class="text-sm text-slate-500 mt-1">
                    Theo dõi và tìm kiếm các tin tuyển dụng đã tạo trong hệ thống.
                </p>
            </div>

            <a href="{{ route('admin.recruitment') }}"
               class="inline-flex items-center gap-2 px-5 py-3 rounded-xl border border-slate-200 bg-white text-slate-600 font-medium hover:bg-slate-50 transition">
                Quay lại tuyển dụng
            </a>
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

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm text-slate-500">Tổng tin tuyển dụng</p>
                <h3 class="text-3xl font-bold mt-2 text-slate-900">{{ $stats['total'] }}</h3>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm text-slate-500">Đang mở</p>
                <h3 class="text-3xl font-bold mt-2 text-emerald-600">{{ $stats['open'] }}</h3>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm text-slate-500">Đã đóng</p>
                <h3 class="text-3xl font-bold mt-2 text-rose-600">{{ $stats['closed'] }}</h3>
            </div>
        </div>

        <div class="admin-card p-6">
            <form method="GET" action="{{ route('admin.recruitment.job-posts') }}" class="flex flex-col gap-4 lg:flex-row lg:items-end">
                <div class="flex-1">
                    <label for="search" class="block text-sm font-medium text-slate-700 mb-2">
                        Tìm kiếm tin tuyển dụng
                    </label>
                    <input
                        type="text"
                        id="search"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Nhập tiêu đề, mô tả hoặc tên phòng ban"
                        class="w-full rounded-xl border-slate-200 focus:border-violet-500 focus:ring-violet-500"
                    >
                </div>

                <div class="flex flex-wrap gap-3">
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center px-5 py-3 rounded-xl bg-violet-600 text-white font-medium hover:bg-violet-700 transition"
                    >
                        Tìm kiếm
                    </button>

                    @if ($search !== '')
                        <a
                            href="{{ route('admin.recruitment.job-posts') }}"
                            class="inline-flex items-center justify-center px-5 py-3 rounded-xl border border-slate-200 bg-white text-slate-600 font-medium hover:bg-slate-50 transition"
                        >
                            Xóa bộ lọc
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">

            <div class="px-6 py-4 border-b border-slate-200 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <h3 class="font-semibold text-slate-800">Danh sách tin tuyển dụng</h3>

                <p class="text-sm text-slate-500">
                    Hiển thị {{ $jobPosts->count() }} / {{ $jobPosts->total() }} bản ghi
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-500">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-500">Tiêu đề</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-500">Phòng ban</th>
                            <th class="px-6 py-3 text-center text-xs font-bold uppercase text-slate-500">Số lượng</th>
                            <th class="px-6 py-3 text-center text-xs font-bold uppercase text-slate-500">Trạng thái</th>
                            <th class="px-6 py-3 text-center text-xs font-bold uppercase text-slate-500">Ngày tạo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($jobPosts as $jobPost)
                            <tr class="border-b border-slate-200 hover:bg-slate-50 transition">
                                <td class="px-6 py-4 text-slate-700">{{ $jobPost->id }}</td>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-slate-800">{{ $jobPost->title }}</div>
                                    <p class="mt-1 text-sm text-slate-500 line-clamp-2">
                                        {{ $jobPost->description ?: 'Không có mô tả.' }}
                                    </p>
                                </td>
                                <td class="px-6 py-4 text-slate-600">
                                    {{ $jobPost->department?->department_name ?? 'Chưa gán phòng ban' }}
                                </td>
                                <td class="px-6 py-4 text-center text-slate-700 font-semibold">
                                    {{ $jobPost->quantity }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if ($jobPost->status === 'open')
                                        <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                                            Đang mở
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-700">
                                            Đã đóng
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center text-slate-600">
                                    {{ $jobPost->created_at?->format('d/m/Y') ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-12 text-slate-400">
                                    Không tìm thấy tin tuyển dụng phù hợp.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($jobPosts->hasPages())
                <div class="px-6 py-4 border-t border-slate-200">
                    {{ $jobPosts->links() }}
                </div>
            @endif
        </div>

    </div>

</x-admin-layout>
