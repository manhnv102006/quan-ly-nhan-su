<x-employee-layout title="Chat nội bộ" subtitle="Nhóm {{ $leader->full_name }}">
    <div class="employee-page space-y-6">
        <div>
            <h2 class="text-2xl font-bold text-slate-900">Chat nội bộ nhóm</h2>
            <p class="text-sm text-slate-500">Trao đổi với Trưởng nhóm và các thành viên trong nhóm.</p>
        </div>

        @include('team-chat.partials.room', [
            'chat' => app(\App\Services\TeamChatService::class),
            'asideClass' => 'employee-card p-5',
            'panelClass' => 'employee-card overflow-hidden',
            'fieldClass' => 'employee-field',
            'buttonClass' => 'employee-btn-primary',
        ])
    </div>
</x-employee-layout>
