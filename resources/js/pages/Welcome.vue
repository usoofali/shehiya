<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { Users, MapPin, Megaphone, Bell, Calendar, Award, ShieldCheck, ChevronRight, Search, Building2, UserCheck } from 'lucide-vue-next';

const props = defineProps<{
    contents: Record<string, { key: string; title: string; body: string | null; image_url: string | null }>;
    states: Array<{ id: number; name: string; lgas: Array<{ id: number; name: string }> }>;
    escos: Array<{
        id: number;
        full_name: string;
        position: { name: string };
        email?: string;
        photo_path?: string;
        state?: { name: string };
        lga?: { name: string };
        ward?: { name: string };
    }>;
    announcements: Array<{
        id: number;
        title: string;
        content: string;
        type: string;
        created_at: string;
        state?: { name: string };
        lga?: { name: string };
        ward?: { name: string };
    }>;
    filters?: { state_id?: string | number; lga_id?: string | number; ward_id?: string | number };
}>();

const selectedState = ref(props.filters?.state_id || '');
const selectedLga = ref(props.filters?.lga_id || '');
const selectedWard = ref(props.filters?.ward_id || '');

const availableLgas = computed(() => {
    if (!selectedState.value) return [];
    const stateObj = props.states.find(s => s.id == selectedState.value);
    return stateObj ? stateObj.lgas : [];
});

const availableWards = ref([]);

const fetchWards = async () => {
    if (!selectedLga.value) {
        availableWards.value = [];
        return;
    }
    try {
        const response = await fetch(`/api/locations/wards?lga_id=${selectedLga.value}`);
        const data = await response.json();
        availableWards.value = data;
    } catch (error) {
        console.error('Error fetching wards:', error);
    }
};

watch(selectedState, (newVal, oldVal) => {
    if (oldVal !== undefined && newVal !== oldVal) {
        selectedLga.value = '';
        selectedWard.value = '';
        availableWards.value = [];
        applyFilters();
    }
});

watch(selectedLga, (newVal, oldVal) => {
    if (oldVal !== undefined && newVal !== oldVal) {
        selectedWard.value = '';
        fetchWards();
        applyFilters();
    }
});

if (selectedLga.value) {
    fetchWards();
}

const applyFilters = () => {
    router.get('/', {
        state_id: selectedState.value || undefined,
        lga_id: selectedLga.value || undefined,
        ward_id: selectedWard.value || undefined,
    }, {
        preserveScroll: true,
        preserveState: true,
    });
};

const getContent = (key: string, defaultTitle: string, defaultBody: string) => {
    const item = props.contents?.[key];
    return {
        title: item?.title || defaultTitle,
        body: item?.body || defaultBody,
    };
};

