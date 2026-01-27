<script setup lang="ts">
import { router } from '@inertiajs/vue3';

type TimelineItem = {
    id: number;
    action: string;
    created_at: string;
    metadata?: any;
};

const props = defineProps<{
    items: TimelineItem[];
}>();

const run = (url: string) => {
    router.post(url, {}, { preserveScroll: true });
};

const isAI = (action: string) => action?.startsWith('ai.');

const badge = (action: string) => {
    if (action === 'ai.timeline.summary') return 'Summary';
    if (action === 'ai.risk.detected') return 'Risk';
    if (action === 'ai.follow_up.draft') return 'Draft';
    if (action === 'ai.suggestion') return 'Suggestion';
    return 'AI';
};

const cardClasses = (action: string) => {
    if (!isAI(action)) return 'border-gray-200 bg-white';
    if (action === 'ai.risk.detected') return 'border-red-200 bg-red-50';
    if (action === 'ai.follow_up.draft')
        return 'border-emerald-200 bg-emerald-50';
    if (action === 'ai.timeline.summary')
        return 'border-indigo-200 bg-indigo-50';
    return 'border-blue-200 bg-blue-50';
};

const badgeClasses = (action: string) => {
    if (action === 'ai.risk.detected') return 'bg-red-600 text-white';
    if (action === 'ai.follow_up.draft') return 'bg-emerald-600 text-white';
    if (action === 'ai.timeline.summary') return 'bg-indigo-600 text-white';
    return 'bg-blue-600 text-white';
};
</script>

<template>
    <div class="mx-auto max-w-4xl space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold">Timeline</h1>

            <div class="flex flex-wrap gap-2">
                <button
                    @click="run('/ai/suggest/invite-follow-up')"
                    class="rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
                >
                    🤖 Suggest
                </button>

                <button
                    @click="run('/ai/summarize/timeline')"
                    class="rounded bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700"
                >
                    🧾 Summary (30d)
                </button>

                <button
                    @click="run('/ai/detect/risks')"
                    class="rounded bg-red-600 px-4 py-2 text-white hover:bg-red-700"
                >
                    ⚠️ Risks
                </button>

                <button
                    @click="run('/ai/draft/follow-up')"
                    class="rounded bg-emerald-600 px-4 py-2 text-white hover:bg-emerald-700"
                >
                    ✉️ Draft email
                </button>
            </div>
        </div>

        <div class="space-y-4">
            <div
                v-for="item in items"
                :key="item.id"
                class="rounded-lg border p-4"
                :class="cardClasses(item.action)"
            >
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="text-sm text-gray-500">
                            {{ item.created_at }}
                        </div>

                        <div class="mt-1 flex items-center gap-2">
                            <span
                                v-if="isAI(item.action)"
                                class="inline-flex items-center rounded px-2 py-0.5 text-xs font-semibold"
                                :class="badgeClasses(item.action)"
                            >
                                {{ badge(item.action) }}
                            </span>

                            <div class="font-semibold">
                                {{ item.action }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ✅ Email draft render bonito -->
                <div
                    v-if="item.action === 'ai.follow_up.draft'"
                    class="mt-3 space-y-2"
                >
                    <div class="text-sm">
                        <div class="font-semibold text-gray-700">To</div>
                        <div class="text-gray-900">
                            {{ item.metadata?.email }}
                        </div>
                    </div>

                    <div class="text-sm">
                        <div class="font-semibold text-gray-700">Subject</div>
                        <div class="text-gray-900">
                            {{ item.metadata?.subject }}
                        </div>
                    </div>

                    <div class="text-sm">
                        <div class="font-semibold text-gray-700">Body</div>
                        <pre
                            class="mt-1 rounded bg-white/60 p-3 text-sm whitespace-pre-wrap text-gray-900"
                            >{{ item.metadata?.body }}</pre
                        >
                    </div>
                </div>

                <!-- ✅ Mensagens AI normais -->
                <div
                    v-else-if="item.metadata?.message"
                    class="mt-3 text-gray-900"
                >
                    {{ item.metadata.message }}
                </div>

                <!-- fallback -->
                <pre
                    v-else
                    class="mt-3 rounded bg-white/60 p-3 text-xs text-gray-800"
                    >{{ JSON.stringify(item.metadata, null, 2) }}</pre
                >
            </div>
        </div>
    </div>
</template>
