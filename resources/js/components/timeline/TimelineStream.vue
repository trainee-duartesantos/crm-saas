<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { onBeforeUnmount, onMounted, ref } from 'vue';

type EventItem = {
    id: number;
    action: string;
    metadata?: any;
    created_at: string;
};

const items = ref<EventItem[]>([]);
let source: EventSource | null = null;

const closeStream = () => {
    if (source) {
        source.close();
        source = null;
    }
};

onMounted(() => {
    source = new EventSource('/stream/timeline');

    source.onmessage = (event) => {
        const data = JSON.parse(event.data);
        items.value.unshift(data);
    };

    source.onerror = () => {
        closeStream();
    };

    // 🔴 FECHA STREAM AO NAVEGAR
    router.on('before', () => {
        closeStream();
    });
});

onBeforeUnmount(() => {
    closeStream();
});
</script>

<template>
    <div class="space-y-2">
        <div
            v-for="e in items"
            :key="e.id"
            class="rounded border bg-white p-3 text-sm"
        >
            <div class="font-semibold">{{ e.action }}</div>

            <pre
                v-if="e.metadata"
                class="mt-1 text-xs whitespace-pre-wrap text-gray-500"
                >{{ e.metadata }}
            </pre>

            <div class="mt-1 text-xs text-gray-400">
                {{ new Date(e.created_at).toLocaleString() }}
            </div>
        </div>
    </div>
</template>