const hero = computed(() => getContent('hero_title', 'Shaihiyya — A Support Organization Built on Service, Unity & Accountability', 'Shaihiyya is a grassroots political and community support organization established to promote effective leadership, strengthen community engagement, empower citizens, and build a well-organized network of supporters dedicated to sustainable development and public service across Nigeria.'));
const about = computed(() => getContent('about_section', 'About Shaihiyya Amanar Jagora', 'Shaihiyya is a long-term grassroots support organization founded by Senator Abdul\'aziz Abubakar Yari, the former Executive Governor of Zamfara State (2011–2019) and current Senator representing Zamfara West. The support organization is built around community organization, leadership development, and civic participation — positioning itself not as a seasonal campaign structure but as a permanent organization that serves its members and communities year-round.'));
const vision = computed(() => getContent('vision_section', 'Vision of the ORGANIZATION', 'The vision of the SHAIHIYYA AMANAR JAGORA SUPPORT ORGANIZATION is founded on the following objectives:\n\nTo create widespread political awareness across Nigeria regarding Jagora\'s future political aspirations, particularly in view of any future national leadership ambitions he may pursue.\n\nTo protect and promote the good image and reputation of Jagora by providing factual information and positive public engagement in response to political misinformation and misrepresentation.\n\nTo remain loyal and accountable solely to Jagora\'s political support organization and to operate in accordance with its objectives and directives.\n\nTo serve as an effective communication platform for conveying information, messages, and mobilization efforts from Jagora\'s political support organization to communities across Zamfara State and Nigeria as a whole.\n\nTo operate under the guidance of respected and experienced leaders who will promote discipline, unity, and responsible conduct among supporters, ensuring that all activities reflect positively on Jagora\'s reputation and political vision.'));
const mission = computed(() => getContent('mission_section', 'Mission of the ORGANIZATION', 'The mission of the SHAIHIYYA AMANAR JAGORA SUPPORT ORGANIZATION is to:\n\nDemonstrate unwavering solidarity and support for Jagora, Distinguished Senator Dr. Abdul\'Aziz Abubakar Yari, in the pursuit of his political vision and aspirations.\n\nServe as responsible ambassadors of Jagora by upholding his dignity, protecting his reputation, and promoting the values he represents.\n\nShowcase Jagora\'s leadership, influence, and contributions within Nigeria\'s contemporary political landscape.\n\nPromote awareness of Jagora\'s developmental initiatives, leadership achievements, and service to his constituents among neighboring states and across the nation.\n\nEnlighten and educate the public on Jagora\'s contributions, goodwill, and commitment to the development of Zamfara State and Nigeria as a whole.'));

const missionPoints = [
    'Demonstrate unwavering solidarity and support for Jagora',
    'Serve as responsible ambassadors of Jagora and uphold his dignity',
    "Showcase Jagora's leadership and contributions across Nigeria",
    "Promote awareness of Jagora's developmental initiatives and achievements",
    "Enlighten and educate the public on Jagora's commitment to development",
];
</script>

