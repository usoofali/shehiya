<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Award, BookOpen, Folder, LayoutGrid, Megaphone, ShieldCheck, Users, FileText, Globe } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const page = usePage();

const footerNavItems: NavItem[] = [
    {
        title: 'Public Portal',
        href: '/',
        icon: Globe,
    },
    {
        title: 'Verify Member ID',
        href: '/check-status',
        icon: ShieldCheck,
    },
];

const mainNavItems = computed<NavItem[]>(() => {
    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: '/dashboard',
            icon: LayoutGrid,
        },
        {
            title: 'Members Directory',
            href: '/members',
            icon: Users,
        },
        {
            title: 'EXCO Leadership',
            href: '/esco',
            icon: ShieldCheck,
        },
        {
            title: 'Organization Patrons',
            href: '/patrons',
            icon: Award,
        },
        {
            title: 'Announcements',
            href: '/announcements',
            icon: Megaphone,
        },
    ];

    const roles = (page.props.auth as any)?.user?.roles || [];
    const isSuperAdmin = roles.includes('Super Administrator');
    const canManageCoordinators = isSuperAdmin || roles.some((r: string) => 
        ['National Administrator', 'State Coordinator', 'LGA Coordinator', 'Ward Coordinator'].includes(r)
    );

    if (isSuperAdmin) {
        items.push({
            title: 'Website Content',
            href: '/admin/content',
            icon: FileText,
        });
    }

    if (canManageCoordinators) {
        items.push({
            title: 'Coordinator Accounts',
            href: '/admin/users',
            icon: Users,
        });
    }

    if (isSuperAdmin) {
        items.push({
            title: 'Roles & Permissions',
            href: '/admin/roles',
            icon: ShieldCheck,
        });
    }

    return items;
});


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
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
