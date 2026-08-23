@php
    $section = $section ?? 'button';
    $routePrefix = $routePrefix ?? 'accountant';
    $buttonClass = $buttonClass ?? 'accountant-btn-secondary';
    $bankDocuments = $payrollPeriod->relationLoaded('bankDocuments')
        ? $payrollPeriod->bankDocuments
        : $payrollPeriod->bankDocuments()->with('uploader')->latest()->get();
    $hasBankDocErrors = $errors->has('file') || $errors->has('note');
@endphp

@if ($section === 'button')
    <button type="button"
            onclick="document.getElementById('bank-doc-modal')?.classList.remove('hidden')"
            class="{{ $buttonClass }}">
        Up file ngân hàng
        @if ($bankDocuments->isNotEmpty())
            <span class="inline-flex items-center justify-center min-w-5 h-5 px-1.5 rounded-full bg-amber-500 text-white text-[11px] font-bold">
                {{ $bankDocuments->count() }}
            </span>
        @endif
    </button>
@else
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
        <div class="flex flex-wrap items-start justify-between gap-3 mb-4">
            <div>
                <h3 class="text-lg font-bold text-slate-800">File ngân hàng đã đóng dấu</h3>
                <p class="text-sm text-slate-500 mt-1">
                    Xuất Excel gửi ngân hàng, nhận bản đóng dấu rồi tải lên đây để lưu chứng từ kỳ lương.
                    Có thể lưu nhiều file (bản bổ sung / đính chính).
                </p>
            </div>
            <button type="button"
                    onclick="document.getElementById('bank-doc-modal')?.classList.remove('hidden')"
                    class="{{ $buttonClass }} !py-1.5 !text-xs">
                + Thêm file
            </button>
        </div>

        @if ($bankDocuments->isEmpty())
            <p class="text-sm text-slate-500 bg-slate-50 border border-dashed border-slate-200 rounded-xl px-4 py-6 text-center">
                Chưa có file ngân hàng nào được lưu cho kỳ này.
            </p>
        @else
            <ul class="divide-y divide-slate-100">
                @foreach ($bankDocuments as $document)
                    <li class="py-3 first:pt-0 last:pb-0 flex flex-wrap items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-medium text-slate-800 truncate">{{ $document->original_name }}</p>
                            <p class="text-xs text-slate-500 mt-0.5">
                                {{ $document->formattedSize() }}
                                • {{ $document->uploader?->name ?? 'Hệ thống' }}
                                • {{ $document->created_at?->format('H:i d/m/Y') }}
                                @if ($document->note)
                                    • {{ $document->note }}
                                @endif
                            </p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <a href="{{ route($routePrefix.'.payroll-periods.bank-documents.download', [$payrollPeriod, $document]) }}"
                               class="{{ $buttonClass }} !py-1.5 !text-xs"
                               data-no-loader>
                                Tải về
                            </a>
                            <form method="POST"
                                  action="{{ route($routePrefix.'.payroll-periods.bank-documents.destroy', [$payrollPeriod, $document]) }}"
                                  onsubmit="return confirm('Xóa file này khỏi hệ thống? File đã xóa không khôi phục được.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="{{ $buttonClass }} !py-1.5 !text-xs text-rose-700">Xóa</button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    <div id="bank-doc-modal"
         class="{{ $hasBankDocErrors ? '' : 'hidden' }} fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 px-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg p-6">
            <div class="flex items-start justify-between gap-3 mb-4">
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Lưu file ngân hàng đã đóng dấu</h3>
                    <p class="text-sm text-slate-500 mt-1">PDF, Excel, Word hoặc ảnh. Tối đa 10MB.</p>
                </div>
                <button type="button"
                        onclick="document.getElementById('bank-doc-modal').classList.add('hidden')"
                        class="w-8 h-8 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                    ×
                </button>
            </div>

            <form method="POST"
                  action="{{ route($routePrefix.'.payroll-periods.bank-documents.store', $payrollPeriod) }}"
                  enctype="multipart/form-data"
                  class="space-y-4">
                @csrf
                <div>
                    <label for="bank-doc-file" class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-1.5">File</label>
                    <input id="bank-doc-file"
                           type="file"
                           name="file"
                           required
                           accept=".pdf,.xls,.xlsx,.doc,.docx,.jpg,.jpeg,.png"
                           class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 file:mr-3 file:rounded-lg file:border-0 file:bg-amber-500 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-white">
                    @error('file')
                        <p class="text-rose-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="bank-doc-note" class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-1.5">Ghi chú (không bắt buộc)</label>
                    <input id="bank-doc-note"
                           type="text"
                           name="note"
                           value="{{ old('note') }}"
                           maxlength="255"
                           placeholder="VD: Bản đóng dấu chuyển khoản 07/2026"
                           class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-800 outline-none focus:border-amber-400 focus:ring-2 focus:ring-amber-400/20">
                    @error('note')
                        <p class="text-rose-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div class="flex justify-end gap-2 pt-1">
                    <button type="button"
                            onclick="document.getElementById('bank-doc-modal').classList.add('hidden')"
                            class="{{ $buttonClass }}">
                        Hủy
                    </button>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-semibold text-white hover:bg-amber-600">
                        Lưu file
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif
