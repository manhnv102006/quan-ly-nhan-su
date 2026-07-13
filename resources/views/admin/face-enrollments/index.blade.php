<x-admin-layout title="Chấm công khuôn mặt">

    <div class="space-y-6">

        <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-violet-600">Quản lý chấm công</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-800">Chấm công bằng khuôn mặt</h1>
            <p class="mt-1 text-sm text-slate-500">
                Theo dõi trạng thái đăng ký khuôn mặt của nhân viên. Việc đăng ký được thực hiện tại máy kiosk bằng công cụ <code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs">enroll.py</code>.
            </p>
        </div>

        @if (session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-800">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm font-medium text-rose-800">
                {{ session('error') }}
            </div>
        @endif

        {{-- Thống kê --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Nhân viên đang làm</p>
                <p class="mt-1 text-2xl font-bold text-slate-800">{{ $stats['total'] }}</p>
            </div>
            <div class="rounded-2xl border border-emerald-100 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Đã đăng ký khuôn mặt</p>
                <p class="mt-1 text-2xl font-bold text-emerald-600">{{ $stats['enrolled'] }}</p>
            </div>
            <div class="rounded-2xl border border-amber-100 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Chưa đăng ký</p>
                <p class="mt-1 text-2xl font-bold text-amber-600">{{ $stats['missing'] }}</p>
            </div>
        </div>

        {{-- Hướng dẫn --}}
        <div class="rounded-2xl border border-violet-100 bg-violet-50/60 p-5">
            <p class="text-sm font-semibold text-violet-800">Cách đăng ký khuôn mặt cho nhân viên</p>
            <ol class="mt-2 list-decimal space-y-1 pl-5 text-sm text-violet-700">
                <li>Tại máy kiosk, mở thư mục dự án và kích hoạt môi trường Python.</li>
                <li>Chạy lệnh: <code class="rounded bg-white px-1.5 py-0.5 text-xs">python face-service/enroll.py --employee-code MÃ_NV --samples 5</code></li>
                <li>Nhìn vào camera, nhấn SPACE để chụp đủ số mẫu rồi nhấn ENTER để lưu.</li>
            </ol>
        </div>

        {{-- Bộ lọc --}}
        <form method="GET" class="flex flex-wrap items-center gap-3">
            <input type="text" name="search" value="{{ $search }}" placeholder="Tìm theo tên hoặc mã nhân viên"
                   class="w-64 rounded-xl border-slate-200 text-sm focus:border-violet-400 focus:ring-violet-400">
            <select name="status" class="rounded-xl border-slate-200 text-sm focus:border-violet-400 focus:ring-violet-400">
                <option value="">Tất cả trạng thái</option>
                <option value="enrolled" @selected($status === 'enrolled')>Đã đăng ký</option>
                <option value="missing" @selected($status === 'missing')>Chưa đăng ký</option>
            </select>
            <button type="submit" class="rounded-xl bg-violet-600 px-4 py-2 text-sm font-semibold text-white hover:bg-violet-700">
                Lọc
            </button>
            @if ($search || $status)
                <a href="{{ route('admin.face-enrollments.index') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700">Xoá lọc</a>
            @endif
        </form>

        {{-- Bảng --}}
        <div class="overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-5 py-3">Nhân viên</th>
                        <th class="px-5 py-3">Mã NV</th>
                        <th class="px-5 py-3">Phòng ban</th>
                        <th class="px-5 py-3 text-center">Số mẫu</th>
                        <th class="px-5 py-3 text-center">Trạng thái</th>
                        <th class="px-5 py-3 text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($employees as $employee)
                        <tr class="border-t border-slate-100">
                            <td class="px-5 py-4 font-medium text-slate-800">{{ $employee->full_name }}</td>
                            <td class="px-5 py-4 text-slate-500">{{ $employee->employee_code }}</td>
                            <td class="px-5 py-4 text-slate-500">{{ $employee->department?->department_name ?? '—' }}</td>
                            <td class="px-5 py-4 text-center text-slate-600">{{ $employee->face_descriptors_count }}</td>
                            <td class="px-5 py-4 text-center">
                                @if ($employee->face_descriptors_count > 0)
                                    <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">Đã đăng ký</span>
                                @else
                                    <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">Chưa đăng ký</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex justify-center">
                                    @if ($employee->face_descriptors_count > 0)
                                        <form action="{{ route('admin.face-enrollments.destroy', $employee) }}" method="POST"
                                              onsubmit="return confirm('Xoá toàn bộ dữ liệu khuôn mặt của nhân viên này?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-700">
                                                Xoá dữ liệu mặt
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-xs text-slate-400">—</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-8 text-center text-slate-500">Không có nhân viên phù hợp.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $employees->links() }}

    </div>

</x-admin-layout>
