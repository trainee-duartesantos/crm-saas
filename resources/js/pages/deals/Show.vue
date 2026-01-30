<script setup lang="ts">
import Heading from '@/components/Heading.vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

defineOptions({
    layout: AppLayout,
});

const props = defineProps<{
    deal: any;
}>();

const formatDate = (date: string | null) => {
    if (!date) return '—';
    return new Date(date).toLocaleString();
};

const move = (status: string) => {
    router.post(
        `/deals/${props.deal.id}/move`,
        { status },
        {
            preserveScroll: true,
        },
    );
};
</script>

<template>
    <div class="mx-auto max-w-5xl space-y-8">
        <!-- Header -->
        <Heading
            :title="deal.title"
            description="Deal details and activity history"
        />

        <!-- Main info -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <!-- Deal summary -->
            <div class="space-y-4 md:col-span-2">
                <div class="space-y-4 rounded-lg border bg-white p-6">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-500">Status</div>
                        <span
                            class="rounded-full px-3 py-1 text-xs font-semibold"
                            :class="{
                                'bg-gray-200 text-gray-700':
                                    deal.status === 'lead',
                                'bg-blue-200 text-blue-700':
                                    deal.status === 'qualified',
                                'bg-indigo-200 text-indigo-700':
                                    deal.status === 'proposal',
                                'bg-emerald-200 text-emerald-700':
                                    deal.status === 'won',
                                'bg-red-200 text-red-700':
                                    deal.status === 'lost',
                            }"
                        >
                            {{ deal.status }}
                        </span>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">Value</div>
                        <div class="text-lg font-semibold">
                            {{ deal.value ? `€ ${deal.value}` : '—' }}
                        </div>
                    </div>

                    <div>
                        <div class="text-sm text-gray-500">Last activity</div>
                        <div class="text-sm">
                            {{ formatDate(deal.last_activity_at) }}
                        </div>
                    </div>

                    <!-- Move actions -->
                    <div
                        v-if="!['won', 'lost'].includes(deal.status)"
                        class="border-t pt-4"
                    >
                        <button
                            @click="move('qualified')"
                            v-if="deal.status === 'lead'"
                            class="rounded bg-blue-600 px-4 py-2 text-sm text-white"
                        >
                            Move to qualified →
                        </button>

                        <button
                            @click="move('proposal')"
                            v-if="deal.status === 'qualified'"
                            class="rounded bg-indigo-600 px-4 py-2 text-sm text-white"
                        >
                            Move to proposal →
                        </button>

                        <button
                            @click="move('won')"
                            v-if="deal.status === 'proposal'"
                            class="rounded bg-emerald-600 px-4 py-2 text-sm text-white"
                        >
                            Mark as won 🎉
                        </button>
                    </div>
                </div>
            </div>

            <!-- Relations -->
            <div class="space-y-4">
                <!-- Person -->
                <div class="rounded-lg border bg-white p-5">
                    <div class="mb-2 text-sm font-semibold">Person</div>

                    <div v-if="deal.person">
                        <a
                            :href="`/people/${deal.person.id}`"
                            class="font-medium text-indigo-600 hover:underline"
                        >
                            {{ deal.person.first_name }}
                            {{ deal.person.last_name }}
                        </a>

                        <div class="text-xs text-gray-500">
                            {{ deal.person.email ?? '—' }}
                        </div>
                    </div>

                    <div v-else class="text-sm text-gray-500">
                        No person linked.
                    </div>
                </div>

                <!-- Entity -->
                <div class="rounded-lg border bg-white p-5">
                    <div class="mb-2 text-sm font-semibold">Entity</div>

                    <div v-if="deal.entity">
                        <a
                            :href="`/entities/${deal.entity.id}`"
                            class="font-medium text-indigo-600 hover:underline"
                        >
                            {{ deal.entity.name }}
                        </a>
                    </div>

                    <div v-else class="text-sm text-gray-500">
                        No entity linked.
                    </div>
                </div>
            </div>
        </div>

        <!-- Activities -->
        <div class="rounded-lg border bg-white p-6">
            <div class="mb-4 text-sm font-semibold">Activities</div>

            <div
                v-if="deal.activities.length === 0"
                class="rounded border border-dashed p-6 text-center text-sm text-gray-500"
            >
                No activities linked to this deal yet.
            </div>

            <div v-else class="space-y-3">
                <div
                    v-for="a in deal.activities"
                    :key="a.id"
                    class="rounded border p-4"
                >
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span
                                class="rounded bg-gray-100 px-2 py-0.5 text-xs"
                            >
                                {{ a.type }}
                            </span>
                            <div class="font-semibold">
                                {{ a.title }}
                            </div>
                        </div>

                        <div class="text-xs text-gray-500">
                            {{ formatDate(a.due_at) }}
                        </div>
                    </div>

                    <div v-if="a.notes" class="mt-2 text-sm text-gray-700">
                        {{ a.notes }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
