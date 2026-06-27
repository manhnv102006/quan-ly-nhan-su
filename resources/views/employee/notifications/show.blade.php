@php
    $typeMeta = \App\Support\NotificationTypeMeta::all();
    $meta = $typeMeta[$notification->type] ?? $typeMeta['system'];
    $isRead = (bool) $notification->is_read;
@endphp

<x-staff-layout
    title="Chi tiết thông báo"
    subtitle="{{ $notification->title }}"
    role="employee"
    :navigation="$navigation"
>
    <div class="space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <a href="{{ route('employee.notifications.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-600 text-sm font-medium hover:bg-slate-50 transition">
                ← Danh sách thông báo
            </a>
            @unless ($isRead)
                <span class="inline-flex items-center rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-700">
                    Mới
                </span>
            @endunless
        </div>

        <article class="overflow-hidden rounded-3xl border border-slate-100 bg-white shadow-sm">
            <div class="border-b border-slate-100 bg-gradient-to-r from-sky-50/80 to-white px-6 py-6 sm:px-8">
                <div class="flex flex-wrap items-start gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl {{ $meta['icon'] }}">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $meta['path'] }}" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $meta['badge'] }}">
                                {{ $meta['label'] }}
                            </span>
                            @if ($isRead)
                                <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-700">
                                    Đã đọc
                                </span>
                            @endif
                        </div>
                        <h1 class="mt-3 text-2xl font-bold text-slate-900 leading-snug">
                            {{ $notification->title }}
                        </h1>
                        <p class="mt-2 text-sm text-slate-500">
                            {{ \Illuminate\Support\Carbon::parse($notification->created_at)->format('d/m/Y H:i') }}
                            @if ($notification->sender_name ?? null)
                                · Gửi bởi <span class="font-medium text-slate-700">{{ $notification->sender_name }}</span>
                            @else
                                · Hệ thống tự động
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            <div class="px-6 py-8 sm:px-8">
                <div class="prose prose-slate max-w-none">
                    <p class="whitespace-pre-wrap text-base leading-relaxed text-slate-700">
                        {{ $notification->content }}
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 bg-slate-50/60 px-6 py-4 sm:px-8">
                <p class="text-xs text-slate-500">
                    @if ($notification->read_at ?? null)
                        Đã xem lúc {{ \Illuminate\Support\Carbon::parse($notification->read_at)->format('d/m/Y H:i') }}
                    @else
                        Vừa được đánh dấu đã đọc
                    @endif
                </p>
                <a href="{{ route('employee.notifications.index') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-sky-600 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-sky-700">
                    Quay lại danh sách
                </a>
            </div>
        </article>
    </div>
</x-staff-layout>
