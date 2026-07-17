@php
    $initialMessages = $chat->serializeMessages($messages, (int) $membership['member']->id);
@endphp

<div
    x-data="teamChatRoom({
        initialMessages: @js($initialMessages),
        messagesUrl: @js($messagesRoute),
        storeUrl: @js($storeRoute),
        csrf: @js(csrf_token()),
    })"
    class="grid grid-cols-1 gap-6 xl:grid-cols-[280px_minmax(0,1fr)]"
>
    <aside class="{{ $asideClass ?? 'leader-card p-5' }}">
        <h3 class="text-sm font-bold text-slate-800">Thành viên nhóm</h3>
        <p class="mt-1 text-xs text-slate-500">Trưởng nhóm: {{ $leader->full_name }}</p>
        <ul class="mt-4 space-y-2">
            @foreach($participants as $participant)
                <li class="flex items-center gap-3 rounded-xl bg-slate-50 px-3 py-2.5">
                    <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-violet-100 text-sm font-bold text-violet-700">
                        {{ strtoupper(mb_substr($participant->full_name, 0, 1)) }}
                    </span>
                    <span class="min-w-0">
                        <span class="block truncate text-sm font-semibold text-slate-800">{{ $participant->full_name }}</span>
                        <span class="block truncate text-[11px] text-slate-400">
                            @if((int) $participant->id === (int) $leader->id)
                                Trưởng nhóm
                            @else
                                {{ $participant->employee_code }}
                            @endif
                        </span>
                    </span>
                </li>
            @endforeach
        </ul>
    </aside>

    <div class="{{ $panelClass ?? 'leader-card overflow-hidden' }} flex min-h-[560px] flex-col">
        <div class="border-b border-violet-100/80 px-5 py-4">
            <h3 class="text-sm font-bold text-slate-800">Chat nội bộ nhóm</h3>
            <p class="text-xs text-slate-500">Trao đổi trực tiếp giữa các thành viên trong nhóm</p>
        </div>

        <div x-ref="messageList" class="flex-1 space-y-3 overflow-y-auto bg-slate-50/60 px-4 py-4">
            <template x-for="message in messages" :key="message.id">
                <div :class="message.is_announcement ? 'rounded-2xl border border-amber-200 bg-amber-50 p-4' : (message.is_mine ? 'ml-auto max-w-[85%] rounded-2xl rounded-br-md bg-violet-600 px-4 py-3 text-white' : 'mr-auto max-w-[85%] rounded-2xl rounded-bl-md border border-slate-200 bg-white px-4 py-3')">
                    <template x-if="message.is_announcement">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wide text-amber-700">Thông báo nội bộ</p>
                            <p class="mt-1 font-bold text-slate-900" x-text="message.title"></p>
                            <p class="mt-2 whitespace-pre-wrap text-sm text-slate-700" x-text="message.body"></p>
                            <p class="mt-2 text-[11px] text-slate-500"><span x-text="message.sender_name"></span> · <span x-text="message.time"></span></p>
                        </div>
                    </template>
                    <template x-if="!message.is_announcement">
                        <div>
                            <p class="text-[11px] font-semibold" :class="message.is_mine ? 'text-violet-100' : 'text-slate-500'" x-text="message.sender_name"></p>
                            <p class="mt-1 whitespace-pre-wrap text-sm" :class="message.is_mine ? 'text-white' : 'text-slate-800'" x-text="message.body"></p>
                            <p class="mt-1 text-[10px]" :class="message.is_mine ? 'text-violet-100/80' : 'text-slate-400'" x-text="message.time"></p>
                        </div>
                    </template>
                </div>
            </template>
        </div>

        <div class="border-t border-slate-100 p-4">
            <form @submit.prevent="sendMessage" class="flex gap-3">
                <textarea
                    x-model="draft"
                    rows="2"
                    class="{{ $fieldClass ?? 'leader-field' }} min-h-[52px] flex-1 resize-none"
                    placeholder="Nhập tin nhắn..."
                ></textarea>
                <button type="submit" class="{{ $buttonClass ?? 'leader-btn-primary' }} self-end" :disabled="sending || draft.trim() === ''">
                    Gửi
                </button>
            </form>
        </div>
    </div>
</div>

@once
    @push('scripts')
        <script>
            function teamChatRoom(config) {
                return {
                    messages: config.initialMessages,
                    draft: '',
                    sending: false,
                    lastId: config.initialMessages.length ? config.initialMessages[config.initialMessages.length - 1].id : 0,
                    pollTimer: null,
                    init() {
                        this.scrollToBottom();
                        this.pollTimer = setInterval(() => this.pollMessages(), 5000);
                    },
                    scrollToBottom() {
                        this.$nextTick(() => {
                            const list = this.$refs.messageList;
                            if (list) {
                                list.scrollTop = list.scrollHeight;
                            }
                        });
                    },
                    async pollMessages() {
                        try {
                            const response = await fetch(`${config.messagesUrl}?after_id=${this.lastId}`, {
                                headers: { 'Accept': 'application/json' },
                            });
                            if (!response.ok) return;
                            const data = await response.json();
                            if (data.messages?.length) {
                                this.messages.push(...data.messages);
                                this.lastId = data.messages[data.messages.length - 1].id;
                                this.scrollToBottom();
                            }
                        } catch (error) {
                            console.error(error);
                        }
                    },
                    async sendMessage() {
                        const body = this.draft.trim();
                        if (!body || this.sending) return;
                        this.sending = true;
                        try {
                            const response = await fetch(config.storeUrl, {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': config.csrf,
                                },
                                body: JSON.stringify({ body }),
                            });
                            if (!response.ok) return;
                            const data = await response.json();
                            if (data.message) {
                                this.messages.push(data.message);
                                this.lastId = data.message.id;
                                this.draft = '';
                                this.scrollToBottom();
                            }
                        } catch (error) {
                            console.error(error);
                        } finally {
                            this.sending = false;
                        }
                    },
                };
            }
        </script>
    @endpush
@endonce
