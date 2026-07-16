<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { Award, ChevronLeft, Download, ShieldCheck } from 'lucide-vue-next';
import html2canvas from 'html2canvas';

const props = defineProps<{
    esco: {
        id: number;
        name: string;
        badge_id: string;
        position: string;
        status: string;
        state?: string;
        lga?: string;
        ward?: string;
        polling_unit?: string;
        photo_url: string | null;
        appointed_at?: string | null;
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
        link.download = `Shaihiyya-EXCO-Badge-${props.esco.badge_id}.png`;
        link.href = canvas.toDataURL('image/png', 1.0);
        link.click();
    } catch (error) {
        console.error('Error generating badge image:', error);
        alert('Could not download the badge. Please try again.');
    } finally {
        isDownloading.value = false;
    }
};

const verificationUrl = typeof window !== 'undefined' ? `${window.location.origin}/esco/verify/${props.esco.id}` : '';
</script>

<template>
    <Head :title="`EXCO Badge - ${esco.name}`" />

    <div class="min-h-screen bg-slate-100 dark:bg-slate-900 print:bg-white print:dark:bg-white">
        <!-- Top Action Bar -->
        <div class="fixed inset-x-0 top-0 z-50 flex items-center justify-between border-b border-slate-200 bg-white px-4 py-3 shadow-sm print:hidden dark:border-slate-800 dark:bg-slate-950">
            <button @click="typeof window !== 'undefined' && window.history.back()" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">
                <ChevronLeft class="size-4" /> Back
            </button>
            <button @click="downloadBadge" :disabled="isDownloading" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-indigo-700 disabled:opacity-75">
                <Download v-if="!isDownloading" class="size-4" /> 
                <span v-if="isDownloading" class="size-4 animate-spin rounded-full border-2 border-white/30 border-t-white"></span>
                {{ isDownloading ? 'Downloading...' : 'Download Image' }}
            </button>
        </div>

        <!-- ID Card Container -->
        <div class="flex items-center justify-center pt-24 pb-12 print:p-0 print:pt-0">
            <div ref="badgeRef" class="badge-card relative overflow-hidden rounded-[14px] bg-white shadow-2xl print:shadow-none sm:w-[320px] w-full max-w-[320px] min-h-[510px] aspect-[2.125/3.375] flex flex-col border border-slate-200 print:border-none">

                <!-- Inactive Overlay Warning -->
                <div v-if="esco.status !== 'active'" class="absolute inset-0 z-40 flex items-center justify-center pointer-events-none">
                    <div class="absolute inset-0 bg-rose-500/10 backdrop-blur-[1px]"></div>
                    <div class="rotate-[-35deg] rounded-[50%/35%] border-4 border-rose-500 px-6 py-3 text-3xl font-black tracking-widest text-rose-500 opacity-90 shadow-sm backdrop-blur-sm shadow-rose-500/20 text-center leading-tight">
                        INACTIVE
                    </div>
                </div>

                <!-- Active Stamp -->
                <div v-if="esco.status === 'active'" class="absolute inset-0 z-40 flex items-center justify-center pointer-events-none opacity-20">
                    <div class="rotate-[-35deg] rounded-[50%/35%] border-4 border-indigo-600 px-6 py-3 text-3xl font-black tracking-widest text-indigo-600 shadow-sm text-center leading-tight">
                        EXECUTIVE
                    </div>
                </div>

                <!-- Background Elements -->
                <div class="absolute inset-0 z-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-indigo-50 to-white print:bg-none"></div>

                <!-- Top Header -->
                <div class="relative z-10 bg-gradient-to-r from-indigo-800 via-purple-800 to-indigo-950 px-4 py-5 text-center text-white">
                    <div class="mx-auto flex size-20 items-center justify-center overflow-hidden rounded-full bg-white shadow-inner backdrop-blur-sm">
                        <img src="/logo.png" alt="Shaihiyya Logo" class="h-full w-full object-cover p-1.5" />
                    </div>
                    <h2 class="mt-2 text-lg font-black uppercase leading-tight text-white">SHAIHIYYA AMANAR JAGORA</h2>
                    <p class="text-[10px] font-bold uppercase tracking-widest text-amber-300">Executive Leadership</p>
                </div>

                <!-- Photo / Body -->
                <div class="relative z-10 flex flex-1 flex-col items-center justify-center px-4 py-3 bg-white">
                    <!-- Profile Photo -->
                    <div class="relative mx-auto mb-2 size-20 shrink-0 overflow-hidden rounded-2xl border-[3px] border-amber-500 bg-slate-100 shadow-md">
                        <img v-if="esco.photo_url" :src="esco.photo_url" :alt="esco.name" class="h-full w-full object-cover" />
                        <div v-else class="flex h-full w-full items-center justify-center bg-slate-100 text-slate-400">
                            <span class="text-3xl font-bold uppercase">{{ esco.name.charAt(0) }}</span>
                        </div>
                        <div v-if="esco.status === 'active'" class="absolute bottom-0.5 right-0.5 rounded-full bg-indigo-600 p-0.5 shadow-sm">
                            <ShieldCheck class="size-3 text-white" />
                        </div>
                    </div>

                    <!-- Official Info -->
                    <div class="w-full text-center">
                        <h3 class="px-1 text-[14px] font-black uppercase leading-[1.3] text-slate-900 break-words">{{ esco.name }}</h3>
                        
                        <div class="mt-1 inline-block rounded-full bg-indigo-100 px-2.5 py-0.5 text-[9px] font-black uppercase tracking-wider text-indigo-800">
                            {{ esco.position }}
                        </div>

                        <p class="mt-1 font-mono text-[11px] font-bold text-amber-600">{{ esco.badge_id }}</p>
                        
                        <div class="mt-1.5 text-[8px] uppercase font-bold text-slate-500 tracking-wider flex flex-col gap-0.5 border-t border-slate-100 pt-1.5">
                            <p><span class="text-slate-400">Jurisdiction:</span> {{ esco.ward ? esco.ward + ' Ward' : esco.lga ? esco.lga + ' LGA' : esco.state ? esco.state + ' State' : 'National Headquarters' }}</p>
                            <p v-if="esco.polling_unit"><span class="text-slate-400">PU:</span> {{ esco.polling_unit }}</p>
                            <p v-if="esco.appointed_at"><span class="text-slate-400">Appointed:</span> {{ esco.appointed_at }}</p>
                        </div>
                    </div>

                    <!-- QR Code -->
                    <div class="mt-auto pt-2 flex w-full flex-col items-center">
                        <div class="rounded-xl border border-slate-200 bg-white p-1.5 shadow-sm">
                            <img :src="`https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(verificationUrl)}`" alt="QR Code" class="size-16 object-contain" />
                        </div>
                        <p class="mt-1 text-[7px] font-bold uppercase tracking-wider text-slate-400">Scan to Verify Executive</p>
                    </div>
                </div>

                <!-- Footer -->
                <div class="relative z-10 bg-slate-950 py-2 text-center text-white">
                    <p class="text-[9px] font-semibold tracking-widest">Property of Shaihiyya Amanar Jagora</p>
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
