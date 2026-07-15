@include('accountant.insurance.partials.sub-nav', ['active' => 'profiles'])

<x-accountant-layout title="Thêm hồ sơ BH" subtitle="Đăng ký tham gia bảo hiểm cho nhân viên">
    <div class="accountant-page max-w-3xl">
        <div class="mb-4">
            <a href="{{ route('accountant.insurance.index') }}" class="text-sm font-semibold text-sky-700 hover:underline">← Danh sách hồ sơ</a>
            <h2 class="mt-2 text-2xl font-bold text-slate-900">Thêm hồ sơ tham gia bảo hiểm</h2>
        </div>

        @if($errors->any())
            <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                <ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('accountant.insurance.store') }}" class="accountant-card space-y-4 p-6">
            @csrf
            @include('accountant.insurance.partials.form', ['employees' => $employees, 'rates' => $rates])
            <div class="flex gap-3 pt-2">
                <button type="submit" class="accountant-btn-primary">Lưu hồ sơ</button>
                <a href="{{ route('accountant.insurance.index') }}" class="accountant-btn-secondary">Hủy</a>
            </div>
        </form>
    </div>
</x-accountant-layout>
