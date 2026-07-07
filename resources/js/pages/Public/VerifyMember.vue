<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Award, CheckCircle, Home, MapPin, ShieldCheck, User } from 'lucide-vue-next';

defineProps<{
    member: {
        name: string;
        membership_number: string;
        status: string;
        state?: string;
        lga?: string;
        ward?: string;
        photo_url?: string | null;
        verified_at?: string | null;
    };
}>();
</script>

<template>
    <Head :title="`Verify Membership - ${member.membership_number}`" />

    <div class="min-h-screen bg-slate-50 selection:bg-amber-500 selection:text-white dark:bg-slate-950">
        <!-- Sticky Header -->
        <header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/80 backdrop-blur-md dark:border-slate-800 dark:bg-slate-950/80">
            <div class="mx-auto flex max-w-4xl items-center justify-center px-4 py-4 sm:px-6">
                <Link :href="route('home')" class="flex items-center gap-2.5">
                    <div class="flex size-12 items-center justify-center overflow-hidden rounded-xl bg-white shadow-md shadow-amber-500/10">
                        <img src="/logo.png" alt="Shaihiyya Logo" class="h-full w-full object-cover p-1" />
                    </div>
                    <span class="text-base font-black tracking-tight text-slate-900 dark:text-white">SHAIHIYYA AMANAR JAGORA</span>
                </Link>
            </div>
        </header>

        <div class="flex flex-col items-center justify-center px-4 py-10 sm:px-6">
            <div class="w-full max-w-md">
                <div class="mb-6 text-center">
                    <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">Authenticity Check</h1>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Verifying official membership credentials.</p>
                </div>

                <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-xl dark:border-slate-800 dark:bg-slate-900">
                    <!-- Status Banner -->
                    <div class="bg-gradient-to-r px-6 py-8 text-center text-white"
                        :class="member.status === 'verified' ? 'from-emerald-500 to-emerald-700' : 'from-amber-500 to-amber-700'">
                        <div class="mx-auto mb-3 flex size-16 items-center justify-center rounded-full bg-white/20 backdrop-blur-sm">
                            <ShieldCheck v-if="member.status === 'verified'" class="size-8" />
                            <Award v-else class="size-8" />
                        </div>
                        <h2 class="text-xl font-black uppercase tracking-wider">
                            {{ member.status === 'verified' ? 'Verified Member' : 'Pending Verification' }}
                        </h2>
                        <p class="mt-1 text-sm font-medium text-white/80 opacity-90">
                            {{ member.status === 'verified' ? 'Valid Shaihiyya Amanar Jagora Credentials' : 'Awaiting Coordinator Approval' }}
                        </p>
                    </div>

                    <div class="p-6 sm:p-8">
                        <!-- Profile Photo -->
                        <div class="mx-auto -mt-16 mb-6 size-24 overflow-hidden rounded-2xl border-[4px] border-white bg-slate-100 shadow-md dark:border-slate-900 dark:bg-slate-800">
                            <img v-if="member.photo_url" :src="member.photo_url" :alt="member.name" class="h-full w-full object-cover" />
                            <div v-else class="flex h-full w-full items-center justify-center text-slate-400">
                                <User class="size-10" />
                            </div>
                        </div>

                        <!-- Details -->
                        <div class="space-y-5">
                            <div class="text-center border-b border-slate-100 pb-5 dark:border-slate-800/60">
                                <h3 class="text-xl font-bold text-slate-900 dark:text-white">{{ member.name }}</h3>
                                <p class="mt-1 font-mono text-sm font-bold text-amber-600 dark:text-amber-500">{{ member.membership_number }}</p>
                            </div>

                            <dl class="space-y-4 text-sm">
                                <div class="flex items-start gap-3">
                                    <MapPin class="mt-0.5 size-4 text-slate-400" />
                                    <div>
                                        <dt class="font-medium text-slate-900 dark:text-white">Registered Jurisdiction</dt>
                                        <dd class="mt-1 text-slate-600 dark:text-slate-400">
                                            {{ member.ward }}, {{ member.lga }}<br />
                                            {{ member.state }}
                                        </dd>
                                    </div>
                                </div>

                                <div v-if="member.verified_at" class="flex items-start gap-3">
                                    <CheckCircle class="mt-0.5 size-4 text-emerald-500" />
                                    <div>
                                        <dt class="font-medium text-slate-900 dark:text-white">Verification Date</dt>
                                        <dd class="mt-1 text-slate-600 dark:text-slate-400">{{ member.verified_at }}</dd>
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
