<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

const form = ref({
    name: '',
    vat: '',
    email: '',
    phone: '',
    status: 'active',
});

const submit = () => {
    router.post('/entities', form.value);
};
</script>

<template>
    <div class="mx-auto max-w-3xl space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold">Create Entity</h1>

            <a
                href="/entities"
                class="rounded border px-3 py-2 text-sm hover:bg-gray-50"
            >
                ← Back
            </a>
        </div>

        <div class="space-y-4 rounded-lg border bg-white p-6">
            <div>
                <label class="text-sm text-gray-600">Name</label>
                <input
                    v-model="form.name"
                    class="mt-1 w-full rounded border px-3 py-2"
                    placeholder="Company name"
                />
            </div>

            <div>
                <label class="text-sm text-gray-600">VAT / NIF</label>
                <input
                    v-model="form.vat"
                    class="mt-1 w-full rounded border px-3 py-2"
                />
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="text-sm text-gray-600">Email</label>
                    <input
                        v-model="form.email"
                        type="email"
                        class="mt-1 w-full rounded border px-3 py-2"
                    />
                </div>

                <div>
                    <label class="text-sm text-gray-600">Phone</label>
                    <input
                        v-model="form.phone"
                        class="mt-1 w-full rounded border px-3 py-2"
                    />
                </div>
            </div>

            <div>
                <label class="text-sm text-gray-600">Status</label>
                <select
                    v-model="form.status"
                    class="mt-1 w-full rounded border px-3 py-2"
                >
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <div class="flex justify-end">
                <button
                    @click="submit"
                    class="rounded bg-indigo-600 px-4 py-2 font-semibold text-white"
                >
                    Create entity
                </button>
            </div>
        </div>
    </div>
</template>
