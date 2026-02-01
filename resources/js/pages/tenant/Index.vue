<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';

const page = usePage();
const tenant = page.props.tenant;

defineProps<{
    tenant: {
        id: number;
        name: string;
        plan: string;
        created_at: string;
    };
}>();
</script>

<template>
    <Head title="Tenant settings" />

    <AppLayout
        :breadcrumbs="[
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Tenant' },
        ]"
    >
        <div class="mx-auto max-w-4xl space-y-6">
            <div>
                <h1 class="text-2xl font-semibold">🏢 Tenant settings</h1>
                <p class="text-sm text-muted-foreground">
                    Informação geral do tenant
                </p>
            </div>

            <div class="space-y-4 rounded-lg border bg-white p-6">
                <div>
                    <label class="text-xs text-gray-500">Name</label>
                    <div class="mt-1 font-semibold">
                        {{ tenant.name }}
                    </div>
                </div>

                <div>
                    <label class="text-xs text-gray-500">Plan</label>
                    <div
                        class="mt-1 inline-flex rounded-full bg-indigo-100 px-3 py-1 text-sm font-semibold text-indigo-700"
                    >
                        {{ tenant.plan }}
                    </div>
                </div>

                <div>
                    <label class="text-xs text-gray-500">Created at</label>
                    <div class="mt-1 text-sm text-gray-700">
                        {{ tenant.created_at }}
                    </div>
                </div>
            </div>

            <div
                class="rounded-lg border border-indigo-300 bg-indigo-50 p-4 text-sm text-indigo-800"
            >
                ℹ️ Gestão avançada do tenant (domínios, limites, branding) pode
                ser adicionada aqui no futuro.
            </div>
        </div>
    </AppLayout>
</template>
