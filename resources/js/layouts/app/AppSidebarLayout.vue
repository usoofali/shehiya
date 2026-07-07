<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent variant="sidebar" class="relative overflow-hidden bg-slate-50/50 dark:bg-slate-950">
            <!-- Subtle ambient background glow matching Welcome page -->
            <div class="pointer-events-none absolute -top-40 right-0 h-96 w-96 rounded-full bg-gradient-to-br from-amber-500/10 via-emerald-500/10 to-transparent blur-3xl"></div>
            <AppSidebarHeader :breadcrumbs="breadcrumbs" class="relative z-10" />
            <div class="relative z-10 flex-1">
                <slot />
            </div>
        </AppContent>
    </AppShell>
</template>
