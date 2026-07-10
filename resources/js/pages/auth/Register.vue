<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { Award, CheckCircle, ChevronRight, LoaderCircle, ShieldCheck, UserPlus, MapPin, Building2, Upload, X } from 'lucide-vue-next';

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
    announcements: Array<any>;
    escos: Array<any>;
}>();

const urlParams = new URLSearchParams(window.location.search);
const refParam = urlParams.get('ref') || '';

const form = useForm({
    first_name: '',
    last_name: '',
    gender: 'Male',
    dob: '',
    phone: '+234',
    email: '',
    occupation: '',
    state_id: '',
    lga_id: '',
    ward_id: '',
    polling_unit_id: '',
    photo: '',
    referral_code: refParam,
});

const availableLgas = computed(() => {
    if (!form.state_id) { return []; }
    const state = props.states.find((s) => s.id == Number(form.state_id));
    return state ? state.lgas : [];
});

const availableWards = computed(() => {
    if (!form.lga_id) { return []; }
    const lga = availableLgas.value.find((l) => l.id == Number(form.lga_id));
    return lga ? lga.wards : [];
});

const availablePollingUnits = ref<Array<{ id: number; code: string | null; name: string }>>([]);
const loadingPollingUnits = ref(false);

const handlePhotoUpload = (e: Event) => {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = (event) => {
        const img = new Image();
        img.onload = () => {
            const canvas = document.createElement('canvas');
            const size = Math.min(img.width, img.height);
            canvas.width = 300;
            canvas.height = 300;
            const ctx = canvas.getContext('2d');
            if (ctx) {
                // Crop to square from center
                const startX = (img.width - size) / 2;
                const startY = (img.height - size) / 2;
                ctx.drawImage(img, startX, startY, size, size, 0, 0, 300, 300);
                
                // Compress to stay roughly under 50KB, 0.7 quality usually does it for 300x300
                form.photo = canvas.toDataURL('image/jpeg', 0.7);
            }
        };
        if (event.target?.result) {
            img.src = event.target.result as string;
        }
    };
    reader.readAsDataURL(file);
};

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

const submit = () => {
    form.clearErrors();
    if (!form.photo) {
        form.setError('photo', 'Passport photograph is required. Please choose or capture your portrait photo before submitting.');
        document.getElementById('photo-section')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }
    form.post(route('register'));
};

const inputClass = 'mt-1 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white';
const selectClass = 'mt-1 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-700 dark:bg-slate-800 dark:text-white';
</script>

