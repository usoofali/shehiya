<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Award, CheckCircle, Home, MapPin, ShieldCheck, User } from 'lucide-vue-next';

defineProps<{
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
        photo_url?: string | null;
        appointed_at?: string | null;
    };
}>();
</script>

<template>
    <Head :title="`Verify EXCO Official - ${esco.badge_id}`" />

    <div class="min-h-screen bg-slate-50 selection:bg-indigo-500 selection:text-white dark:bg-slate-950">
        <!-- Sticky Header -->
        <header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/80 backdrop-blur-md dark:border-slate-800 dark:bg-slate-950/80">
            <div class="mx-auto flex max-w-4xl items-center justify-center px-4 py-4 sm:px-6">
                <Link :href="route('home')" class="flex items-center gap-2 sm:gap-2.5">
                    <div class="flex size-10 sm:size-12 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-white shadow-md shadow-indigo-500/10">
                        <img src="/logo.png" alt="Shaihiyya Logo" class="h-full w-full object-cover p-1" />
                    </div>
                    <span class="text-sm sm:text-base font-black tracking-tight text-slate-900 dark:text-white">SHAIHIYYA AMANAR JAGORA</span>
                </Link>
            </div>
        </header>

        <div class="flex flex-col items-center justify-center px-4 py-8 sm:px-6 sm:py-10">
            <div class="w-full max-w-md">
                <div class="mb-6 text-center">
                    <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">Executive Authenticity Check</h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Verifying official executive leadership credentials.</p>
                </div>

                <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-xl dark:border-slate-800 dark:bg-slate-900">
                    <!-- Status Banner -->
                    <div class="bg-gradient-to-r px-5 pt-8 pb-16 text-center text-white sm:px-6"
                        :class="esco.status === 'active' ? 'from-indigo-600 via-purple-600 to-indigo-800' : 'from-rose-600 to-rose-800'">
                        <div class="mx-auto mb-3 flex size-16 items-center justify-center rounded-full bg-white/20 backdrop-blur-sm">
                            <ShieldCheck v-if="esco.status === 'active'" class="size-8" />
                            <Award v-else class="size-8" />
                        </div>
                        <h2 class="text-xl font-black uppercase tracking-wider">
                            {{ esco.status === 'active' ? 'Verified Executive Official' : 'Inactive / Revoked Official' }}
                        </h2>
                        <p class="mt-1.5 text-xs sm:text-sm font-medium text-white/90">
                            {{ esco.status === 'active' ? 'Valid Shaihiyya Amanar Jagora Leadership Credentials' : 'Official Appointment Currently Inactive' }}
                        </p>
                    </div>

                    <div class="p-5 sm:p-8">
                        <!-- Profile Photo -->
                        <div class="relative z-10 mx-auto -mt-16 mb-6 size-24 shrink-0 overflow-hidden rounded-2xl border-[4px] border-white bg-slate-100 shadow-md dark:border-slate-900 dark:bg-slate-800">
                            <img v-if="esco.photo_url" :src="esco.photo_url" :alt="esco.name" class="h-full w-full object-cover" />
                            <div v-else class="flex h-full w-full items-center justify-center text-slate-400">
                                <User class="size-10" />
                            </div>
                        </div>

                        <!-- Details -->
                        <div class="space-y-5">
                            <div class="text-center border-b border-slate-100 pb-5 dark:border-slate-800/60">
                                <h3 class="text-xl font-bold text-slate-900 dark:text-white">{{ esco.name }}</h3>
                                <div class="mt-1.5 inline-block rounded-full bg-indigo-100 px-3 py-0.5 text-xs font-bold text-indigo-800 dark:bg-indigo-900/50 dark:text-indigo-300">
                                    {{ esco.position }}
                                </div>
                                <p class="mt-2 font-mono text-sm font-bold text-amber-600 dark:text-amber-500">{{ esco.badge_id }}</p>
                            </div>

                            <dl class="space-y-4 text-sm">
                                <div class="flex items-start gap-3">
                                    <MapPin class="mt-0.5 size-4 text-slate-400" />
                                    <div>
                                        <dt class="font-medium text-slate-900 dark:text-white">Assigned Jurisdiction</dt>
                                        <dd class="mt-1 text-slate-600 dark:text-slate-400">
                                            {{ esco.ward ? esco.ward + ' Ward' : esco.lga ? esco.lga + ' LGA' : esco.state ? esco.state + ' State' : 'National Headquarters' }}
                                            <span v-if="esco.polling_unit" class="block text-xs text-slate-500">PU: {{ esco.polling_unit }}</span>
                                        </dd>
                                    </div>
                                </div>

                                <div v-if="esco.appointed_at" class="flex items-start gap-3">
                                    <CheckCircle class="mt-0.5 size-4 text-indigo-500" />
                                    <div>
                                        <dt class="font-medium text-slate-900 dark:text-white">Appointment Date</dt>
                                        <dd class="mt-1 text-slate-600 dark:text-slate-400">{{ esco.appointed_at }}</dd>
                                    </div>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>

                <div class="mt-8 text-center">
                    <Link :href="route('home')" class="inline-flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-slate-800 dark:hover:text-white">
                        <Home class="size-4" /> Go to Homepage
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
