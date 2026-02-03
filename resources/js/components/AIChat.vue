<script setup lang="ts">
import { nextTick, ref } from 'vue';

type ChatMessage = {
    id: number;
    role: 'user' | 'assistant';
    content: string;
};

const props = defineProps<{
    endpoint: string; // /ai/chat
    placeholder?: string;
}>();

const messages = ref<ChatMessage[]>([]);
const input = ref('');
const loading = ref(false);
const container = ref<HTMLDivElement | null>(null);

const scrollToBottom = async () => {
    await nextTick();
    container.value?.scrollTo({
        top: container.value.scrollHeight,
        behavior: 'smooth',
    });
};

const send = async () => {
    if (!input.value.trim()) return;

    const question = input.value;

    messages.value.push({
        id: Date.now(),
        role: 'user',
        content: question,
    });

    loading.value = true;
    input.value = '';
    await scrollToBottom();

    try {
        const res = await fetch(props.endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN':
                    document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute('content') || '',
            },
            body: JSON.stringify({
                message: question,
                page: 'insights',
            }),
        });

        const data = await res.json();

        messages.value.push({
            id: Date.now() + 1,
            role: 'assistant',
            content:
                data?.payload?.answer ??
                data?.answer ??
                data?.clarifying_question ??
                'Sem resposta.',
        });
    } catch (e) {
        messages.value.push({
            id: Date.now() + 2,
            role: 'assistant',
            content:
                '⚠️ Ocorreu um erro ao comunicar com a AI. Verifica a configuração.',
        });
    } finally {
        loading.value = false;
        await scrollToBottom();
    }
};
</script>

<template>
    <div class="rounded-lg border bg-white">
        <div class="flex items-center gap-2 border-b px-4 py-3 font-semibold">
            🤖 Ask the AI
            <span class="text-xs text-muted-foreground">
                Pesquisa direta no CRM
            </span>
        </div>

        <div ref="container" class="h-[420px] space-y-4 overflow-y-auto p-4">
            <div
                v-for="m in messages"
                :key="m.id"
                class="flex"
                :class="m.role === 'user' ? 'justify-end' : 'justify-start'"
            >
                <div
                    class="max-w-[80%] rounded-lg px-4 py-3 text-sm"
                    :class="
                        m.role === 'user'
                            ? 'bg-gray-100 text-gray-900'
                            : 'bg-indigo-600 text-white'
                    "
                >
                    {{ m.content }}
                </div>
            </div>

            <div v-if="loading" class="text-sm text-gray-500">
                🤖 A analisar dados<span class="animate-pulse">…</span>
            </div>
        </div>

        <div class="flex gap-2 border-t p-3">
            <input
                v-model="input"
                @keydown.enter="send"
                :placeholder="placeholder ?? 'Pergunta algo…'"
                class="flex-1 rounded border px-3 py-2 text-sm"
            />
            <button
                @click="send"
                class="rounded bg-indigo-600 px-4 py-2 text-sm text-white"
            >
                Send
            </button>
        </div>
    </div>
</template>
