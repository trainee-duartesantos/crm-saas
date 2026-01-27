<script setup lang="ts">
import { router } from '@inertiajs/vue3';

defineProps<{
    items: any[];
}>();

const askAI = () => {
    router.post('/ai/suggest/invite-follow-up');
};

const summarizeTimeline = () => {
    router.post('/ai/summarize/timeline');
};

const detectRisks = () => {
    router.post('/ai/detect/risks');
};
</script>

<template>
    <div class="mx-auto max-w-4xl space-y-6">
        <h1 class="text-2xl font-semibold">Timeline</h1>

        <!-- ✅ BOTÃO SEM FORM -->
        <button @click="askAI" class="rounded bg-blue-600 px-4 py-2 text-white">
            🤖 Ask AI for suggestion
        </button>

        <button
            @click="summarizeTimeline"
            class="mb-6 rounded bg-indigo-600 px-4 py-2 text-white"
        >
            🤖 Summarize last 30 days
        </button>
        <button
            @click="detectRisks"
            class="rounded bg-red-600 px-4 py-2 text-white"
        >
            ⚠️ Detect risks
        </button>

        <div
            v-for="item in items"
            :key="item.id"
            class="rounded border p-4"
            :class="
                item.action.startsWith('ai.')
                    ? 'border-blue-300 bg-blue-50'
                    : ''
            "
        >
            <div class="text-sm text-gray-500">
                {{ item.created_at }}
            </div>

            <div class="flex items-center gap-2 font-semibold">
                <span v-if="item.action.startsWith('ai.')">🤖</span>
                {{ item.action }}
            </div>

            <div v-if="item.metadata?.message" class="mt-2">
                {{ item.metadata.message }}
            </div>

            <pre v-else class="mt-2 rounded bg-gray-100 p-2 text-xs"
                >{{ JSON.stringify(item.metadata, null, 2) }}
  </pre
            >
        </div>
    </div>
</template>
