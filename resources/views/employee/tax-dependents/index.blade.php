@php
    $role = Auth::user()->role->name ?? 'employee';
    $formatMoney = fn ($n) => number_format((float) $n, 0, ',', '.') . '₫';

    $layout = \App\Support\SelfServiceLayout::component($role);

    $layoutParams = match ($role) {
        'accountant' => [
            'title' => 'Đăng ký NPT',
            'subtitle' => 'Gửi yêu cầu tới kế toán · GT phụ thuộc 4,4 triệu/người/tháng',
        ],
        'manager' => [
            'title' => 'Đăng ký người phụ thuộc',
            'subtitle' => 'Gửi yêu cầu tới kế toán phê duyệt',
        ],
        default => [
            'title' => 'Đăng ký NPT',
            'subtitle' => 'Gửi yêu cầu tới kế toán · Giảm trừ thuế TNCN khi được duyệt',
        ],
    };
@endphp

<x-dynamic-component :component="$layout" :attributes="new \Illuminate\View\ComponentAttributeBag($layoutParams)">
    <div class="space-y-6">
        @if(session('success'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-800">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm font-semibold text-rose-800">
                {{ session('error') }}
            </div>
        @endif

        <div class="rounded-2xl border border-violet-100 bg-violet-50/60 px-5 py-4 text-sm text-violet-900">
            Đăng ký <strong>người phụ thuộc (NPT)</strong> gửi tới <strong>kế toán</strong> duyệt. Sau khi duyệt, mỗi NPT được giảm trừ <strong>4,4 triệu/tháng</strong> vào thuế TNCN (GT phụ thuộc) ngay lập tức.
        </div>

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                <p class="text-[10px] font-bold uppercase text-slate-400">Chờ duyệt</p>
                <p class="mt-1 text-2xl font-bold text-amber-600">{{ $summary['pending'] }}</p>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                <p class="text-[10px] font-bold uppercase text-slate-400">Đã duyệt</p>
                <p class="mt-1 text-2xl font-bold text-emerald-600">{{ $summary['approved'] }}</p>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                <p class="text-[10px] font-bold uppercase text-slate-400">Đang hiệu lực (GT)</p>
                <p class="mt-1 text-2xl font-bold text-violet-600">{{ $summary['active'] }}</p>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                <p class="text-[10px] font-bold uppercase text-slate-400">GT phụ thuộc/tháng</p>
                <p class="mt-1 text-lg font-bold text-slate-800">{{ $formatMoney($summary['active'] * \App\Models\TaxDependent::DEFAULT_MONTHLY_DEDUCTION) }}</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Lịch sử đăng ký NPT</h2>
                <p class="text-xs text-slate-500">Theo dõi trạng thái duyệt của kế toán</p>
            </div>
            <div class="flex gap-2">
                @if($role === 'accountant')
                    <a href="{{ route('accountant.tax.pending-registrations') }}" class="inline-flex items-center rounded-xl border border-violet-200 bg-violet-50 px-4 py-2.5 text-xs font-semibold text-violet-700 hover:bg-violet-100">
                        ← Duyệt đăng ký (Kế toán)
                    </a>
                @endif
                <a href="{{ route('employee.tax-dependents.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-violet-600 px-4 py-2.5 text-xs font-semibold text-white shadow-md shadow-violet-500/20 hover:bg-violet-700">
                    + Đăng ký NPT mới
                </a>
            </div>
        </div>

        <div class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs font-bold uppercase text-slate-400">
                            <th class="px-5 py-4">Họ tên NPT</th>
                            <th class="px-5 py-4">Quan hệ</th>
                            <th class="px-5 py-4">Ngày bắt đầu GT</th>
                            <th class="px-5 py-4 text-right">GT/tháng</th>
                            <th class="px-5 py-4 text-center">Trạng thái</th>
                            <th class="px-5 py-4 text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($dependents as $dependent)
                            <tr class="hover:bg-slate-50/60">
                                <td class="px-5 py-4 font-semibold text-slate-800">{{ $dependent->full_name }}</td>
                                <td class="px-5 py-4">{{ $dependent->relationshipLabel() }}</td>
                                <td class="px-5 py-4">{{ $dependent->start_date?->format('d/m/Y') }}</td>
                                <td class="px-5 py-4 text-right font-bold">{{ $formatMoney($dependent->monthly_deduction) }}</td>
                                <td class="px-5 py-4 text-center">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-bold uppercase {{ $dependent->statusBadgeClass() }}">
                                        {{ $dependent->statusLabel() }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <a href="{{ route('employee.tax-dependents.show', $dependent) }}" class="inline-flex rounded-lg border border-violet-100 bg-violet-50 px-3 py-1.5 text-xs font-semibold text-violet-700 hover:bg-violet-100">
                                        Chi tiết
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-14 text-center text-slate-400">Bạn chưa đăng ký người phụ thuộc nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($dependents->hasPages())
                <div class="border-t border-slate-100 px-5 py-4">{{ $dependents->links() }}</div>
            @endif
        </div>
    </div>
</x-dynamic-component>
