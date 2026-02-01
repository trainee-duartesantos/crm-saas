<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';

const page = usePage();
const tenant = page.props.tenant;
</script>

<template>
    <Head title="Billing" />

    <AppLayout
        :breadcrumbs="[
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Billing' },
        ]"
    >
        <div class="mx-auto max-w-4xl space-y-6">
            <div>
                <h1 class="text-2xl font-semibold">💳 Billing</h1>
                <p class="text-sm text-muted-foreground">
                    Plano atual e estado da subscrição
                </p>
            </div>

            <div class="space-y-4 rounded-lg border bg-white p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm text-gray-500">Current plan</div>
                        <div class="text-xl font-semibold">
                            {{ tenant.plan }}
                        </div>
                    </div>

                    <span
                        class="rounded-full bg-emerald-100 px-3 py-1 text-sm font-semibold text-emerald-700"
                    >
                        Active
                    </span>
                </div>

                <div class="text-sm text-gray-600">
                    Billing está atualmente em modo demonstrativo.
                </div>
            </div>

            <div class="rounded-lg border bg-white p-6">
                <div class="mb-2 font-semibold">Available actions</div>

                <ul class="ml-5 list-disc space-y-1 text-sm text-gray-600">
                    <li>Upgrade plan</li>
                    <li>View invoices</li>
                    <li>Manage payment method</li>
                </ul>
            </div>

            <div
                class="rounded-lg border border-yellow-300 bg-yellow-50 p-4 text-sm text-yellow-800"
            >
                ⚠️ Integração com Stripe / pagamentos reais pode ser feita numa
                fase seguinte.
            </div>
        </div>
    </AppLayout>
</template>
