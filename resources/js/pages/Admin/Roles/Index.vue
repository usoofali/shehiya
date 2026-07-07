<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Plus, Shield, Trash2, X, Pencil } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Admin', href: '#' },
    { title: 'Roles & Permissions', href: '/admin/roles' },
];

const props = defineProps<{
    roles: Array<{
        id: number;
        name: string;
        users_count: number;
        permissions: Array<{ id: number; name: string }>;
    }>;
    permissions: Array<{ id: number; name: string }>;
}>();

// ---- Create Role ----
const showCreate = ref(false);
const createForm = useForm({ name: '', permissions: [] as string[] });
const submitCreate = () => {
    createForm.post('/admin/roles', {
        onSuccess: () => { showCreate.value = false; createForm.reset(); },
    });
};

// ---- Edit Permissions ----
const editRole = ref<typeof props.roles[number] | null>(null);
const editForm = useForm({ permissions: [] as string[] });
const openEdit = (role: typeof props.roles[number]) => {
    editRole.value = role;
    editForm.permissions = role.permissions.map(p => p.name);
};
const submitEdit = () => {
    if (!editRole.value) { return; }
    editForm.put(`/admin/roles/${editRole.value.id}`, {
        onSuccess: () => { editRole.value = null; },
    });
};

// ---- Delete ----
const deleteRole = (id: number, name: string) => {
    if (confirm(`Delete role "${name}"? This cannot be undone.`)) {
        router.delete(`/admin/roles/${id}`);
    }
};

const isCoreRole = (name: string) => ['Super Administrator', 'National Administrator', 'State Coordinator', 'LGA Coordinator', 'Ward Coordinator', 'Polling Unit Coordinator'].includes(name);

const permissionGroups = (perms: typeof props.permissions) => {
    const groups: Record<string, typeof props.permissions> = {};
    perms.forEach(p => {
        const group = p.name.split(' ').slice(1).join(' ') || 'other';
        if (!groups[group]) { groups[group] = []; }
        groups[group].push(p);
    });
    return groups;
};

const toggleGroup = (form: { permissions: string[] }, perms: Array<{ id: number; name: string }>) => {
    const permNames = perms.map(p => p.name);
    const allSelected = permNames.every(name => form.permissions.includes(name));
    if (allSelected) {
        form.permissions = form.permissions.filter(name => !permNames.includes(name));
    } else {
        const toAdd = permNames.filter(name => !form.permissions.includes(name));
        form.permissions.push(...toAdd);
    }
};
</script>

