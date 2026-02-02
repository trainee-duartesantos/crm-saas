<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

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
    endpoint: string; // /ai/chat
    page?: string;
    placeholder?: string;
}>();

const messages = ref<ChatMessage[]>([]);
const input = ref('');
const loading = ref(false);
const sessionId = ref<number | null>(null);
const container = ref<HTMLDivElement | null>(null);

const scrollToBottom = async () => {
    await nextTick();
    container.value?.scrollTo({
        top: container.value.scrollHeight,
        behavior: 'smooth',
    });
};

const send = (text?: string) => {
    const message = text ?? input.value;
    if (!message.trim()) return;

    messages.value.push({
        id: Date.now(),
        role: 'user',
        content: message,
    });

    loading.value = true;
    scrollToBottom();

    router.post(
        props.endpoint,
        {
            message,
            page: props.page,
            session_id: sessionId.value,
        },
        {
            preserveScroll: true,
            onSuccess: (page: any) => {
                const payload = page.props?.payload ?? page.props;

                sessionId.value = payload.session_id ?? sessionId.value;

                messages.value.push({
                    id: Date.now() + 1,
                    role: 'assistant',
                    content: payload.payload?.answer ?? 'Sem resposta.',
                    cards: payload.payload?.cards ?? [],
                    quickActions: payload.payload?.quick_actions ?? [],
                });

                loading.value = false;
                scrollToBottom();
            },
            onError: () => {
                loading.value = false;
            },
        },
    );

    input.value = '';
};

const handleAction = (action: QuickAction) => {
    if (action.action === 'ask') {
        send(action.payload.text);
    }

    if (action.action === 'open') {
        window.location.href = action.payload.href;
    }
};

const copy = (text: string) => {
    navigator.clipboard.writeText(text);
};
</script>

<template>
    <div class="rounded-lg border bg-white">
        <!-- Header -->
        <div class="flex items-center gap-2 border-b px-4 py-3 font-semibold">
            🤖 Ask the AI
            <span class="text-xs text-muted-foreground">
                Pesquisa direta no CRM
            </span>
        </div>

        <!-- Messages -->
        <div ref="container" class="h-[420px] space-y-4 overflow-y-auto p-4">
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
                    {{ m.content }}

                    <button
                        v-if="m.role === 'assistant'"
                        @click="copy(m.content)"
                        class="mt-2 block text-xs underline opacity-80"
                    >
                        Copy
                    </button>

                    <!-- Cards -->
                    <div v-if="m.cards?.length" class="mt-3 space-y-2">
                        <a
                            v-for="card in m.cards"
                            :key="card.href"
                            :href="card.href"
                            class="block rounded border bg-white p-3 text-sm text-gray-900 hover:bg-gray-50"
                        >
                            <div class="font-medium">{{ card.title }}</div>
                            <div
                                v-if="card.meta"
                                class="mt-1 text-xs text-gray-500"
                            >
                                {{ card.meta }}
                            </div>
                        </a>
                    </div>

                    <!-- Quick actions -->
                    <div
                        v-if="m.quickActions?.length"
                        class="mt-3 flex flex-wrap gap-2"
                    >
                        <button
                            v-for="(qa, i) in m.quickActions"
                            :key="i"
                            @click="handleAction(qa)"
                            class="rounded bg-gray-200 px-3 py-1 text-xs hover:bg-white"
                        >
                            {{ qa.label }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Typing -->
            <div v-if="loading" class="text-sm text-gray-500">
                🤖 A analisar dados<span class="animate-pulse">…</span>
            </div>
        </div>

        <!-- Input -->
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
</template>
