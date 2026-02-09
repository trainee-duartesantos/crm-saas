<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import {
    CategoryScale,
    Chart,
    Legend,
    LinearScale,
    LineController,
    LineElement,
    PointElement,
    Tooltip,
} from 'chart.js';
import { computed, onMounted, ref, watch } from 'vue';

Chart.register(
    LineController,
    LineElement,
    PointElement,
    LinearScale,
    CategoryScale,
    Tooltip,
    Legend,
);

defineOptions({ layout: AppLayout });

const props = defineProps<{
    product: any;
    breakdown: Record<string, { units: number; value: number }>;
    timeline: {
        month: string;
        won: number;
        lost: number;
        margin: number | null;
    }[];
    period: string;
}>();

/* -----------------------
   Global stats
----------------------- */
const totalUnits = computed(() =>
    props.product.deals.reduce(
        (sum: number, d: any) => sum + d.pivot.quantity,
        0,
    ),
);

const totalValue = computed(() =>
    props.product.deals.reduce(
        (sum: number, d: any) => sum + d.pivot.quantity * d.pivot.unit_price,
        0,
    ),
);

/* -----------------------
   Chart
----------------------- */
const chartRef = ref<HTMLCanvasElement | null>(null);
let chart: Chart | null = null;

const renderChart = () => {
    if (!chartRef.value) return;

    if (chart) chart.destroy();

    chart = new Chart(chartRef.value, {
        type: 'line',
        data: {
            labels: props.timeline.map((t) => t.month),
            datasets: [
                {
                    label: 'Won (€)',
                    data: props.timeline.map((t) => t.won),
                    borderColor: '#16a34a',
                    tension: 0.3,
                },
                {
                    label: 'Lost (€)',
                    data: props.timeline.map((t) => t.lost),
                    borderColor: '#dc2626',
                    tension: 0.3,
                },
                ...(props.timeline.some((t) => t.margin !== null)
                    ? [
                          {
                              label: 'Margin (€)',
                              data: props.timeline.map((t) => t.margin),
                              borderColor: '#9333ea',
                              borderDash: [5, 5],
                              tension: 0.3,
                          },
                      ]
                    : []),
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
        },
    });
};

onMounted(renderChart);
watch(() => props.timeline, renderChart);

/* -----------------------
   Period filter
----------------------- */
const setPeriod = (p: string) => {
    window.location.href = `/products/${props.product.id}?period=${p}`;
};
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
                ← Back
            </a>
        </div>

        <!-- Period filter -->
        <div class="flex gap-2">
            <button
                @click="setPeriod('30')"
                :class="period === '30' ? 'bg-indigo-600 text-white' : 'border'"
                class="rounded px-3 py-1 text-sm"
            >
                Last 30 days
            </button>

            <button
                @click="setPeriod('90')"
                :class="period === '90' ? 'bg-indigo-600 text-white' : 'border'"
                class="rounded px-3 py-1 text-sm"
            >
                Last 90 days
            </button>
        </div>

        <!-- KPIs -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
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

                <div class="text-sm">{{ data.units }} units</div>
                <div class="text-lg font-semibold">
                    € {{ data.value.toFixed(2) }}
                </div>
            </div>
        </div>

        <!-- Chart -->
        <div class="rounded border bg-white p-5">
            <h3 class="mb-3 text-sm font-semibold">
                Revenue / Margin over time
            </h3>

            <div v-if="timeline.length === 0" class="text-sm text-gray-500">
                No data yet.
            </div>

            <div v-else class="h-64">
                <canvas ref="chartRef"></canvas>
            </div>
        </div>
    </div>
</template>
