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
                            Quản lý tuyển dụng
                        </h2>
                        <p class="mt-1.5 text-sm text-slate-500 max-w-2xl">
                            Quản lý các nghiệp vụ tuyển dụng theo từng phần nhỏ để đảm bảo an toàn cho hệ thống hiện tại.
                            Chức năng đang hoạt động ở giai đoạn này là danh sách và tìm kiếm tin tuyển dụng.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('admin.recruitment.job-posts') }}"
                           class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-violet-600 text-white font-medium shadow-lg shadow-violet-500/20 hover:bg-violet-700 transition">
                            Xem tin tuyển dụng
                        </a>

                        <a href="{{ route('admin.dashboard') }}"
                           class="inline-flex items-center gap-2 px-5 py-3 rounded-xl border border-slate-200 bg-white text-slate-600 font-medium hover:bg-slate-50 transition">
                            Về dashboard
                        </a>
                    </div>
                </div>
            </div>

            <div class="px-6 sm:px-8 py-6 sm:py-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                    <a href="{{ route('admin.recruitment.job-posts') }}"
                       class="group rounded-3xl border border-slate-200 bg-white p-6 shadow-sm hover:border-violet-200 hover:shadow-md hover:shadow-violet-100 transition">
                        <div class="flex items-center justify-between gap-4">
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center text-white shadow-lg shadow-violet-500/20">
                                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <span class="inline-flex items-center px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold">
                                Đang hoạt động
                            </span>
                        </div>

                        <h3 class="mt-5 text-lg font-bold text-slate-800 group-hover:text-violet-700 transition">
                            Quản lý tin tuyển dụng
                        </h3>

                        <p class="mt-2 text-sm text-slate-500 leading-relaxed">
                            Xem danh sách và tìm kiếm tin tuyển dụng theo tiêu đề, mô tả và phòng ban mà không ảnh hưởng các module khác.
                        </p>

                        <div class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-violet-600">
                            Truy cập chức năng
                            <svg class="w-4 h-4 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </div>
                    </a>

                    <div class="rounded-3xl border border-dashed border-slate-300 bg-slate-50/80 p-6">
                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-slate-200 text-slate-600 text-xs font-semibold">
                            Phạm vi hiện tại
                        </span>
                        <h3 class="mt-5 text-lg font-bold text-slate-800">
                            Triển khai theo từng bước nhỏ
                        </h3>
                        <p class="mt-2 text-sm text-slate-500 leading-relaxed">
                            Các phần ứng viên, phỏng vấn và dashboard tuyển dụng sẽ được bổ sung ở các bước tiếp theo
                            sau khi hoàn thành và xác nhận xong chức năng hiện tại.
                        </p>
                    </div>
                </div>
            </div>
        </div>

    </div>

</x-admin-layout>
