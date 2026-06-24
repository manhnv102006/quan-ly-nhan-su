@props([
    'employee',
    'documents',
    'showDownloadAll' => true,
])

@php
    $downloadableDocuments = $documents->filter(fn ($document) => $document->existsOnDisk());

    $typeStyles = [
        'cccd' => ['bg' => 'from-blue-500 to-indigo-600', 'badge' => 'bg-blue-100 text-blue-700', 'icon' => 'M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2'],
        'cv' => ['bg' => 'from-violet-500 to-purple-600', 'badge' => 'bg-violet-100 text-violet-700', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
        'certificate' => ['bg' => 'from-amber-500 to-orange-600', 'badge' => 'bg-amber-100 text-amber-700', 'icon' => 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z'],
        'degree' => ['bg' => 'from-emerald-500 to-teal-600', 'badge' => 'bg-emerald-100 text-emerald-700', 'icon' => 'M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222'],
        'contract' => ['bg' => 'from-rose-500 to-pink-600', 'badge' => 'bg-rose-100 text-rose-700', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
    ];
@endphp

<div id="ho-so-tai-lieu" class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="relative px-6 py-6 sm:px-8 border-b border-slate-100 bg-gradient-to-r from-violet-50 via-white to-indigo-50">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-violet-600 to-indigo-600 flex items-center justify-center shadow-lg shadow-violet-500/25 shrink-0">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                              d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-slate-800">Tài liệu hồ sơ</h3>
                    <p class="text-sm text-slate-500 mt-1">
                        Quản lý CCCD, CV, bằng cấp, chứng chỉ và hợp đồng của nhân viên
                    </p>
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white border border-slate-200 text-xs font-semibold text-slate-600">
                            <span class="w-2 h-2 rounded-full bg-violet-500"></span>
                            {{ $documents->count() }} tài liệu
                        </span>
                        @if ($downloadableDocuments->isNotEmpty())
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-100 text-xs font-semibold text-emerald-700">
                                {{ $downloadableDocuments->count() }} có thể tải
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.employees.edit', $employee) }}#tai-lieu-ho-so"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm font-medium text-slate-700 hover:bg-slate-50 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Thêm tài liệu
                </a>
                @if ($showDownloadAll && $downloadableDocuments->isNotEmpty())
                    <a href="{{ route('admin.employees.documents.download-all', $employee) }}"
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-violet-600 text-white text-sm font-medium hover:bg-violet-700 transition shadow-sm shadow-violet-500/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4" />
                        </svg>
                        Tải tất cả ({{ $downloadableDocuments->count() }})
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="p-6 sm:p-8">
        @if ($documents->isEmpty())
            <div class="rounded-3xl border-2 border-dashed border-slate-200 bg-slate-50/80 px-6 py-16 text-center">
                <div class="w-20 h-20 mx-auto rounded-3xl bg-white border border-slate-200 flex items-center justify-center shadow-sm">
                    <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                </div>
                <h4 class="mt-6 text-lg font-semibold text-slate-700">Chưa có tài liệu hồ sơ</h4>
                <p class="mt-2 text-sm text-slate-500 max-w-md mx-auto">
                    Tải lên CCCD, CV, bằng cấp hoặc các giấy tờ liên quan để hoàn thiện hồ sơ nhân viên.
                </p>
                <a href="{{ route('admin.employees.edit', $employee) }}#tai-lieu-ho-so"
                   class="mt-6 inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-violet-600 text-white text-sm font-medium hover:bg-violet-700 transition">
                    Tải lên tài liệu đầu tiên
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                @foreach ($documents as $document)
                    @php
                        $style = $typeStyles[$document->document_type] ?? $typeStyles['cv'];
                        $extension = strtoupper(pathinfo($document->file_path, PATHINFO_EXTENSION) ?: 'FILE');
                    @endphp
                    <div class="group relative rounded-2xl border border-slate-200 bg-white p-5 hover:border-violet-200 hover:shadow-lg hover:shadow-violet-100/50 transition-all duration-200">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br {{ $style['bg'] }} flex items-center justify-center shrink-0 shadow-md">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $style['icon'] }}" />
                                </svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="font-semibold text-slate-800 truncate" title="{{ $document->document_name }}">
                                    {{ $document->document_name }}
                                </p>
                                <div class="mt-2 flex flex-wrap items-center gap-2">
                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-[11px] font-semibold {{ $style['badge'] }}">
                                        {{ $document->typeLabel() }}
                                    </span>
                                    <span class="inline-flex px-2 py-0.5 rounded-md bg-slate-100 text-[10px] font-bold uppercase tracking-wide text-slate-500">
                                        {{ $extension }}
                                    </span>
                                </div>
                                <p class="mt-2 text-xs text-slate-400">
                                    Tải lên {{ $document->created_at?->format('d/m/Y') ?? '—' }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-4 pt-4 border-t border-slate-100">
                            @if ($document->existsOnDisk())
                                <a href="{{ route('admin.employees.documents.download', [$employee, $document]) }}"
                                   class="inline-flex w-full items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-violet-50 text-violet-700 text-sm font-semibold hover:bg-violet-100 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4" />
                                    </svg>
                                    Tải xuống
                                </a>
                            @else
                                <div class="inline-flex w-full items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100 text-slate-400 text-sm font-medium cursor-not-allowed">
                                    File không tồn tại
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
