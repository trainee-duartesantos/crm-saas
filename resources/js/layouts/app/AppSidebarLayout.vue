<script setup lang="ts">
import AIChat from '@/components/AIChat.vue';
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import NavFooter from '@/components/NavFooter.vue';
import type { BreadcrumbItem } from '@/types';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const user = page.props.auth.user;

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppShell variant="sidebar">
        <!-- Sidebar -->
        <AppSidebar :role="user.role" />

        <!-- Main column -->
        <div class="flex min-h-screen flex-1 flex-col">
            <!-- Content -->
            <AppContent variant="sidebar" class="flex-1 overflow-x-hidden">
                <AppSidebarHeader :breadcrumbs="breadcrumbs" />
                <slot />
            </AppContent>

            <!-- Footer global -->
            <NavFooter />
        </div>

        <!-- 🤖 AI CHAT GLOBAL (ONLY HERE) -->
        <AIChat
            endpoint="/ai/chat"
            page="global"
            placeholder="Pergunta algo ao CRM…"
        />
    </AppShell>
</template>
