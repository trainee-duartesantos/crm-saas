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
import { dashboard } from '@/routes';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import {
    BookOpen,
    Building,
    CreditCard,
    Folder,
    LayoutGrid,
    Users,
} from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

/* 👇 role vem do layout */
const props = defineProps<{
    role: 'user' | 'admin' | 'owner';
}>();

/* Base: todos veem */
const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
];

/* Admin + Owner */
if (props.role === 'admin' || props.role === 'owner') {
    mainNavItems.push(
        {
            title: 'Users',
            href: '/users',
            icon: Users,
        },
        {
            title: 'Projects',
            href: '/projects',
            icon: Folder,
        },
    );
}

/* Só Owner */
if (props.role === 'owner') {
    mainNavItems.push(
        {
            title: 'Tenant',
            href: '/tenant',
            icon: Building,
        },
        {
            title: 'Billing',
            href: '/billing',
            icon: CreditCard,
        },
    );
}

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
</template>
