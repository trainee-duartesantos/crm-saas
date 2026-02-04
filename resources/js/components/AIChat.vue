<script setup lang="ts">
import { nextTick, onMounted, ref, watch } from 'vue';

type ChatCard = {
    title: string;
    href: string;
    meta?: Record<string, any>;
};

type QuickAction = {
    label: string;
    action: 'ask' | 'open' | 'create_activity' | 'create_activity_prefill';
    payload: any;
};

type ChatMessage = {
    id: number;
    role: 'user' | 'assistant';
    content: string;
    cards?: ChatCard[];
    quickActions?: QuickAction[];
};

const props = defineProps<{
    endpoint: string;
    page?: string;
    placeholder?: string;
    initialSessionId?: number | null;
}>();

/* ---------------- STATE ---------------- */
const open = ref(false);
const messages = ref<ChatMessage[]>([]);
const input = ref('');
const loading = ref(false);
const sessionId = ref<number | null>(
    props.initialSessionId ??
        (localStorage.getItem('ai_chat_session')
            ? Number(localStorage.getItem('ai_chat_session'))
            : null),
);

const container = ref<HTMLDivElement | null>(null);

/* ---------------- UX HELPERS ---------------- */
const scrollToBottom = async () => {
    await nextTick();
    container.value?.scrollTo({
        top: container.value.scrollHeight,
        behavior: 'smooth',
    });
};

watch(open, (v) => {
    localStorage.setItem('ai_chat_open', v ? '1' : '0');
});

onMounted(() => {
    open.value = localStorage.getItem('ai_chat_open') === '1';
});

/* ---------------- CHAT LOGIC ---------------- */
const send = (text?: string) => {
    const message = (text ?? input.value).trim();
    if (!message) return;

    messages.value.push({
        id: Date.now(),
        role: 'user',
        content: message,
    });

    loading.value = true;
    scrollToBottom();

    fetch(props.endpoint, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN':
                document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute('content') || '',
        },
        body: JSON.stringify({
            message,
            page: props.page ?? 'global',
            session_id: sessionId.value,
        }),
    })
        .then(async (res) => res.json())
        .then((data) => {
            if (data.session_id) {
                sessionId.value = data.session_id;
                localStorage.setItem(
                    'ai_chat_session',
                    String(data.session_id),
                );
            }

            messages.value.push({
                id: Date.now(),
                role: 'assistant',
                content:
                    data.payload?.answer ??
                    data.clarifying_question ??
                    'Sem resposta.',
                cards: data.payload?.cards ?? [],
                quickActions: data.payload?.quick_actions ?? [],
            });
        })

        .catch(() => {
            messages.value.push({
                id: Date.now(),
                role: 'assistant',
                content:
                    'Ocorreu um erro ao comunicar com o AI. Verifica a configuração.',
            });
        })
        .finally(() => {
            loading.value = false;
            scrollToBottom();
        });

    input.value = '';
};

const resetChat = () => {
    messages.value = [
        {
            id: Date.now(),
            role: 'assistant',
            content: '👋 Nova conversa iniciada. Em que posso ajudar?',
        },
    ];

    sessionId.value = null;
    localStorage.removeItem('ai_chat_session');
};

const handleAction = (action: QuickAction) => {
    if (action.action === 'ask') send(action.payload.text);
    if (action.action === 'open') window.location.href = action.payload.href;
};

const copy = (text: string) => navigator.clipboard.writeText(text);
</script>

<template>
    <!-- FLOATING WRAPPER -->
    <div class="fixed right-6 bottom-6 z-50">
        <!-- CLOSED STATE -->
        <button
            v-if="!open"
            @click="open = true"
            class="flex h-14 w-14 items-center justify-center rounded-full bg-indigo-600 text-white shadow-xl hover:bg-indigo-700"
            title="Ask the AI"
        >
            🤖
        </button>

        <!-- OPEN CHAT -->
        <div
            v-else
            class="flex w-[380px] flex-col rounded-xl border bg-white shadow-2xl"
        >
            <!-- HEADER -->
            <div
                class="flex items-center justify-between border-b px-4 py-3 font-semibold"
            >
                <div class="flex items-center gap-2">
                    🤖 Ask the AI
                    <span class="text-xs text-gray-500">CRM</span>
                </div>
                <button
                    @click="resetChat"
                    class="text-xs text-gray-400 hover:text-gray-700"
                >
                    Nova conversa
                </button>

                <button
                    @click="open = false"
                    class="text-gray-400 hover:text-gray-700"
                >
                    ✕
                </button>
            </div>

            <!-- MESSAGES -->
            <div
                ref="container"
                class="h-[380px] space-y-4 overflow-y-auto p-4"
            >
                <div
                    v-for="m in messages"
                    :key="m.id"
                    class="flex"
                    :class="m.role === 'user' ? 'justify-end' : 'justify-start'"
                >
                    <div
                        class="max-w-[80%] rounded-lg px-4 py-3 text-sm leading-relaxed"
                        :class="
                            m.role === 'user'
                                ? 'bg-gray-100 text-gray-900'
                                : 'bg-indigo-600 text-white'
                        "
                    >
                        <div v-html="m.content"></div>

                        <button
                            v-if="m.role === 'assistant'"
                            @click="copy(m.content)"
                            class="mt-2 block text-xs underline opacity-80"
                        >
                            Copy
                        </button>

                        <!-- CARDS -->
                        <div v-if="m.cards?.length" class="mt-3 space-y-2">
                            <a
                                v-for="card in m.cards"
                                :key="card.href"
                                :href="card.href"
                                class="block rounded border bg-white p-3 text-sm text-gray-900 hover:bg-gray-50"
                            >
                                <div class="font-medium">
                                    {{ card.title }}
                                </div>
                                <div
                                    v-if="card.meta"
                                    class="mt-1 text-xs text-gray-500"
                                >
                                    {{ card.meta }}
                                </div>
                            </a>
                        </div>

                        <!-- QUICK ACTIONS -->
                        <div
                            v-if="m.quickActions?.length"
                            class="mt-3 flex flex-wrap gap-2"
                        >
                            <button
                                v-for="(qa, i) in m.quickActions"
                                :key="i"
                                @click="handleAction(qa)"
                                class="rounded bg-gray-200 px-3 py-1 text-xs text-gray-900 hover:bg-white"
                            >
                                {{ qa.label }}
                            </button>
                        </div>
                    </div>
                </div>

                <div v-if="loading" class="text-sm text-gray-500">
                    🤖 A analisar dados<span class="animate-pulse">…</span>
                </div>
            </div>

            <!-- INPUT -->
            <div class="flex gap-2 border-t p-3">
                <input
                    v-model="input"
                    @keydown.enter="send()"
                    :placeholder="placeholder ?? 'Pergunta algo…'"
                    class="flex-1 rounded border px-3 py-2 text-sm"
                />
                <button
                    @click="send()"
                    class="rounded bg-indigo-600 px-4 py-2 text-sm text-white"
                >
                    Send
                </button>
            </div>
        </div>
    </div>
</template>
