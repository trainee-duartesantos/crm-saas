<script setup lang="ts">
import Heading from '@/components/Heading.vue';
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

/* -----------------------
   Helpers
------------------------*/
const formatDate = (date: string | null) => {
    if (!date) return '—';
    return new Date(date).toLocaleString();
};

const isOverdue = (a: any) =>
    !a.completed_at && a.due_at && new Date(a.due_at) < new Date();

/* -----------------------
   Form
------------------------*/
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
            person_id: form.value.person_id
                ? Number(form.value.person_id)
                : null,
            deal_id: form.value.deal_id ? Number(form.value.deal_id) : null,
            due_at: form.value.due_at || null,
        },
        {
            onSuccess: () => {
                form.value.title = '';
                form.value.notes = '';
                form.value.due_at = '';
                form.value.person_id = '';
                form.value.deal_id = '';
            },
        },
    );
};

const complete = (id: number) => {
    router.post(`/activities/${id}/complete`, {}, { preserveScroll: true });
};

/* -----------------------
   Computed
------------------------*/
const pending = computed(() => props.activities.filter((a) => !a.completed_at));

const done = computed(() => props.activities.filter((a) => a.completed_at));
</script>

<template>
    <div class="mx-auto max-w-6xl space-y-8">
        <Heading
            title="Activities"
            description="Tasks, calls and follow-ups across your pipeline"
        />

        <!-- 📅 Calendar overview -->
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
                        <div class="text-sm font-semibold">{{ a.title }}</div>
                        <div class="text-xs text-gray-500">
                            Due {{ formatDate(a.due_at) }}
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
                        <div class="text-sm font-semibold">{{ a.title }}</div>
                        <div class="text-xs text-gray-500">
                            {{ a.type }} • {{ formatDate(a.due_at) }}
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
                        <div class="text-sm font-semibold">{{ a.title }}</div>
                        <div class="text-xs text-gray-500">
                            Due {{ formatDate(a.due_at) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ➕ Create -->
        <div class="space-y-4 rounded-lg border bg-white p-6">
            <div class="text-sm font-semibold text-gray-700">
                Create activity
            </div>

            <div class="grid grid-cols-1 gap-3 md:grid-cols-6">
                <div>
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
                        placeholder="e.g. Follow up with client"
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
                    <label class="text-xs text-gray-500">Person</label>
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
                    <label class="text-xs text-gray-500">Deal</label>
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
                    <label class="text-xs text-gray-500">Notes</label>
                    <textarea
                        v-model="form.notes"
                        rows="3"
                        class="mt-1 w-full rounded border px-3 py-2 text-sm"
                    />
                </div>

                <div class="flex justify-end md:col-span-6">
                    <button
                        @click="submit"
                        class="rounded bg-indigo-600 px-4 py-2 text-sm font-semibold text-white"
                    >
                        Create activity
                    </button>
                </div>
            </div>
        </div>

        <!-- ⏳ Pending -->
        <div class="rounded-lg border bg-white p-6">
            <div class="mb-4 text-sm font-semibold text-gray-700">
                Pending ({{ pending.length }})
            </div>

            <div v-if="pending.length === 0" class="text-sm text-gray-500">
                No pending activities.
            </div>

            <div v-else class="space-y-3">
                <div
                    v-for="a in pending"
                    :key="a.id"
                    class="rounded border p-4"
                    :class="isOverdue(a) ? 'border-red-300 bg-red-50' : ''"
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
                                Due: {{ formatDate(a.due_at) }}
                                <span v-if="a.deal">
                                    • Deal:
                                    <a
                                        :href="`/deals/${a.deal.id}`"
                                        class="text-indigo-600 hover:underline"
                                    >
                                        {{ a.deal.title }}
                                    </a>
                                </span>
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

        <!-- ✅ Done -->
        <div class="rounded-lg border bg-white p-6">
            <div class="mb-4 text-sm font-semibold text-gray-700">
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
                        <span class="ml-2 text-xs text-gray-500">
                            ({{ a.type }})
                        </span>
                    </div>
                    <div class="text-xs text-gray-500">
                        Completed {{ formatDate(a.completed_at) }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
