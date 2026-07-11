<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { Award, Camera, Check, Crown, Edit, Plus, Search, Trash2, X } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Organization Patrons', href: '/patrons' },
];

const props = defineProps<{
    patrons: {
        data: Array<{
            id: number;
            name: string;
            title: string;
            category: 'Grand Patron' | 'Patron' | 'Royal Father' | 'Special Adviser';
            photo_path?: string;
            order_index: number;
            is_active: boolean;
            created_at: string;
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
        from: number;
        to: number;
        total: number;
    };
    filters: {
        search?: string;
        category?: string;
    };
}>();

const page = usePage();
const user = computed(() => (page.props.auth as any)?.user);
const userRoles = computed(() => user.value?.roles || []);
const canManage = computed(() => {
    return userRoles.value.includes('Super Administrator') || userRoles.value.includes('National Administrator') || userRoles.value.includes('State Coordinator');
});

const searchInput = ref(props.filters.search || '');
const categoryFilter = ref(props.filters.category || '');

const applyFilters = () => {
    router.get('/patrons', { search: searchInput.value, category: categoryFilter.value }, { preserveState: true, replace: true });
};

watch([searchInput, categoryFilter], () => {
    applyFilters();
});

const showAddModal = ref(false);
const editPatron = ref<any | null>(null);
const showDeleteModal = ref(false);
const itemToDelete = ref<number | null>(null);

const form = useForm({
    name: '',
    title: '',
    category: 'Patron' as 'Grand Patron' | 'Patron' | 'Royal Father' | 'Special Adviser',
    photo: null as string | null,
    order_index: 0,
    is_active: true,
});

const imagePreview = ref<string | null>(null);

const openAddModal = () => {
    editPatron.value = null;
    form.reset();
    imagePreview.value = null;
    showAddModal.value = true;
};

const openEditModal = (patron: any) => {
    editPatron.value = patron;
    form.name = patron.name;
    form.title = patron.title;
    form.category = patron.category;
    form.photo = null;
    form.order_index = patron.order_index;
    form.is_active = patron.is_active;
    imagePreview.value = patron.photo_path ? `/storage/${patron.photo_path}` : null;
    showAddModal.value = true;
};

const handlePhotoUpload = (e: Event) => {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = (event) => {
        const img = new Image();
        img.onload = () => {
            const canvas = document.createElement('canvas');
            const size = Math.min(img.width, img.height);
            canvas.width = 600; // Retina Square 600x600
            canvas.height = 600;
            const ctx = canvas.getContext('2d');
            if (ctx) {
                const startX = (img.width - size) / 2;
                const startY = (img.height - size) / 2;
                ctx.drawImage(img, startX, startY, size, size, 0, 0, 600, 600);
                const base64 = canvas.toDataURL('image/jpeg', 0.85);
                form.photo = base64;
                imagePreview.value = base64;
            }
        };
        if (event.target?.result) {
            img.src = event.target.result as string;
        }
    };
    reader.readAsDataURL(file);
};

const submitForm = () => {
    if (editPatron.value) {
        form.put(`/patrons/${editPatron.value.id}`, {
            onSuccess: () => {
                showAddModal.value = false;
                editPatron.value = null;
            },
        });
    } else {
        form.post('/patrons', {
            onSuccess: () => {
                showAddModal.value = false;
            },
        });
    }
};

const confirmDelete = (id: number) => {
    itemToDelete.value = id;
    showDeleteModal.value = true;
};

const deletePatron = () => {
    if (itemToDelete.value) {
        router.delete(`/patrons/${itemToDelete.value}`, {
            onSuccess: () => {
                showDeleteModal.value = false;
                itemToDelete.value = null;
            },
        });
    }
};
</script>

<template>
    <Head title="Organization Patrons" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex flex-col justify-between gap-4 border-b border-border pb-6 sm:flex-row sm:items-center">
                <div>
                    <h1 class="flex items-center gap-2.5 text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                        <Crown class="size-8 text-amber-500 dark:text-amber-400" /> Organization Patrons & Royal Leadership
                    </h1>
                    <p class="mt-1 text-sm text-muted-foreground">Appoint and manage Grand Patrons, Patrons, and Royal Fathers.</p>
                </div>
                <div v-if="canManage">
                    <button
                        @click="openAddModal"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-600 dark:bg-amber-500 dark:hover:bg-amber-400"
                    >
                        <Plus class="size-4" /> Appoint New Patron
                    </button>
                </div>
            </div>

            <!-- Filters & Search -->
            <div class="flex flex-col gap-4 rounded-2xl border border-border bg-card p-4 shadow-sm dark:bg-sidebar sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-wrap gap-2">
                    <button
                        @click="categoryFilter = ''"
                        class="rounded-xl px-3.5 py-1.5 text-xs font-semibold transition"
                        :class="categoryFilter === '' ? 'bg-amber-600 text-white shadow-sm' : 'border border-border bg-background text-muted-foreground hover:bg-muted'"
                    >
                        All Categories
                    </button>
                    <button
                        @click="categoryFilter = 'Grand Patron'"
                        class="rounded-xl px-3.5 py-1.5 text-xs font-semibold transition"
                        :class="categoryFilter === 'Grand Patron' ? 'bg-amber-600 text-white shadow-sm' : 'border border-border bg-background text-muted-foreground hover:bg-muted'"
                    >
                        Grand Patrons
                    </button>
                    <button
                        @click="categoryFilter = 'Patron'"
                        class="rounded-xl px-3.5 py-1.5 text-xs font-semibold transition"
                        :class="categoryFilter === 'Patron' ? 'bg-amber-600 text-white shadow-sm' : 'border border-border bg-background text-muted-foreground hover:bg-muted'"
                    >
                        Patrons
                    </button>
                    <button
                        @click="categoryFilter = 'Royal Father'"
                        class="rounded-xl px-3.5 py-1.5 text-xs font-semibold transition"
                        :class="categoryFilter === 'Royal Father' ? 'bg-amber-600 text-white shadow-sm' : 'border border-border bg-background text-muted-foreground hover:bg-muted'"
                    >
                        Royal Fathers
                    </button>
                    <button
                        @click="categoryFilter = 'Special Adviser'"
                        class="rounded-xl px-3.5 py-1.5 text-xs font-semibold transition"
                        :class="categoryFilter === 'Special Adviser' ? 'bg-amber-600 text-white shadow-sm' : 'border border-border bg-background text-muted-foreground hover:bg-muted'"
                    >
                        Special Advisers
                    </button>
                </div>

                <div class="relative w-full sm:w-72">
                    <Search class="absolute left-3 top-2.5 size-4 text-muted-foreground" />
                    <input
                        v-model="searchInput"
                        type="text"
                        placeholder="Search patron or title..."
                        class="w-full rounded-xl border border-input bg-background py-2 pl-9 pr-3 text-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500"
                    />
                </div>
            </div>

            <!-- Patrons Grid -->
            <div v-if="patrons.data.length === 0" class="rounded-2xl border border-border bg-card p-12 text-center text-muted-foreground shadow-sm dark:bg-sidebar">
                No patrons found in this category.
            </div>

            <div v-else class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <div
                    v-for="patron in patrons.data"
                    :key="patron.id"
                    class="group relative flex flex-col items-center overflow-hidden rounded-3xl border border-border bg-card p-6 text-center shadow-sm transition hover:shadow-md dark:bg-sidebar"
                >
                    <div class="absolute right-3 top-3 flex items-center gap-1 opacity-0 transition group-hover:opacity-100" v-if="canManage">
                        <button @click="openEditModal(patron)" class="rounded-lg border border-border bg-background p-1.5 text-muted-foreground hover:text-foreground">
                            <Edit class="size-4" />
                        </button>
                        <button @click="confirmDelete(patron.id)" class="rounded-lg border border-border bg-background p-1.5 text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-900/20">
                            <Trash2 class="size-4" />
                        </button>
                    </div>

                    <!-- Photo -->
                    <div class="relative mb-4 size-28 overflow-hidden rounded-full border-4 border-amber-500/20 shadow-md">
                        <img
                            v-if="patron.photo_path"
                            :src="`/storage/${patron.photo_path}`"
                            :alt="patron.name"
                            class="size-full object-cover"
                        />
                        <div v-else class="flex size-full items-center justify-center bg-muted text-2xl font-bold text-muted-foreground">
                            {{ patron.name[0] }}
                        </div>
                    </div>

                    <!-- Category Badge -->
                    <span
                        class="mb-2 inline-flex items-center gap-1 rounded-full px-3 py-0.5 text-[11px] font-bold uppercase tracking-wider"
                        :class="{
                            'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300': patron.category === 'Grand Patron',
                            'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300': patron.category === 'Patron',
                            'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300': patron.category === 'Royal Father',
                            'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300': patron.category === 'Special Adviser',
                        }"
                    >
                        <Crown v-if="patron.category === 'Grand Patron'" class="size-3" />
                        <Award v-else class="size-3" />
                        {{ patron.category }}
                    </span>

                    <h3 class="mt-1 text-lg font-bold text-foreground">{{ patron.name }}</h3>
                    <p class="mt-1 text-xs font-medium text-muted-foreground">{{ patron.title }}</p>

                    <!-- Card Actions Footer -->
                    <div class="mt-5 w-full border-t border-border pt-4">
                        <a
                            :href="`/patrons/badge/${patron.id}`"
                            target="_blank"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-amber-200 bg-amber-50/50 px-3 py-2 text-xs font-semibold text-amber-800 transition hover:bg-amber-600 hover:text-white dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-300 dark:hover:bg-amber-600 dark:hover:text-white"
                        >
                            <Award class="size-4" /> View ID Badge
                        </a>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="patrons.total > 15" class="flex items-center justify-between border-t border-border pt-4">
                <span class="text-xs text-muted-foreground"> Showing {{ patrons.from }} to {{ patrons.to }} of {{ patrons.total }} patrons </span>
                <div class="flex gap-1">
                    <Link
                        v-for="(link, index) in patrons.links"
                        :key="index"
                        :href="link.url || '#'"
                        v-html="link.label"
                        class="rounded-lg px-3 py-1 text-xs font-medium transition"
                        :class="{
                            'bg-amber-600 text-white': link.active,
                            'text-muted-foreground hover:bg-muted': !link.active && link.url,
                            'pointer-events-none opacity-40': !link.url,
                        }"
                    />
                </div>
            </div>
        </div>

        <!-- Add / Edit Modal -->
        <Teleport to="body">
            <div
                v-if="showAddModal"
                class="fixed inset-0 z-50 flex min-h-screen items-center justify-center overflow-y-auto bg-black/60 p-3 sm:p-6 backdrop-blur-sm"
                @click.self="showAddModal = false"
            >
                <div class="my-auto w-full max-w-lg max-h-[90vh] overflow-y-auto rounded-2xl sm:rounded-3xl border border-border bg-card p-4 sm:p-6 shadow-2xl dark:bg-sidebar">
                    <div class="mb-5 flex items-center justify-between border-b border-border pb-4">
                        <h2 class="flex items-center gap-2 text-lg font-bold text-foreground">
                            <Crown class="size-5 text-amber-500" />
                            {{ editPatron ? 'Edit Patron details' : 'Appoint New Patron' }}
                        </h2>
                        <button @click="showAddModal = false" class="rounded-lg p-1.5 hover:bg-muted"><X class="size-5" /></button>
                    </div>

                    <form @submit.prevent="submitForm" class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-foreground">Category <span class="text-rose-500">*</span></label>
                            <select
                                v-model="form.category"
                                required
                                class="mt-1 w-full rounded-xl border border-input bg-background px-3 py-2 text-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500"
                            >
                                <option value="Grand Patron">Grand Patron</option>
                                <option value="Patron">Patron</option>
                                <option value="Royal Father">Royal Father</option>
                                <option value="Special Adviser">Special Adviser</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-foreground">Full Name <span class="text-rose-500">*</span></label>
                            <input
                                v-model="form.name"
                                type="text"
                                required
                                placeholder="e.g. Senator Abdul'aziz Abubakar Yari"
                                class="mt-1 w-full rounded-xl border border-input bg-background px-3 py-2 text-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-foreground">Official Title / Chieftaincy <span class="text-rose-500">*</span></label>
                            <input
                                v-model="form.title"
                                type="text"
                                required
                                placeholder="e.g. Shattiman Sokoto, Former Governor Zamfara State"
                                class="mt-1 w-full rounded-xl border border-input bg-background px-3 py-2 text-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500"
                            />
                        </div>

                        <!-- Photo Cropper Upload -->
                        <div>
                            <label class="block text-xs font-medium text-foreground">Official Portrait Photo (Square)</label>
                            <div class="mt-2 flex items-center gap-4">
                                <div class="relative flex size-20 shrink-0 items-center justify-center overflow-hidden rounded-full border-2 border-dashed border-border bg-muted">
                                    <img v-if="imagePreview" :src="imagePreview" class="size-full object-cover" />
                                    <Camera v-else class="size-6 text-muted-foreground" />
                                </div>
                                <div class="flex-1">
                                    <label class="inline-flex cursor-pointer items-center justify-center rounded-xl border border-border bg-background px-4 py-2 text-xs font-semibold shadow-sm transition hover:bg-muted">
                                        <span>Upload Photo</span>
                                        <input type="file" accept="image/*" @change="handlePhotoUpload" class="hidden" />
                                    </label>
                                    <p class="mt-1 text-[11px] text-muted-foreground">Auto-cropped to high-def 600x600 circle/square.</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 pt-2">
                            <label class="flex items-center gap-2 text-xs font-medium text-foreground">
                                <input type="checkbox" v-model="form.is_active" class="rounded border-input text-amber-600 focus:ring-amber-500" />
                                Active & Visible on Public Website
                            </label>
                        </div>

                        <div class="mt-6 flex justify-end gap-3 border-t border-border pt-4">
                            <button type="button" @click="showAddModal = false" class="rounded-xl border border-input px-4 py-2 text-sm font-medium hover:bg-muted">Cancel</button>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex items-center justify-center rounded-xl bg-amber-600 px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-500 disabled:opacity-50 dark:bg-amber-500 dark:hover:bg-amber-400"
                            >
                                {{ form.processing ? 'Saving...' : editPatron ? 'Update Patron' : 'Save Patron' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>

        <!-- Delete Confirmation Modal -->
        <Teleport to="body">
            <div
                v-if="showDeleteModal"
                class="fixed inset-0 z-50 flex min-h-screen items-center justify-center overflow-y-auto bg-black/60 p-3 sm:p-6 backdrop-blur-sm"
            >
                <div class="my-auto w-full max-w-sm max-h-[90vh] overflow-y-auto rounded-2xl sm:rounded-3xl border border-border bg-card p-5 sm:p-6 shadow-2xl dark:bg-sidebar">
                    <h2 class="text-lg font-bold text-foreground">Remove Patron?</h2>
                    <p class="mt-2 text-sm text-muted-foreground">Are you sure you want to remove this patron from the directory? This action cannot be undone.</p>
                    <div class="mt-6 flex justify-end gap-3">
                        <button @click="showDeleteModal = false" class="rounded-xl border border-input px-4 py-2 text-sm font-medium hover:bg-muted">Cancel</button>
                        <button @click="deletePatron" class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-500">Remove</button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AppLayout>
</template>
