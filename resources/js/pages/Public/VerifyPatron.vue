<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Award, CheckCircle, Crown, Home, ShieldCheck, User } from 'lucide-vue-next';

defineProps<{
    patron: {
        id: number;
        name: string;
        title: string;
        category: string;
        badge_id: string;
        is_active: boolean;
        photo_url?: string | null;
    };
}>();
</script>

<template>
    <Head :title="`Verify Royal Leadership - ${patron.badge_id}`" />

    <div class="min-h-screen bg-slate-50 selection:bg-amber-500 selection:text-white dark:bg-slate-950">
        <!-- Sticky Header -->
        <header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/80 backdrop-blur-md dark:border-slate-800 dark:bg-slate-950/80">
            <div class="mx-auto flex max-w-4xl items-center justify-center px-4 py-4 sm:px-6">
                <Link :href="route('home')" class="flex items-center gap-2 sm:gap-2.5">
                    <div class="flex size-10 sm:size-12 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-white shadow-md shadow-amber-500/10">
                        <img src="/logo.png" alt="Shaihiyya Logo" class="h-full w-full object-cover p-1" />
                    </div>
                    <span class="text-sm sm:text-base font-black tracking-tight text-slate-900 dark:text-white">SHAIHIYYA AMANAR JAGORA</span>
                </Link>
            </div>
        </header>

        <div class="flex flex-col items-center justify-center px-4 py-8 sm:px-6 sm:py-10">
            <div class="w-full max-w-md">
                <div class="mb-6 text-center">
                    <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">Royal Leadership Verification</h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Verifying official organizational patronage & advisory status.</p>
                </div>

                <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-xl dark:border-slate-800 dark:bg-slate-900">
                    <!-- Status Banner -->
                    <div class="bg-gradient-to-r px-5 pt-8 pb-16 text-center text-white sm:px-6"
                        :class="patron.is_active ? 'from-amber-700 via-rose-900 to-amber-800' : 'from-rose-600 to-rose-800'">
                        <div class="mx-auto mb-3 flex size-16 items-center justify-center rounded-full bg-white/20 backdrop-blur-sm ring-2 ring-amber-400">
                            <Crown v-if="patron.is_active" class="size-8 text-amber-300" />
                            <Award v-else class="size-8" />
                        </div>
                        <h2 class="text-xl font-black uppercase tracking-wider">
                            {{ patron.is_active ? `Official ${patron.category}` : 'Inactive Dignitary Record' }}
                        </h2>
                        <p class="mt-1.5 text-xs sm:text-sm font-medium text-white/90">
                            {{ patron.is_active ? 'Recognized Member of Shaihiyya Amanar Jagora Leadership Council' : 'Patronage Status Currently Inactive' }}
                        </p>
                    </div>

                    <div class="p-5 sm:p-8">
                        <!-- Profile Photo -->
                        <div class="relative z-10 mx-auto -mt-16 mb-6 size-24 shrink-0 overflow-hidden rounded-2xl border-[4px] border-white bg-slate-100 shadow-md dark:border-slate-900 dark:bg-slate-800">
                            <img v-if="patron.photo_url" :src="patron.photo_url" :alt="patron.name" class="h-full w-full object-cover" />
                            <div v-else class="flex h-full w-full items-center justify-center text-slate-400">
                                <User class="size-10" />
                            </div>
                        </div>

                        <!-- Details -->
                        <div class="space-y-5">
                            <div class="text-center border-b border-slate-100 pb-5 dark:border-slate-800/60">
                                <p class="text-xs font-bold uppercase text-amber-700 dark:text-amber-500">{{ patron.title }}</p>
                                <h3 class="text-xl font-bold text-slate-900 dark:text-white">{{ patron.name }}</h3>
                                <div class="mt-1.5 inline-block rounded-full bg-amber-100 px-3 py-0.5 text-xs font-black uppercase tracking-wider text-amber-900 dark:bg-amber-900/50 dark:text-amber-300">
                                    {{ patron.category }}
                                </div>
                                <p class="mt-2 font-mono text-sm font-bold text-slate-600 dark:text-slate-400">{{ patron.badge_id }}</p>
                            </div>

                            <dl class="space-y-4 text-sm">
                                <div class="flex items-start gap-3">
                                    <Crown class="mt-0.5 size-4 text-amber-500" />
                                    <div>
                                        <dt class="font-medium text-slate-900 dark:text-white">Organization Standing</dt>
                                        <dd class="mt-1 text-slate-600 dark:text-slate-400">
                                            Official {{ patron.category }} of Shaihiyya Amanar Jagora
                                        </dd>
                                    </div>
                                </div>

                                <div class="flex items-start gap-3">
                                    <CheckCircle class="mt-0.5 size-4 text-emerald-500" />
                                    <div>
                                        <dt class="font-medium text-slate-900 dark:text-white">Verification Status</dt>
                                        <dd class="mt-1 font-semibold text-emerald-600 dark:text-emerald-400">
                                            {{ patron.is_active ? 'Active & Verified In Good Standing' : 'Inactive' }}
                                        </dd>
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
