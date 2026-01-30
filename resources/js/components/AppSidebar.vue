<script setup lang="ts">
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';

import {
    Briefcase,
    Building2,
    CheckSquare,
    Clock,
    CreditCard,
    LayoutGrid,
    Shield,
    Sparkles,
    Users,
} from 'lucide-vue-next';

import AppLogo from './AppLogo.vue';

const props = defineProps<{
    role: 'user' | 'admin' | 'owner';
}>();

/* 🔹 Core */
const coreNav: NavItem[] = [
    { title: 'Dashboard', href: '/dashboard', icon: LayoutGrid },
    { title: 'Timeline', href: '/timeline', icon: Clock },
    { title: 'Activities', href: '/activities', icon: CheckSquare },
];

/* 🔹 CRM */
const crmNav: NavItem[] = [
    { title: 'Entities', href: '/entities', icon: Building2 },
    { title: 'People', href: '/people', icon: Users },
    { title: 'Deals', href: '/deals', icon: Briefcase },
];

/* 🔹 AI */
const aiNav: NavItem[] = [
    { title: 'Insights', href: '/insights', icon: Sparkles },
];

/* 🔹 Admin */
const adminNav: NavItem[] = [];

if (props.role === 'admin' || props.role === 'owner') {
    adminNav.push({
        title: 'Users',
        href: '/users',
        icon: Shield,
    });
}

if (props.role === 'owner') {
    adminNav.push(
        { title: 'Tenant', href: '/tenant', icon: Building2 },
        { title: 'Billing', href: '/billing', icon: CreditCard },
    );
}
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <!-- Logo -->
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link href="/dashboard">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <!-- Navigation -->
        <SidebarContent>
            <NavMain label="Core" :items="coreNav" />
            <NavMain label="CRM" :items="crmNav" />
            <NavMain label="AI" :items="aiNav" />

            <NavMain
                v-if="adminNav.length"
                label="Administration"
                :items="adminNav"
            />
        </SidebarContent>

        <!-- Footer -->
        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
</template>
