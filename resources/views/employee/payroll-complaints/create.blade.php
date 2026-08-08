@php
    $layout = \App\Support\SelfServiceLayout::component();
    $layoutParams = ['title' => 'Gửi khiếu nại lương', 'subtitle' => 'Mô tả chi tiết vấn đề về phiếu lương.'];
    $fmt = fn ($n) => number_format((float) $n, 0, ',', '.');
@endphp

<x-dynamic-component :component="$layout" :attributes="new \Illuminate\View\ComponentAttributeBag($layoutParams)">
    <div class="max-w-2xl space-y-6">
        <a href="{{ route('employee.payroll-complaints.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800">← Danh sách khiếu nại</a>

        <div class="rounded-3xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-900">
            <p class="font-bold">Quy trình xử lý</p>
            <ol class="mt-2 list-decimal list-inside space-y-1 text-xs">
                <li>Bạn gửi khiếu nại kèm mô tả và kỳ lương liên quan.</li>
                <li>Quản lý phòng ban xem xét và chuyển kế toán.</li>
                <li>Kế toán kiểm tra — nếu xác nhận công ty tính sai, số tiền thiếu sẽ <strong>được cộng vào bảng lương tháng liền sau</strong> khi kế toán tính lương kỳ đó.</li>
                <li>Nếu khiếu nại không hợp lệ, kế toán từ chối và ghi rõ lý do.</li>
            </ol>
        </div>

        <div class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm sm:p-8">
            <h2 class="text-lg font-bold text-slate-800 mb-6">Gửi khiếu nại lương</h2>

            @if($errors->any())
                <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    <ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ session('error') }}</div>
            @endif

            <form method="POST" action="{{ route('employee.payroll-complaints.store') }}" class="space-y-5">
                @csrf
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-slate-500">Kỳ lương <span class="text-rose-500">*</span></label>
                    <select name="payroll_id" required class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm">
                        <option value="">— Chọn phiếu lương —</option>
                        @foreach($payrolls as $p)
                            @php $period = $p->payrollPeriod; @endphp
                            <option value="{{ $p->id }}" @selected(old('payroll_id', $selectedPayroll?->id) == $p->id)>
                                {{ $period?->name ?? 'Kỳ lương' }} — Thực lĩnh {{ $fmt($p->total_salary) }} ₫
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-slate-500">Loại khiếu nại <span class="text-rose-500">*</span></label>
                    <select name="issue_type" required class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm">
                        @foreach($issueTypes as $value => $label)
                            <option value="{{ $value }}" @selected(old('issue_type') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-slate-500">Tiêu đề <span class="text-rose-500">*</span></label>
                    <input type="text" name="subject" required maxlength="255" value="{{ old('subject') }}"
                           class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm" placeholder="VD: Thưởng KPI tháng này tính sai">
                </div>
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-slate-500">Số tiền chênh lệch (nếu có)</label>
                    @php
                        $disputedRaw = old('disputed_amount');
                        $disputedDisplay = filled($disputedRaw)
                            ? number_format((float) $disputedRaw, 0, ',', '.')
                            : '';
                    @endphp
                    <input type="text" name="disputed_amount"
                           inputmode="numeric" autocomplete="off"
                           value="{{ $disputedDisplay }}"
                           class="money-input w-full rounded-xl border border-slate-200 px-4 py-3 text-sm"
                           placeholder="VD: 500.000">
                </div>
                <div>
                    <label class="mb-2 block text-xs font-bold uppercase text-slate-500">Mô tả chi tiết <span class="text-rose-500">*</span></label>
                    <textarea name="description" rows="5" required maxlength="2000"
                              class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm"
                              placeholder="Mô tả cụ thể ngày, khoản mục bị sai, số liệu đúng theo bạn...">{{ old('description') }}</textarea>
                </div>
                <button type="submit" class="w-full rounded-xl bg-rose-600 py-3 text-sm font-bold text-white hover:bg-rose-700">Gửi khiếu nại</button>
            </form>
        </div>
    </div>

    @include('partials.money-input-script')
</x-dynamic-component>
