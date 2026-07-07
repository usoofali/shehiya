<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { Award, ChevronLeft, Download, ShieldCheck } from 'lucide-vue-next';
import html2canvas from 'html2canvas';

const props = defineProps<{
    member: {
        name: string;
        membership_number: string;
        photo_url: string | null;
        status: string;
        state?: string;
        lga?: string;
        ward?: string;
    };
}>();

const badgeRef = ref<HTMLElement | null>(null);
const isDownloading = ref(false);

const downloadBadge = async () => {
    if (!badgeRef.value || isDownloading.value) return;
    
    try {
        isDownloading.value = true;
        // Yield to browser to render loading spinner before blocking thread
        await new Promise(resolve => setTimeout(resolve, 100));
        // Briefly remove border/shadow before capturing to ensure a clean CR80 edge
        const originalClasses = badgeRef.value.className;
        badgeRef.value.classList.remove('shadow-2xl', 'sm:w-[320px]', 'w-full', 'max-w-[320px]');
        badgeRef.value.style.width = '320px';
        
        const canvas = await html2canvas(badgeRef.value, {
            scale: 3, // High resolution
            useCORS: true,
            backgroundColor: '#ffffff',
            logging: false
        });
        
        // Restore styling
        badgeRef.value.className = originalClasses;
        badgeRef.value.style.width = '';
        
        const link = document.createElement('a');
        link.download = `Shaihiyya-Badge-${props.member.membership_number}.png`;
        link.href = canvas.toDataURL('image/png', 1.0);
        link.click();
    } catch (error) {
        console.error('Error generating badge image:', error);
        alert('Could not download the badge. Please try again.');
    } finally {
        isDownloading.value = false;
    }
};

// Construct the verification URL that the QR code will point to
const verificationUrl = window.location.origin + '/verify/' + props.member.membership_number;
</script>

