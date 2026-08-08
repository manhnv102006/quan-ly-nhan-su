<x-accountant-layout title="Quản lý tỷ lệ BH" subtitle="Cấu hình tỷ lệ đóng BHXH · BHYT · BHTN áp dụng cho hồ sơ mới">
<div class="accountant-page max-w-3xl">
        @include('accountant.insurance.partials.sub-nav', ['active' => 'rates'])

        <div class="mb-4">
            <h2 class="text-2xl font-bold text-slate-900">Quản lý tỷ lệ bảo hiểm</h2>
            <p class="text-sm text-slate-500">Thiết lập tỷ lệ đóng BH mặc định. Khi thêm hồ sơ mới, hệ thống tự áp dụng tỷ lệ tại đây.</p>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                <ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form id="insurance-rates-form" method="POST" action="{{ route('accountant.insurance.rates.update') }}" class="accountant-card space-y-4 p-6">
            @csrf
            @method('PUT')

            <div class="rounded-2xl border border-sky-100 bg-sky-50/40 p-4">
                <p class="mb-3 text-xs font-bold uppercase text-sky-800">Tỷ lệ đóng bảo hiểm (%)</p>
                @include('accountant.insurance.partials.rate-fields', ['rates' => $setting->toRatesArray()])
            </div>

            <div>
                <label class="accountant-label">Ghi chú</label>
                <textarea name="note" rows="3" class="accountant-field" placeholder="VD: Theo Nghị định ...">{{ old('note', $setting->note) }}</textarea>
            </div>

            @if($setting->updater)
                <p class="text-xs text-slate-400">
                    Cập nhật lần cuối: {{ $setting->updated_at?->format('H:i d/m/Y') }}
                    · {{ $setting->updater->name }}
                </p>
            @endif

            <div class="flex gap-3 pt-2">
                <button type="submit" class="accountant-btn-primary">Lưu tỷ lệ</button>
                <a href="{{ route('accountant.insurance.index') }}" class="accountant-btn-secondary">← Hồ sơ BH</a>
            </div>
        </form>
    </div>
</x-accountant-layout>
