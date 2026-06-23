<x-admin-layout title="Tuyển dụng">

    <div class="space-y-6">

        <div class="admin-card overflow-hidden">
            <div class="relative px-6 sm:px-8 py-7 border-b border-slate-100 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-r from-violet-50 via-indigo-50 to-cyan-50"></div>

                <div class="relative flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-violet-100 text-violet-700 text-[10px] font-bold uppercase tracking-wider mb-3">
                            Module tuyển dụng
                        </span>
                        <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">
                            Dashboard tuyển dụng
                        </h2>
                        <p class="mt-1.5 text-sm text-slate-500 max-w-2xl">
                            Theo dõi nhanh hiệu quả tuyển dụng của PeopleHub qua các chỉ số trọng tâm và truy cập nhanh tới từng nghiệp vụ chính.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('admin.recruitment.job-posts') }}"
                           class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-violet-600 text-white font-medium shadow-lg shadow-violet-500/20 hover:bg-violet-700 transition">
                            Xem tin tuyển dụng
                        </a>

                        <a href="{{ route('admin.dashboard') }}"
                           class="inline-flex items-center gap-2 px-5 py-3 rounded-xl border border-slate-200 bg-white text-slate-600 font-medium hover:bg-slate-50 transition">
                            Về dashboard tổng
                        </a>
                    </div>
                </div>
            </div>

            <div class="px-6 sm:px-8 py-6 sm:py-8 space-y-8">
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
                    <div class="rounded-3xl border border-violet-100 bg-gradient-to-br from-violet-50 to-white p-6 shadow-sm">
                        <p class="text-sm font-medium text-violet-600">Tổng tin tuyển dụng</p>
                        <h3 class="mt-3 text-4xl font-black tracking-tight text-slate-900">{{ $stats['job_posts'] }}</h3>
                        <p class="mt-2 text-sm text-slate-500">Số lượng tin đang được quản lý trong hệ thống.</p>
                    </div>

                    <div class="rounded-3xl border border-cyan-100 bg-gradient-to-br from-cyan-50 to-white p-6 shadow-sm">
                        <p class="text-sm font-medium text-cyan-600">Tổng ứng viên</p>
                        <h3 class="mt-3 text-4xl font-black tracking-tight text-slate-900">{{ $stats['candidates'] }}</h3>
                        <p class="mt-2 text-sm text-slate-500">Toàn bộ hồ sơ ứng viên đã tiếp nhận.</p>
                    </div>

                    <div class="rounded-3xl border border-amber-100 bg-gradient-to-br from-amber-50 to-white p-6 shadow-sm">
                        <p class="text-sm font-medium text-amber-600">Tổng lịch phỏng vấn</p>
                        <h3 class="mt-3 text-4xl font-black tracking-tight text-slate-900">{{ $stats['interviews'] }}</h3>
                        <p class="mt-2 text-sm text-slate-500">Số buổi phỏng vấn đã được tạo trong hệ thống.</p>
                    </div>

                    <div class="rounded-3xl border border-emerald-100 bg-gradient-to-br from-emerald-50 to-white p-6 shadow-sm">
                        <p class="text-sm font-medium text-emerald-600">Tổng ứng viên đạt</p>
                        <h3 class="mt-3 text-4xl font-black tracking-tight text-slate-900">{{ $stats['passed_candidates'] }}</h3>
                        <p class="mt-2 text-sm text-slate-500">Ứng viên đã được đánh dấu đạt sau quá trình tuyển dụng.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                    <a href="{{ route('admin.recruitment.job-posts') }}"
                       class="group rounded-3xl border border-slate-200 bg-white p-6 shadow-sm hover:border-violet-200 hover:shadow-md hover:shadow-violet-100 transition">
                        <div class="flex items-center justify-between gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center text-white shadow-lg shadow-violet-500/20">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-violet-100 text-violet-700 text-xs font-semibold">
                                {{ $stats['job_posts'] }} tin
                            </span>
                        </div>

                        <h3 class="mt-5 text-lg font-bold text-slate-800 group-hover:text-violet-700 transition">
                            Quản lý tin tuyển dụng
                        </h3>

                        <p class="mt-2 text-sm text-slate-500 leading-relaxed">
                            Xem danh sách, tìm kiếm và quản lý các tin tuyển dụng theo phòng ban, trạng thái và nhu cầu thực tế.
                        </p>

                        <div class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-violet-600">
                            Truy cập chức năng
                            <svg class="w-4 h-4 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </div>
                    </a>

                    <a href="{{ route('admin.recruitment.candidates') }}"
                       class="group rounded-3xl border border-slate-200 bg-white p-6 shadow-sm hover:border-cyan-200 hover:shadow-md hover:shadow-cyan-100 transition">
                        <div class="flex items-center justify-between gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center text-white shadow-lg shadow-cyan-500/20">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-cyan-100 text-cyan-700 text-xs font-semibold">
                                {{ $stats['candidates'] }} hồ sơ
                            </span>
                        </div>

                        <h3 class="mt-5 text-lg font-bold text-slate-800 group-hover:text-cyan-700 transition">
                            Quản lý ứng viên
                        </h3>

                        <p class="mt-2 text-sm text-slate-500 leading-relaxed">
                            Theo dõi hồ sơ ứng viên, trạng thái xử lý và chi tiết CV trong toàn bộ quy trình tuyển dụng.
                        </p>

                        <div class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-cyan-600">
                            Truy cập chức năng
                            <svg class="w-4 h-4 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </div>
                    </a>

                    <a href="{{ route('admin.recruitment.interviews') }}"
                       class="group rounded-3xl border border-slate-200 bg-white p-6 shadow-sm hover:border-amber-200 hover:shadow-md hover:shadow-amber-100 transition">
                        <div class="flex items-center justify-between gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-white shadow-lg shadow-amber-500/20">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-semibold">
                                {{ $stats['interviews'] }} lịch
                            </span>
                        </div>

                        <h3 class="mt-5 text-lg font-bold text-slate-800 group-hover:text-amber-700 transition">
                            Quản lý phỏng vấn
                        </h3>

                        <p class="mt-2 text-sm text-slate-500 leading-relaxed">
                            Theo dõi lịch phỏng vấn, người phụ trách và cập nhật kết quả phỏng vấn ngay trong cùng một luồng làm việc.
                        </p>

                        <div class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-amber-600">
                            Truy cập chức năng
                            <svg class="w-4 h-4 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </div>
                    </a>
                </div>
            </div>
        </div>

    </div>

</x-admin-layout>