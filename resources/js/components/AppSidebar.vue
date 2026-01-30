<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
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
import AppLogo from './AppLogo.vue';

import {
    Activity,
    BarChart3,
    Brain,
    Briefcase,
    Building2,
    CalendarCheck,
    LayoutGrid,
    Users,
} from 'lucide-vue-next';

/* role vem do layout */
defineProps<{
    role: 'user' | 'admin' | 'owner';
}>();

/* -----------------------
   MAIN NAV (produto real)
------------------------*/
const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Timeline',
        href: '/timeline',
        icon: Activity,
    },
    {
        title: 'Activities',
        href: '/activities',
        icon: CalendarCheck,
    },
    {
        title: 'Deals',
        href: '/deals',
        icon: Briefcase,
    },
    {
        title: 'People',
        href: '/people',
        icon: Users,
    },
    {
        title: 'Entities',
        href: '/entities',
        icon: Building2,
    },
    {
        title: 'Insights',
        href: '/insights',
        icon: Brain,
    },
];

/* -----------------------
   FOOTER NAV
------------------------*/
const footerNavItems: NavItem[] = [
    {
        title: 'Reports',
        href: '/activity-logs',
        icon: BarChart3,
    },
];
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

        <!-- Main navigation -->
        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <!-- Footer -->
        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
</template>
