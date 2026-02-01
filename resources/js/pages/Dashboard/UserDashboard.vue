<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="space-y-8">
        <div>
            <h1 class="text-2xl font-semibold">🙋 Your Dashboard</h1>
            <p class="text-sm text-muted-foreground">
                O que precisa da tua atenção hoje
            </p>
        </div>

        <div class="rounded-lg border bg-white p-6">
            <div class="mb-3 font-semibold text-gray-700">Quick access</div>

            <div class="flex gap-3">
                <Link
                    href="/activities"
                    class="rounded bg-indigo-600 px-4 py-2 text-sm font-semibold text-white"
                >
                    My activities
                </Link>

                <Link href="/timeline" class="rounded border px-4 py-2 text-sm">
                    Timeline
                </Link>
            </div>
        </div>

        <div class="text-sm text-gray-500">
            ℹ️ Só vês dados relevantes para ti.
        </div>
    </div>
</template>
