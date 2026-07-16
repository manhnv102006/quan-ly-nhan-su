@php $formatMoney = fn ($n) => number_format((float) $n, 0, ',', '.') . '₫'; @endphp

@include('accountant.tax.partials.sub-nav', ['active' => 'pending'])

<x-accountant-layout title="Duyệt đăng ký NPT" subtitle="Yêu cầu từ nhân viên · Duyệt xong áp dụng GT phụ thuộc ngay">
    <div class="accountant-page">
        @if(session('success'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Đăng ký NPT chờ duyệt</h2>
                <p class="text-sm text-slate-500">{{ $pendingCount }} yêu cầu đang chờ · Duyệt xong chuyển vào người phụ thuộc thuế</p>
            </div>
            <a href="{{ route('accountant.tax.dependents') }}" class="accountant-btn-secondary !text-xs">
                Quản lý người phụ thuộc →
            </a>
        </div>

        <div class="accountant-card mt-6 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-sm">
                    <thead>
                        <tr class="bg-violet-50 text-left text-xs font-bold uppercase text-slate-500">
                            <th class="px-4 py-3">Nhân viên</th>
                            <th class="px-4 py-3">Họ tên NPT</th>
                            <th class="px-4 py-3">Quan hệ</th>
                            <th class="px-4 py-3">Ngày GT</th>
                            <th class="px-4 py-3 text-right">GT/tháng</th>
                            <th class="px-4 py-3">Ngày gửi</th>
                            <th class="px-4 py-3 text-center">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($pending as $dep)
                            <tr class="hover:bg-violet-50/30">
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-slate-800">{{ $dep->employee?->full_name }}</p>
                                    <p class="text-xs text-slate-400">{{ $dep->employee?->department?->department_name ?? '—' }}</p>
                                </td>
                                <td class="px-4 py-3 font-medium">{{ $dep->full_name }}</td>
                                <td class="px-4 py-3">{{ $dep->relationshipLabel() }}</td>
                                <td class="px-4 py-3">{{ $dep->start_date?->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-right font-bold text-violet-800">{{ $formatMoney($dep->monthly_deduction) }}</td>
                                <td class="px-4 py-3 text-xs text-slate-500">{{ $dep->created_at?->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap items-center justify-center gap-2">
                                        <form method="POST" action="{{ route('accountant.tax.registrations.approve', $dep) }}">
                                            @csrf
                                            <button type="submit" class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700">
                                                Duyệt
                                            </button>
                                        </form>
                                        <button type="button"
                                                onclick="document.getElementById('reject-form-{{ $dep->id }}').classList.toggle('hidden')"
                                                class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-100">
                                            Từ chối
                                        </button>
                                    </div>
                                    <form id="reject-form-{{ $dep->id }}" method="POST" action="{{ route('accountant.tax.registrations.reject', $dep) }}" class="mt-2 hidden space-y-2">
                                        @csrf
                                        <textarea name="rejection_reason" rows="2" required maxlength="1000" placeholder="Lý do từ chối..."
                                                  class="accountant-field !text-xs"></textarea>
                                        <button type="submit" class="w-full rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-rose-700">
                                            Xác nhận từ chối
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @if($dep->note)
                                <tr class="bg-slate-50/50">
                                    <td colspan="7" class="px-4 py-2 text-xs text-slate-500">
                                        <span class="font-semibold text-slate-600">Ghi chú:</span> {{ $dep->note }}
                                        @if($dep->id_number)
                                            · CCCD: {{ $dep->id_number }}
                                        @endif
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-14 text-center text-slate-500">Không có đăng ký NPT chờ duyệt.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-accountant-layout>
