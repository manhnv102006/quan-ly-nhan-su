@php
    $user = Auth::user();
    $isManager = $user->role->name === 'manager';
    $layout = $isManager ? 'manager-layout' : 'employee-layout';
    $layoutParams = [
        'title'    => 'Đơn xin về sớm',
        'subtitle' => 'Xem và gửi yêu cầu về sớm.',
    ];
@endphp

<x-dynamic-component :component="$layout" :attributes="new \Illuminate\View\ComponentAttributeBag($layoutParams)">

    <div class="space-y-6">

        @if (session('success'))
            <div id="toast-success" class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 rounded-2xl px-5 py-4 shadow-sm">
                <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
            </div>
        @endif

        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-800">Đơn xin về sớm của tôi</h1>
                <p class="text-xs text-slate-500 mt-1">
                    Có đơn được duyệt → check-out sớm, tính đủ công.<br>
                    Không có đơn → vẫn check-out được nhưng chỉ tính <strong>0.5 công</strong>.
                </p>
            </div>
            <a href="{{ route('employee.early-leave.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-violet-600 text-white text-sm font-semibold shadow-md shadow-violet-500/20 hover:bg-violet-700 transition">
                + Tạo đơn xin về sớm
            </a>
        </div>

        <div class="bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-400">Ngày</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-400">Giờ muốn về</th>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-slate-400">Lý do</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-400">Trạng thái</th>
                            <th class="px-6 py-4 text-center text-xs font-bold uppercase text-slate-400">Ngày tạo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($requests as $req)
                            <tr class="hover:bg-slate-50/50 transition">
                                <td class="px-6 py-4 text-sm font-medium text-slate-700">
                                    {{ $req->request_date->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 text-sm font-bold text-violet-600">
                                    {{ \Carbon\Carbon::parse($req->leave_time)->format('H:i') }}
                                </td>
                                <td class="px-6 py-4 text-xs text-slate-500 max-w-[220px] truncate" title="{{ $req->reason }}">
                                    {{ $req->reason }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex border px-2.5 py-1 rounded-full text-xs font-bold {{ $req->statusBadgeClass() }}">
                                        {{ $req->statusLabel() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center text-xs text-slate-400">
                                    {{ $req->created_at->format('d/m/Y') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-14 text-slate-400 text-sm">
                                    Bạn chưa có đơn xin về sớm nào.
                                    <a href="{{ route('employee.early-leave.create') }}" class="text-violet-600 font-semibold hover:underline">Tạo đơn ngay</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($requests->hasPages())
                <div class="px-6 py-4 border-t border-slate-100">
                    {{ $requests->links() }}
                </div>
            @endif
        </div>
    </div>

</x-dynamic-component>
