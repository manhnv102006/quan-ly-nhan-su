<x-admin-layout :title="$title">
    <div class="admin-card overflow-hidden">
        <div class="relative px-6 sm:px-8 py-6 border-b border-slate-100 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-violet-50 via-indigo-50 to-cyan-50"></div>
            <div class="relative">
                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-violet-100 text-violet-700 text-[10px] font-bold uppercase tracking-wider mb-3">
                    Module
                </span>
                <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">{{ $title }}</h2>
                <p class="mt-1.5 text-sm text-slate-500 max-w-xl">{{ $description }}</p>
            </div>
        </div>

        <div class="px-6 sm:px-8 py-12 sm:py-16 text-center">
            <div class="relative mx-auto w-20 h-20 mb-6">
                <div class="absolute inset-0 bg-gradient-to-br from-violet-400 to-cyan-400 rounded-3xl blur-xl opacity-40"></div>
                <div class="relative w-20 h-20 rounded-3xl bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center text-white shadow-xl shadow-violet-500/30">
                    <svg class="w-9 h-9" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.88m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z" />
                    </svg>
                </div>
            </div>

            <h3 class="text-xl font-bold text-slate-800">Đang phát triển 🛠️</h3>
            <p class="mt-3 text-sm text-slate-500 max-w-md mx-auto leading-relaxed">
                Module <strong class="text-violet-600">{{ $title }}</strong> sẽ sớm ra mắt với giao diện hiện đại và đầy đủ tính năng.
            </p>

            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 mt-8 px-6 py-3 bg-gradient-to-r from-violet-600 to-indigo-600 text-white text-sm font-bold rounded-xl hover:from-violet-700 hover:to-indigo-700 transition shadow-lg shadow-violet-500/25 hover:shadow-violet-500/40 hover:-translate-y-0.5">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Về Dashboard
            </a>
        </div>
    </div>
</x-admin-layout>
