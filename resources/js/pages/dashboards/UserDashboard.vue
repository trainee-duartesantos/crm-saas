<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

defineOptions({
    layout: AppLayout,
});
</script>

<template>
    <div class="mx-auto max-w-7xl space-y-10 px-6 py-8 lg:px-8">
        <!-- HERO -->
        <div>
            <h2 class="flex items-center gap-2 text-2xl font-semibold">
                🙋 Your Dashboard
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                What needs your attention today
            </p>
        </div>

        <!-- USER FOCUS -->
        <div class="rounded-xl border bg-white p-6 shadow-sm">
            <div class="mb-4 text-sm font-semibold text-gray-700">
                Your workspace
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <Link
                    href="/activities"
                    class="flex flex-col justify-between rounded-lg border bg-linear-to-br from-indigo-50 to-white p-5 hover:shadow-sm"
                >
                    <div>
                        <div class="text-lg font-semibold">My activities</div>
                        <div class="mt-1 text-sm text-gray-500">
                            Tasks and follow-ups assigned to you
                        </div>
                    </div>

                    <div class="mt-4 text-sm font-semibold text-indigo-600">
                        Open →
                    </div>
                </Link>

                <Link
                    href="/timeline"
                    class="flex flex-col justify-between rounded-lg border bg-linear-to-br from-gray-50 to-white p-5 hover:shadow-sm"
                >
                    <div>
                        <div class="text-lg font-semibold">Timeline</div>
                        <div class="mt-1 text-sm text-gray-500">
                            Recent activity and updates
                        </div>
                    </div>

                    <div class="mt-4 text-sm font-semibold text-gray-600">
                        View →
                    </div>
                </Link>
            </div>
        </div>

        <!-- USER NOTE -->
        <div
            class="rounded-xl border border-indigo-200 bg-indigo-50 p-6 text-sm text-indigo-800"
        >
            ℹ️ You only see information relevant to your role and assigned
            activities.
        </div>
    </div>
</template>
