<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import {
    CategoryScale,
    Chart,
    Filler,
    Legend,
    LinearScale,
    LineController,
    LineElement,
    PointElement,
    Tooltip,
} from 'chart.js';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';

Chart.register(
    LineController,
    LineElement,
    PointElement,
    LinearScale,
    CategoryScale,
    Tooltip,
    Filler,
    Legend,
);

defineOptions({ layout: AppLayout });

const props = defineProps<{
    product: any;
    breakdown: Record<string, { units: number; value: number }>;
    timeline: { month: string; value: number }[];
    margin: number | null;
    filters: { period: string };
}>();

const totalUnits = computed(() =>
    (props.product.deals ?? []).reduce(
        (sum: number, deal: any) => sum + Number(deal.pivot?.quantity ?? 0),
        0,
    ),
);

const totalValue = computed(() =>
    (props.product.deals ?? []).reduce((sum: number, deal: any) => {
        const q = Number(deal.pivot?.quantity ?? 0);
        const u = Number(deal.pivot?.unit_price ?? 0);
        return sum + q * u;
    }, 0),
);

/* -----------------------
   Period filter
----------------------- */
const period = ref(props.filters?.period ?? 'all');

watch(period, (v) => {
    router.get(
        `/products/${props.product.id}`,
        { period: v },
        { preserveState: true, preserveScroll: true },
    );
});

/* -----------------------
   Chart.js
----------------------- */
const chartRef = ref<HTMLCanvasElement | null>(null);
let chart: Chart | null = null;

const renderChart = () => {
    if (!chartRef.value) return;

    if (chart) chart.destroy();

    const labels = props.timeline.map((t) => t.month);
    const data = props.timeline.map((t) => Number(t.value));

    chart = new Chart(chartRef.value, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'Revenue (€)',
                    data,
                    tension: 0.3,
                    fill: true,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: true },
                tooltip: {
                    callbacks: {
                        label: (ctx) => `€ ${Number(ctx.raw).toFixed(2)}`,
                    },
                },
            },
            scales: {
                y: {
                    ticks: {
                        callback: (v) => `€ ${v}`,
                    },
                },
            },
        },
    });
};

onMounted(renderChart);

watch(
    () => props.timeline,
    () => renderChart(),
    { deep: true },
);

onBeforeUnmount(() => {
    if (chart) chart.destroy();
});
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

        <!-- Period filter -->
        <div class="flex items-center gap-2">
            <div class="text-sm text-gray-500">Period:</div>

            <select v-model="period" class="rounded border px-3 py-2 text-sm">
                <option value="all">All time</option>
                <option value="30">Last 30 days</option>
                <option value="90">Last 90 days</option>
            </select>
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
                    € {{ Number(data.value).toFixed(2) }}
                </div>
            </div>
        </div>

        <!-- Timeline chart -->
        <div class="rounded border bg-white p-5">
            <h3 class="mb-3 text-sm font-semibold text-gray-700">
                Revenue over time
            </h3>

            <div v-if="timeline.length === 0" class="text-sm text-gray-500">
                No historical data yet.
            </div>

            <div v-else class="h-64">
                <canvas ref="chartRef"></canvas>
            </div>
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
                v-if="(product.deals ?? []).length === 0"
                class="text-sm text-gray-500"
            >
                This product has not been used in any deals yet.
            </div>

            <table v-else class="w-full border text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-2 text-left">Deal</th>
                        <th class="p-2 text-right">Status</th>
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
                            {{ deal.status }}
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
                                    Number(deal.pivot.quantity) *
                                    Number(deal.pivot.unit_price)
                                ).toFixed(2)
                            }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
