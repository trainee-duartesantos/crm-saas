<script setup lang="ts">
import AIInsightStream from '@/components/ai/AIInsightStream.vue';
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

defineOptions({ layout: AppLayout });

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
    metrics: any;
    lastInsight: any | null;
    engagementScore: 'low' | 'moderate' | 'high';
    charts: any;
    can: {
        products: boolean;
        deals: boolean;
        revenue: boolean;
        ai_generate: boolean;
        ai_next_action: boolean;
    };
}>();

const invitesCanvas = ref<HTMLCanvasElement | null>(null);
const activityCanvas = ref<HTMLCanvasElement | null>(null);

const generateInsight = () => {
    if (!props.can.ai_generate) return;
    router.post('/ai/tenant/insight');
};

const generateNextAction = () => {
    if (!props.can.ai_next_action) return;
    router.post('/ai/tenant/next-action');
};

const copyInsight = () => {
    if (!props.lastInsight?.message) return;
    navigator.clipboard.writeText(props.lastInsight.message);
};

const timeAgo = (date?: string) => {
    if (!date) return null;

    const diff = Math.floor((Date.now() - new Date(date).getTime()) / 1000);

    if (diff < 60) return 'just now';
    if (diff < 3600) return `${Math.floor(diff / 60)} min ago`;
    if (diff < 86400) return `${Math.floor(diff / 3600)} h ago`;

    return `${Math.floor(diff / 86400)} days ago`;
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
                <div
                    class="mt-1 text-2xl font-semibold"
                    :class="
                        metrics.invites_pending === 0
                            ? 'text-green-600'
                            : 'text-gray-900'
                    "
                >
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
        <div
            class="space-y-4 rounded-lg border border-indigo-300 bg-indigo-50 p-6"
        >
            <div class="flex items-center justify-between">
                <div class="text-lg font-semibold text-indigo-900">
                    🤖 AI Executive Insight
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

            <!-- Last insight (stored) -->
            <p
                v-if="lastInsight"
                class="text-sm leading-relaxed text-indigo-800"
            >
                {{ lastInsight.message }}
            </p>

            <div
                v-else
                class="rounded border border-dashed border-indigo-300 bg-white/60 p-4 text-sm text-indigo-600"
            >
                No AI insight generated yet.
            </div>

            <!-- ACTIONS -->
            <div class="flex flex-wrap gap-3">
                <button
                    v-if="can.ai_generate"
                    @click="generateInsight"
                    class="rounded bg-indigo-600 px-4 py-2 text-white"
                >
                    Generate insight
                </button>

                <button
                    v-if="can.ai_next_action"
                    @click="generateNextAction"
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

            <!-- 🔴 LIVE AI STREAM -->
            <div class="border-t border-indigo-200 pt-4">
                <div
                    class="mb-2 flex items-center gap-2 text-sm font-semibold text-indigo-700"
                >
                    <span
                        class="h-2 w-2 animate-pulse rounded-full bg-indigo-600"
                    ></span>
                    Live AI output
                </div>

                <AIInsightStream />
            </div>
        </div>

        <!-- Charts -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div class="rounded-lg border bg-white p-4">
                <div class="mb-3 font-semibold">Invites status</div>
                <canvas
                    v-if="charts.invites.data.some((v: number) => v > 0)"
                    ref="invitesCanvas"
                />

                <div v-else class="text-sm text-gray-400 italic">
                    No invite data available yet.
                </div>
            </div>

            <div class="rounded-lg border bg-white p-4">
                <div class="mb-3 font-semibold">Activity volume</div>
                <canvas
                    v-if="charts.activity.data.some((v: number) => v > 0)"
                    ref="activityCanvas"
                />

                <div v-else class="text-sm text-gray-400 italic">
                    No activity data available yet.
                </div>
            </div>
        </div>
    </div>
</template>
