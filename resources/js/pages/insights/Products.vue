<script setup lang="ts">
import ProductRevenueChart from '@/components/charts/ProductRevenueChart.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { computed } from 'vue';

defineOptions({ layout: AppLayout });

const props = defineProps<{
    stats: {
        product_id: number;
        product_name: string;
        total_units: number;
        total_value: number;
    }[];
}>();

const chartLabels = computed(() => props.stats.map((s) => s.product_name));

const chartValues = computed(() =>
    props.stats.map((s) => Number(s.total_value)),
);
</script>

<template>
    <div class="mx-auto max-w-6xl space-y-6">
        <h1 class="text-2xl font-semibold">Product statistics</h1>

        <!-- Chart -->
        <div v-if="stats.length" class="rounded border bg-white p-5">
            <h2 class="mb-3 text-sm font-semibold text-gray-700">
                Revenue by product
            </h2>

            <ProductRevenueChart
                :labels="chartLabels"
                :values="chartValues"
                title="Revenue (€)"
            />
        </div>

        <!-- Table -->
        <div v-if="stats.length === 0" class="text-sm text-gray-500">
            No product data available yet.
        </div>

        <table v-else class="w-full border-collapse border bg-white text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="border px-3 py-2 text-left">Product</th>
                    <th class="border px-3 py-2 text-right">Units</th>
                    <th class="border px-3 py-2 text-right">Total value (€)</th>
                </tr>
            </thead>

            <tbody>
                <tr v-for="row in stats" :key="row.product_id">
                    <td class="border px-3 py-2">
                        <a
                            :href="`/products/${row.product_id}`"
                            class="text-indigo-600 hover:underline"
                        >
                            {{ row.product_name }}
                        </a>
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