<template>
    <Head title="Join Shaihiyya — Member Registration" />

    <div class="min-h-screen bg-slate-50 selection:bg-amber-500 selection:text-white dark:bg-slate-950">
        <!-- Sticky Header -->
        <header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/80 backdrop-blur-md dark:border-slate-800 dark:bg-slate-950/80">
            <div class="mx-auto flex max-w-4xl items-center justify-between px-3 py-3 sm:px-6 sm:py-4">
                <Link :href="route('home')" class="flex items-center gap-2 sm:gap-2.5">
                    <div class="flex size-9 sm:size-12 shrink-0 items-center justify-center overflow-hidden rounded-full bg-white shadow-md shadow-amber-500/10">
                        <img src="/logo.png" alt="Shaihiyya Logo" class="h-full w-full object-cover p-1" />
                    </div>
                    <div>
                        <span class="block text-sm sm:text-base font-black tracking-tight text-slate-900 dark:text-white">SHAIHIYYA AMANAR JAGORA</span>
                        <span class="block text-[10px] sm:text-xs font-semibold uppercase tracking-widest text-amber-600 dark:text-amber-400">Support Organization</span>
                    </div>
                </Link>
            </div>
        </header>

        <!-- Hero Banner -->
        <div class="border-b border-slate-200/80 bg-gradient-to-r from-amber-600 to-emerald-600 py-10 text-center text-white dark:border-slate-800">
            <div class="inline-flex items-center gap-2 rounded-full border border-white/30 bg-white/10 px-4 py-1.5 text-xs font-bold uppercase tracking-wider backdrop-blur-md">
                <ShieldCheck class="size-4" /> Public Member Registration
            </div>
            <h1 class="mt-4 text-3xl font-black tracking-tight sm:text-4xl">Join the Shaihiyya Amanar Jagora</h1>
            <p class="mx-auto mt-3 max-w-xl text-sm leading-relaxed text-white/80 sm:text-base">
                Register as a supporter of Sen. Abdul'aziz Abubakar Yari's grassroots support organization.
                Your membership number will be generated automatically.
            </p>
        </div>

        <!-- Registration Form -->
        <div class="mx-auto max-w-3xl px-4 py-10 sm:px-6">
            <form @submit.prevent="submit" class="space-y-8 rounded-3xl border border-slate-200 bg-white p-6 shadow-lg dark:border-slate-800 dark:bg-slate-900 sm:p-8">

                <!-- Personal Information -->
                <div>
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">Personal Information</h2>
                    <p class="mt-0.5 text-xs text-slate-500">Basic identification details as on your national ID.</p>
                    <div class="mt-5 grid gap-5 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">First Name <span class="text-rose-500">*</span></label>
                            <input v-model="form.first_name" type="text" required :class="inputClass" placeholder="Enter first name" />
                            <p v-if="form.errors.first_name" class="mt-1 text-xs text-rose-500">{{ form.errors.first_name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Last Name <span class="text-rose-500">*</span></label>
                            <input v-model="form.last_name" type="text" required :class="inputClass" placeholder="Enter last name" />
                            <p v-if="form.errors.last_name" class="mt-1 text-xs text-rose-500">{{ form.errors.last_name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Gender <span class="text-rose-500">*</span></label>
                            <select v-model="form.gender" required :class="selectClass">
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                            <p v-if="form.errors.gender" class="mt-1 text-xs text-rose-500">{{ form.errors.gender }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Date of Birth <span class="text-rose-500">*</span></label>
                            <input v-model="form.dob" type="date" required :class="inputClass" />
                            <p v-if="form.errors.dob" class="mt-1 text-xs text-rose-500">{{ form.errors.dob }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Phone Number <span class="text-rose-500">*</span></label>
                            <input v-model="form.phone" type="tel" required pattern="^\+234[789][01]\d{8}$" title="Must be a valid Nigerian phone number starting with +234 followed by 10 digits" :class="inputClass" placeholder="+2348012345678" />
                            <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">We recommend using your active WhatsApp number if available.</p>
                            <p v-if="form.errors.phone" class="mt-1 text-xs text-rose-500">{{ form.errors.phone }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Email Address <span class="text-xs text-slate-400">(Optional)</span></label>
                            <input v-model="form.email" type="email" :class="inputClass" placeholder="member@example.com" />
                            <p v-if="form.errors.email" class="mt-1 text-xs text-rose-500">{{ form.errors.email }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Referral Code <span class="text-xs text-slate-400">(Optional)</span></label>
                            <input v-model="form.referral_code" type="text" maxlength="8" :class="inputClass" placeholder="e.g. 80123456" />
                            <p v-if="form.errors.referral_code" class="mt-1 text-xs text-rose-500">{{ form.errors.referral_code }}</p>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Occupation <span class="text-xs text-slate-400">(Optional)</span></label>
                            <input v-model="form.occupation" type="text" :class="inputClass" placeholder="Civil Servant, Farmer, Trader, etc." />
                            <p v-if="form.errors.occupation" class="mt-1 text-xs text-rose-500">{{ form.errors.occupation }}</p>
                        </div>
                    </div>
                </div>

                <!-- Geographical Jurisdiction -->
                <div class="border-t border-slate-200 pt-8 dark:border-slate-800">
                    <h2 class="text-base font-bold text-slate-900 dark:text-white">Geographical Location</h2>
                    <p class="mt-0.5 text-xs text-slate-500">Select your voting registration area.</p>
                    <div class="mt-5 grid gap-5 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">State <span class="text-rose-500">*</span></label>
                            <select v-model="form.state_id" required :class="selectClass">
                                <option value="" disabled>Select State</option>
                                <option v-for="state in states" :key="state.id" :value="state.id">{{ state.name }}</option>
                            </select>
                            <p v-if="form.errors.state_id" class="mt-1 text-xs text-rose-500">{{ form.errors.state_id }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Local Government (LGA) <span class="text-rose-500">*</span></label>
                            <select v-model="form.lga_id" required :disabled="!form.state_id" :class="selectClass">
                                <option value="" disabled>Select LGA</option>
                                <option v-for="lga in availableLgas" :key="lga.id" :value="lga.id">{{ lga.name }}</option>
                            </select>
                            <p v-if="form.errors.lga_id" class="mt-1 text-xs text-rose-500">{{ form.errors.lga_id }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">Registration Ward <span class="text-rose-500">*</span></label>
                            <select v-model="form.ward_id" required :disabled="!form.lga_id" :class="selectClass">
                                <option value="" disabled>Select Ward</option>
                                <option v-for="ward in availableWards" :key="ward.id" :value="ward.id">{{ ward.name }}</option>
                            </select>
                            <p v-if="form.errors.ward_id" class="mt-1 text-xs text-rose-500">{{ form.errors.ward_id }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                                Polling Unit
                                <span class="ml-1 text-[11px] font-normal text-slate-400">(Optional — INEC data)</span>
                            </label>
                            <div class="relative">
                                <select v-model="form.polling_unit_id" :disabled="!form.ward_id || loadingPollingUnits" :class="selectClass">
                                    <option value="">
                                        {{ loadingPollingUnits ? 'Loading...' : !form.ward_id ? 'Select Ward first' : availablePollingUnits.length === 0 ? 'No polling units found' : 'Select Polling Unit' }}
                                    </option>
                                    <option v-for="pu in availablePollingUnits" :key="pu.id" :value="pu.id">
                                        {{ pu.code ? `${pu.code} - ` : '' }}{{ pu.name }}
                                    </option>
                                </select>
                                <div v-if="loadingPollingUnits" class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                                    <div class="size-4 animate-spin rounded-full border-2 border-amber-500 border-t-transparent"></div>
                                </div>
                            </div>
                            <p v-if="form.errors.polling_unit_id" class="mt-1 text-xs text-rose-500">{{ form.errors.polling_unit_id }}</p>
                        </div>
                    </div>
                </div>

                <!-- Photograph Section -->
                <div id="photo-section" class="border-t border-slate-200 pt-8 dark:border-slate-800">
                    <h2 class="flex items-center gap-1.5 text-base font-bold text-slate-900 dark:text-white">
                        Passport Photograph <span class="text-xs font-bold text-rose-500">* Required</span>
                    </h2>
                    <p class="mt-0.5 text-xs text-slate-500">Upload a clear passport portrait photograph (Required — Max 5MB, JPG/PNG supported). A photo is required for ID badge generation.</p>

                    <div class="mt-5">
                        <div v-if="!form.photo" class="relative flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50/60 px-6 py-8 text-center transition hover:border-amber-500 hover:bg-amber-50/20 dark:border-slate-700 dark:bg-slate-800/40 dark:hover:border-amber-500/80">
                            <div class="flex size-14 items-center justify-center rounded-full bg-amber-50 text-amber-600 shadow-sm dark:bg-amber-950/50 dark:text-amber-400">
                                <Upload class="size-6" />
                            </div>
                            <h3 class="mt-4 text-sm font-semibold text-slate-900 dark:text-white">Click to choose photo or drag &amp; drop</h3>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Portrait or square photo (PNG, JPG up to 5MB). Will be automatically cropped &amp; optimized.</p>
                            <label class="mt-4 inline-flex cursor-pointer items-center gap-2 rounded-xl bg-gradient-to-r from-amber-600 to-emerald-600 px-5 py-2.5 text-xs font-bold text-white shadow-md shadow-amber-600/10 transition hover:from-amber-500 hover:to-emerald-500">
                                <Upload class="size-4" /> Browse Photo
                                <input type="file" accept="image/*" @change="handlePhotoUpload" class="hidden" />
                            </label>
                        </div>

                        <div v-else class="flex flex-col items-center justify-between gap-4 rounded-2xl border border-slate-200 bg-slate-50/80 p-5 dark:border-slate-800 dark:bg-slate-800/50 sm:flex-row">
                            <div class="flex items-center gap-4">
                                <div class="size-20 shrink-0 overflow-hidden rounded-2xl border-2 border-amber-500 bg-white shadow-md dark:bg-slate-900">
                                    <img :src="form.photo" alt="Profile Preview" class="h-full w-full object-cover" />
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-slate-900 dark:text-white">Photograph Ready</h4>
                                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">Cropped to high-quality square portrait.</p>
                                    <span class="mt-1.5 inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-[10px] font-bold text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300">
                                        <CheckCircle class="size-3" /> Optimized for ID Tag
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <label class="cursor-pointer rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">
                                    Change Photo
                                    <input type="file" accept="image/*" @change="handlePhotoUpload" class="hidden" />
                                </label>
                                <button
                                    type="button"
                                    @click="form.photo = ''"
                                    class="inline-flex items-center gap-1 rounded-xl border border-rose-200 bg-rose-50 px-3.5 py-2 text-xs font-semibold text-rose-700 transition hover:bg-rose-100 dark:border-rose-900/50 dark:bg-rose-950/40 dark:text-rose-400 dark:hover:bg-rose-950/80"
                                >
                                    <X class="size-3.5" /> Remove
                                </button>
                            </div>
                        </div>
                    </div>
                    <p v-if="form.errors.photo" class="mt-2 text-xs text-rose-500">{{ form.errors.photo }}</p>
                </div>

                <!-- Notice -->
                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-800 dark:border-amber-900/40 dark:bg-amber-950/30 dark:text-amber-300">
                    <p class="font-semibold">📋 Important Notice</p>
                    <p class="mt-1 text-xs leading-relaxed">Your registration will be reviewed and verified by your ward coordinator. A unique membership number will be assigned upon submission. You do not need a password — this form registers your support organization membership, not a login account.</p>
                </div>

                <!-- Submit -->
                <div class="flex items-center justify-between border-t border-slate-200 pt-6 dark:border-slate-800">
                    <Link :href="route('home')" class="text-sm font-medium text-slate-500 hover:text-slate-800 dark:hover:text-white">
                        ← Back to Home
                    </Link>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-amber-600 to-emerald-600 px-8 py-3 text-sm font-bold text-white shadow-lg shadow-amber-600/20 transition hover:from-amber-500 hover:to-emerald-500 disabled:opacity-60"
                    >
                        <LoaderCircle v-if="form.processing" class="size-4 animate-spin" />
                        <UserPlus v-else class="size-4" />
                        {{ form.processing ? 'Submitting...' : 'Submit Registration' }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Latest Announcements Section -->
        <div v-if="announcements && announcements.length > 0" class="mx-auto max-w-5xl px-4 py-12 sm:px-6">
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Latest Announcements</h2>
            <div class="mt-6 grid gap-6 sm:grid-cols-3">
                <div v-for="item in announcements" :key="item.id" class="flex flex-col justify-between overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div v-if="item.image_path" class="h-32 w-full overflow-hidden border-b border-slate-200 dark:border-slate-800">
                        <img :src="`/storage/${item.image_path}`" class="h-full w-full object-cover" />
                    </div>
                    <div class="flex flex-1 flex-col p-5">
                        <h3 class="font-bold text-slate-900 dark:text-white">{{ item.title }}</h3>
                        <p class="mt-2 line-clamp-2 text-sm text-slate-500">{{ item.content }}</p>
                        <div class="mt-auto pt-4 text-xs font-semibold">
                            <Link :href="route('announcements.show', item.id)" class="text-amber-600 hover:text-amber-500">Read Notice &rarr;</Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Latest ESCOs Section -->
        <div v-if="escos && escos.length > 0" class="mx-auto max-w-5xl px-4 pb-12 sm:px-6">
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Recent EXCO Appointments</h2>
            <div class="mt-6 grid gap-6 sm:grid-cols-3">
                <div v-for="official in escos" :key="official.id" class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center gap-4 border-b border-slate-100 pb-4 dark:border-slate-800">
                        <div class="flex size-14 shrink-0 items-center justify-center overflow-hidden rounded-full bg-slate-100 text-lg font-bold text-slate-500 shadow-sm dark:bg-slate-800">
                            <img v-if="official.photo_path" :src="`/storage/${official.photo_path}`" class="h-full w-full object-cover" />
                            <span v-else>{{ official.full_name.charAt(0) }}</span>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900 dark:text-white">{{ official.full_name }}</h3>
                            <span class="inline-block rounded-lg bg-amber-100 px-2.5 py-0.5 text-xs font-bold text-amber-900 dark:bg-amber-950/80 dark:text-amber-300">
                                {{ official.position?.name }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="space-y-2 text-xs text-slate-600 dark:text-slate-400">
                        <div class="flex items-center gap-2">
                            <Building2 class="size-3.5 shrink-0 text-slate-400" />
                            <span class="truncate">State: <strong class="text-slate-900 dark:text-white">{{ official.state?.name || 'National Level' }}</strong></span>
                        </div>
                        <div v-if="official.lga" class="flex items-center gap-2">
                            <MapPin class="size-3.5 shrink-0 text-slate-400" />
                            <span class="truncate">LGA: <strong class="text-slate-900 dark:text-white">{{ official.lga.name }}</strong></span>
                        </div>
                        <div v-if="official.ward" class="flex items-center gap-2">
                            <MapPin class="size-3.5 shrink-0 text-slate-400" />
                            <span class="truncate">Ward: <strong class="text-slate-900 dark:text-white">{{ official.ward.name }}</strong></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="border-t border-slate-200 bg-white py-12 text-sm text-slate-600 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-400">
            <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-6 px-4 sm:flex-row sm:px-6 lg:px-8">
                <div class="flex items-center gap-3 font-bold text-slate-900 dark:text-white">
                    <img src="/logo.png" alt="Shaihiyya Logo" class="size-10 object-contain" />
                    <div>
                        <span class="block text-sm font-bold text-slate-900 dark:text-white">Shaihiyya Amanar Jagora</span>
                        <span class="block text-[10px] font-semibold uppercase tracking-widest text-amber-600 dark:text-amber-400">Support Organization</span>
                    </div>
                </div>
                <div class="text-center">
                    <p>&copy; {{ new Date().getFullYear() }} Shaihiyya Amanar Jagora. All rights reserved.</p>
                    <p class="mt-1 text-xs font-medium text-slate-500 dark:text-slate-500">Developed by YUM IT SOLUTIONS LTD. +2348167768410</p>
                </div>
                <div class="flex gap-6">
                    <Link :href="route('login')" class="hover:underline">Coordinator Login</Link>
                    <Link :href="route('home')" class="hover:underline">Home</Link>
                </div>
            </div>
        </footer>
    </div>
</template>
