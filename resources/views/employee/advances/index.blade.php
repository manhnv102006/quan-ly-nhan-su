@php
    $user = Auth::user();
    $role = $user->role->name ?? 'employee';
    $formatMoney = fn ($n) => number_format((float) $n, 0, ',', '.') . '₫';

    $layout = match ($role) {
        'manager' => 'manager-layout',
        'leader' => 'leader-layout',
        'accountant' => 'accountant-layout',
        default => 'employee-layout',
    };

    $layoutParams = match ($role) {
        'accountant', 'leader' => [
            'title' => 'Ứng lương',
            'subtitle' => 'Gửi yêu cầu tới kế toán · Trừ vào lương thực lĩnh khi được duyệt',
        ],
        'manager' => [
            'title' => 'Ứng lương của tôi',
            'subtitle' => 'Gửi yêu cầu tới kế toán phê duyệt',
        ],
        default => [
            'title' => 'Ứng lương',
            'subtitle' => 'Gửi yêu cầu tới kế toán · Trừ vào lương thực lĩnh khi được duyệt',
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

        <div class="rounded-2xl border border-cyan-100 bg-cyan-50/60 px-5 py-4 text-sm text-cyan-900">
            Yêu cầu ứng lương gửi tới <strong>kế toán</strong> duyệt. Sau khi duyệt, số tiền sẽ được <strong>trừ trực tiếp vào lương thực lĩnh</strong> ở kỳ lương kế tiếp.
        </div>

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                <p class="text-[10px] font-bold uppercase text-slate-400">Chờ duyệt</p>
                <p class="mt-1 text-2xl font-bold text-amber-600">{{ $summary['pending'] }}</p>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                <p class="text-[10px] font-bold uppercase text-slate-400">Đã duyệt (chưa trừ hết)</p>
                <p class="mt-1 text-2xl font-bold text-sky-600">{{ $summary['approved'] }}</p>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                <p class="text-[10px] font-bold uppercase text-slate-400">Còn phải trừ</p>
                <p class="mt-1 text-2xl font-bold text-rose-600">{{ $formatMoney($summary['outstanding']) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                <p class="text-[10px] font-bold uppercase text-slate-400">Hạn mức ứng/lần</p>
                <p class="mt-1 text-lg font-bold text-slate-800">{{ $formatMoney($maxAdvanceAmount) }}</p>
                @if($referenceSalary > 0)
                    <p class="text-[10px] text-slate-400">≈ 50% lương {{ $formatMoney($referenceSalary) }}</p>
                @endif
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Lịch sử ứng lương</h2>
                <p class="text-xs text-slate-500">Theo dõi trạng thái duyệt và các kỳ đã khấu trừ</p>
            </div>
            <div class="flex gap-2">
                @if($role === 'accountant')
                    <a href="{{ route('accountant.advances.index') }}" class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                        ← Duyệt yêu cầu (Kế toán)
                    </a>
                @endif
                <a href="{{ route('employee.advances.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-cyan-600 px-4 py-2.5 text-xs font-semibold text-white shadow-md shadow-cyan-500/20 hover:bg-cyan-700">
                    + Gửi yêu cầu ứng lương
                </a>
            </div>
        </div>

        <div class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50 text-left text-xs font-bold uppercase text-slate-400">
                            <th class="px-5 py-4">Mã yêu cầu</th>
                            <th class="px-5 py-4">Ngày gửi</th>
                            <th class="px-5 py-4 text-right">Số tiền</th>
                            <th class="px-5 py-4 text-right">Còn lại</th>
                            <th class="px-5 py-4 text-center">Trạng thái</th>
                            <th class="px-5 py-4 text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($advances as $advance)
                            <tr class="hover:bg-slate-50/60">
                                <td class="px-5 py-4 font-mono text-xs font-semibold text-cyan-800">{{ $advance->advance_code }}</td>
                                <td class="px-5 py-4">{{ $advance->request_date?->format('d/m/Y') }}</td>
                                <td class="px-5 py-4 text-right font-bold">{{ $formatMoney($advance->amount) }}</td>
                                <td class="px-5 py-4 text-right text-rose-700">{{ $formatMoney($advance->remainingBalance()) }}</td>
                                <td class="px-5 py-4 text-center">
                                    <span class="inline-flex rounded-full px-2.5 py-1 text-[10px] font-bold uppercase {{ $advance->statusBadgeClass() }}">
                                        {{ $advance->statusLabel() }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <a href="{{ route('employee.advances.show', $advance) }}" class="inline-flex rounded-lg border border-cyan-100 bg-cyan-50 px-3 py-1.5 text-xs font-semibold text-cyan-700 hover:bg-cyan-100">
                                        Chi tiết
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-14 text-center text-slate-400">Bạn chưa gửi yêu cầu ứng lương nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($advances->hasPages())
                <div class="border-t border-slate-100 px-5 py-4">{{ $advances->links() }}</div>
            @endif
        </div>
    </div>
</x-dynamic-component>
