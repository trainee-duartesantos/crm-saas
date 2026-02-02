<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

type ChatMessage = {
    id: number;
    role: 'user' | 'assistant';
    content: string;
};

const props = defineProps<{
    endpoint: string; // ex: /ai/tenant/next-action
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

const send = () => {
    if (!input.value.trim()) return;

    messages.value.push({
        id: Date.now(),
        role: 'user',
        content: input.value,
    });

    loading.value = true;
    scrollToBottom();

    router.post(
        props.endpoint,
        { message: input.value },
        {
            preserveScroll: true,
            onSuccess: (page: any) => {
                const reply =
                    page.props?.ai_reply ||
                    page.props?.lastInsight ||
                    'No response';

                messages.value.push({
                    id: Date.now() + 1,
                    role: 'assistant',
                    content: reply,
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

const copy = (text: string) => {
    navigator.clipboard.writeText(text);
};
</script>

<template>
    <div class="flex h-[500px] flex-col rounded-lg border bg-white">
        <!-- Messages -->
        <div ref="container" class="flex-1 space-y-4 overflow-y-auto p-4">
            <div
                v-for="m in messages"
                :key="m.id"
                class="flex"
                :class="m.role === 'user' ? 'justify-end' : 'justify-start'"
            >
                <div
                    class="max-w-[80%] rounded-lg px-4 py-2 text-sm leading-relaxed"
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
                </div>
            </div>

            <!-- Typing -->
            <div v-if="loading" class="flex gap-2 text-sm text-gray-500">
                🤖 AI is typing<span class="animate-pulse">...</span>
            </div>
        </div>

        <!-- Input -->
        <div class="flex gap-2 border-t p-3">
            <input
                v-model="input"
                @keydown.enter="send"
                :placeholder="placeholder ?? 'Ask the AI…'"
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
