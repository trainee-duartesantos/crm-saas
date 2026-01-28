<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps<{
    activities: any[];
    people: { id: number; first_name: string; last_name: string }[];
    deals: { id: number; title: string; status: string }[];
}>();

const form = ref({
    type: 'task',
    title: '',
    notes: '',
    due_at: '',
    person_id: '',
    deal_id: '',
});

const submit = () => {
    router.post('/activities', {
        ...form.value,
        person_id: form.value.person_id ? Number(form.value.person_id) : null,
        deal_id: form.value.deal_id ? Number(form.value.deal_id) : null,
        due_at: form.value.due_at || null,
    });
};

const complete = (id: number) => {
    router.post(`/activities/${id}/complete`);
};

const pending = computed(() => props.activities.filter((a) => !a.completed_at));
const done = computed(() => props.activities.filter((a) => a.completed_at));
</script>

<template>
    <div class="mx-auto max-w-6xl space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold">Activities</h1>

            <div class="flex gap-2">
                <a
                    href="/timeline"
                    class="rounded border px-3 py-2 text-sm hover:bg-gray-50"
                    >Timeline</a
                >
                <a
                    href="/deals"
                    class="rounded border px-3 py-2 text-sm hover:bg-gray-50"
                    >Pipeline</a
                >
                <a
                    href="/insights"
                    class="rounded border px-3 py-2 text-sm hover:bg-gray-50"
                    >Insights</a
                >
                <a
                    href="/people"
                    class="rounded border px-3 py-2 text-sm hover:bg-gray-50"
                    >People</a
                >
            </div>
        </div>

        <!-- Create -->
        <div class="rounded-lg border bg-white p-5">
            <div class="mb-3 text-sm font-semibold text-gray-700">
                Create activity
            </div>

            <div class="grid grid-cols-1 gap-3 md:grid-cols-6">
                <div class="md:col-span-1">
                    <label class="text-xs text-gray-500">Type</label>
                    <select
                        v-model="form.type"
                        class="mt-1 w-full rounded border px-3 py-2 text-sm"
                    >
                        <option value="task">Task</option>
                        <option value="call">Call</option>
                        <option value="meeting">Meeting</option>
                        <option value="email">Email</option>
                    </select>
                </div>

                <div class="md:col-span-3">
                    <label class="text-xs text-gray-500">Title</label>
                    <input
                        v-model="form.title"
                        class="mt-1 w-full rounded border px-3 py-2 text-sm"
                        placeholder="e.g. Follow up with Duarte"
                    />
                </div>

                <div class="md:col-span-2">
                    <label class="text-xs text-gray-500">Due date</label>
                    <input
                        v-model="form.due_at"
                        type="datetime-local"
                        class="mt-1 w-full rounded border px-3 py-2 text-sm"
                    />
                </div>

                <div class="md:col-span-3">
                    <label class="text-xs text-gray-500"
                        >Person (optional)</label
                    >
                    <select
                        v-model="form.person_id"
                        class="mt-1 w-full rounded border px-3 py-2 text-sm"
                    >
                        <option value="">—</option>
                        <option
                            v-for="p in people"
                            :key="p.id"
                            :value="String(p.id)"
                        >
                            {{ p.first_name }} {{ p.last_name }}
                        </option>
                    </select>
                </div>

                <div class="md:col-span-3">
                    <label class="text-xs text-gray-500">Deal (optional)</label>
                    <select
                        v-model="form.deal_id"
                        class="mt-1 w-full rounded border px-3 py-2 text-sm"
                    >
                        <option value="">—</option>
                        <option
                            v-for="d in deals"
                            :key="d.id"
                            :value="String(d.id)"
                        >
                            {{ d.title }} ({{ d.status }})
                        </option>
                    </select>
                </div>

                <div class="md:col-span-6">
                    <label class="text-xs text-gray-500"
                        >Notes (optional)</label
                    >
                    <textarea
                        v-model="form.notes"
                        class="mt-1 w-full rounded border px-3 py-2 text-sm"
                        rows="3"
                        placeholder="Context / next steps..."
                    />
                </div>

                <div class="flex justify-end md:col-span-6">
                    <button
                        @click="submit"
                        class="rounded bg-indigo-600 px-4 py-2 text-sm font-semibold text-white"
                    >
                        Create
                    </button>
                </div>
            </div>
        </div>

        <!-- Pending -->
        <div class="rounded-lg border bg-white p-5">
            <div class="mb-3 flex items-center justify-between">
                <div class="text-sm font-semibold text-gray-700">
                    Pending ({{ pending.length }})
                </div>
            </div>

            <div v-if="pending.length === 0" class="text-sm text-gray-500">
                No pending activities.
            </div>

            <div v-else class="space-y-3">
                <div
                    v-for="a in pending"
                    :key="a.id"
                    class="rounded border p-4"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div>
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

                            <div class="mt-1 text-xs text-gray-500">
                                Due:
                                {{ a.due_at ?? '—' }}
                                <span v-if="a.person">
                                    • Person: {{ a.person.first_name }}
                                    {{ a.person.last_name }}</span
                                >
                                <span v-if="a.deal">
                                    • Deal: {{ a.deal.title }}</span
                                >
                            </div>

                            <div
                                v-if="a.notes"
                                class="mt-2 text-sm text-gray-700"
                            >
                                {{ a.notes }}
                            </div>
                        </div>

                        <button
                            @click="complete(a.id)"
                            class="rounded bg-emerald-600 px-3 py-2 text-sm text-white"
                        >
                            Mark done
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Done -->
        <div class="rounded-lg border bg-white p-5">
            <div class="mb-3 text-sm font-semibold text-gray-700">
                Completed ({{ done.length }})
            </div>

            <div v-if="done.length === 0" class="text-sm text-gray-500">
                No completed activities yet.
            </div>

            <div v-else class="space-y-2">
                <div
                    v-for="a in done"
                    :key="a.id"
                    class="rounded border border-gray-100 bg-gray-50 p-3"
                >
                    <div class="text-sm font-semibold text-gray-700">
                        {{ a.title }}
                        <span class="ml-2 text-xs text-gray-500"
                            >({{ a.type }})</span
                        >
                    </div>
                    <div class="text-xs text-gray-500">
                        Completed: {{ a.completed_at }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
