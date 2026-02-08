<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';

defineOptions({
    layout: AppLayout,
});

defineProps<{
    stats: any[];
    filters: {
        statuses: string[];
        from?: string | null;
        to?: string | null;
    };
}>();
</script>

<template>
    <div class="mx-auto max-w-6xl space-y-6">
        <h1 class="text-2xl font-semibold">Product statistics</h1>

        <div v-if="stats.length === 0" class="text-sm text-gray-500">
            No product data available yet.
        </div>

        <table v-else class="w-full border-collapse border">
            <thead>
                <tr class="bg-gray-50">
                    <th class="border px-3 py-2 text-left">Product</th>
                    <th class="border px-3 py-2 text-right">Units</th>
                    <th class="border px-3 py-2 text-right">Total value (€)</th>
                </tr>
            </thead>

            <tbody>
                <tr v-for="row in stats" :key="row.product_id">
                    <td class="border px-3 py-2">
                        {{ row.product_name }}
                    </td>
                    <td class="border px-3 py-2 text-right">
                        {{ row.total_units }}
                    </td>
                    <td class="border px-3 py-2 text-right">
                        € {{ Number(row.total_value).toFixed(2) }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
