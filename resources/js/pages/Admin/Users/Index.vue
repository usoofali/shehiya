<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { Plus, Search, Shield, Trash2, Users, X, Pencil } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Admin', href: '#' },
    { title: 'Coordinator Accounts', href: '/admin/users' },
];

const props = defineProps<{
    users: {
        data: Array<{
            id: number;
            name: string;
            email: string;
            roles: Array<{ name: string }>;
            state?: { name: string };
            lga?: { name: string };
            ward?: { name: string };
            polling_unit?: { name: string };
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
        from: number; to: number; total: number;
    };
    roles: Array<{ id: number; name: string }>;
    states: Array<{ id: number; name: string; lgas: Array<{ id: number; name: string; wards: Array<{ id: number; name: string }> }> }>;
    filters: { search?: string; role?: string };
}>();

// ---- Search ----
const search = ref(props.filters.search || '');
const roleFilter = ref(props.filters.role || '');
const applySearch = () => router.get('/admin/users', { search: search.value || undefined, role: roleFilter.value || undefined }, { preserveState: true, replace: true });
watch(roleFilter, applySearch);

// ---- Create Modal ----
const showCreate = ref(false);
const createForm = useForm({
    name: '', email: '', password: '', password_confirmation: '',
    role: '', state_id: '', lga_id: '', ward_id: '', polling_unit_id: '',
});
const createLgas = computed(() => {
    if (!createForm.state_id) { return []; }
    return props.states.find(s => s.id == Number(createForm.state_id))?.lgas ?? [];
});
const createWards = computed(() => {
    if (!createForm.lga_id) { return []; }
    return createLgas.value.find(l => l.id == Number(createForm.lga_id))?.wards ?? [];
});
const createPollingUnits = ref<Array<{ id: number; code: string | null; name: string }>>([]);
const loadingCreatePollingUnits = ref(false);
watch(() => createForm.state_id, () => { createForm.lga_id = ''; createForm.ward_id = ''; createForm.polling_unit_id = ''; createPollingUnits.value = []; });
watch(() => createForm.lga_id, () => { createForm.ward_id = ''; createForm.polling_unit_id = ''; createPollingUnits.value = []; });
watch(() => createForm.ward_id, async (wardId) => {
    createForm.polling_unit_id = '';
    createPollingUnits.value = [];
    if (!wardId) { return; }
    loadingCreatePollingUnits.value = true;
    try {
        const res = await fetch(`/api/wards/${wardId}/polling-units`);
        if (res.ok) {
            createPollingUnits.value = await res.json();
        }
    } catch {
        createPollingUnits.value = [];
    } finally {
        loadingCreatePollingUnits.value = false;
    }
});
const submitCreate = () => {
    createForm.post('/admin/users', {
        onSuccess: () => { showCreate.value = false; createForm.reset(); },
    });
};

// ---- Edit Role Modal ----
const isOpeningEdit = ref(false);
const editUser = ref<null | { id: number; name: string; role: string; state_id: string; lga_id: string; ward_id: string; polling_unit_id: string }>(null);
const editForm = useForm({ role: '', state_id: '', lga_id: '', ward_id: '', polling_unit_id: '' });
const editPollingUnits = ref<Array<{ id: number; code: string | null; name: string }>>([]);
const loadingEditPollingUnits = ref(false);

const openEdit = async (user: typeof props.users.data[number]) => {
    isOpeningEdit.value = true;
    editUser.value = {
        id: user.id, name: user.name,
        role: user.roles[0]?.name ?? '',
        state_id: user.state?.id?.toString() ?? '',
        lga_id: user.lga?.id?.toString() ?? '',
        ward_id: user.ward?.id?.toString() ?? '',
        polling_unit_id: user.polling_unit?.id?.toString() ?? '',
    };
    editForm.role = user.roles[0]?.name ?? '';
    editForm.state_id = user.state?.id?.toString() ?? '';
    editForm.lga_id = user.lga?.id?.toString() ?? '';
    editForm.ward_id = user.ward?.id?.toString() ?? '';
    editForm.polling_unit_id = user.polling_unit?.id?.toString() ?? '';
    editPollingUnits.value = [];

    if (editForm.ward_id) {
        loadingEditPollingUnits.value = true;
        try {
            const res = await fetch(`/api/wards/${editForm.ward_id}/polling-units`);
            if (res.ok) {
                editPollingUnits.value = await res.json();
            }
        } finally {
            loadingEditPollingUnits.value = false;
        }
    }
    setTimeout(() => { isOpeningEdit.value = false; }, 100);
};
const editLgas = computed(() => {
    if (!editForm.state_id) { return []; }
    return props.states.find(s => s.id == Number(editForm.state_id))?.lgas ?? [];
});
const editWards = computed(() => {
    if (!editForm.lga_id) { return []; }
    return editLgas.value.find(l => l.id == Number(editForm.lga_id))?.wards ?? [];
});
watch(() => editForm.state_id, () => { if (isOpeningEdit.value) return; editForm.lga_id = ''; editForm.ward_id = ''; editForm.polling_unit_id = ''; editPollingUnits.value = []; });
watch(() => editForm.lga_id, () => { if (isOpeningEdit.value) return; editForm.ward_id = ''; editForm.polling_unit_id = ''; editPollingUnits.value = []; });
watch(() => editForm.ward_id, async (wardId) => {
    if (!isOpeningEdit.value) {
        editForm.polling_unit_id = '';
    }
    if (isOpeningEdit.value) return;
    editPollingUnits.value = [];
    if (!wardId) { return; }
    loadingEditPollingUnits.value = true;
    try {
        const res = await fetch(`/api/wards/${wardId}/polling-units`);
        if (res.ok) {
            editPollingUnits.value = await res.json();
        }
    } catch {
        editPollingUnits.value = [];
    } finally {
        loadingEditPollingUnits.value = false;
    }
});
const submitEdit = () => {
    if (!editUser.value) { return; }
    editForm.put(`/admin/users/${editUser.value.id}`, {
        onSuccess: () => { editUser.value = null; },
    });
};

// ---- Delete ----
const showDeleteModal = ref(false);
const itemToDelete = ref<{ id: number, name: string } | null>(null);

const confirmDelete = (id: number, name: string) => {
    itemToDelete.value = { id, name };
    showDeleteModal.value = true;
};

const deleteAccount = () => {
    if (itemToDelete.value) {
        router.delete(`/admin/users/${itemToDelete.value.id}`, {
            onSuccess: () => {
                showDeleteModal.value = false;
                itemToDelete.value = null;
            }
        });
    }
};

const roleColor: Record<string, string> = {
    'Super Administrator': 'bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-300',
    'National Administrator': 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
    'State Coordinator': 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300',
    'LGA Coordinator': 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
    'Ward Coordinator': 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300',
    'Polling Unit Coordinator': 'bg-cyan-100 text-cyan-800 dark:bg-cyan-900/30 dark:text-cyan-300',
};

const selectClass = 'mt-1 w-full rounded-xl border border-input bg-background px-3.5 py-2.5 text-sm disabled:opacity-50 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500';
const inputClass = 'mt-1 w-full rounded-xl border border-input bg-background px-3.5 py-2.5 text-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500';
</script>

<template>
    <Head title="Coordinator Accounts — Admin" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex flex-col justify-between gap-4 border-b border-border pb-6 sm:flex-row sm:items-center">
                <div>
                    <h1 class="flex items-center gap-2 text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                        <Users class="size-8 text-amber-600 dark:text-amber-400" /> Coordinator Accounts
                    </h1>
                    <p class="mt-1 text-sm text-muted-foreground">Manage platform access — create, assign roles, and set jurisdictions for coordinators.</p>
                </div>
                <button @click="showCreate = true"
                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-amber-600 to-emerald-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:from-amber-500 hover:to-emerald-500">
                    <Plus class="size-4" /> Add Coordinator
                </button>
            </div>

            <!-- Filters -->
            <div class="flex flex-col gap-3 sm:flex-row">
                <div class="relative flex-1">
                    <Search class="absolute left-3 top-3 size-4 text-muted-foreground" />
                    <input v-model="search" @keyup.enter="applySearch" type="text" placeholder="Search by name or email..."
                        class="w-full rounded-xl border border-input bg-background py-2.5 pl-9 pr-3 text-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500" />
                </div>
                <select v-model="roleFilter" class="rounded-xl border border-input bg-background px-3 py-2.5 text-sm focus:border-amber-500 focus:outline-none">
                    <option value="">All Roles</option>
                    <option v-for="role in roles" :key="role.id" :value="role.name">{{ role.name }}</option>
                </select>
            </div>

            <!-- Table -->
            <div class="overflow-hidden rounded-2xl border border-border bg-card shadow-sm dark:bg-sidebar">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-border bg-muted/50 text-xs uppercase text-muted-foreground">
                            <tr>
                                <th class="px-5 py-4 font-semibold">Name</th>
                                <th class="px-5 py-4 font-semibold">Email</th>
                                <th class="px-5 py-4 font-semibold">Role</th>
                                <th class="px-5 py-4 font-semibold">Jurisdiction</th>
                                <th class="px-5 py-4 text-right font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr v-if="users.data.length === 0">
                                <td colspan="5" class="p-8 text-center text-muted-foreground">No coordinator accounts found.</td>
                            </tr>
                            <tr v-for="user in users.data" :key="user.id" class="transition hover:bg-muted/50">
                                <td class="px-5 py-4 font-semibold text-foreground">{{ user.name }}</td>
                                <td class="px-5 py-4 text-muted-foreground">{{ user.email }}</td>
                                <td class="px-5 py-4">
                                    <span v-if="user.roles.length" :class="['inline-block rounded-full px-2.5 py-0.5 text-xs font-semibold', roleColor[user.roles[0].name] ?? 'bg-muted text-muted-foreground']">
                                        {{ user.roles[0].name }}
                                    </span>
                                    <span v-else class="text-xs italic text-muted-foreground">No role</span>
                                </td>
                                <td class="px-5 py-4 text-xs text-muted-foreground">
                                    <div v-if="user.state">{{ user.state.name }}</div>
                                    <div v-if="user.lga" class="text-[11px]">{{ user.lga.name }}</div>
                                    <div v-if="user.ward" class="text-[11px]">{{ user.ward.name }}</div>
                                    <div v-if="user.polling_unit" class="text-[11px] font-medium text-amber-600 dark:text-amber-400">{{ user.polling_unit.name }}</div>
                                    <span v-if="!user.state" class="italic">National</span>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="inline-flex gap-2">
                                        <button @click="openEdit(user)"
                                            class="inline-flex items-center gap-1 rounded-lg border border-border px-2.5 py-1.5 text-xs font-medium text-foreground hover:bg-muted">
                                            <Pencil class="size-3" /> Edit
                                        </button>
                                        <button @click="confirmDelete(user.id, user.name)"
                                            class="inline-flex items-center gap-1 rounded-lg border border-rose-200 px-2.5 py-1.5 text-xs font-medium text-rose-600 hover:bg-rose-50 dark:border-rose-900 dark:hover:bg-rose-950/30">
                                            <Trash2 class="size-3" /> Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="users.total > 0" class="flex items-center justify-between border-t border-border px-5 py-4">
                    <span class="text-xs text-muted-foreground">Showing {{ users.from }} to {{ users.to }} of {{ users.total }}</span>
                    <div class="flex gap-1">
                        <a v-for="(link, i) in users.links" :key="i" :href="link.url || '#'" v-html="link.label"
                            class="rounded-lg px-3 py-1 text-xs font-medium transition"
                            :class="{ 'bg-amber-600 text-white': link.active, 'text-muted-foreground hover:bg-muted': !link.active && link.url, 'pointer-events-none opacity-40': !link.url }" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Modal -->
        <Teleport to="body">
            <div v-if="showCreate" class="fixed inset-0 z-50 flex min-h-screen items-center justify-center overflow-y-auto bg-black/60 p-3 sm:p-6 backdrop-blur-sm" @click.self="showCreate = false">
                <div class="my-auto w-full max-w-lg max-h-[90vh] overflow-y-auto rounded-2xl sm:rounded-3xl border border-border bg-card p-4 sm:p-6 shadow-2xl dark:bg-sidebar">
                    <div class="mb-5 flex items-center justify-between">
                        <h2 class="text-lg font-bold text-foreground">Add Coordinator Account</h2>
                        <button @click="showCreate = false" class="rounded-lg p-1.5 hover:bg-muted"><X class="size-5" /></button>
                    </div>
                    <form @submit.prevent="submitCreate" class="space-y-4">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-xs font-medium text-foreground">Full Name *</label>
                                <input v-model="createForm.name" required :class="inputClass" placeholder="Full name" />
                                <p v-if="createForm.errors.name" class="mt-1 text-xs text-rose-500">{{ createForm.errors.name }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-foreground">Email *</label>
                                <input v-model="createForm.email" type="email" required :class="inputClass" placeholder="email@example.com" />
                                <p v-if="createForm.errors.email" class="mt-1 text-xs text-rose-500">{{ createForm.errors.email }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-foreground">Password *</label>
                                <input v-model="createForm.password" type="password" required :class="inputClass" />
                                <p v-if="createForm.errors.password" class="mt-1 text-xs text-rose-500">{{ createForm.errors.password }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-foreground">Confirm Password *</label>
                                <input v-model="createForm.password_confirmation" type="password" required :class="inputClass" />
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-foreground">Role *</label>
                            <select v-model="createForm.role" required :class="selectClass">
                                <option value="" disabled>Select Role</option>
                                <option v-for="role in roles" :key="role.id" :value="role.name">{{ role.name }}</option>
                            </select>
                            <p v-if="createForm.errors.role" class="mt-1 text-xs text-rose-500">{{ createForm.errors.role }}</p>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-xs font-medium text-foreground">State</label>
                                <select v-model="createForm.state_id" :class="selectClass">
                                    <option value="">None</option>
                                    <option v-for="s in states" :key="s.id" :value="s.id">{{ s.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-foreground">LGA</label>
                                <select v-model="createForm.lga_id" :disabled="!createForm.state_id" :class="selectClass">
                                    <option value="">None</option>
                                    <option v-for="l in createLgas" :key="l.id" :value="l.id">{{ l.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-foreground">Ward</label>
                                <select v-model="createForm.ward_id" :disabled="!createForm.lga_id" :class="selectClass">
                                    <option value="">None</option>
                                    <option v-for="w in createWards" :key="w.id" :value="w.id">{{ w.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-foreground">Polling Unit</label>
                                <select v-model="createForm.polling_unit_id" :disabled="!createForm.ward_id || loadingCreatePollingUnits" :class="selectClass">
                                    <option value="">{{ loadingCreatePollingUnits ? 'Loading polling units...' : !createForm.ward_id ? 'Select Ward first' : 'None' }}</option>
                                    <option v-for="pu in createPollingUnits" :key="pu.id" :value="pu.id">{{ pu.code ? `${pu.code} - ` : '' }}{{ pu.name }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" @click="showCreate = false" class="rounded-xl px-4 py-2 text-sm text-muted-foreground hover:bg-muted">Cancel</button>
                            <button type="submit" :disabled="createForm.processing"
                                class="rounded-xl bg-gradient-to-r from-amber-600 to-emerald-600 px-6 py-2 text-sm font-bold text-white hover:from-amber-500 hover:to-emerald-500 disabled:opacity-50">
                                {{ createForm.processing ? 'Creating...' : 'Create Account' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Edit Modal -->
            <div v-if="editUser" class="fixed inset-0 z-50 flex min-h-screen items-center justify-center overflow-y-auto bg-black/60 p-3 sm:p-6 backdrop-blur-sm" @click.self="editUser = null">
                <div class="my-auto w-full max-w-md max-h-[90vh] overflow-y-auto rounded-2xl sm:rounded-3xl border border-border bg-card p-4 sm:p-6 shadow-2xl dark:bg-sidebar">
                    <div class="mb-5 flex items-center justify-between">
                        <h2 class="text-lg font-bold text-foreground">Edit: {{ editUser.name }}</h2>
                        <button @click="editUser = null" class="rounded-lg p-1.5 hover:bg-muted"><X class="size-5" /></button>
                    </div>
                    <form @submit.prevent="submitEdit" class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-foreground">Role *</label>
                            <select v-model="editForm.role" required :class="selectClass">
                                <option value="" disabled>Select Role</option>
                                <option v-for="role in roles" :key="role.id" :value="role.name">{{ role.name }}</option>
                            </select>
                        </div>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-xs font-medium text-foreground">State</label>
                                <select v-model="editForm.state_id" :class="selectClass">
                                    <option value="">None</option>
                                    <option v-for="s in states" :key="s.id" :value="s.id">{{ s.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-foreground">LGA</label>
                                <select v-model="editForm.lga_id" :disabled="!editForm.state_id" :class="selectClass">
                                    <option value="">None</option>
                                    <option v-for="l in editLgas" :key="l.id" :value="l.id">{{ l.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-foreground">Ward</label>
                                <select v-model="editForm.ward_id" :disabled="!editForm.lga_id" :class="selectClass">
                                    <option value="">None</option>
                                    <option v-for="w in editWards" :key="w.id" :value="w.id">{{ w.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-foreground">Polling Unit</label>
                                <select v-model="editForm.polling_unit_id" :disabled="!editForm.ward_id || loadingEditPollingUnits" :class="selectClass">
                                    <option value="">{{ loadingEditPollingUnits ? 'Loading polling units...' : !editForm.ward_id ? 'Select Ward first' : 'None' }}</option>
                                    <option v-for="pu in editPollingUnits" :key="pu.id" :value="pu.id">{{ pu.code ? `${pu.code} - ` : '' }}{{ pu.name }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" @click="editUser = null" class="rounded-xl px-4 py-2 text-sm text-muted-foreground hover:bg-muted">Cancel</button>
                            <button type="submit" :disabled="editForm.processing"
                                class="rounded-xl bg-gradient-to-r from-amber-600 to-emerald-600 px-6 py-2 text-sm font-bold text-white hover:from-amber-500 hover:to-emerald-500 disabled:opacity-50">
                                {{ editForm.processing ? 'Saving...' : 'Save Changes' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Delete Confirmation Modal -->
            <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex min-h-screen items-center justify-center overflow-y-auto bg-black/60 p-3 sm:p-6 backdrop-blur-sm">
                <div class="my-auto w-full max-w-sm max-h-[90vh] overflow-y-auto rounded-2xl sm:rounded-3xl border border-border bg-card p-5 sm:p-6 shadow-2xl dark:bg-sidebar">
                    <h2 class="text-lg font-bold text-foreground">Delete Coordinator</h2>
                    <p class="mt-2 text-sm text-muted-foreground">Are you sure you want to delete the coordinator account for <strong>{{ itemToDelete?.name }}</strong>? This action cannot be undone.</p>
                    <div class="mt-6 flex justify-end gap-3">
                        <button @click="showDeleteModal = false" class="rounded-xl px-4 py-2 text-sm font-semibold text-muted-foreground hover:bg-muted">Cancel</button>
                        <button @click="deleteAccount" class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-500">Delete Account</button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AppLayout>
</template>
