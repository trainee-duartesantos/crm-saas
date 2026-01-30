<script setup lang="ts">
import { router } from '@inertiajs/vue3';

const props = defineProps<{
    deal: any;
}>();

const move = (status: string) => {
    router.post(`/deals/${props.deal.id}/move`, { status });
};

const createActivity = () => {
    router.visit('/activities', {
        data: {
            deal_id: props.deal.id,
        },
    });
};
</script>

<template>
    <div class="mx-auto max-w-5xl space-y-6">
        <!-- Header -->
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-semibold">
                    {{ deal.title }}
                </h1>
                <div class="mt-1 text-sm text-gray-500">Deal details</div>
            </div>

            <div class="flex gap-2">
                <a
                    href="/deals"
                    class="rounded border px-3 py-2 text-sm hover:bg-gray-50"
                >
                    ← Back to pipeline
                </a>
            </div>
        </div>

        <!-- Summary -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <div class="rounded-lg border bg-white p-5">
                <div class="text-xs text-gray-500">Status</div>
                <div class="mt-1 font-semibold capitalize">
                    {{ deal.status }}
                </div>
            </div>

            <div class="rounded-lg border bg-white p-5">
                <div class="text-xs text-gray-500">Value</div>
                <div class="mt-1 font-semibold">
                    <span v-if="deal.value">€ {{ deal.value }}</span>
                    <span v-else class="text-gray-400">—</span>
                </div>
            </div>

            <div class="rounded-lg border bg-white p-5">
                <div class="text-xs text-gray-500">Last activity</div>
                <div class="mt-1 text-sm">
                    {{ deal.last_activity_at ?? '—' }}
                </div>
            </div>
        </div>

        <!-- Relations -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <!-- Person -->
            <div class="rounded-lg border bg-white p-5">
                <div class="mb-2 text-sm font-semibold">Person</div>

                <div v-if="deal.person">
                    <div class="font-medium">
                        {{ deal.person.first_name }} {{ deal.person.last_name }}
                    </div>
                    <div class="text-sm text-gray-500">
                        {{ deal.person.email ?? '—' }}
                    </div>

                    <a
                        :href="`/people/${deal.person.id}`"
                        class="mt-2 inline-block text-sm text-indigo-600 hover:underline"
                    >
                        View person →
                    </a>
                </div>

                <div v-else class="text-sm text-gray-500">No person linked</div>
            </div>

            <!-- Entity -->
            <div class="rounded-lg border bg-white p-5">
                <div class="mb-2 text-sm font-semibold">Entity</div>

                <div v-if="deal.entity">
                    <div class="font-medium">
                        {{ deal.entity.name }}
                    </div>
                    <div class="text-sm text-gray-500">
                        {{ deal.entity.vat ?? '—' }}
                    </div>

                    <a
                        :href="`/entities/${deal.entity.id}`"
                        class="mt-2 inline-block text-sm text-indigo-600 hover:underline"
                    >
                        View entity →
                    </a>
                </div>

                <div v-else class="text-sm text-gray-500">No entity linked</div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex flex-wrap gap-3">
            <button
                v-if="deal.status !== 'won'"
                @click="move('won')"
                class="rounded bg-emerald-600 px-4 py-2 text-sm font-semibold text-white"
            >
                Mark as won
            </button>

            <button
                v-if="deal.status !== 'lost'"
                @click="move('lost')"
                class="rounded bg-red-600 px-4 py-2 text-sm font-semibold text-white"
            >
                Mark as lost
            </button>

            <button
                @click="createActivity"
                class="rounded border px-4 py-2 text-sm hover:bg-gray-50"
            >
                + Add activity
            </button>

            <a
                href="/timeline"
                class="rounded border px-4 py-2 text-sm hover:bg-gray-50"
            >
                View timeline
            </a>
        </div>

        <!-- Activities -->
        <div class="rounded-lg border bg-white p-5">
            <div class="mb-3 text-sm font-semibold">Activities</div>

            <div
                v-if="deal.activities?.length === 0"
                class="text-sm text-gray-500"
            >
                No activities for this deal yet.
            </div>

            <ul v-else class="space-y-3">
                <li
                    v-for="a in deal.activities"
                    :key="a.id"
                    class="rounded border p-3 text-sm"
                >
                    <div class="font-medium">
                        {{ a.title }}
                    </div>
                    <div class="text-xs text-gray-500">
                        {{ a.type }} • Due {{ a.due_at ?? '—' }}
                    </div>
                </li>
            </ul>
        </div>
    </div>
</template>
