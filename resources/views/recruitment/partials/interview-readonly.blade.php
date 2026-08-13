@php
    $statusLabels = \App\Models\Interview::statusLabels();
    $resultLabels = \App\Models\Interview::resultLabels();
    $recommendationLabels = \App\Models\Interview::recommendationLabels();
    $scoreFields = [
        'overall_score' => 'Tổng quan',
        'technical_score' => 'Kỹ thuật',
        'attitude_score' => 'Thái độ',
        'culture_score' => 'Văn hóa',
    ];
@endphp

@if ($interview)
    <div class="space-y-4 rounded-2xl border border-slate-100 bg-slate-50/60 p-5">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h3 class="text-sm font-bold text-slate-800">Kết quả phỏng vấn</h3>
            <p class="text-xs text-slate-500">{{ $interview->interview_date?->format('d/m/Y H:i') ?? '—' }}</p>
        </div>

        <div class="grid grid-cols-2 gap-3 text-sm sm:grid-cols-4">
            <div>
                <p class="text-xs text-slate-400">Trạng thái</p>
                <p class="font-semibold">{{ $statusLabels[$interview->status] ?? $interview->status }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-400">Kết quả</p>
                <p class="font-semibold">{{ $resultLabels[$interview->result] ?? $interview->result }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-400">Đề xuất</p>
                <p class="font-semibold">{{ $recommendationLabels[$interview->recommendation] ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-400">Phỏng vấn viên</p>
                <p class="font-semibold">{{ $interview->interviewer?->full_name ?? '—' }}</p>
            </div>
        </div>

        @if ($interview->status === \App\Models\Interview::STATUS_COMPLETED)
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                @foreach ($scoreFields as $field => $label)
                    <div class="rounded-xl bg-white p-3">
                        <p class="text-xs text-slate-400">{{ $label }}</p>
                        <p class="text-lg font-bold text-slate-800">{{ $interview->{$field} ?? '—' }}/10</p>
                    </div>
                @endforeach
            </div>
        @endif

        @if ($interview->note)
            <div>
                <p class="text-xs font-bold text-slate-500">Ghi chú</p>
                <p class="mt-1 whitespace-pre-line text-sm text-slate-700">{{ $interview->note }}</p>
            </div>
        @endif

        @if ($interview->strengths || $interview->weaknesses)
            <div class="grid gap-3 sm:grid-cols-2">
                @if ($interview->strengths)
                    <div class="rounded-xl bg-emerald-50 p-3 text-sm">
                        <p class="text-xs font-bold text-emerald-700">Điểm mạnh</p>
                        <p class="mt-1 whitespace-pre-line text-emerald-900">{{ $interview->strengths }}</p>
                    </div>
                @endif
                @if ($interview->weaknesses)
                    <div class="rounded-xl bg-amber-50 p-3 text-sm">
                        <p class="text-xs font-bold text-amber-700">Cần cải thiện</p>
                        <p class="mt-1 whitespace-pre-line text-amber-900">{{ $interview->weaknesses }}</p>
                    </div>
                @endif
            </div>
        @endif
    </div>
@endif
