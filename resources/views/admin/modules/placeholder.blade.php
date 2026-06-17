<x-admin-layout :title="$title">
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-100 bg-gradient-to-r from-blue-50 to-white">
            <h2 class="text-xl font-semibold text-slate-800">{{ $title }}</h2>
            <p class="mt-1 text-sm text-slate-500">{{ $description }}</p>
        </div>

        <div class="p-8 text-center">
            <div class="mx-auto w-16 h-16 rounded-2xl bg-blue-50 flex items-center justify-center text-blue-600 mb-4">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.88m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z" />
                </svg>
            </div>
            <h3 class="text-lg font-medium text-slate-700">Chức năng đang được phát triển</h3>
            <p class="mt-2 text-sm text-slate-500 max-w-md mx-auto">
                Module <strong>{{ $title }}</strong> sẽ sớm được triển khai. Bạn có thể quay lại Dashboard để xem tổng quan hệ thống.
            </p>
            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 mt-6 px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition shadow-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Về Dashboard
            </a>
        </div>
    </div>
</x-admin-layout>
