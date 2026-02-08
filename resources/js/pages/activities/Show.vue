<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

defineOptions({ layout: AppLayout });

defineProps<{
    activity: any;
}>();
</script>

<template>
    <div class="mx-auto max-w-4xl space-y-6 px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold">
                    {{ activity.title }}
                </h1>
                <p class="text-sm text-gray-500">Activity details</p>
            </div>

            <Link
                href="/calendar"
                class="rounded border px-3 py-2 text-sm hover:bg-gray-50"
            >
                Calendar
            </Link>
            <Link
                href="/activities"
                class="rounded border px-3 py-2 text-sm hover:bg-gray-50"
            >
                Activities
            </Link>
        </div>

        <div class="rounded-xl border bg-white p-6">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <div class="text-xs font-semibold text-gray-500">Type</div>
                    <div class="text-sm">{{ activity.type ?? '—' }}</div>
                </div>

                <div>
                    <div class="text-xs font-semibold text-gray-500">Due</div>
                    <div class="text-sm">{{ activity.due_at ?? '—' }}</div>
                </div>

                <div>
                    <div class="text-xs font-semibold text-gray-500">
                        Status
                    </div>
                    <div class="text-sm">
                        <span
                            v-if="activity.completed_at"
                            class="text-emerald-700"
                            >Completed</span
                        >
                        <span v-else class="text-amber-700">Open</span>
                    </div>
                </div>

                <div>
                    <div class="text-xs font-semibold text-gray-500">
                        Created
                    </div>
                    <div class="text-sm">{{ activity.created_at ?? '—' }}</div>
                </div>
            </div>

            <div v-if="activity.notes" class="mt-6">
                <div class="text-xs font-semibold text-gray-500">Notes</div>
                <div class="mt-2 text-sm whitespace-pre-wrap text-gray-800">
                    {{ activity.notes }}
                </div>
            </div>
        </div>
    </div>
</template>
