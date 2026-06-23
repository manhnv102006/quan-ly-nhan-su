<x-admin-layout title="Lá»‹ch phá»ng váº¥n">

    <div class="space-y-6">

        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-sm text-slate-500">
                    <a href="{{ route('admin.recruitment') }}" class="hover:text-amber-600 transition">Tuyá»ƒn dá»¥ng</a>
                    <span>/</span>
                    <span class="text-slate-700 font-medium">Lá»‹ch phá»ng váº¥n</span>
                </div>

                <h2 class="mt-2 text-2xl font-bold text-slate-800">Danh sĂ¡ch lá»‹ch phá»ng váº¥n</h2>
                <p class="text-sm text-slate-500 mt-1">
                    Theo dĂµi toĂ n bá»™ lá»‹ch phá»ng váº¥n hiá»‡n cĂ³, á»©ng viĂªn liĂªn quan vĂ  káº¿t quáº£ hiá»‡n táº¡i cá»§a tá»«ng buá»•i phá»ng váº¥n.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('admin.recruitment.interviews.create') }}"
                   class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-amber-600 text-white font-medium hover:bg-amber-700 transition shadow-lg shadow-amber-500/20">
                    + Táº¡o lá»‹ch phá»ng váº¥n
                </a>

                <a href="{{ route('admin.recruitment') }}"
                   class="inline-flex items-center gap-2 px-5 py-3 rounded-xl border border-slate-200 bg-white text-slate-600 font-medium hover:bg-slate-50 transition">
                    Quay láº¡i tuyá»ƒn dá»¥ng
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="flex items-center gap-3 bg-white border border-emerald-200 shadow-sm rounded-2xl px-5 py-4">
                <p class="text-sm font-medium text-emerald-700">{{ session('success') }}</p>
            </div>
        @endif

        @if (session('error'))
            <div class="flex items-center gap-3 bg-white border border-red-200 shadow-sm rounded-2xl px-5 py-4">
                <p class="text-sm font-medium text-red-700">{{ session('error') }}</p>
            </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm text-slate-500">Tá»•ng lá»‹ch phá»ng váº¥n</p>
                <h3 class="text-3xl font-bold mt-2 text-slate-900">{{ $stats['total'] }}</h3>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm text-slate-500">Äang chá» káº¿t quáº£</p>
                <h3 class="text-3xl font-bold mt-2 text-amber-600">{{ $stats['pending'] }}</h3>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm text-slate-500">Äáº¡t</p>
                <h3 class="text-3xl font-bold mt-2 text-emerald-600">{{ $stats['passed'] }}</h3>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-sm text-slate-500">KhĂ´ng Ä‘áº¡t</p>
                <h3 class="text-3xl font-bold mt-2 text-rose-600">{{ $stats['failed'] }}</h3>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <h3 class="font-semibold text-slate-800">Danh sĂ¡ch lá»‹ch phá»ng váº¥n</h3>
                <p class="text-sm text-slate-500">Hiá»ƒn thá»‹ {{ $interviews->count() }} / {{ $interviews->total() }} báº£n ghi</p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200">
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-500">á»¨ng viĂªn</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-500">Tin tuyá»ƒn dá»¥ng</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-500">NgÆ°á»i phá»ng váº¥n</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-500">Thá»i gian</th>
                            <th class="px-6 py-3 text-center text-xs font-bold uppercase text-slate-500">Káº¿t quáº£</th>
                            <th class="px-6 py-3 text-left text-xs font-bold uppercase text-slate-500">Ghi chĂº</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($interviews as $interview)
                            <tr class="border-b border-slate-200 hover:bg-slate-50 transition">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-slate-800">{{ $interview->candidate?->full_name ?? 'á»¨ng viĂªn khĂ´ng tá»“n táº¡i' }}</div>
                                    <p class="mt-1 text-sm text-slate-500">{{ $interview->candidate?->phone ?? 'KhĂ´ng cĂ³ sá»‘ Ä‘iá»‡n thoáº¡i' }}</p>
                                </td>
                                <td class="px-6 py-4 text-slate-600">{{ $interview->candidate?->jobPost?->title ?? 'ChÆ°a gáº¯n tin tuyá»ƒn dá»¥ng' }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $interview->interviewer?->full_name ?? 'ChÆ°a phĂ¢n cĂ´ng' }}</td>
                                <td class="px-6 py-4 text-slate-600">{{ $interview->interview_date?->format('d/m/Y H:i') ?? '-' }}</td>
                                <td class="px-6 py-4 text-center">
                                    @if ($interview->result === 'pending')
                                        <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Äang chá»</span>
                                    @elseif ($interview->result === 'passed')
                                        <span class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Äáº¡t</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-700">KhĂ´ng Ä‘áº¡t</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-slate-600 max-w-sm">{{ $interview->note ?: 'KhĂ´ng cĂ³ ghi chĂº' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-12 text-slate-400">ChÆ°a cĂ³ lá»‹ch phá»ng váº¥n nĂ o trong há»‡ thá»‘ng.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($interviews->hasPages())
                <div class="px-6 py-4 border-t border-slate-200">
                    {{ $interviews->links() }}
                </div>
            @endif
        </div>

    </div>

</x-admin-layout>