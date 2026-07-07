<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { Plus, Search, ShieldCheck, Trash2, Building2, MapPin, X } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'EXCO Leadership', href: '/esco' },
];

const props = defineProps<{
    officials: {
        data: Array<{
            id: number;
            full_name: string;
            position: { name: string };
            phone: string;
            email?: string;
            photo_path?: string | null;
            appointed_at: string;
            status: string;
            state?: { name: string };
            lga?: { name: string };
            ward?: { name: string };
            polling_unit?: { code?: string | null; name: string };
        }>;
        links: Array<{ url: string | null; label: string; active: boolean }>;
        total: number;
    };
    positions: Array<{ id: number; name: string }>;
    filters: {
        search?: string;
        state_id?: number | string;
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

const page = usePage<any>();
const user = computed(() => page.props.auth.user);
const userRole = computed(() => user.value?.roles?.[0] || '');

const searchInput = ref(props.filters.search || '');
const stateFilter = ref(props.filters.state_id || '');
const showAddModal = ref(false);
const showDeleteModal = ref(false);
const itemToDelete = ref<number | null>(null);

const applyFilters = () => {
    router.get('/esco', { search: searchInput.value, state_id: stateFilter.value }, { preserveState: true, replace: true });
};

watch([searchInput, stateFilter], () => {
    applyFilters();
});

const form = useForm({
    full_name: '',
    position_id: '',
    phone: '',
    email: '',
    photo: null as string | null,
    state_id: '',
    lga_id: '',
    ward_id: '',
    polling_unit_id: '',
    appointed_at: new Date().toISOString().split('T')[0],
    status: 'active',
});

const availablePollingUnits = ref<Array<{ id: number; code: string | null; name: string }>>([]);
const loadingPollingUnits = ref(false);

watch(() => form.state_id, () => { form.lga_id = ''; form.ward_id = ''; form.polling_unit_id = ''; availablePollingUnits.value = []; });
watch(() => form.lga_id, () => { form.ward_id = ''; form.polling_unit_id = ''; availablePollingUnits.value = []; });
watch(() => form.ward_id, async (wardId) => {
    form.polling_unit_id = '';
    availablePollingUnits.value = [];
    if (!wardId) return;
    loadingPollingUnits.value = true;
    try {
        const res = await fetch(`/api/wards/${wardId}/polling-units`);
        if (res.ok) {
            availablePollingUnits.value = await res.json();
        }
    } catch {
        availablePollingUnits.value = [];
    } finally {
        loadingPollingUnits.value = false;
    }
});

const openAddModal = () => {
    form.reset();
    availablePollingUnits.value = [];
    if (userRole.value === 'State Coordinator') {
        form.state_id = user.value.state_id?.toString() || '';
    } else if (userRole.value === 'LGA Coordinator') {
        form.state_id = user.value.state_id?.toString() || '';
        form.lga_id = user.value.lga_id?.toString() || '';
    } else if (userRole.value === 'Ward Coordinator') {
        form.state_id = user.value.state_id?.toString() || '';
        form.lga_id = user.value.lga_id?.toString() || '';
        form.ward_id = user.value.ward_id?.toString() || '';
    }
    showAddModal.value = true;
};

const imagePreview = ref<string | null>(null);

const confirmDelete = (id: number) => {
    itemToDelete.value = id;
    showDeleteModal.value = true;
};

const deleteOfficial = () => {
    if (itemToDelete.value) {
        router.delete(`/esco/${itemToDelete.value}`, {
            onSuccess: () => {
                showDeleteModal.value = false;
                itemToDelete.value = null;
            }
        });
    }
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
                form.photo = canvas.toDataURL('image/jpeg', 0.85);
            }
        };
        if (event.target?.result) {
            img.src = event.target.result as string;
        }
    };
    reader.readAsDataURL(file);
};

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

const submitOfficial = () => {
    form.post('/esco', {
        onSuccess: () => {
            showAddModal.value = false;
            form.reset();
        },
    });
};

</script>

