<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { ArrowLeft, Upload, UserPlus } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Members Directory', href: '/members' },
    { title: 'Register Member', href: '/members/create' },
];

const props = defineProps<{
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

const form = useForm({
    first_name: '',
    last_name: '',
    gender: 'Male',
    dob: '',
    phone: '',
    email: '',
    occupation: '',
    photo: null as File | null,
    voter_card: null as File | null,
    state_id: '',
    lga_id: '',
    ward_id: '',
    polling_unit_id: '',
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

const availablePollingUnits = ref<Array<{ id: number; code: string | null; name: string }>>([]);
const loadingPollingUnits = ref(false);

watch(() => form.state_id, () => {
    form.lga_id = '';
    form.ward_id = '';
    form.polling_unit_id = '';
    availablePollingUnits.value = [];
});

watch(() => form.lga_id, () => {
    form.ward_id = '';
    form.polling_unit_id = '';
    availablePollingUnits.value = [];
});

watch(() => form.ward_id, async (wardId) => {
    form.polling_unit_id = '';
    availablePollingUnits.value = [];
    if (!wardId) { return; }
    loadingPollingUnits.value = true;
    try {
        const res = await fetch(`/api/wards/${wardId}/polling-units`);
        if (res.ok) {
            availablePollingUnits.value = await res.json();
        } else {
            availablePollingUnits.value = [];
        }
    } catch {
        availablePollingUnits.value = [];
    } finally {
        loadingPollingUnits.value = false;
    }
});

const handleFileUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        form.photo = target.files[0];
    }
};

const handleVoterCardUpload = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        form.voter_card = target.files[0];
    }
};

const submit = () => {
    form.post('/members');
};
</script>

