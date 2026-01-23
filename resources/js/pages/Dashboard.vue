<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';

const page = usePage();

// 👇 agora sim, existe
const user = page.props.auth.user;

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <p>User: {{ user.name }}</p>
            <p>Role: {{ user.role }}</p>

            <div v-if="user.role === 'owner'">👑 Owner dashboard</div>

            <div v-else-if="user.role === 'admin'">🛠 Admin dashboard</div>

            <div v-else>🙋 User dashboard</div>
        </div>
    </AppLayout>
</template>
