<x-admin-layout title="Dashboard">
    {{-- Welcome banner --}}
    <div class="mb-6 rounded-2xl bg-gradient-to-r from-blue-600 to-blue-800 p-6 sm:p-8 text-white shadow-lg shadow-blue-200">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold">Chào mừng trở lại, {{ Auth::user()->name }}!</h2>
                <p class="mt-1 text-blue-100 text-sm sm:text-base">Tổng quan hệ thống quản lý nhân sự của bạn hôm nay.</p>
            </div>
            <div class="flex items-center gap-2 text-sm bg-white/15 rounded-xl px-4 py-2 backdrop-blur">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                </svg>
                {{ now()->translatedFormat('l, d/m/Y') }}
            </div>
        </div>
    </div>

    {{-- Stats grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
        @foreach ($stats as $stat)
            <a href="{{ route($stat['route']) }}" class="group bg-white rounded-xl border border-slate-200 p-5 shadow-sm hover:shadow-md hover:border-blue-200 transition">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm text-slate-500 font-medium">{{ $stat['label'] }}</p>
                        <p class="mt-2 text-3xl font-bold text-slate-800 group-hover:text-blue-700 transition">{{ number_format($stat['value']) }}</p>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" />
                        </svg>
                    </div>
                </div>
            </a>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Quick actions --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 shadow-sm p-6">
            <h3 class="text-lg font-semibold text-slate-800 mb-4">Truy cập nhanh</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                @foreach ([
                    ['route' => 'admin.employees', 'label' => 'Thêm nhân viên', 'icon' => 'M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM3 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 019.374 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z'],
                    ['route' => 'admin.attendances', 'label' => 'Chấm công', 'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['route' => 'admin.payrolls', 'label' => 'Tính lương', 'icon' => 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['route' => 'admin.kpis', 'label' => 'Đánh giá KPI', 'icon' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z'],
                    ['route' => 'admin.contracts', 'label' => 'Hợp đồng', 'icon' => 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z'],
                    ['route' => 'admin.recruitment', 'label' => 'Tuyển dụng', 'icon' => 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                ] as $action)
                    <a href="{{ route($action['route']) }}" class="flex flex-col items-center gap-2 p-4 rounded-xl border border-slate-100 bg-slate-50 hover:bg-blue-50 hover:border-blue-200 transition text-center group">
                        <div class="w-10 h-10 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-blue-600 group-hover:bg-blue-600 group-hover:text-white group-hover:border-blue-600 transition">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $action['icon'] }}" />
                            </svg>
                        </div>
                        <span class="text-xs font-medium text-slate-600 group-hover:text-blue-700">{{ $action['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- System info --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
            <h3 class="text-lg font-semibold text-slate-800 mb-4">Thông tin hệ thống</h3>
            <ul class="space-y-4">
                <li class="flex items-center gap-3">
                    <span class="w-2 h-2 rounded-full bg-green-500"></span>
                    <div>
                        <p class="text-sm font-medium text-slate-700">Hệ thống hoạt động</p>
                        <p class="text-xs text-slate-500">Tất cả dịch vụ đang chạy bình thường</p>
                    </div>
                </li>
                <li class="flex items-center gap-3">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                    <div>
                        <p class="text-sm font-medium text-slate-700">Vai trò: Admin</p>
                        <p class="text-xs text-slate-500">Toàn quyền quản trị</p>
                    </div>
                </li>
                <li class="flex items-center gap-3">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    <div>
                        <p class="text-sm font-medium text-slate-700">Tin tuyển dụng</p>
                        <p class="text-xs text-slate-500">{{ $recentJobs->count() }} bài đăng gần đây</p>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</x-admin-layout>
