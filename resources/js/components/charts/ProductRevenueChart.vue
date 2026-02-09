<script setup lang="ts">
import {
    Chart,
    BarController,
    BarElement,
    CategoryScale,
    LinearScale,
    Tooltip,
} from 'chart.js';
import { onMounted, onBeforeUnmount, ref, watch } from 'vue';

Chart.register(
    BarController,
    BarElement,
    CategoryScale,
    LinearScale,
    Tooltip,
);

const props = defineProps<{
    labels: string[];
    values: number[];
    title?: string;
}>();

const canvasRef = ref<HTMLCanvasElement | null>(null);
let chart: Chart | null = null;

const renderChart = () => {
    if (!canvasRef.value) return;

    if (chart) chart.destroy();

    chart = new Chart(canvasRef.value, {
        type: 'bar',
        data: {
            labels: props.labels,
            datasets: [
                {
                    label: props.title ?? 'Revenue (€)',
                    data: props.values,
                    backgroundColor: 'rgba(79,70,229,0.7)',
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: (ctx) =>
                            `€ ${Number(ctx.raw).toFixed(2)}`,
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
onBeforeUnmount(() => chart?.destroy());
watch(() => [props.labels, props.values], renderChart);
</script>

<template>
    <div class="h-72">
        <canvas ref="canvasRef"></canvas>
    </div>
</template>
