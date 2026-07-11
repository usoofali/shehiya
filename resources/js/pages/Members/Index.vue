<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, reactive, ref, watch } from 'vue';
import { CheckCircle, Clock, Filter, LoaderCircle, Search, ShieldAlert, Users, XCircle } from 'lucide-vue-next';

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
    const params: Record<string, any> = {};
    if (filterForm.search) params.search = filterForm.search;
    if (filterForm.state_id) params.state_id = filterForm.state_id;
    if (filterForm.lga_id) params.lga_id = filterForm.lga_id;
    if (filterForm.ward_id) params.ward_id = filterForm.ward_id;
    if (filterForm.status) params.status = filterForm.status;

    router.get('/members', params, { preserveState: true, replace: true });
};

const resetFilters = () => {
    filterForm.search = '';
    filterForm.state_id = '';
    filterForm.lga_id = '';
    filterForm.ward_id = '';
    filterForm.status = '';
    applyFilters();
};

watch(() => filterForm.status, () => {
    applyFilters();
});

watch(() => filterForm.state_id, () => {
    filterForm.lga_id = '';
    filterForm.ward_id = '';
    applyFilters();
});

watch(() => filterForm.lga_id, () => {
    filterForm.ward_id = '';
    applyFilters();
});

watch(() => filterForm.ward_id, () => {
    applyFilters();
});

const page = usePage();
const user = computed(() => (page.props.auth as any)?.user);
const userRoles = computed(() => user.value?.roles || []);
const canVerify = computed(() => {
    return userRoles.value.some((role: string) => 
        ['Super Administrator', 'National Administrator', 'State Coordinator', 'LGA Coordinator', 'Ward Coordinator', 'Polling Unit Coordinator'].includes(role)
    ) || (user.value?.permissions && user.value.permissions.includes('verify members'));
});

const selectedMembers = ref<number[]>([]);
const markAllFilteredActive = ref(false);
const markAllFilteredLoading = ref(false);

const selectAllPage = computed({
    get: () => props.members.data.length > 0 && props.members.data.every(m => selectedMembers.value.includes(m.id)),
    set: (val: boolean) => {
        if (val) {
            const pageIds = props.members.data.map(m => m.id);
            const set = new Set([...selectedMembers.value, ...pageIds]);
            selectedMembers.value = Array.from(set);
        } else {
            const pageIds = new Set(props.members.data.map(m => m.id));
            selectedMembers.value = selectedMembers.value.filter(id => !pageIds.has(id));
            markAllFilteredActive.value = false;
        }
    }
});

const markAllFiltered = async () => {
    markAllFilteredLoading.value = true;
    try {
        const params = new URLSearchParams({ ...filterForm, get_all_ids: '1' } as any);
        const res = await fetch(`/members?${params.toString()}`, {
            headers: { 'Accept': 'application/json' }
        });
        if (res.ok) {
            const ids = await res.json();
            if (Array.isArray(ids)) {
                selectedMembers.value = ids;
                markAllFilteredActive.value = true;
            }
        }
    } catch (e) {
        console.error(e);
    } finally {
        markAllFilteredLoading.value = false;
    }
};

watch(() => filterForm, () => {
    selectedMembers.value = [];
    markAllFilteredActive.value = false;
}, { deep: true });

const showBulkModal = ref(false);
const bulkActionStatus = ref<'verified' | 'rejected'>('verified');
const bulkForm = useForm({
    member_ids: [] as number[],
    new_status: 'verified' as 'verified' | 'rejected',
    comments: '',
});

const openBulkModal = (status: 'verified' | 'rejected') => {
    if (selectedMembers.value.length === 0) return;
    bulkActionStatus.value = status;
    bulkForm.member_ids = [...selectedMembers.value];
    bulkForm.new_status = status;
    bulkForm.comments = '';
    showBulkModal.value = true;
};

