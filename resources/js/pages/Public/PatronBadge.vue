<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { Award, ChevronLeft, Crown, Download, ShieldCheck } from 'lucide-vue-next';
import html2canvas from 'html2canvas';

const props = defineProps<{
    patron: {
        id: number;
        name: string;
        title: string;
        category: string;
        badge_id: string;
        is_active: boolean;
        photo_url: string | null;
    };
}>();

const badgeRef = ref<HTMLElement | null>(null);
const isDownloading = ref(false);

const downloadBadge = async () => {
    if (!badgeRef.value || isDownloading.value) return;
    
    try {
        isDownloading.value = true;
        await new Promise(resolve => setTimeout(resolve, 100));
        const originalClasses = badgeRef.value.className;
        const originalWidth = badgeRef.value.style.width;
        const originalHeight = badgeRef.value.style.height;
        badgeRef.value.classList.remove('shadow-2xl', 'sm:w-[320px]', 'w-full', 'max-w-[320px]');
        badgeRef.value.style.width = '320px';
        badgeRef.value.style.height = '510px';
        
        const canvas = await html2canvas(badgeRef.value, {
            scale: 3,
            useCORS: true,
            backgroundColor: '#ffffff',
            logging: false
        });
        
        badgeRef.value.className = originalClasses;
        badgeRef.value.style.width = originalWidth;
        badgeRef.value.style.height = originalHeight;
        
        const link = document.createElement('a');
        link.download = `Shaihiyya-Patron-Badge-${props.patron.badge_id}.png`;
        link.href = canvas.toDataURL('image/png', 1.0);
        link.click();
    } catch (error) {
        console.error('Error generating badge image:', error);
        alert('Could not download the badge. Please try again.');
    } finally {
        isDownloading.value = false;
    }
};

const verificationUrl = typeof window !== 'undefined' ? `${window.location.origin}/patrons/verify/${props.patron.id}` : '';
</script>

