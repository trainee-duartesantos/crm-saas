<script setup lang="ts">
import { onBeforeUnmount, ref } from 'vue';

const text = ref('');
let source: EventSource | null = null;

const start = () => {
    text.value = '';

    source?.close();
    source = new EventSource('/stream/ai/insight');

    source.onmessage = (e) => {
        const { chunk } = JSON.parse(e.data);
        text.value += chunk;
    };

    source.addEventListener('end', () => {
        source?.close();
    });
};

onBeforeUnmount(() => {
    source?.close();
});
</script>

<template>
    <div class="rounded-lg border border-indigo-300 bg-indigo-50 p-4">
        <button
            @click="start"
            class="mb-3 rounded bg-indigo-600 px-4 py-2 text-white"
        >
            🤖 Generate AI Insight (Live)
        </button>

        <p class="text-sm whitespace-pre-wrap text-indigo-900">
            {{ text || 'Waiting for AI output…' }}
        </p>
    </div>
</template>
