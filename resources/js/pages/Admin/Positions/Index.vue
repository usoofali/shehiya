<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Plus, ShieldCheck, Trash2, X, Pencil, Award } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Admin', href: '#' },
    { title: 'EXCO Positions', href: '/admin/positions' },
];

const props = defineProps<{
    positions: Array<{
        id: number;
        name: string;
        esco_officials_count: number;
    }>;
}>();

// ---- Create Position ----
const showCreate = ref(false);
const createForm = useForm({ name: '' });
const submitCreate = () => {
    createForm.post('/admin/positions', {
        onSuccess: () => { showCreate.value = false; createForm.reset(); },
    });
};

// ---- Edit Position ----
const editPosition = ref<typeof props.positions[number] | null>(null);
const editForm = useForm({ name: '' });
const openEdit = (position: typeof props.positions[number]) => {
    editPosition.value = position;
    editForm.name = position.name;
};
const submitEdit = () => {
    if (!editPosition.value) { return; }
    editForm.put(`/admin/positions/${editPosition.value.id}`, {
        onSuccess: () => { editPosition.value = null; editForm.reset(); },
    });
};

// ---- Delete ----
const deletePosition = (id: number, name: string) => {
    if (confirm(`Delete position "${name}"? This cannot be undone.`)) {
        router.delete(`/admin/positions/${id}`);
    }
};
</script>

<template>
    <Head title="EXCO Positions — Admin" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex flex-col justify-between gap-4 border-b border-border pb-6 sm:flex-row sm:items-center">
                <div>
                    <h1 class="flex items-center gap-2 text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                        <Award class="size-8 text-indigo-600 dark:text-indigo-400" /> EXCO Positions
                    </h1>
                    <p class="mt-1 text-sm text-muted-foreground">Manage organizational leadership titles available when appointing EXCO officials.</p>
                </div>
                <button @click="showCreate = true"
                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:from-indigo-500 hover:to-purple-500">
                    <Plus class="size-4" /> Create Position
                </button>
            </div>

            <!-- Position Cards Grid -->
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <div v-if="positions.length === 0" class="col-span-full rounded-2xl border border-border p-12 text-center text-muted-foreground">
                    No EXCO positions created yet. Click "Create Position" above to add the first leadership title.
                </div>
                <div v-for="position in positions" :key="position.id"
                    class="relative flex flex-col justify-between overflow-hidden rounded-2xl border border-border bg-card p-5 shadow-sm transition hover:shadow-md dark:bg-sidebar">
                    <div class="absolute top-0 right-0 h-16 w-16 translate-x-4 -translate-y-4 rounded-full bg-indigo-500/10 blur-xl"></div>
                    <div>
                        <div class="flex items-start justify-between">
                            <span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-700/10 dark:bg-indigo-900/30 dark:text-indigo-300">
                                {{ position.esco_officials_count || 0 }} official{{ position.esco_officials_count !== 1 ? 's' : '' }}
                            </span>
                        </div>
                        <h3 class="mt-3 text-lg font-bold text-foreground">{{ position.name }}</h3>
                    </div>

                    <div class="mt-5 flex gap-2 border-t border-border pt-4">
                        <button @click="openEdit(position)"
                            class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-xl border border-border py-2 text-xs font-semibold text-foreground hover:bg-muted">
                            <Pencil class="size-3.5" /> Edit Title
                        </button>
                        <button @click="deletePosition(position.id, position.name)"
                            class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-rose-200 px-3 py-2 text-xs font-semibold text-rose-600 hover:bg-rose-50 dark:border-rose-900 dark:hover:bg-rose-950/30">
                            <Trash2 class="size-3.5" />
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create Position Modal -->
        <Teleport to="body">
            <div v-if="showCreate" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm" @click.self="showCreate = false">
                <div class="w-full max-w-md rounded-3xl border border-border bg-card p-6 shadow-2xl dark:bg-sidebar">
                    <div class="mb-5 flex items-center justify-between">
                        <h2 class="text-lg font-bold text-foreground">Create EXCO Position</h2>
                        <button @click="showCreate = false" class="rounded-lg p-1.5 hover:bg-muted"><X class="size-5" /></button>
                    </div>
                    <form @submit.prevent="submitCreate" class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-foreground">Position Title *</label>
                            <input v-model="createForm.name" required type="text"
                                class="mt-1 w-full rounded-xl border border-input bg-background px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                                placeholder="e.g. National Secretary" />
                            <p v-if="createForm.errors.name" class="mt-1 text-xs text-rose-500">{{ createForm.errors.name }}</p>
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" @click="showCreate = false" class="rounded-xl px-4 py-2 text-sm text-muted-foreground hover:bg-muted">Cancel</button>
                            <button type="submit" :disabled="createForm.processing"
                                class="rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-2 text-sm font-bold text-white hover:from-indigo-500 hover:to-purple-500 disabled:opacity-50">
                                {{ createForm.processing ? 'Creating...' : 'Create Position' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Edit Position Modal -->
            <div v-if="editPosition" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm" @click.self="editPosition = null">
                <div class="w-full max-w-md rounded-3xl border border-border bg-card p-6 shadow-2xl dark:bg-sidebar">
                    <div class="mb-5 flex items-center justify-between">
                        <h2 class="text-lg font-bold text-foreground">Edit Position Title</h2>
                        <button @click="editPosition = null" class="rounded-lg p-1.5 hover:bg-muted"><X class="size-5" /></button>
                    </div>
                    <form @submit.prevent="submitEdit" class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-foreground">Position Title *</label>
                            <input v-model="editForm.name" required type="text"
                                class="mt-1 w-full rounded-xl border border-input bg-background px-3.5 py-2.5 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500" />
                            <p v-if="editForm.errors.name" class="mt-1 text-xs text-rose-500">{{ editForm.errors.name }}</p>
                        </div>
                        <div class="flex justify-end gap-3 pt-2">
                            <button type="button" @click="editPosition = null" class="rounded-xl px-4 py-2 text-sm text-muted-foreground hover:bg-muted">Cancel</button>
                            <button type="submit" :disabled="editForm.processing"
                                class="rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-2 text-sm font-bold text-white hover:from-indigo-500 hover:to-purple-500 disabled:opacity-50">
                                {{ editForm.processing ? 'Saving...' : 'Save Changes' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>
    </AppLayout>
</template>
