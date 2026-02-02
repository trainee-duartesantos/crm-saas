<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
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

defineOptions({
    layout: AppLayout,
});

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
    lastInsight: string | null;
    engagementScore: 'low' | 'moderate' | 'high';
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

const copyInsight = () => {
    if (!props.lastInsight) return;
    navigator.clipboard.writeText(props.lastInsight);
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
                        backgroundColor: ['#4f46e5', '#e5e7eb'],
                        borderWidth: 0,
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
                        backgroundColor: '#6366f1',
                        borderRadius: 6,
                        maxBarThickness: 40,
                    },
                ],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 },
                    },
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
            <div class="flex gap-2">
                <a
                    href="/timeline"
                    class="rounded border px-3 py-2 text-sm hover:bg-gray-50"
                    >Timeline</a
                >
                <a
                    href="/deals"
                    class="rounded border px-3 py-2 text-sm hover:bg-gray-50"
                    >Pipeline</a
                >
                <a
                    href="/people"
                    class="rounded border px-3 py-2 text-sm hover:bg-gray-50"
                    >People</a
                >
                <a
                    href="/activities"
                    class="rounded border px-3 py-2 text-sm hover:bg-gray-50"
                    >Activities</a
                >
            </div>
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

        <!-- AI INSIGHT -->
        <div class="rounded-lg border border-indigo-300 bg-indigo-50 p-6">
            <div class="mb-2 flex items-center justify-between">
                <div
                    class="flex items-center gap-2 text-lg font-semibold text-indigo-900"
                >
                    🤖 AI Executive Insight
                    <span
                        class="rounded bg-indigo-600 px-2 py-0.5 text-xs text-white"
                    >
                        AI-generated
                    </span>
                </div>

                <span
                    class="rounded-full px-3 py-1 text-xs font-semibold"
                    :class="{
                        'bg-red-100 text-red-700': engagementScore === 'low',
                        'bg-yellow-100 text-yellow-700':
                            engagementScore === 'moderate',
                        'bg-green-100 text-green-700':
                            engagementScore === 'high',
                    }"
                >
                    {{ engagementScore.toUpperCase() }} engagement
                </span>
            </div>

            <!-- 🧠 Explain WHY -->
            <p
                v-if="lastInsight"
                class="mt-3 text-sm leading-relaxed text-indigo-800"
            >
                {{ lastInsight }}
            </p>

            <p v-else class="mt-3 text-sm text-indigo-500 italic">
                No executive insight generated yet.
            </p>

            <!-- 🎯 What should I do next -->
            <div class="mt-4 flex flex-wrap gap-3">
                <button
                    @click="router.post('/ai/tenant/insight')"
                    class="rounded bg-indigo-600 px-4 py-2 text-white"
                >
                    Generate insight
                </button>

                <button
                    @click="router.post('/ai/tenant/next-action')"
                    class="rounded bg-emerald-600 px-4 py-2 text-white"
                >
                    🤖 What should I do next?
                </button>

                <button
                    v-if="lastInsight"
                    @click="copyInsight"
                    class="rounded bg-gray-200 px-4 py-2 text-sm hover:bg-white"
                >
                    Copy insight
                </button>
            </div>
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
