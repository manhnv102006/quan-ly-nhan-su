@include('accountant.payrolls.partials.sub-nav', ['active' => 'hub'])

<x-accountant-layout title="Quản lý lương" subtitle="Tính lương · Duyệt · Chi trả · Xuất báo cáo">
    <div class="accountant-page">
        <section class="accountant-hero !mb-4">
            <div class="relative max-w-2xl">
                <h2 class="text-2xl font-bold sm:text-3xl">Quản lý lương</h2>
                <p class="mt-2 text-sm text-amber-100/90">Tạo kỳ lương, tính lương tự động, điều chỉnh trước khi duyệt, chi trả và xuất phiếu lương.</p>
            </div>
        </section>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach([
                ['Tạo & quản lý kỳ lương', 'Lịch 12 tháng, tính lương theo phòng ban', route('accountant.payroll-periods.index'), 'from-amber-500 to-orange-500', 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                ['Xem bảng lương', 'Danh sách phiếu lương toàn hệ thống', route('accountant.payrolls.slips'), 'from-sky-500 to-indigo-500', 'M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0z'],
                ['Lịch sử thay đổi lương', 'Theo dõi mức lương & điều chỉnh theo nhân viên', route('accountant.payrolls.salary-history'), 'from-violet-500 to-purple-600', 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z'],
            ] as [$title, $desc, $href, $tone, $icon])
                <a href="{{ $href }}" class="group accountant-card overflow-hidden p-0 transition hover:-translate-y-1 hover:shadow-xl">
                    <div class="h-2 bg-gradient-to-r {{ $tone }}"></div>
                    <div class="p-5">
                        <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br {{ $tone }} text-white shadow-lg">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}" />
                            </svg>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 group-hover:text-amber-800">{{ $title }}</h3>
                        <p class="mt-2 text-sm text-slate-500">{{ $desc }}</p>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="accountant-card p-5">
            <h3 class="mb-3 text-sm font-bold text-slate-800">Quy trình xử lý lương</h3>
            <div class="flex flex-wrap items-center gap-2 text-xs font-semibold">
                <span class="accountant-badge bg-sky-100 text-sky-800">1. Tạo kỳ</span>
                <span class="text-slate-300">→</span>
                <span class="accountant-badge bg-amber-100 text-amber-800">2. Tính lương</span>
                <span class="text-slate-300">→</span>
                <span class="accountant-badge bg-orange-100 text-orange-800">3. Sửa (trước duyệt)</span>
                <span class="text-slate-300">→</span>
                <span class="accountant-badge bg-violet-100 text-violet-800">4. Duyệt</span>
                <span class="text-slate-300">→</span>
                <span class="accountant-badge bg-emerald-100 text-emerald-800">5. Chi trả</span>
                <span class="text-slate-300">→</span>
                <span class="accountant-badge bg-slate-100 text-slate-700">6. Xuất PDF/Excel</span>
            </div>
        </div>
    </div>
</x-accountant-layout>
