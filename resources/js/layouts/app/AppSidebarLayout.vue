<script setup lang="ts">
import AIChat from '@/components/AIChat.vue'; // 🤖 AI CHAT
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
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

        <!-- Conteúdo principal -->
        <AppContent variant="sidebar" class="overflow-x-hidden">
            <AppSidebarHeader :breadcrumbs="breadcrumbs" />
            <slot />
        </AppContent>

        <!-- 🤖 AI CHAT GLOBAL (FLOATING) -->
        <AIChat
            endpoint="/ai/chat"
            page="global"
            placeholder="Pergunta algo ao CRM…"
        />
    </AppShell>
</template>
