<script setup>
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
import { Link } from '@inertiajs/vue3';
import { AlertTriangle, BookOpen, CheckCircle2, Clock, Folder, LayoutGrid, Send } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const mainNavItems = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Manage Post',
        href: '/posts',
        icon: Folder,
    },
    {
        title: 'Post Antrian',
        href: '/posts?status=queued',
        icon: Send,
    },
    {
        title: 'Post Terjadwal',
        href: '/posts?status=scheduled',
        icon: Clock,
    },
    {
        title: 'Post Selesai',
        href: '/posts?status=completed',
        icon: CheckCircle2,
    },
    {
        title: 'Post Gagal',
        href: '/posts?status=failed',
        icon: AlertTriangle,
    },
];

const footerNavItems = [
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
    <slot />
</template>
