<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

defineOptions({
    layout: AppLayout,
});

defineProps<{
    statuses: string[];
    deals: Record<string, any[]>;
}>();

const showCreate = ref(false);

const form = ref({
    title: '',
    value: '',
});

const moveDeal = (dealId: number, status: string) => {
    router.post(`/deals/${dealId}/move`, { status });
};

const createDeal = () => {
    router.post('/deals', form.value, {
        onSuccess: () => {
            form.value = { title: '', value: '' };
            showCreate.value = false;
        },
    });
};
</script>

<template>
    <div class="mx-auto max-w-6xl space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <Heading
                title="Deals pipeline"
                description="Track and move deals across stages"
            />

            <button
                @click="showCreate = true"
                class="rounded bg-indigo-600 px-4 py-2 text-sm font-semibold text-white"
            >
                + New deal
            </button>
        </div>

        <!-- Pipeline -->
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

        <!-- Create Deal Modal -->
        <div
            v-if="showCreate"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
        >
            <div class="w-full max-w-md space-y-4 rounded bg-white p-6">
                <h3 class="text-lg font-semibold">New deal</h3>

                <input
                    v-model="form.title"
                    class="w-full rounded border px-3 py-2 text-sm"
                    placeholder="Deal title"
                />

                <input
                    v-model="form.value"
                    type="number"
                    class="w-full rounded border px-3 py-2 text-sm"
                    placeholder="Value (optional)"
                />

                <div class="flex justify-end gap-2">
                    <button
                        @click="showCreate = false"
                        class="rounded border px-4 py-2 text-sm"
                    >
                        Cancel
                    </button>

                    <button
                        @click="createDeal"
                        class="rounded bg-indigo-600 px-4 py-2 text-sm text-white"
                    >
                        Create
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
