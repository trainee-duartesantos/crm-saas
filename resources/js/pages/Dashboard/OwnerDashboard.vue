<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const metrics = page.props.metrics ?? {};
</script>

<template>
    <div class="space-y-8">
        <!-- Header -->
        <div>
            <h1 class="text-2xl font-semibold">👑 Owner Dashboard</h1>
            <p class="text-sm text-muted-foreground">
                Visão geral do teu tenant e atividade recente
            </p>
        </div>

        <!-- KPI Cards -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <div class="rounded-lg border bg-white p-4">
                <div class="text-sm text-gray-500">People</div>
                <div class="mt-1 text-2xl font-semibold">
                    {{ metrics.people ?? '—' }}
                </div>
            </div>

            <div class="rounded-lg border bg-white p-4">
                <div class="text-sm text-gray-500">Active deals</div>
                <div class="mt-1 text-2xl font-semibold">
                    {{ metrics.deals ?? '—' }}
                </div>
            </div>

            <div class="rounded-lg border bg-white p-4">
                <div class="text-sm text-gray-500">Pending activities</div>
                <div class="mt-1 text-2xl font-semibold">
                    {{ metrics.activities ?? '—' }}
                </div>
            </div>

            <div class="rounded-lg border bg-white p-4">
                <div class="text-sm text-gray-500">Plan</div>
                <div class="mt-1 text-2xl font-semibold">
                    {{ metrics.plan ?? 'Free' }}
                </div>
            </div>
        </div>

        <!-- Quick actions -->
        <div class="rounded-lg border bg-white p-6">
            <div class="mb-3 font-semibold text-gray-700">
                Quick actions
            </div>

            <div class="flex flex-wrap gap-3">
                <Link
                    href="/activities"
                    class="rounded bg-indigo-600 px-4 py-2 text-sm font-semibold text-white"
                >
                    + New activity
                </Link>

                <Link
                    href="/deals"
                    class="rounded border px-4 py-2 text-sm"
                >
                    View pipeline
                </Link>

                <Link
                    href="/people"
                    class="rounded border px-4 py-2 text-sm"
                >
                    Manage people
                </Link>

                <Link
                    href="/tenant"
                    class="rounded border px-4 py-2 text-sm"
                >
                    Tenant settings
                </Link>
            </div>
        </div>

        <!-- Hint -->
        <div class="rounded-lg border border-indigo-300 bg-indigo-50 p-4 text-sm text-indigo-800">
            💡 Tip: acompanhar atividades pendentes aumenta a taxa de conversão.
        </div>
    </div>
</template>
