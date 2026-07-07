<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { FileText, Edit, Plus, Trash2 } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Website Content', href: '/admin/content' },
];

const props = defineProps<{
    contents: Array<{
        id: number;
        key: string;
        title: string;
        body: string | null;
        image_url: string | null;
        created_at: string;
    }>;
}>();

const showModal = ref(false);
const isEditing = ref(false);
const editingId = ref<number | null>(null);

const form = useForm({
    key: '',
    title: '',
    body: '',
    image_url: '',
});

const openCreateModal = () => {
    isEditing.value = false;
    editingId.value = null;
    form.reset();
    showModal.value = true;
};

const openEditModal = (item: any) => {
    isEditing.value = true;
    editingId.value = item.id;
    form.key = item.key;
    form.title = item.title;
    form.body = item.body || '';
    form.image_url = item.image_url || '';
    showModal.value = true;
};

const submitForm = () => {
    if (isEditing.value && editingId.value) {
        form.put(`/admin/content/${editingId.value}`, {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            },
        });
    } else {
        form.post('/admin/content', {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            },
        });
    }
};

const deleteContent = (id: number) => {
    if (confirm('Are you sure you want to delete this content block?')) {
        router.delete(`/admin/content/${id}`);
    }
};
</script>

<template>
    <Head title="Manage Website Content" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-5xl flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex flex-col justify-between gap-4 border-b border-border pb-6 sm:flex-row sm:items-center">
                <div>
                    <h1 class="flex items-center gap-2 text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                        <FileText class="size-8 text-amber-600 dark:text-amber-400" /> Website Content Manager
                    </h1>
                    <p class="mt-1 text-sm text-muted-foreground">Manage dynamic text, slogans, and movement bio displayed on the public welcome page.</p>
                </div>
                <div>
                    <button
                        @click="openCreateModal"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-600 dark:bg-amber-500 dark:hover:bg-amber-400"
                    >
                        <Plus class="size-4" /> Add Content Block
                    </button>
                </div>
            </div>

            <!-- Content Blocks Grid -->
            <div class="grid gap-4 sm:grid-cols-2">
                <div v-if="contents.length === 0" class="col-span-2 rounded-2xl border border-border bg-card p-12 text-center text-muted-foreground dark:bg-sidebar">
                    No website content blocks found. Click "Add Content Block" to create one.
                </div>

                <div
                    v-for="item in contents"
                    :key="item.id"
                    class="group relative flex flex-col justify-between rounded-2xl border border-border bg-card p-6 shadow-sm transition hover:shadow-md dark:bg-sidebar"
                >
                    <div>
                        <div class="flex items-start justify-between gap-4">
                            <span class="rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-mono font-semibold text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">
                                {{ item.key }}
                            </span>
                            <div class="flex items-center gap-2">
                                <button @click="openEditModal(item)" class="text-muted-foreground transition hover:text-amber-600 dark:hover:text-amber-400" title="Edit content">
                                    <Edit class="size-4" />
                                </button>
                                <button @click="deleteContent(item.id)" class="text-muted-foreground transition hover:text-rose-500" title="Delete content">
                                    <Trash2 class="size-4" />
                                </button>
                            </div>
                        </div>

                        <h3 class="mt-3 text-lg font-bold text-foreground">{{ item.title }}</h3>
                        <p class="mt-2 line-clamp-8 whitespace-pre-line text-sm leading-relaxed text-muted-foreground">{{ item.body || 'No text content provided.' }}</p>
                    </div>

                    <div class="mt-4 border-t border-border pt-4 text-xs text-muted-foreground">
                        Last updated on {{ new Date(item.created_at).toLocaleDateString() }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Add/Edit Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-lg rounded-2xl bg-card p-6 shadow-2xl dark:bg-sidebar">
                <h3 class="text-lg font-bold text-foreground">{{ isEditing ? 'Edit Content Block' : 'Create Content Block' }}</h3>
                <p class="text-xs text-muted-foreground">Configure the section key, heading title, and body text.</p>

                <form @submit.prevent="submitForm" class="mt-6 space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-foreground">Section Identifier (Key) *</label>
                        <input
                            v-model="form.key"
                            :disabled="isEditing"
                            required
                            type="text"
                            class="mt-1 w-full rounded-xl border border-input bg-background px-3 py-2 font-mono text-sm disabled:opacity-50"
                            placeholder="e.g. hero_title, vision_section"
                        />
                        <p class="mt-1 text-[11px] text-muted-foreground">Unique system code used by the website template.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-foreground">Heading / Title *</label>
                        <input v-model="form.title" required type="text" class="mt-1 w-full rounded-xl border border-input bg-background px-3 py-2 text-sm" placeholder="Enter title..." />
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-foreground">Body Content</label>
                        <textarea v-model="form.body" rows="10" class="mt-1 w-full rounded-xl border border-input bg-background p-3 text-sm" placeholder="Enter full body text..."></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button" @click="showModal = false" class="rounded-xl px-4 py-2 text-sm text-muted-foreground hover:bg-muted">Cancel</button>
                        <button type="submit" :disabled="form.processing" class="rounded-xl bg-amber-600 px-6 py-2 text-sm font-semibold text-white hover:bg-amber-500 disabled:opacity-50">
                            {{ isEditing ? 'Save Changes' : 'Create Block' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
