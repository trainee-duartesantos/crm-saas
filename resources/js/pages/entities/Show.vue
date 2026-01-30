<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';

defineOptions({
    layout: AppLayout,
});

const props = defineProps<{
    entity: any;
}>();

const editing = ref(false);
const form = ref({
    name: props.entity.name,
    vat: props.entity.vat,
    email: props.entity.email,
    phone: props.entity.phone,
    website: props.entity.website,
    status: props.entity.status,
    notes: props.entity.notes,
});

const save = () => {
    router.put(`/entities/${props.entity.id}`, form.value, {
        onSuccess: () => (editing.value = false),
    });
};

const destroyEntity = () => {
    if (!confirm('Delete this entity?')) return;

    router.delete(`/entities/${props.entity.id}`);
};
</script>

<template>
    <div class="mx-auto max-w-5xl space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold">
                    {{ entity.name }}
                </h1>
                <div class="text-sm text-gray-500">Entity details</div>
            </div>

            <div class="flex gap-2">
                <button
                    @click="editing = !editing"
                    class="rounded border px-4 py-2 text-sm"
                >
                    {{ editing ? 'Cancel' : 'Edit' }}
                </button>

                <button
                    @click="destroyEntity"
                    class="rounded bg-red-600 px-4 py-2 text-sm text-white"
                >
                    Delete
                </button>
            </div>
        </div>

        <!-- Details -->
        <div class="rounded-lg border bg-white p-6">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="text-xs text-gray-500">Name</label>
                    <input
                        v-model="form.name"
                        :disabled="!editing"
                        class="mt-1 w-full rounded border px-3 py-2 text-sm"
                    />
                </div>

                <div>
                    <label class="text-xs text-gray-500">VAT / NIF</label>
                    <input
                        v-model="form.vat"
                        :disabled="!editing"
                        class="mt-1 w-full rounded border px-3 py-2 text-sm"
                    />
                </div>

                <div>
                    <label class="text-xs text-gray-500">Email</label>
                    <input
                        v-model="form.email"
                        :disabled="!editing"
                        class="mt-1 w-full rounded border px-3 py-2 text-sm"
                    />
                </div>

                <div>
                    <label class="text-xs text-gray-500">Phone</label>
                    <input
                        v-model="form.phone"
                        :disabled="!editing"
                        class="mt-1 w-full rounded border px-3 py-2 text-sm"
                    />
                </div>

                <div>
                    <label class="text-xs text-gray-500">Website</label>
                    <input
                        v-model="form.website"
                        :disabled="!editing"
                        class="mt-1 w-full rounded border px-3 py-2 text-sm"
                    />
                </div>

                <div>
                    <label class="text-xs text-gray-500">Status</label>
                    <select
                        v-model="form.status"
                        :disabled="!editing"
                        class="mt-1 w-full rounded border px-3 py-2 text-sm"
                    >
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="text-xs text-gray-500">Notes</label>
                    <textarea
                        v-model="form.notes"
                        :disabled="!editing"
                        rows="4"
                        class="mt-1 w-full rounded border px-3 py-2 text-sm"
                    />
                </div>
            </div>

            <div v-if="editing" class="mt-4 flex justify-end">
                <button
                    @click="save"
                    class="rounded bg-indigo-600 px-4 py-2 text-sm font-semibold text-white"
                >
                    Save changes
                </button>
            </div>
        </div>

        <!-- Relations -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <!-- People -->
            <div class="rounded-lg border bg-white p-5">
                <div class="mb-3 font-semibold">People</div>

                <div
                    v-if="entity.people.length === 0"
                    class="text-sm text-gray-500"
                >
                    No people linked to this entity.
                </div>

                <ul v-else class="space-y-2">
                    <li
                        v-for="p in entity.people"
                        :key="p.id"
                        class="flex justify-between rounded border p-2 text-sm"
                    >
                        <span> {{ p.first_name }} {{ p.last_name }} </span>
                        <a :href="`/people/${p.id}`" class="text-indigo-600">
                            View
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Deals -->
            <div class="rounded-lg border bg-white p-5">
                <div class="mb-3 font-semibold">Deals</div>

                <div
                    v-if="entity.deals.length === 0"
                    class="text-sm text-gray-500"
                >
                    No deals linked to this entity.
                </div>

                <ul v-else class="space-y-2">
                    <li
                        v-for="d in entity.deals"
                        :key="d.id"
                        class="flex justify-between rounded border p-2 text-sm"
                    >
                        <span>
                            {{ d.title }}
                            <span class="text-xs text-gray-500">
                                ({{ d.status }})
                            </span>
                        </span>
                        <a :href="`/deals/${d.id}`" class="text-indigo-600">
                            View
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>