<template>
    <Head :title="`Royal Leadership Badge - ${patron.title} ${patron.name}`" />

    <div class="min-h-screen bg-slate-100 dark:bg-slate-900 print:bg-white print:dark:bg-white">
        <!-- Top Action Bar -->
        <div class="fixed inset-x-0 top-0 z-50 flex items-center justify-between border-b border-slate-200 bg-white px-4 py-3 shadow-sm print:hidden dark:border-slate-800 dark:bg-slate-950">
            <button @click="typeof window !== 'undefined' && window.history.back()" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">
                <ChevronLeft class="size-4" /> Back
            </button>
            <button @click="downloadBadge" :disabled="isDownloading" class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-amber-700 disabled:opacity-75">
                <Download v-if="!isDownloading" class="size-4" /> 
                <span v-if="isDownloading" class="size-4 animate-spin rounded-full border-2 border-white/30 border-t-white"></span>
                {{ isDownloading ? 'Downloading...' : 'Download Image' }}
            </button>
        </div>

        <!-- ID Card Container -->
        <div class="flex items-center justify-center pt-24 pb-12 print:p-0 print:pt-0">
            <div ref="badgeRef" class="badge-card relative overflow-hidden rounded-[14px] bg-white shadow-2xl print:shadow-none sm:w-[320px] w-full max-w-[320px] min-h-[510px] aspect-[2.125/3.375] flex flex-col border border-slate-200 print:border-none">

                <!-- Inactive Overlay Warning -->
                <div v-if="!patron.is_active" class="absolute inset-0 z-40 flex items-center justify-center pointer-events-none">
                    <div class="absolute inset-0 bg-rose-500/10 backdrop-blur-[1px]"></div>
                    <div class="rotate-[-35deg] border-4 border-rose-500 px-6 py-2 text-3xl font-black tracking-widest text-rose-500 opacity-90 shadow-sm backdrop-blur-sm shadow-rose-500/20">
                        INACTIVE
                    </div>
                </div>

                <!-- Active Stamp -->
                <div v-if="patron.is_active" class="absolute inset-0 z-40 flex items-center justify-center pointer-events-none opacity-20">
                    <div class="rotate-[-35deg] border-4 border-amber-600 px-6 py-2 text-3xl font-black tracking-widest text-amber-600 shadow-sm">
                        HONORARY
                    </div>
                </div>

                <!-- Background Elements -->
                <div class="absolute inset-0 z-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-amber-50 to-white print:bg-none"></div>

                <!-- Top Header -->
                <div class="relative z-10 bg-gradient-to-r from-amber-800 via-rose-950 to-amber-900 px-4 py-5 text-center text-white">
                    <div class="mx-auto flex size-20 items-center justify-center overflow-hidden rounded-full bg-white shadow-inner backdrop-blur-sm ring-2 ring-amber-400">
                        <img src="/logo.png" alt="Shaihiyya Logo" class="h-full w-full object-cover p-1.5" />
                    </div>
                    <h2 class="mt-2 text-lg font-black uppercase leading-tight text-white">SHAIHIYYA AMANAR JAGORA</h2>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-amber-300 flex items-center justify-center gap-1">
                        <Crown class="size-3 text-amber-400" /> Royal Leadership & Advisory
                    </p>
                </div>

                <!-- Photo / Body -->
                <div class="relative z-10 flex flex-1 flex-col items-center justify-center px-4 py-3 bg-white">
                    <!-- Profile Photo -->
                    <div class="relative mx-auto mb-2 size-20 shrink-0 overflow-hidden rounded-2xl border-[3px] border-amber-500 bg-slate-100 shadow-md">
                        <img v-if="patron.photo_url" :src="patron.photo_url" :alt="patron.name" class="h-full w-full object-cover" />
                        <div v-else class="flex h-full w-full items-center justify-center bg-slate-100 text-slate-400">
                            <span class="text-3xl font-bold uppercase">{{ patron.name.charAt(0) }}</span>
                        </div>
                        <div v-if="patron.is_active" class="absolute bottom-0.5 right-0.5 rounded-full bg-amber-600 p-0.5 shadow-sm">
                            <Crown class="size-3 text-white" />
                        </div>
                    </div>

                    <!-- Patron Info -->
                    <div class="w-full text-center">
                        <p class="text-xs font-bold text-amber-700 uppercase">{{ patron.title }}</p>
                        <h3 class="px-1 text-[14px] font-black uppercase leading-[1.3] text-slate-900 break-words">{{ patron.name }}</h3>
                        
                        <div class="mt-1 inline-block rounded-full bg-amber-100 px-2.5 py-0.5 text-[9px] font-black uppercase tracking-wider text-amber-900 ring-1 ring-amber-500/20">
                            {{ patron.category }}
                        </div>

                        <p class="mt-1.5 font-mono text-[11px] font-bold text-slate-700">{{ patron.badge_id }}</p>
                        
                        <div class="mt-1.5 text-[8px] uppercase font-bold text-slate-500 tracking-wider flex flex-col gap-0.5 border-t border-slate-100 pt-1.5">
                            <p><span class="text-slate-400">Organization Status:</span> Official {{ patron.category }}</p>
                            <p><span class="text-slate-400">Recognition:</span> Shaihiyya Amanar Jagora Leadership Council</p>
                        </div>
                    </div>

                    <!-- QR Code -->
                    <div class="mt-auto pt-2 flex w-full flex-col items-center">
                        <div class="rounded-xl border border-slate-200 bg-white p-1.5 shadow-sm">
                            <img :src="`https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(verificationUrl)}`" alt="QR Code" class="size-16 object-contain" />
                        </div>
                        <p class="mt-1 text-[7px] font-bold uppercase tracking-wider text-slate-400">Scan to Verify Dignitary</p>
                    </div>
                </div>

                <!-- Footer -->
                <div class="relative z-10 bg-gradient-to-r from-amber-900 via-slate-950 to-amber-900 py-2 text-center text-white">
                    <p class="text-[9px] font-semibold tracking-widest text-amber-200">Property of Shaihiyya Amanar Jagora</p>
                </div>

            </div>
        </div>
    </div>
</template>

<style scoped>
@media print {
    @page {
        margin: 0;
        size: 54mm 86mm;
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