<template>
    <Head title="Register New Member" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-4xl flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between border-b border-border pb-6">
                <div>
                    <h1 class="flex items-center gap-2 text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                        <UserPlus class="size-8 text-emerald-600 dark:text-emerald-400" /> Member Registration
                    </h1>
                    <p class="mt-1 text-sm text-muted-foreground">Enter member details below. Membership number will be automatically generated.</p>
                </div>
                <Link href="/members" class="inline-flex items-center gap-1 text-sm font-medium text-muted-foreground hover:text-foreground">
                    <ArrowLeft class="size-4" /> Back to Directory
                </Link>
            </div>

            <form @submit.prevent="submit" class="space-y-8 rounded-2xl border border-border bg-card p-6 shadow-sm dark:bg-sidebar sm:p-8">
                <!-- Personal Information Section -->
                <div>
                    <h3 class="text-lg font-semibold text-foreground">Personal Information</h3>
                    <p class="text-xs text-muted-foreground">Basic identification details.</p>
                    <div class="mt-6 grid gap-6 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-foreground">First Name <span class="text-rose-500">*</span></label>
                            <input
                                v-model="form.first_name"
                                type="text"
                                required
                                class="mt-1 w-full rounded-xl border border-input bg-background px-3.5 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                placeholder="Enter first name"
                            />
                            <p v-if="form.errors.first_name" class="mt-1 text-xs text-rose-500">{{ form.errors.first_name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-foreground">Last Name <span class="text-rose-500">*</span></label>
                            <input
                                v-model="form.last_name"
                                type="text"
                                required
                                class="mt-1 w-full rounded-xl border border-input bg-background px-3.5 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                placeholder="Enter last name"
                            />
                            <p v-if="form.errors.last_name" class="mt-1 text-xs text-rose-500">{{ form.errors.last_name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-foreground">Gender <span class="text-rose-500">*</span></label>
                            <select
                                v-model="form.gender"
                                required
                                class="mt-1 w-full rounded-xl border border-input bg-background px-3.5 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                            >
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                            <p v-if="form.errors.gender" class="mt-1 text-xs text-rose-500">{{ form.errors.gender }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-foreground">Date of Birth <span class="text-rose-500">*</span></label>
                            <input
                                v-model="form.dob"
                                type="date"
                                required
                                class="mt-1 w-full rounded-xl border border-input bg-background px-3.5 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                            />
                            <p v-if="form.errors.dob" class="mt-1 text-xs text-rose-500">{{ form.errors.dob }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-foreground">Phone Number <span class="text-rose-500">*</span></label>
                            <input
                                v-model="form.phone"
                                type="tel"
                                required
                                class="mt-1 w-full rounded-xl border border-input bg-background px-3.5 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                placeholder="08012345678"
                            />
                            <p v-if="form.errors.phone" class="mt-1 text-xs text-rose-500">{{ form.errors.phone }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-foreground">Email Address (Optional)</label>
                            <input
                                v-model="form.email"
                                type="email"
                                class="mt-1 w-full rounded-xl border border-input bg-background px-3.5 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                placeholder="member@example.com"
                            />
                            <p v-if="form.errors.email" class="mt-1 text-xs text-rose-500">{{ form.errors.email }}</p>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-foreground">Occupation (Optional)</label>
                            <input
                                v-model="form.occupation"
                                type="text"
                                class="mt-1 w-full rounded-xl border border-input bg-background px-3.5 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                placeholder="Civil Servant, Merchant, Farmer, etc."
                            />
                            <p v-if="form.errors.occupation" class="mt-1 text-xs text-rose-500">{{ form.errors.occupation }}</p>
                        </div>
                    </div>
                </div>

                <!-- Geographical Hierarchy Section -->
                <div class="border-t border-border pt-8">
                    <h3 class="text-lg font-semibold text-foreground">Geographical Jurisdiction</h3>
                    <p class="text-xs text-muted-foreground">Select the movement location assignment for this member.</p>
                    <div class="mt-6 grid gap-6 sm:grid-cols-3">
                        <div>
                            <label class="block text-sm font-medium text-foreground">State <span class="text-rose-500">*</span></label>
                            <select
                                v-model="form.state_id"
                                required
                                class="mt-1 w-full rounded-xl border border-input bg-background px-3.5 py-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                            >
                                <option value="" disabled>Select State</option>
                                <option v-for="state in states" :key="state.id" :value="state.id">{{ state.name }}</option>
                            </select>
                            <p v-if="form.errors.state_id" class="mt-1 text-xs text-rose-500">{{ form.errors.state_id }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-foreground">Local Government (LGA) <span class="text-rose-500">*</span></label>
                            <select
                                v-model="form.lga_id"
                                required
                                :disabled="!form.state_id"
                                class="mt-1 w-full rounded-xl border border-input bg-background px-3.5 py-2.5 text-sm disabled:opacity-50 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                            >
                                <option value="" disabled>Select LGA</option>
                                <option v-for="lga in availableLgas" :key="lga.id" :value="lga.id">{{ lga.name }}</option>
                            </select>
                            <p v-if="form.errors.lga_id" class="mt-1 text-xs text-rose-500">{{ form.errors.lga_id }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-foreground">Registration Ward <span class="text-rose-500">*</span></label>
                            <select
                                v-model="form.ward_id"
                                required
                                :disabled="!form.lga_id"
                                class="mt-1 w-full rounded-xl border border-input bg-background px-3.5 py-2.5 text-sm disabled:opacity-50 focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                            >
                                <option value="" disabled>Select Ward</option>
                                <option v-for="ward in availableWards" :key="ward.id" :value="ward.id">{{ ward.name }}</option>
                            </select>
                            <p v-if="form.errors.ward_id" class="mt-1 text-xs text-rose-500">{{ form.errors.ward_id }}</p>
                        </div>
                    </div>

                    <!-- Polling Unit (loaded dynamically from INEC data) -->
                    <div class="mt-6">
                        <label class="block text-sm font-medium text-foreground">
                            Polling Unit
                            <span class="ml-1 text-xs font-normal text-muted-foreground">(Optional — INEC data)</span>
                        </label>
                        <div class="relative">
                            <select
                                v-model="form.polling_unit_id"
                                :disabled="!form.ward_id || loadingPollingUnits"
                                class="mt-1 w-full rounded-xl border border-input bg-background px-3.5 py-2.5 text-sm disabled:cursor-not-allowed disabled:opacity-50 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500"
                            >
                                <option value="">
                                    {{ loadingPollingUnits ? 'Loading polling units...' : !form.ward_id ? 'Select Ward first' : availablePollingUnits.length === 0 ? 'No polling units found' : 'Select Polling Unit' }}
                                </option>
                                <option v-for="pu in availablePollingUnits" :key="pu.id" :value="pu.id">
                                    {{ pu.code ? `${pu.code} - ` : '' }}{{ pu.name }}
                                </option>
                            </select>
                            <div v-if="loadingPollingUnits" class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                                <div class="size-4 animate-spin rounded-full border-2 border-amber-500 border-t-transparent"></div>
                            </div>
                        </div>
                        <p class="mt-1 text-xs text-muted-foreground">Polling units are sourced live from INEC's voter registration data.</p>
                        <p v-if="form.errors.polling_unit_id" class="mt-1 text-xs text-rose-500">{{ form.errors.polling_unit_id }}</p>
                    </div>
                </div>

                <!-- Photograph Section -->
                <div class="border-t border-border pt-8">
                    <h3 class="text-lg font-semibold text-foreground">Passport Photograph</h3>
                    <p class="text-xs text-muted-foreground">Upload a clear passport portrait photograph (Max 2MB).</p>
                    <div class="mt-4 flex items-center gap-4">
                        <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-dashed border-border px-4 py-3 text-sm font-medium text-muted-foreground hover:bg-muted">
                            <Upload class="size-4" /> Choose Photograph File
                            <input type="file" accept="image/*" @change="handleFileUpload" class="hidden" />
                        </label>
                        <span v-if="form.photo" class="text-sm font-medium text-emerald-600 dark:text-emerald-400">{{ form.photo.name }}</span>
                    </div>
                    <p v-if="form.errors.photo" class="mt-1 text-xs text-rose-500">{{ form.errors.photo }}</p>
                </div>

                <!-- Voter Card Section -->
                <div class="border-t border-border pt-8">
                    <h3 class="text-lg font-semibold text-foreground flex items-center gap-1.5">
                        Voter Registration Card <span class="text-xs font-bold text-rose-500">* Required</span>
                    </h3>
                    <p class="text-xs text-muted-foreground">Upload a clear scan or photograph of permanent or temporary voter card (Required — Max 5MB).</p>
                    <div class="mt-4 flex items-center gap-4">
                        <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-dashed border-border px-4 py-3 text-sm font-medium text-muted-foreground hover:bg-muted">
                            <Upload class="size-4" /> Choose Voter Card File
                            <input type="file" accept="image/*" @change="handleVoterCardUpload" class="hidden" />
                        </label>
                        <span v-if="form.voter_card" class="text-sm font-medium text-emerald-600 dark:text-emerald-400">{{ form.voter_card.name }}</span>
                    </div>
                    <p v-if="form.errors.voter_card" class="mt-1 text-xs text-rose-500">{{ form.errors.voter_card }}</p>
                </div>

                <!-- Submit Section -->
                <div class="flex items-center justify-end gap-3 border-t border-border pt-6">
                    <Link href="/members" class="rounded-xl px-4 py-2.5 text-sm font-medium text-muted-foreground hover:bg-muted">Cancel</Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-500 disabled:opacity-50 dark:bg-emerald-500 dark:hover:bg-emerald-400"
                    >
                        {{ form.processing ? 'Registering...' : 'Submit Member Registration' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
