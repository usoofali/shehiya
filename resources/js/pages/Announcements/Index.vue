<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Bell, Calendar, Megaphone, Plus, Trash2, Edit } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Announcements', href: '/announcements' },
];

const props = defineProps<{
    announcements: {
        data: Array<{
            id: number;
            title: string;
            content: string;
            type: string;
            target_level: string;
            created_at: string;
            image_path?: string;
            published_by?: { id: number; name: string };
            state?: { name: string };
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
        total: number;
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

const showAddModal = ref(false);
const showDeleteModal = ref(false);
const itemToDelete = ref<number | null>(null);
const isEditing = ref(false);
const editId = ref<number | null>(null);

const form = useForm({
    title: '',
    content: '',
    type: 'notice',
    image: null as File | null,
    target_level: 'national',
    state_id: '',
    lga_id: '',
    ward_id: '',
});

const availableLgas = computed(() => {
    if (!form.state_id) return [];
    const state = props.states.find((s) => s.id == Number(form.state_id));
    return state ? state.lgas : [];
});

const availableWards = computed(() => {
    if (!form.lga_id) return [];
    const lga = availableLgas.value.find((l) => l.id == Number(form.lga_id));
    return lga ? lga.wards : [];
});

const handleImageUpload = (e: Event) => {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (file) {
        form.image = file;
    }
};

const openAddModal = () => {
    isEditing.value = false;
    editId.value = null;
    form.reset();
    showAddModal.value = true;
};

const openEditModal = (item: any) => {
    isEditing.value = true;
    editId.value = item.id;
    form.title = item.title;
    form.content = item.content;
    form.type = item.type;
    form.target_level = item.target_level;
    form.state_id = item.state_id || '';
    form.lga_id = item.lga_id || '';
    form.ward_id = item.ward_id || '';
    form.image = null;
    showAddModal.value = true;
};

const confirmDelete = (id: number) => {
    itemToDelete.value = id;
    showDeleteModal.value = true;
};

const submitAnnouncement = () => {
    if (isEditing.value && editId.value) {
        form.post(`/announcements/${editId.value}?_method=PUT`, {
            forceFormData: true,
            onSuccess: () => {
                showAddModal.value = false;
                form.reset();
            },
        });
    } else {
        form.post('/announcements', {
            forceFormData: true,
            onSuccess: () => {
                showAddModal.value = false;
                form.reset();
            },
        });
    }
};

const deleteAnnouncement = () => {
    if (itemToDelete.value) {
        router.delete(`/announcements/${itemToDelete.value}`, {
            onSuccess: () => {
                showDeleteModal.value = false;
                itemToDelete.value = null;
            }
        });
    }
};
</script>

<template>
    <Head title="Movement Announcements" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-4xl flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex flex-col justify-between gap-4 border-b border-border pb-6 sm:flex-row sm:items-center">
                <div>
                    <h1 class="flex items-center gap-2 text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                        <Megaphone class="size-8 text-amber-600 dark:text-amber-400" /> Shaihiyya Amanar Jagora Noticeboard
                    </h1>
                    <p class="mt-1 text-sm text-muted-foreground">Official notices, meeting schedules, and general updates.</p>
                </div>
                <div>
                    <button
                        @click="showAddModal = true"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-600 dark:bg-amber-500 dark:hover:bg-amber-400"
                    >
                        <Plus class="size-4" /> Publish Notice
                    </button>
                </div>
            </div>

            <!-- Notice Feed -->
            <div class="space-y-4">
                <div v-if="announcements.data.length === 0" class="rounded-2xl border border-border bg-card p-12 text-center text-muted-foreground dark:bg-sidebar">
                    No announcements published for your jurisdiction yet.
                </div>

                <div
                    v-for="item in announcements.data"
                    :key="item.id"
                    class="group relative rounded-2xl border border-border bg-card p-6 shadow-sm transition hover:shadow-md dark:bg-sidebar"
                >
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-center gap-2">
                            <span
                                class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize"
                                :class="{
                                    'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300': item.type === 'notice',
                                    'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300': item.type === 'meeting',
                                    'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300': item.type === 'update',
                                }"
                            >
                                <Bell v-if="item.type === 'notice'" class="size-3" />
                                <Calendar v-else-if="item.type === 'meeting'" class="size-3" />
                                {{ item.type }}
                            </span>
                            <span class="rounded-full bg-muted px-2.5 py-0.5 text-xs font-medium uppercase text-muted-foreground">
                                {{ item.target_level }} {{ item.state ? '• ' + item.state.name : '' }}
                            </span>
                        </div>

                        <div class="flex gap-2">
                            <button @click="openEditModal(item)" class="text-muted-foreground opacity-0 transition group-hover:opacity-100 hover:text-amber-600" title="Edit notice">
                                <Edit class="size-4" />
                            </button>
                            <button @click="confirmDelete(item.id)" class="text-muted-foreground opacity-0 transition group-hover:opacity-100 hover:text-rose-500" title="Delete notice">
                                <Trash2 class="size-4" />
                            </button>
                        </div>
                    </div>

                    <div v-if="item.image_path" class="mt-4 overflow-hidden rounded-xl border border-border">
                        <img :src="`/storage/${item.image_path}`" alt="Announcement Image" class="h-48 w-full object-cover" />
                    </div>

                    <h3 class="mt-3 text-lg font-bold text-foreground">{{ item.title }}</h3>
                    <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-muted-foreground">{{ item.content }}</p>

                    <div class="mt-4 flex items-center justify-between border-t border-border pt-4 text-xs font-medium text-muted-foreground">
                        <span>{{ new Date(item.created_at).toLocaleDateString() }}</span>
                        <span>{{ item.published_by?.name }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add/Edit Modal -->
        <div v-if="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
            <div class="w-full max-w-lg rounded-3xl bg-background p-6 shadow-2xl sm:p-8">
                <h2 class="text-xl font-bold text-foreground">{{ isEditing ? 'Edit Announcement' : 'Publish New Announcement' }}</h2>

                <form @submit.prevent="submitAnnouncement" class="mt-6 space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-foreground">Title *</label>
                        <input v-model="form.title" required type="text" class="mt-1 w-full rounded-xl border border-input bg-background px-3 py-2 text-sm" placeholder="e.g. National Executive Meeting" />
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-xs font-medium text-foreground">Notice Type</label>
                            <select v-model="form.type" class="mt-1 w-full rounded-xl border border-input bg-background px-3 py-2 text-sm">
                                <option value="notice">General Notice</option>
                                <option value="meeting">Meeting Schedule</option>
                                <option value="update">Movement Update</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-foreground">Target Scope</label>
                            <select v-model="form.target_level" class="mt-1 w-full rounded-xl border border-input bg-background px-3 py-2 text-sm">
                                <option value="national">National (All Movement)</option>
                                <option value="state">Specific State</option>
                                <option value="lga">Specific LGA</option>
                                <option value="ward">Specific Ward</option>
                            </select>
                        </div>
                    </div>

                    <div v-if="form.target_level === 'state' || form.target_level === 'lga' || form.target_level === 'ward'">
                        <label class="block text-xs font-medium text-foreground">Select State</label>
                        <select v-model="form.state_id" required class="mt-1 w-full rounded-xl border border-input bg-background px-3 py-2 text-sm">
                            <option value="">Select State</option>
                            <option v-for="state in states" :key="state.id" :value="state.id">{{ state.name }}</option>
                        </select>
                    </div>

                    <div v-if="form.target_level === 'lga' || form.target_level === 'ward'">
                        <label class="block text-xs font-medium text-foreground">Select LGA</label>
                        <select v-model="form.lga_id" required class="mt-1 w-full rounded-xl border border-input bg-background px-3 py-2 text-sm">
                            <option value="">Select LGA</option>
                            <option v-for="lga in availableLgas" :key="lga.id" :value="lga.id">{{ lga.name }}</option>
                        </select>
                    </div>

                    <div v-if="form.target_level === 'ward'">
                        <label class="block text-xs font-medium text-foreground">Select Ward</label>
                        <select v-model="form.ward_id" required class="mt-1 w-full rounded-xl border border-input bg-background px-3 py-2 text-sm">
                            <option value="">Select Ward</option>
                            <option v-for="ward in availableWards" :key="ward.id" :value="ward.id">{{ ward.name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-foreground">Featured Image <span class="text-muted-foreground">(Optional)</span></label>
                        <input type="file" accept="image/*" @change="handleImageUpload" class="mt-1 block w-full text-sm text-muted-foreground file:mr-4 file:rounded-xl file:border-0 file:bg-amber-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-amber-700 hover:file:bg-amber-100 dark:file:bg-amber-900/30 dark:file:text-amber-400" />
                        <p v-if="form.errors.image" class="mt-1 text-xs text-rose-500">{{ form.errors.image }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-foreground">Content Details *</label>
                        <textarea v-model="form.content" required rows="4" class="mt-1 w-full rounded-xl border border-input bg-background p-3 text-sm" placeholder="Enter announcement body..."></textarea>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button" @click="showAddModal = false" class="rounded-xl px-4 py-2 text-sm text-muted-foreground hover:bg-muted">Cancel</button>
                        <button type="submit" :disabled="form.processing" class="rounded-xl bg-amber-600 px-6 py-2 text-sm font-semibold text-white hover:bg-amber-500 disabled:opacity-50">
                            {{ isEditing ? 'Update Notice' : 'Publish Notice' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
            <div class="w-full max-w-sm rounded-3xl bg-background p-6 shadow-2xl">
                <h2 class="text-lg font-bold text-foreground">Delete Announcement</h2>
                <p class="mt-2 text-sm text-muted-foreground">Are you sure you want to delete this announcement? This action cannot be undone.</p>
                <div class="mt-6 flex justify-end gap-3">
                    <button @click="showDeleteModal = false" class="rounded-xl px-4 py-2 text-sm font-semibold text-muted-foreground hover:bg-muted">Cancel</button>
                    <button @click="deleteAnnouncement" class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-500">Delete</button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
