@php($typeMeta = \App\Support\NotificationTypeMeta::all())

<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Tất cả thông báo</h2>
            <p class="text-sm text-slate-500 mt-1">Theo dõi cập nhật hệ thống, nghỉ phép, lương và KPI</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            @if (auth()->user()?->isAdmin())
                <a href="{{ route('admin.notifications.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-violet-600 text-white text-sm font-medium hover:bg-violet-700 transition">
                    + Thêm thông báo
                </a>
            @endif
            @if ($stats['unread'] > 0)
                <form action="{{ route('notifications.read-all') }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-violet-100 text-violet-700 text-sm font-medium hover:bg-violet-200 transition">
                        Đánh dấu tất cả đã đọc
                    </button>
                </form>
            @endif
            <a href="{{ route(auth()->user()->dashboardRouteName()) }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 text-sm font-medium hover:bg-slate-50 transition">
                ← Về Dashboard
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-white px-5 py-4 shadow-sm">
            <p class="text-sm font-medium text-emerald-700">{{ session('success') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm text-slate-500">Tổng thông báo</p>
            <p class="mt-2 text-3xl font-bold text-slate-900">{{ $stats['total'] }}</p>
        </div>
        <div class="rounded-2xl border border-rose-100 bg-rose-50/50 p-5 shadow-sm">
            <p class="text-sm text-rose-600">Chưa đọc</p>
            <p class="mt-2 text-3xl font-bold text-rose-700">{{ $stats['unread'] }}</p>
        </div>
        <div class="rounded-2xl border border-emerald-100 bg-emerald-50/50 p-5 shadow-sm">
            <p class="text-sm text-emerald-600">Đã đọc</p>
            <p class="mt-2 text-3xl font-bold text-emerald-700">{{ $stats['read'] }}</p>
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100">
            <form action="{{ route('notifications.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="md:col-span-2">
                    <label for="search" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Tìm kiếm</label>
                    <input id="search" name="search" type="text" value="{{ $filters['search'] }}"
                           placeholder="Tiêu đề hoặc nội dung..."
                           class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 focus:border-violet-400 focus:outline-none">
                </div>
                <div>
                    <label for="status" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Trạng thái</label>
                    <select id="status" name="status" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 focus:border-violet-400 focus:outline-none">
                        <option value="all" @selected($filters['status'] === 'all')>Tất cả</option>
                        <option value="unread" @selected($filters['status'] === 'unread')>Chưa đọc</option>
                        <option value="read" @selected($filters['status'] === 'read')>Đã đọc</option>
                    </select>
                </div>
                <div>
                    <label for="type" class="block text-xs font-semibold uppercase tracking-wider text-slate-400 mb-2">Loại</label>
                    <select id="type" name="type" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 focus:border-violet-400 focus:outline-none">
                        <option value="">Tất cả loại</option>
                        @foreach ($typeMeta as $typeKey => $meta)
                            <option value="{{ $typeKey }}" @selected($filters['type'] === $typeKey)>{{ $meta['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-4 flex justify-end">
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-violet-600 text-white text-sm font-medium hover:bg-violet-700 transition">
                        Lọc thông báo
                    </button>
                </div>
            </form>
        </div>

        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-semibold text-slate-800">Danh sách thông báo</h3>
            <span class="text-sm text-slate-500">{{ $notifications->total() }} bản ghi</span>
        </div>

        @if ($notifications->isEmpty())
            <div class="px-6 py-16 text-center">
                <div class="mx-auto w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center">
                    <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                    </svg>
                </div>
                <p class="mt-4 text-slate-500 font-medium">Không có thông báo nào</p>
                <p class="mt-1 text-sm text-slate-400">Thử đổi bộ lọc hoặc quay lại sau</p>
            </div>
        @else
            <div class="divide-y divide-slate-100">
                @foreach ($notifications as $notification)
                    @include('admin.partials.notification-item', ['notification' => $notification])
                @endforeach
            </div>
        @endif

        @if ($notifications->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</div>
