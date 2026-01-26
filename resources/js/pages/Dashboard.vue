<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';

import AdminDashboard from './Dashboard/AdminDashboard.vue';
import OwnerDashboard from './Dashboard/OwnerDashboard.vue';
import UserDashboard from './Dashboard/UserDashboard.vue';

const page = usePage();
const user = page.props.auth.user;

const roleComponentMap = {
    owner: OwnerDashboard,
    admin: AdminDashboard,
    user: UserDashboard,
};

const role = user.role as 'owner' | 'admin' | 'user';
const DashboardComponent = roleComponentMap[role] ?? UserDashboard;
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout>
        <component :is="DashboardComponent" />
    </AppLayout>
</template>