<template>
    <Head title="Shaihiyya Amanar Jagora Support Organization — Official Gateway" />

    <div class="min-h-screen bg-slate-50 text-slate-900 selection:bg-amber-500 selection:text-white dark:bg-slate-950 dark:text-slate-100">
        <!-- Top Navigation -->
        <header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/80 backdrop-blur-md dark:border-slate-800/80 dark:bg-slate-950/80">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-3 py-3 sm:px-6 sm:py-4 lg:px-8">
                <div class="flex min-w-0 items-center gap-2 sm:gap-3">
                    <div class="flex size-9 sm:size-14 shrink-0 items-center justify-center overflow-hidden rounded-full bg-white shadow-md shadow-amber-500/10">
                        <img src="/logo.png" alt="Shaihiyya Logo" class="h-full w-full object-cover p-1" />
                    </div>
                    <div class="min-w-0">
                        <span class="block truncate sm:overflow-visible text-xs sm:text-xl font-black tracking-tight">SHAIHIYYA AMANAR JAGORA</span>
                        <span class="block truncate sm:overflow-visible text-[9px] sm:text-xs font-semibold uppercase tracking-widest text-amber-600 dark:text-amber-400">Support Organization</span>
                    </div>
                </div>

                <nav class="flex shrink-0 items-center gap-2 sm:gap-3">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="inline-flex shrink-0 items-center gap-1 sm:gap-2 rounded-xl bg-gradient-to-r from-amber-600 to-emerald-600 px-3 py-2 sm:px-5 sm:py-2.5 text-xs sm:text-sm font-bold text-white shadow-md shadow-amber-600/20 transition hover:from-amber-500 hover:to-emerald-500"
                    >
                        Dashboard <ChevronRight class="size-3.5 sm:size-4" />
                    </Link>
                    <Link
                        v-else
                        :href="route('register')"
                        class="inline-flex shrink-0 items-center gap-1 sm:gap-1.5 rounded-xl bg-gradient-to-r from-amber-600 to-emerald-600 px-3.5 py-2 sm:px-5 sm:py-2.5 text-xs sm:text-sm font-bold text-white shadow-md shadow-amber-600/20 transition hover:from-amber-500 hover:to-emerald-500"
                    >
                        Join <ChevronRight class="size-3.5 sm:size-4" />
                    </Link>
                </nav>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="relative overflow-hidden pt-12 pb-20 lg:pt-20 lg:pb-32">
            <div class="absolute inset-0 -z-10 bg-[radial-gradient(45rem_50rem_at_top,theme(colors.amber.100),white)] dark:bg-[radial-gradient(45rem_50rem_at_top,theme(colors.amber.950/30%),theme(colors.slate.950))]"></div>
            <div class="mx-auto max-w-7xl px-4 text-center sm:px-6 lg:px-8">
                <div class="inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-50 px-4 py-1.5 text-xs font-bold uppercase tracking-wider text-amber-800 dark:border-amber-900/50 dark:bg-amber-950/50 dark:text-amber-300">
                    <ShieldCheck class="size-4 text-amber-600 dark:text-amber-400" /> Senator Abdul'aziz Abubakar Yari Support Organization
                </div>
                <h1 class="mt-6 text-4xl font-black tracking-tight text-slate-900 sm:text-6xl lg:text-7xl dark:text-white">
                    {{ hero.title }}
                </h1>
                <p class="mx-auto mt-6 max-w-3xl text-lg leading-relaxed text-slate-600 sm:text-xl dark:text-slate-300">
                    {{ hero.body }}
                </p>
                <div class="mt-10 flex flex-wrap items-center justify-center gap-4">
                    <Link
                        :href="route('register')"
                        class="rounded-2xl bg-gradient-to-r from-amber-600 to-emerald-600 px-8 py-4 text-base font-bold text-white shadow-xl shadow-amber-600/25 transition hover:scale-105"
                    >
                        Register as a Member
                    </Link>
                    <Link
                        :href="route('status.check')"
                        class="rounded-2xl border border-slate-300 bg-white px-8 py-4 text-base font-bold text-slate-800 shadow-sm transition hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                    >
                        Check Status &amp; Print ID
                    </Link>
                    <a
                        href="#esco-directory"
                        class="rounded-2xl border border-slate-300 bg-white px-8 py-4 text-base font-bold text-slate-800 shadow-sm transition hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                    >
                        View EXCO Directory
                    </a>
                </div>
            </div>
        </section>

        <!-- About Support Organization Section -->
        <section class="border-y border-slate-200/80 bg-white py-20 dark:border-slate-800/80 dark:bg-slate-900/50">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h2 class="text-3xl font-extrabold tracking-tight sm:text-4xl">{{ about.title }}</h2>
                    <p class="mx-auto mt-4 max-w-3xl text-base leading-relaxed text-slate-600 dark:text-slate-400">{{ about.body }}</p>
                </div>

                <div class="mt-16 grid gap-8 md:grid-cols-2">
                    <!-- Core Values Infobox -->
                    <div class="relative overflow-hidden rounded-3xl border border-slate-200 bg-gradient-to-br from-amber-500/5 to-transparent p-8 shadow-sm dark:border-slate-800">
                        <div class="mb-4 inline-flex size-12 items-center justify-center rounded-2xl bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300">
                            <Award class="size-6" />
                        </div>
                        <h3 class="text-2xl font-bold text-slate-900 dark:text-white">{{ vision.title }}</h3>
                        <p class="mt-4 whitespace-pre-line leading-relaxed text-slate-600 dark:text-slate-400">{{ vision.body }}</p>

                        <div class="mt-6 flex flex-wrap gap-2">
                            <span v-for="value in ['Service','Integrity','Unity','Discipline','Accountability','Loyalty','Respect','Transparency','Grassroots Participation']" :key="value"
                                class="rounded-full border border-amber-200 bg-amber-50 px-3 py-0.5 text-xs font-bold text-amber-800 dark:border-amber-900/30 dark:bg-amber-950/40 dark:text-amber-400">
                                {{ value }}
                            </span>
                        </div>
                    </div>

                    <div class="relative overflow-hidden rounded-3xl border border-slate-200 bg-gradient-to-br from-emerald-500/5 to-transparent p-8 shadow-sm dark:border-slate-800">
                        <div class="mb-4 inline-flex size-12 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300">
                            <Users class="size-6" />
                        </div>
                        <h3 class="text-2xl font-bold text-slate-900 dark:text-white">{{ mission.title }}</h3>
                        <p class="mt-4 whitespace-pre-line leading-relaxed text-slate-600 dark:text-slate-400">{{ mission.body }}</p>

                        <ul class="mt-5 space-y-2 text-sm text-slate-600 dark:text-slate-400">
                            <li v-for="point in missionPoints" :key="point" class="flex items-start gap-2">
                                <ShieldCheck class="mt-0.5 size-4 shrink-0 text-emerald-500" />
                                {{ point }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- Public Announcements Noticeboard -->
        <section v-if="announcements && announcements.length > 0" class="py-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                    <div>
                        <div class="inline-flex items-center gap-2 text-sm font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">
                            <Megaphone class="size-4" /> Shaihiyya Amanar Jagora Broadcasts
                        </div>
                        <h2 class="mt-2 text-3xl font-extrabold tracking-tight sm:text-4xl">Latest Announcements</h2>
                    </div>
                </div>

                <div class="mt-10 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="notice in announcements"
                        :key="notice.id"
                        class="flex flex-col justify-between overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm transition hover:shadow-md dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div v-if="notice.image_path" class="h-48 w-full overflow-hidden border-b border-slate-200 dark:border-slate-800">
                            <img :src="`/storage/${notice.image_path}`" alt="Notice Image" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" />
                        </div>
                        <div class="flex flex-1 flex-col p-6">
                            <div class="flex items-center justify-between gap-2">
                                <span
                                    class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-bold uppercase"
                                    :class="{
                                        'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300': notice.type === 'notice',
                                        'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300': notice.type === 'meeting',
                                        'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300': notice.type === 'update',
                                    }"
                                >
                                    <Bell v-if="notice.type === 'notice'" class="size-3" />
                                    <Calendar v-else-if="notice.type === 'meeting'" class="size-3" />
                                    {{ notice.type }}
                                </span>
                                <span class="text-xs text-slate-400">{{ new Date(notice.created_at).toLocaleDateString() }}</span>
                            </div>
                            <h3 class="mt-4 text-xl font-bold text-slate-900 dark:text-white">{{ notice.title }}</h3>
                            <p class="mt-2 line-clamp-4 text-sm leading-relaxed text-slate-600 dark:text-slate-400">{{ notice.content }}</p>
                            
                            <div class="mt-auto pt-6 flex items-center justify-between border-t border-slate-100 text-xs font-semibold text-slate-500 dark:border-slate-800">
                                <span>{{ notice.state ? notice.state.name : 'National Broadcast' }}</span>
                                <Link :href="route('announcements.show', notice.id)" class="text-xs font-bold text-amber-600 hover:text-amber-500 hover:underline dark:text-amber-400">Read Notice &rarr;</Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- EXCO Leadership Directory Section -->
        <section id="esco-directory" class="border-t border-slate-200/80 bg-slate-100/50 py-20 dark:border-slate-800/80 dark:bg-slate-900/30">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <div class="inline-flex items-center gap-2 text-sm font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">
                        <UserCheck class="size-4" /> Leadership Registry
                    </div>
                    <h2 class="mt-2 text-3xl font-extrabold tracking-tight sm:text-4xl">Public EXCO Directory</h2>
                    <p class="mx-auto mt-3 max-w-2xl text-base text-slate-600 dark:text-slate-400">
                        Explore verified Executive State & Local Government Officials coordinating the support organization across jurisdictions.
                    </p>
                </div>

                <!-- Interactive Filter Toolbar -->
                <div class="mt-10 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500">Filter by State</label>
                            <select
                                v-model="selectedState"
                                class="mt-2 w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-800 focus:border-amber-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                            >
                                <option value="">All States (National View)</option>
                                <option v-for="state in states" :key="state.id" :value="state.id">{{ state.name }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500">Filter by LGA</label>
                            <select
                                v-model="selectedLga"
                                :disabled="!selectedState"
                                class="mt-2 w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-800 focus:border-amber-500 focus:outline-none disabled:opacity-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                            >
                                <option value="">All LGAs in State</option>
                                <option v-for="lga in availableLgas" :key="lga.id" :value="lga.id">{{ lga.name }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-500">Filter by Ward</label>
                            <select
                                v-model="selectedWard"
                                :disabled="!selectedLga"
                                class="mt-2 w-full rounded-2xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-800 focus:border-amber-500 focus:outline-none disabled:opacity-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                            >
                                <option value="">All Wards in LGA</option>
                                <option v-for="ward in availableWards" :key="ward.id" :value="ward.id">{{ ward.name }}</option>
                            </select>
                        </div>

                        <div class="flex items-end">
                            <button
                                @click="applyFilters"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-slate-900 px-6 py-3 text-sm font-bold text-white transition hover:bg-slate-800 dark:bg-slate-800 dark:hover:bg-slate-700"
                            >
                                <Search class="size-4" /> Filter Directory
                            </button>
                        </div>
                    </div>
                </div>

                <!-- EXCO Cards Grid -->
                <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3 sm:gap-8">
                    <div v-if="escos.length === 0" class="col-span-full rounded-3xl border border-slate-200 bg-white p-12 text-center text-slate-500 dark:border-slate-800 dark:bg-slate-900">
                        No active EXCO officials found matching the selected jurisdiction criteria.
                    </div>

                    <div
                        v-for="official in escos"
                        :key="official.id"
                        class="group relative flex flex-col justify-between overflow-hidden rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition duration-300 hover:-translate-y-1 hover:shadow-xl dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div class="absolute top-0 right-0 h-24 w-24 translate-x-8 -translate-y-8 rounded-full bg-amber-500/10 blur-xl transition group-hover:bg-amber-500/20"></div>

                        <div class="flex items-center gap-4">
                            <div class="flex size-14 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-gradient-to-br from-amber-500 to-emerald-600 text-xl font-black text-white shadow-md shadow-amber-500/20">
                                <img v-if="official.photo_path" :src="`/storage/${official.photo_path}`" class="h-full w-full object-cover" />
                                <span v-else>{{ official.full_name.charAt(0) }}</span>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ official.full_name }}</h3>
                                <span class="inline-block rounded-lg bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-900 dark:bg-amber-950/80 dark:text-amber-300">
                                    {{ official.position?.name || 'Official' }}
                                </span>
                            </div>
                        </div>

                        <div class="mt-6 space-y-2 border-t border-slate-100 pt-4 text-xs font-medium text-slate-600 dark:border-slate-800 dark:text-slate-400">
                            <div class="flex items-center gap-2">
                                <Building2 class="size-4 shrink-0 text-slate-400" />
                                <span>State: <strong class="text-slate-900 dark:text-white">{{ official.state?.name || 'National Level' }}</strong></span>
                            </div>
                            <div v-if="official.lga" class="flex items-center gap-2">
                                <MapPin class="size-4 shrink-0 text-slate-400" />
                                <span>LGA: <strong class="text-slate-900 dark:text-white">{{ official.lga.name }}</strong></span>
                            </div>
                            <div v-if="official.ward" class="flex items-center gap-2">
                                <MapPin class="size-4 shrink-0 text-slate-400" />
                                <span>Ward: <strong class="text-slate-900 dark:text-white">{{ official.ward.name }}</strong></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

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
                    <Link :href="route('register')" class="hover:underline">Member Registration</Link>
                </div>
            </div>
        </footer>
    </div>
</template>
