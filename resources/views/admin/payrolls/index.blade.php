<x-admin-layout title="Quản lý lương">

    <div class="space-y-6">

        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Bảng lương nhân viên</h2>
                <p class="text-sm text-slate-500 mt-1">
                    Xem và lọc thông tin thực lĩnh của nhân sự
                </p>
            </div>
        </div>

        <!-- Thống kê tổng quan -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <p class="text-slate-500 text-sm">Tổng số phiếu lương</p>
                <h3 class="text-3xl font-bold mt-2">
                    {{ \App\Models\Payroll::count() }}
                </h3>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <p class="text-slate-500 text-sm">Đã thanh toán (Paid)</p>
                <h3 class="text-3xl font-bold mt-2 text-emerald-600">
                    {{ number_format(\App\Models\Payroll::where('status', 'paid')->sum('total_salary'), 0, ',', '.') }} ₫
                </h3>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <p class="text-slate-500 text-sm">Chờ thanh toán (Approved)</p>
                <h3 class="text-3xl font-bold mt-2 text-blue-600">
                    {{ number_format(\App\Models\Payroll::where('status', 'approved')->sum('total_salary'), 0, ',', '.') }} ₫
                </h3>
            </div>
        </div>

        <!-- Bộ lọc và tìm kiếm -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-6">
            <form action="{{ route('admin.payrolls') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="search" class="block text-xs font-bold text-slate-500 uppercase mb-2">Tìm kiếm nhân viên</label>
                    <div class="relative">
                        <input type="text" id="search" name="search"
                               value="{{ request('search') }}"
                               placeholder="Nhập tên nhân viên..."
                               class="w-full rounded-xl border border-slate-200 pl-10 pr-4 py-3 text-slate-800 placeholder:text-slate-400 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition text-sm">
                        <span class="absolute left-3 top-3.5 text-slate-400">🔍</span>
                    </div>
                </div>

                <div>
                    <label for="period_id" class="block text-xs font-bold text-slate-500 uppercase mb-2">Lọc theo kỳ lương</label>
                    <select id="period_id" name="period_id"
                            class="w-full rounded-xl border border-slate-200 px-4 py-3 text-slate-800 focus:border-violet-500 focus:ring-2 focus:ring-violet-500/20 outline-none transition text-sm">
                        <option value="">-- Tất cả kỳ lương --</option>
                        @foreach ($periods as $period)
                            <option value="{{ $period->id }}" @selected(request('period_id') == $period->id)>
                                {{ $period->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit"
                            class="flex-1 bg-violet-600 text-white font-medium px-5 py-3 rounded-xl hover:bg-violet-700 transition shadow-lg shadow-violet-500/10 text-sm">
                        Lọc kết quả
                    </button>
                    @if(request()->anyFilled(['search', 'period_id']))
                        <a href="{{ route('admin.payrolls') }}"
                           class="bg-slate-100 text-slate-700 font-medium px-5 py-3 rounded-xl hover:bg-slate-200 transition text-sm text-center">
                            Xóa lọc
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Bảng danh sách lương -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-slate-50">
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Mã NV</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Họ và tên</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Kỳ lương</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Lương cơ bản</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Phụ cấp</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Khấu trừ</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-500">Thực lĩnh</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-500">Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($payrolls as $payroll)
                            <tr class="border-t border-slate-100 hover:bg-slate-50 transition">
                                <td class="px-6 py-4 font-medium text-slate-700">
                                    {{ $payroll->employee?->employee_code ?: '—' }}
                                </td>
                                <td class="px-6 py-4 font-semibold text-slate-800">
                                    {{ $payroll->employee?->full_name ?: '—' }}
                                </td>
                                <td class="px-6 py-4 text-slate-600">
                                    {{ $payroll->payrollPeriod?->name ?: '—' }}
                                </td>
                                <td class="px-6 py-4 text-slate-600">
                                    {{ number_format($payroll->basic_salary, 0, ',', '.') }} ₫
                                </td>
                                <td class="px-6 py-4 text-slate-600">
                                    {{ number_format($payroll->allowance + $payroll->bonus, 0, ',', '.') }} ₫
                                </td>
                                <td class="px-6 py-4 text-red-500">
                                    -{{ number_format($payroll->deduction, 0, ',', '.') }} ₫
                                </td>
                                <td class="px-6 py-4 font-bold text-violet-600">
                                    {{ number_format($payroll->total_salary, 0, ',', '.') }} ₫
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if ($payroll->status === 'paid')
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
                                            Đã trả
                                        </span>
                                    @elseif ($payroll->status === 'approved')
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                                            Đã duyệt
                                        </span>
                                    @else
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">
                                            Bản nháp
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-12 text-slate-400">
                                    Không tìm thấy dữ liệu bảng lương phù hợp
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($payrolls->hasPages())
                <div class="px-6 py-4 border-t border-slate-100">
                    {{ $payrolls->links() }}
                </div>
            @endif
        </div>

    </div>

</x-admin-layout>
