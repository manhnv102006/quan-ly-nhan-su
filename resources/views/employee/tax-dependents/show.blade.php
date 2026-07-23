@php
    $role = Auth::user()->role->name ?? 'employee';
    $formatMoney = fn ($n) => number_format((float) $n, 0, ',', '.') . '₫';

    $layout = \App\Support\SelfServiceLayout::component($role);

    $layoutParams = [
        'title' => 'Chi tiết đăng ký NPT',
        'subtitle' => $dependent->full_name,
    ];
@endphp

<x-dynamic-component :component="$layout" :attributes="new \Illuminate\View\ComponentAttributeBag($layoutParams)">
    <div class="max-w-3xl space-y-6">
        <a href="{{ route('employee.tax-dependents.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-800">
            ← Quay lại danh sách
        </a>

        <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm">
            <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-100 pb-5">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">{{ $dependent->full_name }}</h2>
                    <p class="text-sm text-slate-500">{{ $dependent->relationshipLabel() }} · Gửi {{ $dependent->created_at?->format('d/m/Y H:i') }}</p>
                </div>
                <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold uppercase {{ $dependent->statusBadgeClass() }}">
                    {{ $dependent->statusLabel() }}
                </span>
            </div>

            <dl class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div class="rounded-xl bg-slate-50 p-4">
                    <dt class="text-xs font-bold uppercase text-slate-400">GT phụ thuộc/tháng</dt>
                    <dd class="mt-1 text-xl font-bold text-violet-800">{{ $formatMoney($dependent->monthly_deduction) }}</dd>
                </div>
                <div class="rounded-xl bg-slate-50 p-4">
                    <dt class="text-xs font-bold uppercase text-slate-400">Ngày bắt đầu GT</dt>
                    <dd class="mt-1 text-lg font-bold text-slate-800">{{ $dependent->start_date?->format('d/m/Y') ?? '—' }}</dd>
                </div>
                <div class="rounded-xl bg-slate-50 p-4">
                    <dt class="text-xs font-bold uppercase text-slate-400">Ngày sinh</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-800">{{ $dependent->date_of_birth?->format('d/m/Y') ?? '—' }}</dd>
                </div>
                <div class="rounded-xl bg-slate-50 p-4">
                    <dt class="text-xs font-bold uppercase text-slate-400">CCCD/CMND</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-800">{{ $dependent->id_number ?? '—' }}</dd>
                </div>
                <div class="rounded-xl bg-slate-50 p-4 sm:col-span-2">
                    <dt class="text-xs font-bold uppercase text-slate-400">Áp dụng GT phụ thuộc</dt>
                    <dd class="mt-1 text-sm font-semibold {{ $dependent->countsForTaxDeduction() ? 'text-emerald-700' : 'text-slate-500' }}">
                        @if($dependent->countsForTaxDeduction())
                            Đang hiệu lực — được tính vào thuế TNCN
                        @elseif($dependent->status === \App\Models\TaxDependent::STATUS_PENDING)
                            Chờ kế toán duyệt
                        @elseif($dependent->status === \App\Models\TaxDependent::STATUS_REJECTED)
                            Không được áp dụng (đã từ chối)
                        @else
                            Chưa hiệu lực
                        @endif
                    </dd>
                </div>
            </dl>

            <div class="mt-5 space-y-3 text-sm">
                @if($dependent->documents->isNotEmpty())
                    <div>
                        <p class="text-xs font-bold uppercase text-slate-400">Giấy tờ đính kèm</p>
                        <ul class="mt-2 space-y-2">
                            @foreach($dependent->documents as $doc)
                                <li class="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-slate-100 bg-slate-50 px-4 py-2">
                                    <span class="text-sm font-medium text-slate-800">{{ $doc->typeLabel() }}</span>
                                    <a href="{{ route('employee.tax-dependents.documents.download', [$dependent, $doc]) }}"
                                       class="text-xs font-semibold text-violet-700 hover:underline">
                                        Tải xuống · {{ $doc->original_name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if($dependent->note)
                    <div>
                        <p class="text-xs font-bold uppercase text-slate-400">Ghi chú</p>
                        <p class="mt-1 text-slate-600">{{ $dependent->note }}</p>
                    </div>
                @endif
                @if($dependent->approver)
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800">
                        <p class="text-xs font-bold uppercase">Kế toán duyệt</p>
                        <p class="mt-1">{{ $dependent->approver->name }} · {{ $dependent->approved_at?->format('d/m/Y H:i') }}</p>
                    </div>
                @endif
                @if($dependent->rejection_reason)
                    <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-800">
                        <p class="text-xs font-bold uppercase">Lý do từ chối</p>
                        <p class="mt-1">{{ $dependent->rejection_reason }}</p>
                        @if($dependent->rejecter)
                            <p class="mt-1 text-xs opacity-80">{{ $dependent->rejecter->name }} · {{ $dependent->rejected_at?->format('d/m/Y H:i') }}</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-dynamic-component>
