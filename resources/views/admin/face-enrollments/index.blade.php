<x-admin-layout title="Chấm công khuôn mặt">

    <div class="space-y-6">

        <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.24em] text-violet-600">Quản lý chấm công</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-800">Chấm công bằng khuôn mặt</h1>
            <p class="mt-1 text-sm text-slate-500">
                Theo dõi và đăng ký khuôn mặt nhân viên trực tiếp trên website. Cần chạy
                <code class="rounded bg-slate-100 px-1.5 py-0.5 text-xs">python face-service/api_server.py</code>
                trước khi đăng ký.
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
            <p class="text-sm font-semibold text-violet-800">Cách đăng ký khuôn mặt trên website</p>
            <ol class="mt-2 list-decimal space-y-1 pl-5 text-sm text-violet-700">
                <li>Chạy Face API: <code class="rounded bg-white px-1.5 py-0.5 text-xs">python face-service/api_server.py</code></li>
                <li>Bấm <strong>Đăng ký khuôn mặt</strong> ở cột Thao tác.</li>
                <li>Cho phép webcam, chụp 5 mẫu (nhìn thẳng, hơi nghiêng trái/phải) rồi bấm <strong>Lưu đăng ký</strong>.</li>
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
                                <div class="flex flex-wrap items-center justify-center gap-2">
                                    <button type="button"
                                            data-enroll-open
                                            data-employee-name="{{ $employee->full_name }}"
                                            data-enroll-url="{{ route('admin.face-enrollments.store', $employee) }}"
                                            class="rounded-lg bg-violet-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-violet-700">
                                        {{ $employee->face_descriptors_count > 0 ? 'Đăng ký lại' : 'Đăng ký khuôn mặt' }}
                                    </button>
                                    @if ($employee->face_descriptors_count > 0)
                                        <form action="{{ route('admin.face-enrollments.destroy', $employee) }}" method="POST"
                                              onsubmit="return confirm('Xoá toàn bộ dữ liệu khuôn mặt của nhân viên này?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-700">
                                                Xoá dữ liệu mặt
                                            </button>
                                        </form>
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

    {{-- Modal đăng ký khuôn mặt --}}
    <style>
        #face-enrollment-modal .mirror-enroll {
            transform: scaleX(-1);
        }
    </style>
    <div id="face-enrollment-modal"
         class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 p-4"
         data-required-samples="5">
        <div class="w-full max-w-lg overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-violet-600">Đăng ký khuôn mặt</p>
                    <h3 class="text-lg font-bold text-slate-800" data-enroll-employee-name></h3>
                </div>
                <button type="button" data-enroll-close class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                    ✕
                </button>
            </div>

            <div class="space-y-4 p-6">
                <div class="relative overflow-hidden rounded-2xl border-2 border-slate-200 bg-slate-900 aspect-[4/3]">
                    <video class="h-full w-full object-cover mirror-enroll" playsinline muted autoplay></video>
                    <canvas class="hidden"></canvas>
                    <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
                        <div class="h-40 w-40 rounded-full border-2 border-white/70 border-dashed"></div>
                    </div>
                    <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/70 to-transparent px-4 py-3">
                        <p data-enroll-status class="text-center text-xs font-medium text-white">Đang mở camera...</p>
                    </div>
                </div>

                <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3">
                    <p class="text-sm text-slate-600">Tiến độ chụp mẫu</p>
                    <p class="text-sm font-bold text-violet-700" data-enroll-progress>0/5 mẫu</p>
                </div>

                <div class="flex flex-wrap gap-3">
                    <button type="button" data-enroll-capture
                            class="flex-1 rounded-xl bg-violet-600 px-4 py-3 text-sm font-semibold text-white hover:bg-violet-700">
                        Chụp mẫu
                    </button>
                    <button type="button" data-enroll-submit disabled
                            class="flex-1 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-50">
                        Lưu đăng ký
                    </button>
                    <button type="button" data-enroll-close
                            class="rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                        Huỷ
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        @vite('resources/js/face-enrollment.js')
    @endpush

</x-admin-layout>
