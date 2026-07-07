<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { CheckCircle2, Clock, ShieldAlert, ShieldCheck, Users } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

defineProps<{
    stats: {
        total_members: number;
        verified_members: number;
        pending_members: number;
        total_esco: number;
    };
    charts: {
        by_state: { name: string; total: number }[];
        by_lga: { name: string; total: number }[];
    };
    recentMembers: Array<{
        id: number;
        membership_number: string;
        first_name: string;
        last_name: string;
        phone: string;
        status: string;
        state?: { name: string };
        lga?: { name: string };
        registered_at: string;
    }>;
    userInfo: {
        role: string;
        jurisdiction: string;
    };
}>();
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Jurisdiction Banner -->
            <div class="flex flex-col justify-between gap-4 rounded-2xl bg-gradient-to-r from-emerald-800 to-teal-900 p-6 text-white shadow-xl md:flex-row md:items-center">
                <div>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-700/50 px-3 py-1 text-xs font-medium text-emerald-200 ring-1 ring-inset ring-emerald-400/30">
                        <ShieldCheck class="size-3.5" /> {{ userInfo.role }}
                    </span>
                    <h1 class="mt-2 text-2xl font-bold tracking-tight text-white sm:text-3xl">Shaihiyya Amanar Jagora</h1>
                    <p class="mt-1 text-sm text-emerald-100">Jurisdiction Scope: <strong class="text-white underline decoration-emerald-400 decoration-2">{{ userInfo.jurisdiction }}</strong></p>
                </div>
                <div class="flex items-center gap-3">
                    <Link
                        href="/members/create"
                        class="inline-flex items-center justify-center rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-emerald-900 shadow-sm transition hover:bg-emerald-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white"
                    >
                        + Register New Member
                    </Link>
                </div>
            </div>

            <!-- Stat Cards -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="group relative overflow-hidden rounded-2xl border border-border bg-card p-6 shadow-sm transition duration-300 hover:shadow-md dark:bg-sidebar">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Total Members</p>
                            <h3 class="mt-2 text-3xl font-bold tracking-tight text-foreground">{{ stats.total_members }}</h3>
                        </div>
                        <div class="rounded-xl bg-emerald-500/10 p-3 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400">
                            <Users class="size-6" />
                        </div>
                    </div>
                </div>

                <div class="group relative overflow-hidden rounded-2xl border border-border bg-card p-6 shadow-sm transition duration-300 hover:shadow-md dark:bg-sidebar">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Verified Members</p>
                            <h3 class="mt-2 text-3xl font-bold tracking-tight text-emerald-600 dark:text-emerald-400">{{ stats.verified_members }}</h3>
                        </div>
                        <div class="rounded-xl bg-emerald-500/10 p-3 text-emerald-600 dark:bg-emerald-500/20 dark:text-emerald-400">
                            <CheckCircle2 class="size-6" />
                        </div>
                    </div>
                </div>

                <div class="group relative overflow-hidden rounded-2xl border border-border bg-card p-6 shadow-sm transition duration-300 hover:shadow-md dark:bg-sidebar">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Pending Verification</p>
                            <h3 class="mt-2 text-3xl font-bold tracking-tight text-amber-600 dark:text-amber-400">{{ stats.pending_members }}</h3>
                        </div>
                        <div class="rounded-xl bg-amber-500/10 p-3 text-amber-600 dark:bg-amber-500/20 dark:text-amber-400">
                            <Clock class="size-6" />
                        </div>
                    </div>
                </div>

                <div class="group relative overflow-hidden rounded-2xl border border-border bg-card p-6 shadow-sm transition duration-300 hover:shadow-md dark:bg-sidebar">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">EXCO Officials</p>
                            <h3 class="mt-2 text-3xl font-bold tracking-tight text-indigo-600 dark:text-indigo-400">{{ stats.total_esco }}</h3>
                        </div>
                        <div class="rounded-xl bg-indigo-500/10 p-3 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400">
                            <ShieldAlert class="size-6" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Recent Registrations -->
                <div class="flex flex-col rounded-2xl border border-border bg-card shadow-sm dark:bg-sidebar lg:col-span-2">
                    <div class="flex items-center justify-between border-b border-border px-6 py-4">
                        <h3 class="font-semibold text-foreground">Recently Registered Members</h3>
                        <Link href="/members" class="text-sm font-medium text-emerald-600 hover:underline dark:text-emerald-400">View Directory &rarr;</Link>
                    </div>
                    <div class="divide-y divide-border">
                        <div v-if="recentMembers.length === 0" class="p-8 text-center text-muted-foreground">
                            No members registered yet in this jurisdiction.
                        </div>
                        <div v-for="member in recentMembers" :key="member.id" class="flex items-center justify-between px-6 py-4 hover:bg-muted/50">
                            <div class="flex items-center gap-3">
                                <div class="flex size-10 items-center justify-center rounded-full bg-emerald-100 font-bold text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-200">
                                    {{ member.first_name[0] }}{{ member.last_name[0] }}
                                </div>
                                <div>
                                    <Link :href="`/members/${member.id}`" class="font-medium text-foreground hover:underline">
                                        {{ member.first_name }} {{ member.last_name }}
                                    </Link>
                                    <p class="text-xs text-muted-foreground">{{ member.membership_number }} • {{ member.state?.name }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span
                                    class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize"
                                    :class="{
                                        'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300': member.status === 'verified',
                                        'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300': member.status === 'pending',
                                        'bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-300': member.status === 'rejected',
                                    }"
                                >
                                    {{ member.status }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Geographical Breakdown -->
                <div class="flex flex-col rounded-2xl border border-border bg-card shadow-sm dark:bg-sidebar">
                    <div class="border-b border-border px-6 py-4">
                        <h3 class="font-semibold text-foreground">Distribution by State</h3>
                    </div>
                    <div class="p-6">
                        <div v-if="charts.by_state.length === 0" class="text-center text-sm text-muted-foreground">
                            No data available.
                        </div>
                        <div v-else class="space-y-4">
                            <div v-for="state in charts.by_state" :key="state.name">
                                <div class="flex justify-between text-sm font-medium text-foreground">
                                    <span>{{ state.name }}</span>
                                    <span>{{ state.total }} members</span>
                                </div>
                                <div class="mt-1 h-2 w-full overflow-hidden rounded-full bg-muted">
                                    <div
                                        class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-teal-600"
                                        :style="{ width: `${Math.min((state.total / (stats.total_members || 1)) * 100, 100)}%` }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
