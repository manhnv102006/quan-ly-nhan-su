<x-leader-layout title="Chat nội bộ" subtitle="Trao đổi với thành viên nhóm">
    <div class="leader-page">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">Chat nội bộ nhóm</h2>
            <p class="text-sm text-slate-500">Gửi thông báo hoặc trò chuyện trực tiếp với {{ $participants->count() }} thành viên.</p>
        </div>

        @if (session('success'))
            <div class="rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
        @endif

        @if ($announceRoute)
            <div class="leader-card p-5">
                <h3 class="text-sm font-bold text-slate-800">Gửi thông báo nội bộ</h3>
                <p class="mb-4 text-xs text-slate-500">Thông báo sẽ hiển thị nổi bật trong chat và gửi tới tất cả thành viên nhóm.</p>
                <form method="POST" action="{{ $announceRoute }}" class="grid gap-4 md:grid-cols-2">
                    @csrf
                    <div class="md:col-span-2">
                        <label class="leader-label">Tiêu đề</label>
                        <input type="text" name="title" value="{{ old('title') }}" class="leader-field" placeholder="Ví dụ: Họp nhóm sáng thứ 2">
                        @error('title')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="leader-label">Nội dung</label>
                        <textarea name="body" rows="3" class="leader-field" placeholder="Nội dung thông báo...">{{ old('body') }}</textarea>
                        @error('body')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="md:col-span-2">
                        <button type="submit" class="leader-btn-primary">Gửi thông báo</button>
                    </div>
                </form>
            </div>
        @endif

        @include('team-chat.partials.room', ['chat' => app(\App\Services\TeamChatService::class)])
    </div>
</x-leader-layout>
