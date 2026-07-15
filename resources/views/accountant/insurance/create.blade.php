@include('accountant.insurance.partials.sub-nav', ['active' => 'profiles'])

<x-accountant-layout title="Thêm hồ sơ BH" subtitle="Đăng ký tham gia bảo hiểm cho nhân viên">
    <div class="accountant-page max-w-3xl">
        <div class="mb-4">
            <nav class="mb-2 flex flex-wrap items-center gap-2 text-xs font-semibold text-slate-500">
                <a href="{{ route('accountant.insurance.index') }}" class="text-sky-700 hover:underline">Phòng ban</a>
                @if($selectedEmployee?->department)
                    <span>/</span>
                    <a href="{{ route('accountant.insurance.index', ['department_id' => $selectedEmployee->department->id]) }}" class="text-sky-700 hover:underline">{{ $selectedEmployee->department->department_name }}</a>
                    <span>/</span>
                    <a href="{{ route('accountant.insurance.index', ['employee_id' => $selectedEmployee->id]) }}" class="text-sky-700 hover:underline">{{ $selectedEmployee->full_name }}</a>
                @endif
            </nav>
            <h2 class="text-2xl font-bold text-slate-900">Thêm hồ sơ tham gia bảo hiểm</h2>
            @if($selectedEmployee)
                <p class="text-sm text-slate-500">{{ $selectedEmployee->full_name }} · {{ $selectedEmployee->employee_code }}</p>
            @endif
        </div>

        @if($errors->any())
            <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                <ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('accountant.insurance.store') }}" class="accountant-card space-y-4 p-6">
            @csrf
            @include('accountant.insurance.partials.form', [
                'employees' => $employees,
                'rates' => $rates,
                'selectedEmployee' => $selectedEmployee ?? null,
            ])
            <div class="flex gap-3 pt-2">
                <button type="submit" class="accountant-btn-primary">Lưu hồ sơ</button>
                @if($selectedEmployee)
                    <a href="{{ route('accountant.insurance.index', ['employee_id' => $selectedEmployee->id]) }}" class="accountant-btn-secondary">Hủy</a>
                @else
                    <a href="{{ route('accountant.insurance.index') }}" class="accountant-btn-secondary">Hủy</a>
                @endif
            </div>
        </form>
    </div>
</x-accountant-layout>
