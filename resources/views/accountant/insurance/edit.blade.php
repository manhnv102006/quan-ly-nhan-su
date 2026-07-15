@php $formatMoney = fn ($n) => number_format((float) $n, 0, ',', '.') . '₫'; @endphp

@include('accountant.insurance.partials.sub-nav', ['active' => 'profiles'])

<x-accountant-layout title="Sửa hồ sơ BH" subtitle="{{ $insurance->employee?->full_name }}">
    <div class="accountant-page">
        <div class="mb-4 flex flex-wrap items-start justify-between gap-4">
            <div>
                <nav class="mb-2 flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-500">
                    <a href="{{ route('accountant.insurance.index') }}" class="text-sky-700 hover:underline">Phòng ban</a>
                    @if($insurance->employee?->department)
                        <span>/</span>
                        <a href="{{ route('accountant.insurance.index', ['department_id' => $insurance->employee->department->id]) }}" class="text-sky-700 hover:underline">{{ $insurance->employee->department->department_name }}</a>
                    @endif
                    <span>/</span>
                    <a href="{{ route('accountant.insurance.index', ['employee_id' => $insurance->employee_id]) }}" class="text-sky-700 hover:underline">{{ $insurance->employee?->full_name }}</a>
                    <span>/</span>
                    <span class="text-slate-700">Sửa hồ sơ</span>
                </nav>
                <h2 class="text-2xl font-bold text-slate-900">Cập nhật hồ sơ bảo hiểm</h2>
                <p class="text-sm text-slate-500">{{ $insurance->employee?->full_name }} · {{ $insurance->employee?->employee_code }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('accountant.insurance.index', ['employee_id' => $insurance->employee_id]) }}" class="accountant-btn-secondary">← Hồ sơ BH</a>
                @if($insurance->status === 'active')
                    <button type="button" onclick="document.getElementById('stopModal').classList.remove('hidden')" class="accountant-btn-secondary text-rose-700">Ngừng đóng BH</button>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4 mb-4">
            @include('accountant.partials.stat-card', ['label' => 'NLĐ đóng/tháng', 'value' => $formatMoney($contributions['total_employee']), 'tone' => 'text-sky-600'])
            @include('accountant.partials.stat-card', ['label' => 'DN đóng/tháng', 'value' => $formatMoney($contributions['total_employer']), 'tone' => 'text-indigo-600'])
            @include('accountant.partials.stat-card', ['label' => 'BHXH NLĐ', 'value' => $formatMoney($contributions['bhxh_employee'])])
            @include('accountant.partials.stat-card', ['label' => 'BHXH DN', 'value' => $formatMoney($contributions['bhxh_employer'])])
        </div>

        @if($errors->any())
            <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                <ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('accountant.insurance.update', $insurance) }}" class="accountant-card max-w-3xl space-y-4 p-6">
            @csrf
            @method('PUT')
            @include('accountant.insurance.partials.form', ['insurance' => $insurance])
            <div class="flex gap-3 pt-2">
                <button type="submit" class="accountant-btn-primary">Cập nhật</button>
                <a href="{{ route('accountant.insurance.index', ['employee_id' => $insurance->employee_id]) }}" class="accountant-btn-secondary">Hủy</a>
            </div>
        </form>

        @if($insurance->stop_reason)
            <div class="accountant-card mt-4 p-4 text-sm">
                <p class="font-bold text-slate-700">Lý do ngừng đóng</p>
                <p class="text-slate-600">{{ $insurance->stop_reason }}</p>
            </div>
        @endif
    </div>

    <div id="stopModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-slate-900/50" onclick="document.getElementById('stopModal').classList.add('hidden')"></div>
        <div class="relative mx-auto mt-24 max-w-md rounded-2xl bg-white p-6 shadow-xl">
            <h3 class="text-lg font-bold text-slate-900">Ngừng đóng bảo hiểm</h3>
            <form method="POST" action="{{ route('accountant.insurance.stop', $insurance) }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="accountant-label">Ngày ngừng đóng</label>
                    <input type="date" name="end_date" required value="{{ now()->format('Y-m-d') }}" class="accountant-field">
                </div>
                <div>
                    <label class="accountant-label">Lý do</label>
                    <textarea name="stop_reason" required rows="3" class="accountant-field" placeholder="VD: Nhân viên nghỉ việc..."></textarea>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="accountant-btn-primary bg-rose-600 hover:bg-rose-700">Xác nhận ngừng</button>
                    <button type="button" onclick="document.getElementById('stopModal').classList.add('hidden')" class="accountant-btn-secondary">Hủy</button>
                </div>
            </form>
        </div>
    </div>
</x-accountant-layout>