<template>
    <Head title="Roles & Permissions — Admin" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex flex-col justify-between gap-4 border-b border-border pb-6 sm:flex-row sm:items-center">
                <div>
                    <h1 class="flex items-center gap-2 text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                        <Shield class="size-8 text-purple-600 dark:text-purple-400" /> Roles &amp; Permissions
                    </h1>
                    <p class="mt-1 text-sm text-muted-foreground">Manage what each role can do across the Shaihiyya Amanar Jagora Platform.</p>
                </div>
                <button @click="showCreate = true"
                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-purple-600 to-indigo-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:from-purple-500 hover:to-indigo-500">
                    <Plus class="size-4" /> Create Role
                </button>
            </div>

            <!-- Role Cards Grid -->
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <div v-for="role in roles" :key="role.id"
                    class="relative overflow-hidden rounded-2xl border border-border bg-card p-5 shadow-sm dark:bg-sidebar">
                    <div class="absolute top-0 right-0 h-16 w-16 translate-x-4 -translate-y-4 rounded-full bg-purple-500/10 blur-xl"></div>
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="font-bold text-foreground">{{ role.name }}</h3>
                            <p class="mt-0.5 text-xs text-muted-foreground">{{ role.users_count }} coordinator{{ role.users_count !== 1 ? 's' : '' }}</p>
                        </div>
                        <span v-if="isCoreRole(role.name)" class="rounded-full bg-muted px-2 py-0.5 text-[10px] font-bold uppercase text-muted-foreground">Core</span>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-1.5">
                        <span v-for="perm in role.permissions" :key="perm.id"
                            class="rounded-full bg-purple-100 px-2.5 py-0.5 text-[11px] font-semibold text-purple-800 dark:bg-purple-900/30 dark:text-purple-300">
                            {{ perm.name }}
                        </span>
                        <span v-if="role.permissions.length === 0" class="text-xs italic text-muted-foreground">No permissions assigned</span>
                    </div>

                    <div class="mt-5 flex gap-2 border-t border-border pt-4">
                        <button v-if="role.name !== 'Super Administrator'" @click="openEdit(role)"
                            class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-xl border border-border py-2 text-xs font-semibold text-foreground hover:bg-muted">
                            <Pencil class="size-3.5" /> Edit Permissions
                        </button>
                        <span v-else class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-xl border border-border bg-muted/30 py-2 text-xs font-semibold text-muted-foreground opacity-70 cursor-not-allowed">
                            <Shield class="size-3.5" /> Full Access (Locked)
                        </span>
                        <button v-if="!isCoreRole(role.name)" @click="deleteRole(role.id, role.name)"
                            class="inline-flex items-center gap-1.5 rounded-xl border border-rose-200 px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50 dark:border-rose-900 dark:hover:bg-rose-950/30">
                            <Trash2 class="size-3.5" />
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Role Modal -->
        <Teleport to="body">
            <div v-if="showCreate" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm" @click.self="showCreate = false">
                <div class="w-full max-w-lg rounded-3xl border border-border bg-card p-6 shadow-2xl dark:bg-sidebar">
                    <div class="mb-5 flex items-center justify-between">
                        <h2 class="text-lg font-bold text-foreground">Create New Role</h2>
                        <button @click="showCreate = false" class="rounded-lg p-1.5 hover:bg-muted"><X class="size-5" /></button>
                    </div>
                    <form @submit.prevent="submitCreate" class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-foreground">Role Name *</label>
                            <input v-model="createForm.name" required
                                class="mt-1 w-full rounded-xl border border-input bg-background px-3.5 py-2.5 text-sm focus:border-purple-500 focus:outline-none focus:ring-1 focus:ring-purple-500"
                                placeholder="e.g. Regional Coordinator" />
                            <p v-if="createForm.errors.name" class="mt-1 text-xs text-rose-500">{{ createForm.errors.name }}</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-foreground mb-2">Assign Permissions</label>
                            <div v-for="(perms, group) in permissionGroups(permissions)" :key="group" class="mb-3">
                                <div class="mb-1.5 flex items-center justify-between">
                                    <p class="text-[11px] font-bold uppercase tracking-wider text-muted-foreground">{{ group }}</p>
                                    <button type="button" @click="toggleGroup(createForm, perms)" class="text-[11px] font-semibold text-purple-600 hover:underline dark:text-purple-400">
                                        {{ perms.every(p => createForm.permissions.includes(p.name)) ? 'Clear' : 'Select All' }}
                                    </button>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <label v-for="perm in perms" :key="perm.id"
                                        class="flex cursor-pointer items-center gap-1.5 rounded-lg border border-border bg-muted/50 px-3 py-1.5 text-xs transition hover:bg-muted"
                                        :class="{ 'border-purple-500 bg-purple-50 dark:bg-purple-950/30': createForm.permissions.includes(perm.name) }">
                                        <input type="checkbox" :value="perm.name" v-model="createForm.permissions" class="accent-purple-600" />
                                        {{ perm.name }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" @click="showCreate = false" class="rounded-xl px-4 py-2 text-sm text-muted-foreground hover:bg-muted">Cancel</button>
                            <button type="submit" :disabled="createForm.processing"
                                class="rounded-xl bg-gradient-to-r from-purple-600 to-indigo-600 px-6 py-2 text-sm font-bold text-white hover:from-purple-500 hover:to-indigo-500 disabled:opacity-50">
                                {{ createForm.processing ? 'Creating...' : 'Create Role' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Edit Permissions Modal -->
            <div v-if="editRole" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm" @click.self="editRole = null">
                <div class="w-full max-w-lg rounded-3xl border border-border bg-card p-6 shadow-2xl dark:bg-sidebar">
                    <div class="mb-5 flex items-center justify-between">
                        <h2 class="text-lg font-bold text-foreground">Edit: {{ editRole.name }}</h2>
                        <button @click="editRole = null" class="rounded-lg p-1.5 hover:bg-muted"><X class="size-5" /></button>
                    </div>
                    <div v-if="isCoreRole(editRole.name)" class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-800 dark:border-amber-900/40 dark:bg-amber-950/30 dark:text-amber-300">
                        ⚠️ This is a core system role. Permission changes may affect the entire platform.
                    </div>
                    <form @submit.prevent="submitEdit" class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-foreground mb-2">Permissions</label>
                            <div v-for="(perms, group) in permissionGroups(permissions)" :key="group" class="mb-3">
                                <div class="mb-1.5 flex items-center justify-between">
                                    <p class="text-[11px] font-bold uppercase tracking-wider text-muted-foreground">{{ group }}</p>
                                    <button type="button" @click="toggleGroup(editForm, perms)" class="text-[11px] font-semibold text-purple-600 hover:underline dark:text-purple-400">
                                        {{ perms.every(p => editForm.permissions.includes(p.name)) ? 'Clear' : 'Select All' }}
                                    </button>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <label v-for="perm in perms" :key="perm.id"
                                        class="flex cursor-pointer items-center gap-1.5 rounded-lg border border-border bg-muted/50 px-3 py-1.5 text-xs transition hover:bg-muted"
                                        :class="{ 'border-purple-500 bg-purple-50 dark:bg-purple-950/30': editForm.permissions.includes(perm.name) }">
                                        <input type="checkbox" :value="perm.name" v-model="editForm.permissions" class="accent-purple-600" />
                                        {{ perm.name }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" @click="editRole = null" class="rounded-xl px-4 py-2 text-sm text-muted-foreground hover:bg-muted">Cancel</button>
                            <button type="submit" :disabled="editForm.processing"
                                class="rounded-xl bg-gradient-to-r from-purple-600 to-indigo-600 px-6 py-2 text-sm font-bold text-white hover:from-purple-500 hover:to-indigo-500 disabled:opacity-50">
                                {{ editForm.processing ? 'Saving...' : 'Save Permissions' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>
    </AppLayout>
</template>
