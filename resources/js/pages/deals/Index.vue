<script setup lang="ts">
import { router } from '@inertiajs/vue3';

defineProps<{
    statuses: string[];
    deals: Record<string, any[]>;
}>();

const moveDeal = (dealId: number, status: string) => {
    router.post(`/deals/${dealId}/move`, { status });
};
</script>

<template>
    <div class="mx-auto max-w-7xl space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold">Pipeline</h1>
            <div class="flex gap-2">
                <a href="/timeline" class="rounded border px-3 py-2 text-sm"
                    >Timeline</a
                >
                <a href="/insights" class="rounded border px-3 py-2 text-sm"
                    >Insights</a
                >
                <a href="/people" class="rounded border px-3 py-2 text-sm"
                    >People</a
                >
                <a href="/activities" class="rounded border px-3 py-2 text-sm"
                    >Activities</a
                >
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-5">
            <div
                v-for="status in statuses"
                :key="status"
                class="rounded-lg border bg-gray-50 p-3"
            >
                <h2 class="mb-3 text-sm font-semibold text-gray-600 uppercase">
                    {{ status }}
                </h2>

                <div class="space-y-2">
                    <div
                        v-for="deal in deals[status] ?? []"
                        :key="deal.id"
                        class="rounded bg-white p-3 shadow-sm"
                    >
                        <div class="font-medium">
                            {{ deal.title }}
                        </div>

                        <div v-if="deal.value" class="text-sm text-gray-500">
                            € {{ deal.value }}
                        </div>

                        <button
                            v-if="status !== 'won' && status !== 'lost'"
                            @click="
                                moveDeal(
                                    deal.id,
                                    statuses[statuses.indexOf(status) + 1],
                                )
                            "
                            class="mt-2 text-xs text-blue-600 hover:underline"
                        >
                            → Move forward
                        </button>
                    </div>

                    <div
                        v-if="!deals[status]?.length"
                        class="text-xs text-gray-400 italic"
                    >
                        No deals
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
