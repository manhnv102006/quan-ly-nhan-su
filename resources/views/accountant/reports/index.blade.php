<x-accountant-layout title="Báo cáo" subtitle="Trung tâm báo cáo tài chính & nhân sự">
    @include('accountant.reports.partials.sub-nav', ['active' => 'hub'])

    <div class="accountant-page">
        <section class="accountant-hero !mb-4">
            <div class="relative max-w-2xl">
                <h2 class="text-2xl font-bold sm:text-3xl">Trung tâm báo cáo</h2>
                <p class="mt-2 text-sm text-amber-100/90">Chi phí lương, ngân sách, xuất báo cáo tài chính nhân sự.</p>
            </div>
        </section>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach($reports as $report)
                <a href="{{ $report['href'] }}"
                   class="group accountant-card overflow-hidden p-0 transition hover:-translate-y-1 hover:shadow-xl">
                    <div class="h-2 bg-gradient-to-r {{ $report['tone'] }}"></div>
                    <div class="p-5">
                        <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br {{ $report['tone'] }} text-white shadow-lg">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $report['icon'] }}" />
                            </svg>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 group-hover:text-amber-800">{{ $report['title'] }}</h3>
                        <p class="mt-2 text-sm text-slate-500">{{ $report['description'] }}</p>
                        <p class="mt-4 text-xs font-bold uppercase tracking-wide text-amber-700">Mở báo cáo →</p>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</x-accountant-layout>
