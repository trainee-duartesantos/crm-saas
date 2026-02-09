<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { computed } from 'vue';

defineOptions({ layout: AppLayout });

const props = defineProps<{
    product: any;
    breakdown: Record<string, { units: number; value: number }>;
    timeline: { month: string; value: number }[];
    margin: number | null;
}>();

const totalUnits = computed(() =>
    props.product.deals.reduce(
        (sum: number, deal: any) => sum + deal.pivot.quantity,
        0,
    ),
);

const totalValue = computed(() =>
    props.product.deals.reduce(
        (sum: number, deal: any) =>
            sum + deal.pivot.quantity * deal.pivot.unit_price,
        0,
    ),
);
</script>

<template>
    <div class="mx-auto max-w-6xl space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold">{{ product.name }}</h1>

            <a
                href="/insights/products"
                class="rounded border px-4 py-2 text-sm hover:bg-gray-50"
            >
                Back to product statistics
            </a>
        </div>

        <!-- Global metrics -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="rounded border bg-white p-4">
                <div class="text-sm text-gray-500">Unit price</div>
                <div class="text-lg font-semibold">
                    € {{ product.unit_price }}
                </div>
            </div>

            <div class="rounded border bg-white p-4">
                <div class="text-sm text-gray-500">Total units</div>
                <div class="text-lg font-semibold">{{ totalUnits }}</div>
            </div>

            <div class="rounded border bg-white p-4">
                <div class="text-sm text-gray-500">Total value</div>
                <div class="text-lg font-semibold">
                    € {{ totalValue.toFixed(2) }}
                </div>
            </div>
        </div>

        <!-- Breakdown -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div
                v-for="(data, status) in breakdown"
                :key="status"
                class="rounded border bg-white p-4"
            >
                <div class="text-xs text-gray-500 uppercase">
                    {{ status }}
                </div>

                <div class="mt-1 text-sm">{{ data.units }} units</div>

                <div class="text-lg font-semibold">
                    € {{ data.value.toFixed(2) }}
                </div>
            </div>
        </div>

        <!-- Timeline -->
        <div class="rounded border bg-white p-5">
            <h3 class="mb-3 text-sm font-semibold text-gray-700">
                Revenue over time
            </h3>

            <div v-if="timeline.length === 0" class="text-sm text-gray-500">
                No historical data yet.
            </div>

            <ul v-else class="space-y-2 text-sm">
                <li
                    v-for="row in timeline"
                    :key="row.month"
                    class="flex justify-between"
                >
                    <span>{{ row.month }}</span>
                    <span>€ {{ row.value.toFixed(2) }}</span>
                </li>
            </ul>
        </div>

        <!-- Margin -->
        <div class="rounded border bg-white p-4">
            <div class="text-sm text-gray-500">Margin</div>

            <div v-if="margin !== null" class="text-lg font-semibold">
                € {{ margin.toFixed(2) }}
            </div>

            <div v-else class="text-sm text-gray-400">No cost defined</div>
        </div>

        <!-- Deals table -->
        <div class="rounded-lg border bg-white p-5">
            <h2 class="mb-3 text-sm font-semibold text-gray-700">
                Deals using this product
            </h2>

            <div
                v-if="product.deals.length === 0"
                class="text-sm text-gray-500"
            >
                This product has not been used in any deals yet.
            </div>

            <table v-else class="w-full border text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-2 text-left">Deal</th>
                        <th class="p-2 text-right">Qty</th>
                        <th class="p-2 text-right">Unit €</th>
                        <th class="p-2 text-right">Total €</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="deal in product.deals" :key="deal.id">
                        <td class="p-2">
                            <a
                                :href="`/deals/${deal.id}`"
                                class="text-indigo-600 hover:underline"
                            >
                                {{ deal.title }}
                            </a>
                        </td>

                        <td class="p-2 text-right">
                            {{ deal.pivot.quantity }}
                        </td>

                        <td class="p-2 text-right">
                            € {{ deal.pivot.unit_price }}
                        </td>

                        <td class="p-2 text-right">
                            €
                            {{
                                (
                                    deal.pivot.quantity * deal.pivot.unit_price
                                ).toFixed(2)
                            }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