const confirmBulkAction = () => {
    bulkForm.post(route('members.bulk-verify'), {
        onSuccess: () => {
            showBulkModal.value = false;
            selectedMembers.value = [];
            markAllFilteredActive.value = false;
            bulkForm.reset();
        },
    });
};
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

            <!-- Bulk Verification Action Toolbar -->
            <div
                v-if="canVerify && selectedMembers.length > 0"
                class="flex flex-col justify-between gap-4 rounded-2xl border border-emerald-500/30 bg-emerald-50/90 p-4 text-emerald-950 shadow-md transition duration-300 dark:border-emerald-500/40 dark:bg-emerald-950/50 dark:text-emerald-100 sm:flex-row sm:items-center"
            >
                <div class="flex flex-wrap items-center gap-3">
                    <span class="inline-flex size-7 items-center justify-center rounded-full bg-emerald-600 text-xs font-bold text-white shadow-sm dark:bg-emerald-500">
                        {{ selectedMembers.length }}
                    </span>
                    <span class="text-sm font-bold">Member(s) Selected</span>
                    <button
                        type="button"
                        @click="selectAllPage = !selectAllPage"
                        class="text-xs font-semibold underline underline-offset-2 transition hover:text-emerald-700 dark:hover:text-emerald-300"
                    >
                        {{ selectAllPage ? 'Deselect Page' : 'Select Page' }}
                    </button>
                    <button
                        v-if="members.total > members.data.length && selectedMembers.length !== members.total"
                        type="button"
                        @click="markAllFiltered"
                        :disabled="markAllFilteredLoading"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-200/80 px-3 py-1.5 text-xs font-bold text-emerald-900 transition hover:bg-emerald-300 disabled:opacity-50 dark:bg-emerald-800/80 dark:text-emerald-200 dark:hover:bg-emerald-700"
                    >
                        <LoaderCircle v-if="markAllFilteredLoading" class="size-3.5 animate-spin" />
                        <span>Mark All {{ members.total }} Members (Across All Pages)</span>
                    </button>
                    <span v-else-if="selectedMembers.length === members.total && members.total > members.data.length" class="text-xs font-bold text-emerald-800 dark:text-emerald-300">
                        ✓ All {{ members.total }} filtered members marked
                    </span>
                </div>

                <div class="flex flex-wrap items-center gap-2.5 sm:flex-nowrap">
                    <button
                        type="button"
                        @click="openBulkModal('verified')"
                        class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-xl bg-emerald-600 px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-emerald-500 dark:bg-emerald-500 dark:hover:bg-emerald-400 sm:flex-initial"
                    >
                        <CheckCircle class="size-4" /> Bulk Verify (Approve)
                    </button>
                    <button
                        type="button"
                        @click="openBulkModal('rejected')"
                        class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-xl bg-rose-600 px-4 py-2 text-xs font-bold text-white shadow-sm transition hover:bg-rose-500 sm:flex-initial"
                    >
                        <XCircle class="size-4" /> Bulk Reject
                    </button>
                    <button
                        type="button"
                        @click="selectedMembers = []"
                        class="rounded-xl border border-emerald-300/60 bg-white/70 px-3.5 py-2 text-xs font-semibold text-emerald-800 transition hover:bg-white dark:border-emerald-700/60 dark:bg-emerald-900/40 dark:text-emerald-200 dark:hover:bg-emerald-900"
                    >
                        Clear
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm dark:bg-sidebar">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-border bg-muted/50 text-xs uppercase text-muted-foreground">
                            <tr>
                                <th v-if="canVerify" class="w-10 px-3.5 py-3.5">
                                    <input
                                        type="checkbox"
                                        v-model="selectAllPage"
                                        class="size-4 rounded border-input text-emerald-600 focus:ring-emerald-500 cursor-pointer"
                                        title="Select/Deselect all on this page"
                                    />
                                </th>
                                <th class="px-3.5 py-3.5 font-semibold">Member & ID</th>
                                <th class="px-3.5 py-3.5 font-semibold">Contact</th>
                                <th class="px-3.5 py-3.5 font-semibold">Jurisdiction</th>
                                <th class="px-3.5 py-3.5 font-semibold">Referrals</th>
                                <th class="px-3.5 py-3.5 font-semibold">Status</th>
                                <th class="px-3.5 py-3.5 text-right font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr v-if="members.data.length === 0">
                                <td :colSpan="canVerify ? 7 : 6" class="p-8 text-center text-muted-foreground">No members found matching the specified filters.</td>
                            </tr>
                            <tr v-for="member in members.data" :key="member.id" class="transition hover:bg-muted/50" :class="{ 'bg-emerald-50/50 dark:bg-emerald-950/20': selectedMembers.includes(member.id) }">
                                <td v-if="canVerify" class="w-10 px-3.5 py-3.5">
                                    <input
                                        type="checkbox"
                                        :value="member.id"
                                        v-model="selectedMembers"
                                        class="size-4 rounded border-input text-emerald-600 focus:ring-emerald-500 cursor-pointer"
                                    />
                                </td>
                                <td class="max-w-[180px] sm:max-w-[220px] px-3.5 py-3.5">
                                    <div class="truncate font-bold text-foreground" :title="`${member.first_name} ${member.last_name}`">{{ member.first_name }} {{ member.last_name }}</div>
                                    <div class="font-mono text-xs font-semibold text-emerald-600 dark:text-emerald-400">{{ member.membership_number }}</div>
                                    <div class="truncate text-[11px] text-muted-foreground" :title="`${member.gender} • ${member.occupation || 'N/A'}`">{{ member.gender }} • {{ member.occupation || 'N/A' }}</div>
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
                                    <div class="flex items-center justify-end gap-1.5">
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
                                        <a
                                            :href="`/badge/${member.membership_number}`"
                                            target="_blank"
                                            class="inline-flex items-center gap-1 rounded-lg border border-emerald-500/30 bg-emerald-50 px-2.5 py-1.5 text-xs font-semibold text-emerald-800 shadow-sm transition hover:bg-emerald-100 dark:border-emerald-500/40 dark:bg-emerald-950/40 dark:text-emerald-200 dark:hover:bg-emerald-900"
                                            title="View / Print Digital Membership Badge"
                                        >
                                            View Badge
                                        </a>
                                    </div>
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

        <!-- Bulk Verification Confirmation Modal -->
        <Teleport to="body">
            <div
                v-if="showBulkModal"
                class="fixed inset-0 z-50 flex min-h-screen items-center justify-center overflow-y-auto bg-black/60 p-3 backdrop-blur-sm sm:p-6"
            >
                <div class="my-auto max-h-[90vh] w-full max-w-md overflow-y-auto rounded-2xl border border-border bg-card p-5 shadow-2xl dark:bg-sidebar sm:rounded-3xl sm:p-6">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex size-12 shrink-0 items-center justify-center rounded-full"
                            :class="bulkActionStatus === 'verified' ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-400' : 'bg-rose-100 text-rose-600 dark:bg-rose-900/40 dark:text-rose-400'"
                        >
                            <CheckCircle v-if="bulkActionStatus === 'verified'" class="size-6" />
                            <XCircle v-else class="size-6" />
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-foreground">
                                Confirm Bulk {{ bulkActionStatus === 'verified' ? 'Verification' : 'Rejection' }}
                            </h2>
                            <p class="text-xs text-muted-foreground">
                                You are about to update <span class="font-bold text-foreground">{{ selectedMembers.length }}</span> member(s).
                            </p>
                        </div>
                    </div>

                    <form @submit.prevent="confirmBulkAction" class="mt-5 space-y-4">
                        <div class="rounded-xl border border-border bg-muted/40 p-3.5 text-xs text-muted-foreground">
                            <p>
                                Selected status change:
                                <span class="font-bold uppercase" :class="bulkActionStatus === 'verified' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'">
                                    {{ bulkActionStatus }}
                                </span>
                            </p>
                            <p class="mt-1">All selected members will have their verification status updated and an audit trail log generated.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-foreground">Audit Trail Comments (Optional)</label>
                            <textarea
                                v-model="bulkForm.comments"
                                rows="3"
                                class="mt-1 w-full rounded-xl border border-input bg-background p-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                placeholder="Enter reason or notes for bulk action..."
                            ></textarea>
                            <p v-if="bulkForm.errors.comments" class="mt-1 text-xs text-rose-500">{{ bulkForm.errors.comments }}</p>
                        </div>

                        <div class="mt-6 flex flex-col-reverse gap-2.5 sm:flex-row sm:justify-end sm:gap-3 border-t border-border pt-4">
                            <button
                                type="button"
                                @click="showBulkModal = false"
                                :disabled="bulkForm.processing"
                                class="w-full rounded-xl border border-input px-4 py-2.5 text-sm font-medium hover:bg-muted sm:w-auto"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="bulkForm.processing"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition disabled:opacity-50 sm:w-auto"
                                :class="bulkActionStatus === 'verified' ? 'bg-emerald-600 hover:bg-emerald-500 dark:bg-emerald-500 dark:hover:bg-emerald-400' : 'bg-rose-600 hover:bg-rose-500'"
                            >
                                <LoaderCircle v-if="bulkForm.processing" class="size-4 animate-spin" />
                                <span>{{ bulkForm.processing ? 'Processing...' : `Confirm Bulk ${bulkActionStatus === 'verified' ? 'Verification' : 'Rejection'}` }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>
    </AppLayout>
</template>
