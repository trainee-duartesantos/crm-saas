<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();

defineProps<{
    people?: number;
    deals?: number;
    activities?: number;
    plan?: string;
}>();

defineOptions({
    layout: AppLayout,
});
</script>

<template>
    <!-- PAGE CONTAINER -->
    <div class="mx-auto max-w-7xl space-y-10 px-6 py-8 lg:px-8">
        <!-- HERO -->
        <div>
            <h2 class="flex items-center gap-2 text-2xl font-semibold">
                👑 Owner Dashboard
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Business overview and strategic actions
            </p>
        </div>

        <!-- OVERVIEW / KPIs -->
        <div class="rounded-xl border bg-white p-6 shadow-sm">
            <div class="mb-4 text-sm font-semibold text-gray-700">Overview</div>

            <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
                <div
                    class="rounded-lg bg-linear-to-br from-indigo-50 to-white p-4"
                >
                    <div class="text-sm text-gray-500">People</div>
                    <div class="mt-1 text-3xl font-bold">
                        {{ people ?? '—' }}
                    </div>
                </div>

                <div
                    class="rounded-lg bg-linear-to-br from-emerald-50 to-white p-4"
                >
                    <div class="text-sm text-gray-500">Deals</div>
                    <div class="mt-1 text-3xl font-bold">
                        {{ deals ?? '—' }}
                    </div>
                </div>

                <div
                    class="rounded-lg bg-linear-to-br from-amber-50 to-white p-4"
                >
                    <div class="text-sm text-gray-500">Activities</div>
                    <div class="mt-1 text-3xl font-bold">
                        {{ activities ?? '—' }}
                    </div>
                </div>

                <div class="rounded-lg border bg-gray-50 p-4">
                    <div class="text-sm text-gray-500">Plan</div>
                    <div class="mt-2 text-lg font-semibold uppercase">
                        {{ plan ?? 'free' }}
                    </div>
                </div>
            </div>
        </div>

        <!-- AI EXECUTIVE INSIGHT -->
        <div
            class="rounded-xl border border-indigo-300 bg-indigo-50 p-6 shadow-sm"
        >
            <div class="flex items-center justify-between">
                <div
                    class="flex items-center gap-2 font-semibold text-indigo-900"
                >
                    🤖 AI Executive Insight
                    <span
                        class="rounded bg-indigo-600 px-2 py-0.5 text-xs text-white"
                    >
                        AI
                    </span>
                </div>
            </div>

            <p class="mt-2 max-w-2xl text-sm text-indigo-800">
                Review engagement, risks and next best actions detected across
                your tenant activity.
            </p>

            <Link
                href="/insights"
                class="mt-4 inline-flex items-center rounded bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700"
            >
                Open Insights →
            </Link>
        </div>

        <!-- QUICK ACTIONS -->
        <div class="rounded-xl border bg-white p-6 shadow-sm">
            <div class="mb-4 text-sm font-semibold text-gray-700">
                Quick actions
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                <Link
                    href="/deals"
                    class="flex w-full items-center justify-center rounded bg-indigo-600 px-4 py-3 text-sm font-semibold text-white hover:bg-indigo-700"
                >
                    View pipeline
                </Link>

                <Link
                    href="/activities"
                    class="flex w-full items-center justify-center rounded border px-4 py-3 text-sm hover:bg-gray-50"
                >
                    Activities
                </Link>

                <Link
                    href="/tenant"
                    class="flex w-full items-center justify-center rounded border px-4 py-3 text-sm hover:bg-gray-50"
                >
                    Tenant settings
                </Link>
            </div>
        </div>
    </div>
</template>
