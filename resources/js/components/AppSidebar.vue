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
    BarChart3,
    Briefcase,
    Building2,
    Calendar,
    CheckSquare,
    Clock,
    CreditCard,
    LayoutGrid,
    LineChart,
    Shield,
    Sparkles,
    TrendingUp,
    Users,
} from 'lucide-vue-next';

import AppLogo from './AppLogo.vue';

const props = defineProps<{
    role: 'user' | 'admin' | 'owner';
}>();

/* =======================
   Core
======================= */
const coreNav: NavItem[] = [
    { title: 'Dashboard', href: '/dashboard', icon: LayoutGrid },
    { title: 'Timeline', href: '/timeline', icon: Clock },
    { title: 'Activities', href: '/activities', icon: CheckSquare },
    { title: 'Calendar', href: '/calendar', icon: Calendar },
];

/* =======================
   CRM
======================= */
const crmNav: NavItem[] = [
    { title: 'Entities', href: '/entities', icon: Building2 },
    { title: 'People', href: '/people', icon: Users },
    { title: 'Deals', href: '/deals', icon: Briefcase },
];

/* =======================
   Insights (SaaS-style)
======================= */
const insightsNav: NavItem[] = [];

if (props.role === 'admin' || props.role === 'owner') {
    insightsNav.push(
        {
            title: 'Overview',
            href: '/insights',
            icon: Sparkles,
        },
        {
            title: 'Products',
            href: '/insights/products',
            icon: BarChart3,
        },
        {
            title: 'Deals',
            href: '/insights/deals',
            icon: TrendingUp,
        },
    );
}

if (props.role === 'owner') {
    insightsNav.push({
        title: 'Revenue',
        href: '/insights/revenue',
        icon: LineChart,
    });
}

/* =======================
   Administration
======================= */
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
            <NavMain label="Insights" :items="insightsNav" />

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