<template>
    <Head :title="`ID Badge - ${member.name}`" />

    <div class="min-h-screen bg-slate-100 dark:bg-slate-900 print:bg-white print:dark:bg-white">
        <!-- Top Action Bar (Hidden when printing) -->
        <div class="fixed inset-x-0 top-0 z-50 flex items-center justify-between border-b border-slate-200 bg-white px-4 py-3 shadow-sm print:hidden dark:border-slate-800 dark:bg-slate-950">
            <Link :href="route('status.check')" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">
                <ChevronLeft class="size-4" /> Back
            </Link>
            <button @click="downloadBadge" :disabled="isDownloading" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-emerald-700 disabled:opacity-75">
                <Download v-if="!isDownloading" class="size-4" /> 
                <span v-if="isDownloading" class="size-4 animate-spin rounded-full border-2 border-white/30 border-t-white"></span>
                {{ isDownloading ? 'Downloading...' : 'Download Image' }}
            </button>
        </div>

        <!-- ID Card Container -->
        <div class="flex items-center justify-center pt-24 pb-12 print:p-0 print:pt-0">
            <!-- The Card (CR80 Standard size: 2.125" x 3.375", represented here conceptually but scalable for print) -->
            <div ref="badgeRef" class="badge-card relative overflow-hidden rounded-[14px] bg-white shadow-2xl print:shadow-none sm:w-[320px] w-full max-w-[320px] aspect-[2.125/3.375] flex flex-col border border-slate-200 print:border-none">

                <!-- Unverified Overlay Warning -->
                <div v-if="member.status !== 'verified'" class="absolute inset-0 z-40 flex items-center justify-center pointer-events-none">
                    <div class="absolute inset-0 bg-rose-500/10 backdrop-blur-[1px]"></div>
                    <div class="rotate-[-35deg] border-4 border-rose-500 px-6 py-2 text-3xl font-black tracking-widest text-rose-500 opacity-90 shadow-sm backdrop-blur-sm shadow-rose-500/20">
                        NOT VERIFIED
                    </div>
                </div>

                <!-- Verified Stamp -->
                <div v-if="member.status === 'verified'" class="absolute inset-0 z-40 flex items-center justify-center pointer-events-none opacity-30">
                    <div class="rotate-[-35deg] border-4 border-emerald-600 px-6 py-2 text-3xl font-black tracking-widest text-emerald-600 shadow-sm">
                        VERIFIED
                    </div>
                </div>

                <!-- Background Elements -->
                <div class="absolute inset-0 z-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-amber-50 to-white print:bg-none"></div>

                <!-- Top Header -->
                <div class="relative z-10 bg-gradient-to-r from-amber-600 to-emerald-600 px-4 py-5 text-center text-white">
                    <div class="mx-auto flex size-20 items-center justify-center overflow-hidden rounded-full bg-white shadow-inner backdrop-blur-sm">
                        <img src="/logo.png" alt="Shaihiyya Logo" class="h-full w-full object-cover p-1.5" />
                    </div>
                    <h2 class="mt-2 text-lg font-black uppercase leading-tight text-white">SHAIHIYYA AMANAR JAGORA</h2>
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-amber-200">Official Member</p>
                </div>

                <!-- Photo / Body -->
                <div class="relative z-10 flex flex-1 flex-col items-center justify-center px-4 py-3 bg-white">
                    <!-- Profile Photo -->
                    <div class="relative mx-auto mb-2 size-20 shrink-0 overflow-hidden rounded-2xl border-[3px] border-amber-500 bg-slate-100 shadow-md">
                        <img v-if="member.photo_url" :src="member.photo_url" :alt="member.name" class="h-full w-full object-cover" />
                        <div v-else class="flex h-full w-full items-center justify-center bg-slate-100 text-slate-400">
                            <span class="text-3xl font-bold uppercase">{{ member.name.charAt(0) }}</span>
                        </div>
                        <div v-if="member.status === 'verified'" class="absolute bottom-0.5 right-0.5 rounded-full bg-emerald-500 p-0.5 shadow-sm">
                            <ShieldCheck class="size-3 text-white" />
                        </div>
                    </div>

                    <!-- Member Info -->
                    <div class="w-full text-center">
                        <h3 class="text-[15px] font-black uppercase leading-tight text-slate-900 line-clamp-2">{{ member.name }}</h3>
                        <p class="mt-0.5 font-mono text-[11px] font-bold text-amber-600">{{ member.membership_number }}</p>
                        
                        <div class="mt-1.5 text-[8px] uppercase font-bold text-slate-500 tracking-wider flex flex-col gap-0">
                            <p v-if="member.state"><span class="text-slate-400">State:</span> {{ member.state }}</p>
                            <p v-if="member.lga"><span class="text-slate-400">LGA:</span> {{ member.lga }}</p>
                            <p v-if="member.ward"><span class="text-slate-400">Ward:</span> {{ member.ward }}</p>
                        </div>
                    </div>

                    <!-- QR Code -->
                    <div class="mt-auto pt-2 flex w-full flex-col items-center">
                        <div class="rounded-xl border border-slate-200 bg-white p-1.5 shadow-sm">
                            <img :src="`https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(verificationUrl)}`" alt="QR Code" class="size-16 object-contain" />
                        </div>
                        <p class="mt-1 text-[7px] font-bold uppercase tracking-wider text-slate-400">Scan to Verify</p>
                    </div>
                </div>

                <!-- Footer -->
                <div class="relative z-10 bg-slate-900 py-2 text-center text-white">
                    <p class="text-[9px] font-semibold tracking-widest">Property of Shaihiyya Amanar Jagora</p>
                </div>

            </div>
        </div>
    </div>
</template>

<style scoped>
/* Specific print styles to ensure the ID card prints exactly as expected */
@media print {
    @page {
        margin: 0;
        size: 54mm 86mm; /* Standard ID card size (CR80) */
    }

    body {
        margin: 0;
        padding: 0;
        background: white;
    }

    .badge-card {
        width: 100% !important;
        height: 100vh !important;
        max-width: none !important;
        border-radius: 0 !important;
        box-shadow: none !important;
        margin: 0 !important;
        padding: 0 !important;
        aspect-ratio: auto !important;
    }
}
</style>
