@php
    $layout = \App\Support\SelfServiceLayout::component();
    $historyContracts = $contracts->filter(fn ($item) => ! $activeContract || $item->id !== $activeContract->id);
@endphp

<x-dynamic-component :component="$layout" :attributes="new \Illuminate\View\ComponentAttributeBag(['title' => 'Hợp đồng của tôi', 'subtitle' => 'Xem lịch sử và tải file hợp đồng.'])">
    <div class="space-y-6">

        {{-- Hero --}}
        <section class="relative overflow-hidden rounded-[1.75rem] bg-gradient-to-br from-sky-600 via-blue-600 to-indigo-700 p-6 text-white shadow-xl shadow-sky-500/20 sm:p-8">
            <div class="absolute -right-10 top-0 h-40 w-40 rounded-full bg-white/10 blur-2xl"></div>
            <div class="absolute bottom-0 left-0 h-32 w-32 -translate-x-1/4 translate-y-1/4 rounded-full bg-cyan-400/20 blur-2xl"></div>
            <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-sky-100">Quản lý hồ sơ</p>
                    <h2 class="mt-2 text-2xl font-extrabold tracking-tight sm:text-3xl">Hợp đồng của tôi</h2>
                    <p class="mt-2 text-sm text-sky-100/90">
                        {{ $employee->full_name }} · {{ $employee->employee_code }}
                        @if ($employee->department?->department_name)
                            · {{ $employee->department->department_name }}
                        @endif
                    </p>
                </div>
                <div class="flex flex-wrap gap-3">
                    <div class="rounded-2xl border border-white/20 bg-white/10 px-4 py-3 backdrop-blur">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-sky-100">Tổng hợp đồng</p>
                        <p class="mt-1 text-2xl font-extrabold">{{ $totalContracts }}</p>
                    </div>
                    <div class="rounded-2xl border border-white/20 bg-white/10 px-4 py-3 backdrop-blur">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-sky-100">Đang hiệu lực</p>
                        <p class="mt-1 text-2xl font-extrabold">{{ $activeContract ? 1 : 0 }}</p>
                    </div>
                </div>
            </div>
        </section>

        @if ($activeContract)
            <section class="overflow-hidden rounded-3xl border border-emerald-100 bg-white shadow-sm">
                <div class="bg-gradient-to-r from-emerald-500 to-teal-500 px-6 py-4 text-white">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/20">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.251 2.251 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V19.5a2.25 2.25 0 0 0 2.25 2.25h.75" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-50">Hợp đồng đang hiệu lực</p>
                                <p class="text-lg font-bold">{{ $activeContract->contract_code }}</p>
                            </div>
                        </div>
                        <span class="inline-flex rounded-full border border-white/30 bg-white/15 px-2.5 py-1 text-xs font-bold text-white">
                            {{ $activeContract->status_label }}
                        </span>
                    </div>
                </div>
                <div class="grid gap-4 p-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase text-slate-400">Loại hợp đồng</p>
                        <p class="mt-1 font-bold text-slate-800">{{ $activeContract->contractType->contract_name ?? '—' }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase text-slate-400">Thời hạn</p>
                        <p class="mt-1 font-bold text-slate-800">
                            {{ optional($activeContract->start_date)->format('d/m/Y') }}
                            <span class="font-normal text-slate-400">→</span>
                            {{ optional($activeContract->end_date)->format('d/m/Y') ?? 'Không TH' }}
                        </p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase text-slate-400">Chức vụ</p>
                        <p class="mt-1 font-bold text-slate-800">{{ $activeContract->position?->position_name ?? '—' }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-4">
                        <p class="text-xs font-semibold uppercase text-slate-400">Lương cơ bản</p>
                        <p class="mt-1 font-bold text-emerald-700">
                            {{ $activeContract->salary ? number_format((float) $activeContract->salary, 0, ',', '.').' ₫' : '—' }}
                        </p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-3 border-t border-slate-100 bg-slate-50/50 px-6 py-4">
                    <a href="{{ route('employee.contracts.show', $activeContract) }}"
                       class="inline-flex items-center gap-2 rounded-xl bg-sky-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-sky-500/25 transition hover:bg-sky-700">
                        Xem chi tiết
                    </a>
                    @if ($activeContract->file_path)
                        <a href="{{ route('employee.contracts.download', $activeContract) }}"
                           class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                            Tải file PDF
                        </a>
                    @endif
                </div>
            </section>
        @else
            <div class="rounded-3xl border border-dashed border-slate-200 bg-slate-50/80 px-6 py-10 text-center">
                <p class="text-sm font-semibold text-slate-600">Hiện chưa có hợp đồng đang hiệu lực</p>
                <p class="mt-1 text-xs text-slate-400">Liên hệ phòng Nhân sự nếu bạn cần hỗ trợ.</p>
            </div>
        @endif

        {{-- Lịch sử --}}
        <section>
            <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Lịch sử</p>
                    <h3 class="text-lg font-bold text-slate-800">Các hợp đồng khác</h3>
                </div>
                <p class="text-sm text-slate-500">{{ $contracts->total() }} bản ghi</p>
            </div>

            @if ($historyContracts->isEmpty() && ! $activeContract)
                <div class="rounded-3xl border border-slate-100 bg-white py-16 text-center shadow-sm">
                    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                    </div>
                    <p class="mt-4 font-semibold text-slate-700">Chưa có hợp đồng nào</p>
                    <p class="mt-1 text-sm text-slate-500">Hợp đồng sẽ hiển thị tại đây khi được tạo.</p>
                </div>
            @elseif ($historyContracts->isEmpty())
                <p class="rounded-2xl border border-slate-100 bg-white px-5 py-4 text-sm text-slate-500 shadow-sm">
                    Không còn hợp đồng nào khác trong danh sách.
                </p>
            @else
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    @foreach ($historyContracts as $item)
                        <article class="group rounded-2xl border border-slate-100 bg-white p-5 shadow-sm transition hover:border-sky-200 hover:shadow-md">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-xs font-bold uppercase tracking-wide text-slate-400">Mã hợp đồng</p>
                                    <p class="mt-1 text-lg font-bold text-slate-800 group-hover:text-sky-700">{{ $item->contract_code }}</p>
                                </div>
                                @include('admin.contracts.partials.status-badge', ['contract' => $item])
                            </div>
                            <p class="mt-3 text-sm font-medium text-slate-700">{{ $item->contractType->contract_name ?? '—' }}</p>
                            <div class="mt-4 flex flex-wrap gap-4 text-xs text-slate-500">
                                <span>
                                    <span class="font-semibold text-slate-600">Từ</span>
                                    {{ optional($item->start_date)->format('d/m/Y') ?? '—' }}
                                </span>
                                <span>
                                    <span class="font-semibold text-slate-600">Đến</span>
                                    {{ optional($item->end_date)->format('d/m/Y') ?? 'Không TH' }}
                                </span>
                            </div>
                            <div class="mt-5 flex flex-wrap gap-2 border-t border-slate-50 pt-4">
                                <a href="{{ route('employee.contracts.show', $item) }}"
                                   class="inline-flex rounded-lg bg-sky-50 px-3 py-2 text-xs font-semibold text-sky-700 transition hover:bg-sky-100">
                                    Chi tiết
                                </a>
                                @if ($item->file_path)
                                    <a href="{{ route('employee.contracts.download', $item) }}"
                                       class="inline-flex rounded-lg border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-50">
                                        Tải file
                                    </a>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif

            @if ($contracts->hasPages())
                <div class="mt-6">{{ $contracts->links() }}</div>
            @endif
        </section>
    </div>
</x-dynamic-component>
