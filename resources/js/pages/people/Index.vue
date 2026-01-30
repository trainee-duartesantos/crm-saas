<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

defineOptions({
    layout: AppLayout,
});

defineProps<{
    people: Array<{
        id: number;
        first_name: string;
        last_name: string;
        email: string | null;
        company?: { name: string } | null;
    }>;
}>();

const firstName = ref('');
const lastName = ref('');
const email = ref('');

const submit = () => {
    router.post(
        '/people',
        {
            first_name: firstName.value,
            last_name: lastName.value,
            email: email.value,
        },
        {
            onSuccess: () => {
                firstName.value = '';
                lastName.value = '';
                email.value = '';
            },
        },
    );
};
</script>

<template>
    <div class="mx-auto max-w-5xl space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold">People</h1>

            <div class="flex gap-2">
                <a
                    href="/timeline"
                    class="rounded border px-3 py-2 text-sm hover:bg-gray-50"
                    >Timeline</a
                >
                <a
                    href="/insights"
                    class="rounded border px-3 py-2 text-sm hover:bg-gray-50"
                    >Insights</a
                >
                <a
                    href="/deals"
                    class="rounded border px-3 py-2 text-sm hover:bg-gray-50"
                    >Pipeline</a
                >
                <a
                    href="/activities"
                    class="rounded border px-3 py-2 text-sm hover:bg-gray-50"
                    >Activities</a
                >
            </div>
        </div>

        <!-- Create person -->
        <div class="rounded-lg border bg-white p-4">
            <h2 class="mb-3 font-semibold">Add new person</h2>

            <div class="grid grid-cols-1 gap-3 md:grid-cols-4">
                <input
                    v-model="firstName"
                    placeholder="First name"
                    class="rounded border px-3 py-2"
                />
                <input
                    v-model="lastName"
                    placeholder="Last name"
                    class="rounded border px-3 py-2"
                />
                <input
                    v-model="email"
                    placeholder="Email (optional)"
                    class="rounded border px-3 py-2"
                />

                <button
                    @click="submit"
                    class="rounded bg-indigo-600 px-4 py-2 text-white"
                >
                    Add
                </button>
            </div>
        </div>

        <!-- People list -->
        <div class="rounded-lg border bg-white">
            <table class="w-full text-sm">
                <thead class="border-b bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left">Name</th>
                        <th class="px-4 py-2 text-left">Email</th>
                        <th class="px-4 py-2 text-left">Company</th>
                    </tr>
                </thead>

                <tbody>
                    <tr
                        v-for="person in people"
                        :key="person.id"
                        class="border-b last:border-0"
                    >
                        <td class="px-4 py-2 font-medium">
                            {{ person.first_name }} {{ person.last_name }}
                        </td>

                        <td class="px-4 py-2 text-gray-600">
                            {{ person.email ?? '—' }}
                        </td>

                        <td class="px-4 py-2 text-gray-600">
                            {{ person.company?.name ?? '—' }}
                        </td>
                    </tr>

                    <tr v-if="people.length === 0">
                        <td
                            colspan="3"
                            class="px-4 py-6 text-center text-gray-500"
                        >
                            No people yet.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