<template>
    <Head title="EXCO Leadership" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex flex-col justify-between gap-4 border-b border-border pb-6 sm:flex-row sm:items-center">
                <div>
                    <h1 class="flex items-center gap-2 text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                        <ShieldCheck class="size-8 text-indigo-600 dark:text-indigo-400" /> EXCO Leadership
                    </h1>
                    <p class="mt-1 text-sm text-muted-foreground">Maintain movement officials, coordinators, and leadership structure.</p>
                </div>
                <div>
                    <button
                        @click="openAddModal"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 dark:bg-indigo-500 dark:hover:bg-indigo-400"
                    >
                        <Plus class="size-4" /> Appoint New Official
                    </button>
                </div>
            </div>

            <!-- Filter Bar -->
            <div class="flex flex-col gap-4 sm:flex-row">
                <div class="relative flex-1">
                    <Search class="absolute left-3 top-3 size-4 text-muted-foreground" />
                    <input
                        v-model="searchInput"
                        type="text"
                        placeholder="Search name, position, phone..."
                        class="w-full rounded-xl border border-input bg-background py-2 pl-9 pr-3 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    />
                </div>
                <div class="w-full sm:w-64">
                    <select
                        v-model="stateFilter"
                        class="w-full rounded-xl border border-input bg-background px-3 py-2 text-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                    >
                        <option value="">All States</option>
                        <option v-for="state in states" :key="state.id" :value="state.id">{{ state.name }}</option>
                    </select>
                </div>
            </div>

            <!-- Officials Grid -->
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <div v-if="officials.data.length === 0" class="col-span-full rounded-2xl border border-border p-12 text-center text-muted-foreground">
                    No EXCO officials found matching your criteria.
                </div>
                <div
                    v-for="official in officials.data"
                    :key="official.id"
                    class="group relative flex flex-col justify-between overflow-hidden rounded-2xl border border-border bg-card p-6 shadow-sm transition hover:shadow-md dark:bg-sidebar"
                >
                    <div>
                        <div class="flex items-start justify-between">
                            <span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-semibold text-indigo-700 ring-1 ring-inset ring-indigo-700/10 dark:bg-indigo-900/30 dark:text-indigo-300">
                                {{ official.position?.name || 'Official' }}
                            </span>
                            <button @click="confirmDelete(official.id)" class="text-muted-foreground transition hover:text-rose-500" title="Remove official">
                                <Trash2 class="size-4" />
                            </button>
                        </div>
                        <div class="mt-4 flex items-center gap-3">
                            <div class="flex size-10 shrink-0 items-center justify-center overflow-hidden rounded-full bg-indigo-100 text-sm font-bold text-indigo-600 dark:bg-indigo-900/50 dark:text-indigo-300">
                                <img v-if="official.photo_path" :src="`/storage/${official.photo_path}`" class="h-full w-full object-cover" />
                                <span v-else>{{ official.full_name.charAt(0) }}</span>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-foreground">{{ official.full_name }}</h3>
                                <p class="text-xs text-muted-foreground">{{ official.email || 'No email provided' }}</p>
                            </div>
                        </div>
                        <p class="mt-2 font-mono text-xs text-muted-foreground">{{ official.phone }}</p>
                    </div>

                    <!-- Card Body -->
                    <div class="mt-5 space-y-3 px-1 text-sm text-slate-600 dark:text-slate-400">
                        <div class="flex items-center gap-3">
                            <Building2 class="size-4 shrink-0 text-slate-400" />
                            <span class="truncate">State: <strong class="text-slate-900 dark:text-white">{{ official.state?.name || 'National Level' }}</strong></span>
                        </div>
                        <div v-if="official.lga" class="flex items-center gap-3">
                            <MapPin class="size-4 shrink-0 text-slate-400" />
                            <span class="truncate">LGA: <strong class="text-slate-900 dark:text-white">{{ official.lga.name }}</strong></span>
                        </div>
                        <div v-if="official.ward" class="flex items-center gap-3">
                            <MapPin class="size-4 shrink-0 text-slate-400" />
                            <span class="truncate">Ward: <strong class="text-slate-900 dark:text-white">{{ official.ward.name }}</strong></span>
                        </div>
                        <div v-if="official.polling_unit" class="flex items-center gap-3">
                            <MapPin class="size-4 shrink-0 text-slate-400" />
                            <span class="truncate">PU: <strong class="text-slate-900 dark:text-white">{{ official.polling_unit.code ? `${official.polling_unit.code} - ` : '' }}{{ official.polling_unit.name }}</strong></span>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="mt-8">
                    <div class="flex flex-wrap items-center justify-center gap-2">
                        <Link
                            v-for="(link, i) in officials.links"
                            :key="i"
                            :href="link.url || ''"
                            class="rounded-xl px-4 py-2 text-sm font-semibold transition"
                            :class="{
                                'bg-amber-600 text-white shadow-md': link.active,
                                'bg-card text-muted-foreground hover:bg-muted': !link.active && link.url,
                                'opacity-50 cursor-not-allowed text-muted-foreground': !link.url
                            }"
                            v-html="link.label"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Modal -->
        <div v-if="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
            <div class="w-full max-w-2xl rounded-3xl bg-background p-6 shadow-2xl sm:p-8">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-bold text-foreground">Appoint EXCO Official</h2>
                    <button @click="showAddModal = false" class="rounded-full p-2 text-muted-foreground hover:bg-muted"><X class="size-5" /></button>
                </div>

                <form @submit.prevent="submitOfficial" class="mt-6 space-y-5">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label class="block text-xs font-medium text-foreground">Full Name *</label>
                            <input v-model="form.full_name" required type="text" class="mt-1 w-full rounded-xl border border-input bg-background px-3 py-2 text-sm" placeholder="Full legal name" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-foreground">Position *</label>
                            <select v-model="form.position_id" required class="mt-1 w-full rounded-xl border border-input bg-background px-3 py-2 text-sm">
                                <option value="">Select Position</option>
                                <option v-for="pos in positions" :key="pos.id" :value="pos.id">{{ pos.name }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-foreground">Phone Number *</label>
                            <input v-model="form.phone" required type="tel" class="mt-1 w-full rounded-xl border border-input bg-background px-3 py-2 text-sm" placeholder="+234..." />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-foreground">Email Address</label>
                            <input v-model="form.email" type="email" class="mt-1 w-full rounded-xl border border-input bg-background px-3 py-2 text-sm" placeholder="optional@email.com" />
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-foreground">Passport Photo <span class="text-muted-foreground">(Max 100KB, Optional)</span></label>
                            <input type="file" accept="image/*" @change="handlePhotoUpload" class="mt-1 w-full text-sm text-muted-foreground file:mr-4 file:rounded-xl file:border-0 file:bg-amber-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-amber-700 hover:file:bg-amber-200 dark:file:bg-amber-900/30 dark:file:text-amber-400" />
                            <p v-if="form.errors.photo" class="mt-1 text-xs text-rose-500">{{ form.errors.photo }}</p>
                        </div>
                    </div>

                    <div class="border-t border-border pt-5">
                        <h3 class="mb-3 text-sm font-semibold text-foreground">Jurisdiction / Level</h3>
                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            <div>
                                <label class="block text-xs font-medium text-foreground">State</label>
                                <select v-model="form.state_id" :disabled="userRole === 'State Coordinator' || userRole === 'LGA Coordinator' || userRole === 'Ward Coordinator'" class="mt-1 w-full rounded-xl border border-input bg-background px-3 py-2 text-sm disabled:opacity-50">
                                    <option v-if="!userRole.includes('Coordinator')" value="">National Level</option>
                                    <option v-for="state in states" :key="state.id" :value="state.id">{{ state.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-foreground">LGA</label>
                                <select v-model="form.lga_id" :disabled="!form.state_id || userRole === 'LGA Coordinator' || userRole === 'Ward Coordinator'" class="mt-1 w-full rounded-xl border border-input bg-background px-3 py-2 text-sm disabled:opacity-50">
                                    <option v-if="userRole !== 'LGA Coordinator' && userRole !== 'Ward Coordinator'" value="">All LGAs</option>
                                    <option v-for="lga in availableLgas" :key="lga.id" :value="lga.id">{{ lga.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-foreground">Ward</label>
                                <select v-model="form.ward_id" :disabled="!form.lga_id || userRole === 'Ward Coordinator'" class="mt-1 w-full rounded-xl border border-input bg-background px-3 py-2 text-sm disabled:opacity-50">
                                    <option v-if="userRole !== 'Ward Coordinator'" value="">All Wards</option>
                                    <option v-for="ward in availableWards" :key="ward.id" :value="ward.id">{{ ward.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-foreground">Polling Unit</label>
                                <select v-model="form.polling_unit_id" :disabled="!form.ward_id || loadingPollingUnits" class="mt-1 w-full rounded-xl border border-input bg-background px-3 py-2 text-sm disabled:opacity-50">
                                    <option value="">{{ loadingPollingUnits ? 'Loading polling units...' : !form.ward_id ? 'Select Ward first' : 'All Polling Units' }}</option>
                                    <option v-for="pu in availablePollingUnits" :key="pu.id" :value="pu.id">{{ pu.code ? `${pu.code} - ` : '' }}{{ pu.name }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-5 border-t border-border pt-5 sm:grid-cols-2">
                        <div>
                            <label class="block text-xs font-medium text-foreground">Date of Appointment *</label>
                            <input v-model="form.appointed_at" required type="date" class="mt-1 w-full rounded-xl border border-input bg-background px-3 py-2 text-sm" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-foreground">Status</label>
                            <select v-model="form.status" class="mt-1 w-full rounded-xl border border-input bg-background px-3 py-2 text-sm">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-border">
                        <button type="button" @click="showAddModal = false" class="rounded-xl px-5 py-2.5 text-sm font-semibold text-muted-foreground hover:bg-muted">Cancel</button>
                        <button type="submit" :disabled="form.processing" class="rounded-xl bg-amber-600 px-6 py-2.5 text-sm font-bold text-white hover:bg-amber-500 disabled:opacity-50">
                            {{ form.processing ? 'Saving...' : 'Save Appointment' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
            <div class="w-full max-w-sm rounded-3xl bg-background p-6 shadow-2xl">
                <h2 class="text-lg font-bold text-foreground">Remove Official</h2>
                <p class="mt-2 text-sm text-muted-foreground">Are you sure you want to remove this EXCO official? This action cannot be undone.</p>
                <div class="mt-6 flex justify-end gap-3">
                    <button @click="showDeleteModal = false" class="rounded-xl px-4 py-2 text-sm font-semibold text-muted-foreground hover:bg-muted">Cancel</button>
                    <button @click="deleteOfficial" class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-500">Remove</button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
