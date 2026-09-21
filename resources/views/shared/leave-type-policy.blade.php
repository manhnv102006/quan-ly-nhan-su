@php
    $type = $leaveRequest->leaveTypeConfig();
@endphp
@if ($type)
    <div class="{{ $wrapperClass ?? 'mt-2 space-y-1 text-xs leading-relaxed text-slate-500' }}">
        <p>Trừ phép năm: <span class="font-medium text-slate-700">{{ $type->annualDeductionLabel() }}</span></p>
        <p>Lương: <span class="font-medium text-slate-700">{{ $type->salaryPayerLabel() }}</span></p>
        <p>Hạn mức: <span class="font-medium text-slate-700">{{ $type->quotaLabel() }}</span></p>
        <p>
            Giấy tờ:
            <span class="font-medium text-slate-700">
                {{ $type->requiresDocument() ? ($type->document_hint ?: 'Bắt buộc đính kèm') : 'Không' }}
            </span>
        </p>
    </div>
@endif
