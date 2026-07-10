<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, reactive, watch } from 'vue';
import { Filter, Search, Users } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Members Directory', href: '/members' },
];

const props = defineProps<{
    members: {
        data: Array<{
            id: number;
            membership_number: string;
            first_name: string;
            last_name: string;
            gender: string;
            phone: string;
            occupation: string;
            status: string;
            state?: { name: string };
            lga?: { name: string };
            ward?: { name: string };
            polling_unit?: { code: string | null; name: string };
            referrals_count?: number;
            verified_referrals_count?: number;
            referral_badge?: string;
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
        from: number;
        to: number;
        total: number;
    };
    filters: {
        search?: string;
        state_id?: number | string;
        lga_id?: number | string;
        ward_id?: number | string;
        status?: string;
    };
    states: Array<{
        id: number;
        name: string;
        lgas: Array<{
            id: number;
            name: string;
            wards: Array<{ id: number; name: string }>;
        }>;
    }>;
}>();

const filterForm = reactive({
    search: props.filters.search || '',
    state_id: props.filters.state_id || '',
    lga_id: props.filters.lga_id || '',
    ward_id: props.filters.ward_id || '',
    status: props.filters.status || '',
});

const availableLgas = computed(() => {
    if (!filterForm.state_id) return [];
    const state = props.states.find((s) => s.id == Number(filterForm.state_id));
    return state ? state.lgas : [];
});

const availableWards = computed(() => {
    if (!filterForm.lga_id) return [];
    const lga = availableLgas.value.find((l) => l.id == Number(filterForm.lga_id));
    return lga ? lga.wards : [];
});

const applyFilters = () => {
    router.get('/members', { ...filterForm }, { preserveState: true, replace: true });
};

const resetFilters = () => {
    filterForm.search = '';
    filterForm.state_id = '';
    filterForm.lga_id = '';
    filterForm.ward_id = '';
    filterForm.status = '';
    applyFilters();
};

watch(() => filterForm.state_id, () => {
    filterForm.lga_id = '';
    filterForm.ward_id = '';
});

watch(() => filterForm.lga_id, () => {
    filterForm.ward_id = '';
});
</script>

<template>
    <Head title="Members Directory" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex flex-col justify-between gap-4 border-b border-border pb-6 sm:flex-row sm:items-center">
                <div>
                    <h1 class="flex items-center gap-2 text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                        <Users class="size-8 text-emerald-600 dark:text-emerald-400" /> Members Directory
                    </h1>
                    <p class="mt-1 text-sm text-muted-foreground">Search, filter, and manage verified and pending members.</p>
                </div>
            </div>

            <!-- Filters Bar -->
            <form @submit.prevent="applyFilters" class="grid gap-4 rounded-2xl border border-border bg-card p-4 shadow-sm dark:bg-sidebar sm:grid-cols-2 lg:grid-cols-6">
                <div class="relative sm:col-span-2">
                    <Search class="absolute left-3 top-3 size-4 text-muted-foreground" />
                    <input
                        v-model="filterForm.search"
                        type="text"
                        placeholder="Search name, phone, number..."
                        class="w-full rounded-xl border border-input bg-background py-2 pl-9 pr-3 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                    />
                </div>

                <div>
                    <select
                        v-model="filterForm.state_id"
                        class="w-full rounded-xl border border-input bg-background px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                    >
                        <option value="">All States</option>
                        <option v-for="state in states" :key="state.id" :value="state.id">{{ state.name }}</option>
                    </select>
                </div>

                <div>
                    <select
                        v-model="filterForm.lga_id"
                        :disabled="!filterForm.state_id"
                        class="w-full rounded-xl border border-input bg-background px-3 py-2 text-sm disabled:opacity-50 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                    >
                        <option value="">All LGAs</option>
                        <option v-for="lga in availableLgas" :key="lga.id" :value="lga.id">{{ lga.name }}</option>
                    </select>
                </div>

                <div>
                    <select
                        v-model="filterForm.ward_id"
                        :disabled="!filterForm.lga_id"
                        class="w-full rounded-xl border border-input bg-background px-3 py-2 text-sm disabled:opacity-50 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                    >
                        <option value="">All Wards</option>
                        <option v-for="ward in availableWards" :key="ward.id" :value="ward.id">{{ ward.name }}</option>
                    </select>
                </div>

                <div class="flex flex-wrap gap-2 sm:flex-nowrap">
                    <select
                        v-model="filterForm.status"
                        class="w-full rounded-xl border border-input bg-background px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                    >
                        <option value="">All Statuses</option>
                        <option value="verified">Verified</option>
                        <option value="pending">Pending</option>
                        <option value="rejected">Rejected</option>
                    </select>
                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-xl bg-secondary px-3 py-2 text-sm font-medium text-secondary-foreground hover:bg-secondary/80"
                    >
                        Filter
                    </button>
                    <button
                        type="button"
                        @click="resetFilters"
                        class="inline-flex items-center justify-center rounded-xl border border-input px-3 py-2 text-sm text-muted-foreground hover:bg-muted"
                    >
                        Reset
                    </button>
                </div>
            </form>

            <!-- Table -->
            <div class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm dark:bg-sidebar">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-border bg-muted/50 text-xs uppercase text-muted-foreground">
                            <tr>
                                <th class="px-3.5 py-3.5 font-semibold">Membership #</th>
                                <th class="px-3.5 py-3.5 font-semibold">Member Name</th>
                                <th class="px-3.5 py-3.5 font-semibold">Contact</th>
                                <th class="px-3.5 py-3.5 font-semibold">Jurisdiction</th>
                                <th class="px-3.5 py-3.5 font-semibold">Referrals</th>
                                <th class="px-3.5 py-3.5 font-semibold">Status</th>
                                <th class="px-3.5 py-3.5 text-right font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr v-if="members.data.length === 0">
                                <td colSpan="7" class="p-8 text-center text-muted-foreground">No members found matching the specified filters.</td>
                            </tr>
                            <tr v-for="member in members.data" :key="member.id" class="transition hover:bg-muted/50">
                                <td class="whitespace-nowrap px-3.5 py-3.5 font-mono font-medium text-foreground">{{ member.membership_number }}</td>
                                <td class="max-w-[140px] sm:max-w-[180px] px-3.5 py-3.5">
                                    <div class="truncate font-medium text-foreground" :title="`${member.first_name} ${member.last_name}`">{{ member.first_name }} {{ member.last_name }}</div>
                                    <div class="truncate text-xs text-muted-foreground" :title="`${member.gender} • ${member.occupation || 'N/A'}`">{{ member.gender }} • {{ member.occupation || 'N/A' }}</div>
                                </td>
                                <td class="whitespace-nowrap px-3.5 py-3.5 text-muted-foreground">{{ member.phone }}</td>
                                <td class="max-w-[150px] sm:max-w-[190px] px-3.5 py-3.5 text-muted-foreground">
                                    <div class="truncate font-medium text-foreground" :title="member.state?.name">{{ member.state?.name }}</div>
                                    <div class="truncate text-xs" :title="`${member.lga?.name} • ${member.ward?.name}`">{{ member.lga?.name }} • {{ member.ward?.name }}</div>
                                    <div v-if="member.polling_unit" class="mt-1 truncate">
                                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-semibold text-amber-700 dark:bg-amber-900/20 dark:text-amber-400 truncate max-w-full" :title="`PU: ${member.polling_unit.code ? `${member.polling_unit.code} - ` : ''}${member.polling_unit.name}`">
                                            PU: {{ member.polling_unit.code ? `${member.polling_unit.code} - ` : '' }}{{ member.polling_unit.name }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-3.5 py-3.5">
                                    <div class="font-bold text-foreground">
                                        <span class="text-emerald-600 dark:text-emerald-400">{{ member.verified_referrals_count ?? 0 }}</span> 
                                        <span class="text-muted-foreground font-normal text-xs"> / {{ member.referrals_count ?? 0 }}</span>
                                    </div>
                                    <div class="text-[10px] font-semibold"
                                        :class="{
                                            'text-amber-700 dark:text-amber-500': member.referral_badge === 'Bronze Ambassador',
                                            'text-slate-500 dark:text-slate-300': member.referral_badge === 'Silver Veteran',
                                            'text-yellow-600 dark:text-yellow-400': member.referral_badge === 'Gold Master',
                                            'text-indigo-500 dark:text-indigo-400': member.referral_badge === 'Platinum Grandmaster',
                                            'text-muted-foreground': member.referral_badge === 'None'
                                        }"
                                    >
                                        {{ member.referral_badge !== 'None' ? member.referral_badge : '' }}
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-3.5 py-3.5">
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
                                </td>
                                <td class="whitespace-nowrap px-3.5 py-3.5 text-right">
                                    <Link
                                        :href="`/members/${member.id}`"
                                        class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-xs font-semibold shadow-sm transition"
                                        :class="{
                                            'bg-gradient-to-r from-amber-600 to-emerald-600 text-white hover:from-amber-500 hover:to-emerald-500': member.status === 'pending',
                                            'border border-border bg-card text-foreground hover:bg-muted': member.status !== 'pending'
                                        }"
                                    >
                                        {{ member.status === 'pending' ? 'Verify Profile' : 'View Profile' }}
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="members.total > 0" class="flex items-center justify-between border-t border-border px-6 py-4">
                    <span class="text-xs text-muted-foreground"> Showing {{ members.from }} to {{ members.to }} of {{ members.total }} results </span>
                    <div class="flex gap-1">
                        <Link
                            v-for="(link, index) in members.links"
                            :key="index"
                            :href="link.url || '#'"
                            v-html="link.label"
                            class="rounded-lg px-3 py-1 text-xs font-medium transition"
                            :class="{
                                'bg-emerald-600 text-white': link.active,
                                'text-muted-foreground hover:bg-muted': !link.active && link.url,
                                'pointer-events-none opacity-40': !link.url,
                            }"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
