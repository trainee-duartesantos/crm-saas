<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import { reactive } from 'vue';

defineOptions({
    layout: AppLayout,
});

const props = defineProps<{
    entities: {
        data: any[];
        links: any[];
    };
    filters: {
        search?: string;
        status?: string;
        vat?: string;
    };
}>();

const filters = reactive({
    search: props.filters.search ?? '',
    status: props.filters.status ?? '',
    vat: props.filters.vat ?? '',
});

const applyFilters = () => {
    router.get('/entities', filters, {
        preserveState: true,
        replace: true,
    });
};

const resetFilters = () => {
    filters.search = '';
    filters.status = '';
    filters.vat = '';
    applyFilters();
};
</script>

<template>
    <div class="mx-auto max-w-6xl space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold">Entities</h1>

            <button
                @click="router.visit('/entities/create')"
                class="rounded bg-indigo-600 px-4 py-2 text-sm font-semibold text-white"
            >
                + New entity
            </button>
        </div>

        <!-- Filters -->
        <div class="rounded-lg border bg-white p-4">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <div>
                    <label class="text-xs text-gray-500">Search name</label>
                    <input
                        v-model="filters.search"
                        @keyup.enter="applyFilters"
                        class="mt-1 w-full rounded border px-3 py-2 text-sm"
                        placeholder="Company name"
                    />
                </div>

                <div>
                    <label class="text-xs text-gray-500">VAT / NIF</label>
                    <input
                        v-model="filters.vat"
                        @keyup.enter="applyFilters"
                        class="mt-1 w-full rounded border px-3 py-2 text-sm"
                        placeholder="VAT number"
                    />
                </div>

                <div>
                    <label class="text-xs text-gray-500">Status</label>
                    <select
                        v-model="filters.status"
                        @change="applyFilters"
                        class="mt-1 w-full rounded border px-3 py-2 text-sm"
                    >
                        <option value="">All</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button
                        @click="applyFilters"
                        class="rounded bg-gray-900 px-4 py-2 text-sm text-white"
                    >
                        Apply
                    </button>
                    <button
                        @click="resetFilters"
                        class="rounded border px-4 py-2 text-sm"
                    >
                        Reset
                    </button>
                </div>
            </div>
        </div>

        <!-- List -->
        <div class="rounded-lg border bg-white">
            <table class="w-full text-sm">
                <thead class="border-b bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left">Name</th>
                        <th class="px-4 py-3 text-left">VAT</th>
                        <th class="px-4 py-3 text-left">Email</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>

                <tbody>
                    <tr
                        v-for="entity in entities.data"
                        :key="entity.id"
                        @click="router.visit(`/entities/${entity.id}`)"
                        class="cursor-pointer border-b hover:bg-gray-50"
                    >
                        <td class="px-4 py-3 font-semibold">
                            {{ entity.name }}
                        </td>

                        <td class="px-4 py-3 text-gray-600">
                            {{ entity.vat ?? '—' }}
                        </td>

                        <td class="px-4 py-3 text-gray-600">
                            {{ entity.email ?? '—' }}
                        </td>

                        <td class="px-4 py-3">
                            <span
                                class="rounded-full px-2 py-1 text-xs font-semibold"
                                :class="{
                                    'bg-green-100 text-green-700':
                                        entity.status === 'active',
                                    'bg-gray-200 text-gray-700':
                                        entity.status !== 'active',
                                }"
                            >
                                {{ entity.status }}
                            </span>
                        </td>

                        <td class="px-4 py-3 text-right">
                            <a
                                :href="`/entities/${entity.id}`"
                                class="text-indigo-600 hover:underline"
                            >
                                View →
                            </a>
                        </td>
                    </tr>

                    <tr v-if="entities.data.length === 0">
                        <td
                            colspan="5"
                            class="px-4 py-8 text-center text-gray-500"
                        >
                            No entities found.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
