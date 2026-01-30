<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

defineOptions({
    layout: AppLayout,
});

const props = defineProps<{
    activities: any[];
    people: { id: number; first_name: string; last_name: string }[];
    deals: { id: number; title: string; status: string }[];
    calendar: {
        today: any[];
        overdue: any[];
        upcoming: any[];
    };
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
    router.post(
        '/activities',
        {
            ...form.value,
            person_id: form.value.person_id || null,
            deal_id: form.value.deal_id || null,
            due_at: form.value.due_at || null,
        },
        {
            onSuccess: () => {
                form.value = {
                    type: 'task',
                    title: '',
                    notes: '',
                    due_at: '',
                    person_id: '',
                    deal_id: '',
                };
            },
        },
    );
};

const complete = (id: number) => {
    router.post(`/activities/${id}/complete`);
};

const pending = computed(() => props.activities.filter((a) => !a.completed_at));

const done = computed(() => props.activities.filter((a) => a.completed_at));

const badge = (type: string) => {
    if (type === 'call') return '📞';
    if (type === 'meeting') return '🤝';
    if (type === 'email') return '✉️';
    return '✅';
};
</script>

<template>
    <div class="mx-auto max-w-6xl space-y-8">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold">Activities</h1>

            <div class="flex gap-2">
                <a href="/timeline" class="rounded border px-3 py-2 text-sm"
                    >Timeline</a
                >
                <a href="/deals" class="rounded border px-3 py-2 text-sm"
                    >Pipeline</a
                >
                <a href="/people" class="rounded border px-3 py-2 text-sm"
                    >People</a
                >
                <a href="/insights" class="rounded border px-3 py-2 text-sm"
                    >Insights</a
                >
            </div>
        </div>

        <!-- Calendar overview -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <!-- Overdue -->
            <div class="rounded-lg border border-red-300 bg-red-50 p-4">
                <div class="mb-2 font-semibold text-red-700">⏰ Overdue</div>

                <div
                    v-if="calendar.overdue.length === 0"
                    class="text-sm text-red-600"
                >
                    No overdue activities 🎉
                </div>

                <div v-else class="space-y-2">
                    <div
                        v-for="a in calendar.overdue"
                        :key="a.id"
                        class="rounded border bg-white p-3"
                    >
                        <div class="font-semibold">
                            {{ badge(a.type) }} {{ a.title }}
                        </div>
                        <div class="text-xs text-gray-500">
                            Due {{ a.due_at }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Today -->
            <div class="rounded-lg border border-indigo-300 bg-indigo-50 p-4">
                <div class="mb-2 font-semibold text-indigo-700">📅 Today</div>

                <div
                    v-if="calendar.today.length === 0"
                    class="text-sm text-indigo-600"
                >
                    Nothing scheduled today
                </div>

                <div v-else class="space-y-2">
                    <div
                        v-for="a in calendar.today"
                        :key="a.id"
                        class="rounded border bg-white p-3"
                    >
                        <div class="font-semibold">
                            {{ badge(a.type) }} {{ a.title }}
                        </div>
                        <div class="text-xs text-gray-500">
                            {{ a.due_at }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upcoming -->
            <div class="rounded-lg border bg-white p-4">
                <div class="mb-2 font-semibold text-gray-700">🔮 Upcoming</div>

                <div
                    v-if="calendar.upcoming.length === 0"
                    class="text-sm text-gray-500"
                >
                    No upcoming activities
                </div>

                <div v-else class="space-y-2">
                    <div
                        v-for="a in calendar.upcoming"
                        :key="a.id"
                        class="rounded border p-3"
                    >
                        <div class="font-semibold">
                            {{ badge(a.type) }} {{ a.title }}
                        </div>
                        <div class="text-xs text-gray-500">
                            Due {{ a.due_at }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create -->
        <div class="rounded-lg border bg-white p-5">
            <h2 class="mb-3 text-sm font-semibold text-gray-700">
                Create activity
            </h2>

            <div class="grid grid-cols-1 gap-3 md:grid-cols-6">
                <div>
                    <select
                        v-model="form.type"
                        class="w-full rounded border px-3 py-2 text-sm"
                    >
                        <option value="task">Task</option>
                        <option value="call">Call</option>
                        <option value="meeting">Meeting</option>
                        <option value="email">Email</option>
                    </select>
                </div>

                <div class="md:col-span-3">
                    <input
                        v-model="form.title"
                        class="w-full rounded border px-3 py-2 text-sm"
                        placeholder="Follow up with client"
                    />
                </div>

                <div class="md:col-span-2">
                    <input
                        v-model="form.due_at"
                        type="datetime-local"
                        class="w-full rounded border px-3 py-2 text-sm"
                    />
                </div>

                <div class="md:col-span-3">
                    <select
                        v-model="form.person_id"
                        class="w-full rounded border px-3 py-2 text-sm"
                    >
                        <option value="">Person (optional)</option>
                        <option v-for="p in people" :key="p.id" :value="p.id">
                            {{ p.first_name }} {{ p.last_name }}
                        </option>
                    </select>
                </div>

                <div class="md:col-span-3">
                    <select
                        v-model="form.deal_id"
                        class="w-full rounded border px-3 py-2 text-sm"
                    >
                        <option value="">Deal (optional)</option>
                        <option v-for="d in deals" :key="d.id" :value="d.id">
                            {{ d.title }}
                        </option>
                    </select>
                </div>

                <div class="md:col-span-6">
                    <textarea
                        v-model="form.notes"
                        rows="2"
                        class="w-full rounded border px-3 py-2 text-sm"
                        placeholder="Notes"
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
            <h2 class="mb-3 text-sm font-semibold text-gray-700">
                Pending ({{ pending.length }})
            </h2>

            <div v-if="pending.length === 0" class="text-sm text-gray-500">
                No pending activities.
            </div>

            <div v-else class="space-y-3">
                <div
                    v-for="a in pending"
                    :key="a.id"
                    class="flex justify-between gap-4 rounded border p-4"
                >
                    <div>
                        <div class="font-semibold">
                            {{ badge(a.type) }} {{ a.title }}
                        </div>

                        <div class="mt-1 text-xs text-gray-500">
                            Due {{ a.due_at ?? '—' }}
                            <span v-if="a.deal">
                                •
                                <a
                                    :href="`/deals/${a.deal.id}`"
                                    class="text-indigo-600"
                                >
                                    {{ a.deal.title }}
                                </a>
                            </span>
                        </div>

                        <div v-if="a.notes" class="mt-2 text-sm text-gray-700">
                            {{ a.notes }}
                        </div>
                    </div>

                    <button
                        @click="complete(a.id)"
                        class="rounded bg-emerald-600 px-3 py-2 text-sm text-white"
                    >
                        Done
                    </button>
                </div>
            </div>
        </div>

        <!-- Done -->
        <div class="rounded-lg border bg-white p-5">
            <h2 class="mb-3 text-sm font-semibold text-gray-700">
                Completed ({{ done.length }})
            </h2>

            <div v-if="done.length === 0" class="text-sm text-gray-500">
                No completed activities.
            </div>

            <div v-else class="space-y-2">
                <div
                    v-for="a in done"
                    :key="a.id"
                    class="rounded border bg-gray-50 p-3 text-sm"
                >
                    {{ badge(a.type) }} {{ a.title }}
                </div>
            </div>
        </div>
    </div>
</template>
