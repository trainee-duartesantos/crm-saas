<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';

defineOptions({
    layout: AppLayout,
});

defineProps<{
    statuses: string[];
    deals: Record<string, any[]>;
}>();

const moveDeal = (dealId: number, status: string) => {
    router.post(`/deals/${dealId}/move`, { status });
};
</script>

<template>
    <div class="mx-auto max-w-6xl space-y-6">
        <Heading
            title="Deals pipeline"
            description="Track and move deals across stages"
        />

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
                        <a
                            :href="`/deals/${deal.id}`"
                            class="font-medium text-indigo-600 hover:underline"
                        >
                            {{ deal.title }}
                        </a>

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
                            → Next stage
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
