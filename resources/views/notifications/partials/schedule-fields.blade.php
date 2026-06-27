@php
    $accent = $accent ?? 'violet';
    $inputFocusClass = $accent === 'emerald'
        ? 'focus:border-emerald-400'
        : 'focus:border-violet-400';
    $accentRing = $accent === 'emerald' ? 'focus:ring-emerald-500 text-emerald-600' : 'focus:ring-violet-500 text-violet-600';
    $minSchedule = now()->addMinute()->format('Y-m-d\TH:i');
@endphp

<div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-5 space-y-4"
     x-data="{ sendMode: @js(old('send_mode', 'immediate')) }">
    <p class="text-sm font-semibold text-slate-800">Thời gian gửi</p>

    <div class="flex flex-wrap gap-x-6 gap-y-3">
        <label class="inline-flex items-center gap-2 cursor-pointer">
            <input type="radio" name="send_mode" value="immediate" x-model="sendMode"
                   class="{{ $accentRing }}">
            <span class="text-sm text-slate-700">Gửi ngay</span>
        </label>
        <label class="inline-flex items-center gap-2 cursor-pointer">
            <input type="radio" name="send_mode" value="scheduled" x-model="sendMode"
                   class="{{ $accentRing }}">
            <span class="text-sm text-slate-700">Lên lịch gửi</span>
        </label>
    </div>

    <div x-show="sendMode === 'scheduled'" x-cloak class="space-y-2">
        <label for="scheduled_at" class="block text-xs font-semibold uppercase tracking-wider text-slate-400">
            Ngày giờ gửi
        </label>
        <input id="scheduled_at"
               name="scheduled_at"
               type="datetime-local"
               min="{{ $minSchedule }}"
               value="{{ old('scheduled_at') }}"
               class="w-full max-w-md rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700 {{ $inputFocusClass }} focus:outline-none">
        <p class="text-xs text-slate-500">
            Hệ thống tự gửi thông báo đúng thời điểm đã chọn (chạy mỗi phút).
        </p>
    </div>
</div>
