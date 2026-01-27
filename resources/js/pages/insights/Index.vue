<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import {
    ArcElement,
    BarController,
    BarElement,
    CategoryScale,
    Chart,
    DoughnutController,
    Legend,
    LinearScale,
    Tooltip,
} from 'chart.js';
import { onMounted, ref } from 'vue';

Chart.register(
    DoughnutController,
    BarController,
    ArcElement,
    BarElement,
    CategoryScale,
    LinearScale,
    Tooltip,
    Legend,
);

const props = defineProps<{
    metrics: {
        invites_total: number;
        invites_accepted: number;
        invites_pending: number;
        ai_events_total: number;
        last_activity_at: string | null;
    };
    charts: {
        activity: { labels: string[]; data: number[] };
        invites: { labels: string[]; data: number[] };
    };
}>();

const invitesCanvas = ref<HTMLCanvasElement | null>(null);
const activityCanvas = ref<HTMLCanvasElement | null>(null);
const generateInsight = () => {
    router.post('/ai/tenant/insight');
};

onMounted(() => {
    if (invitesCanvas.value) {
        new Chart(invitesCanvas.value, {
            type: 'doughnut',
            data: {
                labels: props.charts.invites.labels,
                datasets: [
                    {
                        data: props.charts.invites.data,
                    },
                ],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' },
                },
            },
        });
    }

    if (activityCanvas.value) {
        new Chart(activityCanvas.value, {
            type: 'bar',
            data: {
                labels: props.charts.activity.labels,
                datasets: [
                    {
                        label: 'Events / day (last 14 days)',
                        data: props.charts.activity.data,
                    },
                ],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: true },
                },
                scales: {
                    y: { beginAtZero: true },
                },
            },
        });
    }
});
</script>

<template>
    <div class="mx-auto max-w-6xl space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold">Tenant Insights</h1>
            <a
                href="/timeline"
                class="rounded border px-3 py-2 text-sm hover:bg-gray-50"
            >
                ← Back to Timeline
            </a>
        </div>

        <!-- KPIs -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <div class="rounded-lg border bg-white p-4">
                <div class="text-sm text-gray-500">Invites (total)</div>
                <div class="mt-1 text-2xl font-semibold">
                    {{ metrics.invites_total }}
                </div>
            </div>

            <div class="rounded-lg border bg-white p-4">
                <div class="text-sm text-gray-500">Invites (accepted)</div>
                <div class="mt-1 text-2xl font-semibold">
                    {{ metrics.invites_accepted }}
                </div>
            </div>

            <div class="rounded-lg border bg-white p-4">
                <div class="text-sm text-gray-500">Invites (pending)</div>
                <div class="mt-1 text-2xl font-semibold">
                    {{ metrics.invites_pending }}
                </div>
            </div>

            <div class="rounded-lg border bg-white p-4">
                <div class="text-sm text-gray-500">AI events (total)</div>
                <div class="mt-1 text-2xl font-semibold">
                    {{ metrics.ai_events_total }}
                </div>
            </div>
        </div>

        <div class="rounded-lg border bg-white p-4 text-sm text-gray-600">
            <span class="font-semibold text-gray-700">Last activity:</span>
            {{ metrics.last_activity_at ?? 'No activity yet' }}
        </div>

        <div class="rounded-lg border border-indigo-300 bg-indigo-50 p-6">
            <div
                class="mb-2 flex items-center gap-2 text-lg font-semibold text-indigo-900"
            >
                🤖 AI Executive Insight
            </div>

            <p class="text-sm text-indigo-800">
                This section provides an AI-generated executive overview of the
                tenant’s current activity, engagement, and onboarding
                performance.
            </p>

            <button
                @click="generateInsight"
                class="mt-4 rounded bg-indigo-600 px-4 py-2 text-white"
            >
                Generate executive insight
            </button>
        </div>

        <!-- Charts -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div class="rounded-lg border bg-white p-4">
                <div class="mb-3 font-semibold">Invites status</div>
                <canvas ref="invitesCanvas"></canvas>
            </div>

            <div class="rounded-lg border bg-white p-4">
                <div class="mb-3 font-semibold">Activity volume</div>
                <canvas ref="activityCanvas"></canvas>
            </div>
        </div>
    </div>
</template>
