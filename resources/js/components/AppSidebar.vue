<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { Folder, LayoutGrid, UserPlus, UserCog, DollarSign, BarChart3 } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
    {
        title: 'Halaman Utama',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Data Master',
        href: '/data-master',
        icon: Folder,
    },
];

const adminNavItems: NavItem[] = [
    {
        title: 'Buat Akun',
        href: '/admin/users/create',
        icon: UserPlus,
    },
    {
        title: 'Edit Akun',
        href: '/admin/users/edit',
        icon: UserCog,
    },
    {
        title: 'Input Gaji',
        href: '/admin/payroll/create',
        icon: DollarSign,
    },
    {
        title: 'Lihat Gaji',
        href: '/admin/payroll',
        icon: BarChart3,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Developer Github',
        href: 'https://github.com/rnrran',
        icon: Folder,
    },
    // {
    //     title: 'Documentation',
    //     href: 'https://laravel.com/docs/starter-kits#vue',
    //     icon: BookOpen,
    // },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
            <NavMain :items="adminNavItems" title="Admin" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
