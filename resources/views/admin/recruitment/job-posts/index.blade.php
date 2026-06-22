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

            <div class="flex flex-wrap gap-3">
                @if ($showCreateForm ?? false)
                    <a href="{{ route('admin.recruitment.job-posts') }}"
                       class="inline-flex items-center gap-2 px-5 py-3 rounded-xl border border-slate-200 bg-white text-slate-600 font-medium hover:bg-slate-50 transition">
                        Quay lại danh sách
                    </a>
                @else
                    <a href="{{ route('admin.recruitment.job-posts.create') }}"
                       class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-violet-600 text-white font-medium hover:bg-violet-700 transition">
                        + Thêm tin tuyển dụng
                    </a>
                @endif

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

        @if ($showCreateForm ?? false)
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6 sm:p-8">
                <div class="mb-6">
                    <h3 class="text-xl font-bold text-slate-800">Thêm tin tuyển dụng mới</h3>
                    <p class="text-sm text-slate-500 mt-1">Điền thông tin theo đúng dữ liệu hiện có của hệ thống.</p>
                </div>

                @if ($errors->any())
                    <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        <p class="font-semibold mb-1">Vui lòng kiểm tra lại thông tin:</p>
                        <ul class="list-disc list-inside space-y-0.5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('admin.recruitment.job-posts.store') }}" method="POST" class="space-y-5 max-w-3xl">
                    @csrf

                    <div>
                        <label for="title" class="block text-sm font-semibold text-slate-700 mb-2">
                            Tiêu đề tin tuyển dụng <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="title"
                            name="title"
                            value="{{ old('title') }}"
                            placeholder="Ví dụ: Nhân viên tuyển dụng, Lập trình viên PHP"
                            maxlength="255"
                            required
                            class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 placeholder:text-slate-400 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition @error('title') border-red-400 @enderror"
                        >
                        @error('title')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="department_id" class="block text-sm font-semibold text-slate-700 mb-2">
                            Phòng ban
                        </label>
                        <select
                            id="department_id"
                            name="department_id"
                            class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition @error('department_id') border-red-400 @enderror"
                        >
                            <option value="">-- Chưa gán phòng ban --</option>
                            @foreach (($departments ?? collect()) as $department)
                                <option value="{{ $department->id }}" @selected((string) old('department_id') === (string) $department->id)>
                                    {{ $department->department_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('department_id')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="quantity" class="block text-sm font-semibold text-slate-700 mb-2">
                            Số lượng tuyển <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="number"
                            id="quantity"
                            name="quantity"
                            value="{{ old('quantity') }}"
                            placeholder="Nhập số lượng cần tuyển"
                            min="1"
                            required
                            class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 placeholder:text-slate-400 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition @error('quantity') border-red-400 @enderror"
                        >
                        @error('quantity')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-semibold text-slate-700 mb-2">
                            Trạng thái <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="status"
                            name="status"
                            required
                            class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition @error('status') border-red-400 @enderror"
                        >
                            <option value="">-- Chọn trạng thái --</option>
                            <option value="open" @selected(old('status') === 'open')>Đang mở</option>
                            <option value="closed" @selected(old('status') === 'closed')>Đã đóng</option>
                        </select>
                        @error('status')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-semibold text-slate-700 mb-2">
                            Mô tả
                        </label>
                        <textarea
                            id="description"
                            name="description"
                            rows="5"
                            placeholder="Mô tả công việc, yêu cầu hoặc ghi chú tuyển dụng"
                            class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 placeholder:text-slate-400 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition resize-y @error('description') border-red-400 @enderror"
                        >{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-wrap gap-3 pt-2">
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-violet-600 text-white font-medium shadow-lg shadow-violet-500/20 hover:bg-violet-700 transition">
                            + Lưu tin tuyển dụng
                        </button>
                        <a href="{{ route('admin.recruitment.job-posts') }}"
                           class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-slate-100 text-slate-700 font-medium hover:bg-slate-200 transition">
                            Hủy
                        </a>
                    </div>
                </form>
            </div>
        @endif

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
