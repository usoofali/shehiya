<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Award, CheckCircle, ChevronRight, Home, LoaderCircle, Printer, Search, ShieldCheck, XCircle } from 'lucide-vue-next';

const form = useForm({
    phone: '',
});

const page = usePage();

const submit = () => {
    form.post(route('status.check'), {
        preserveScroll: true,
    });
};

const copied = ref(false);

const getAppUrl = () => {
    return window.location.origin;
};

const copyReferralLink = async (link: string) => {
    try {
        await navigator.clipboard.writeText(link);
        copied.value = true;
        setTimeout(() => { copied.value = false; }, 2000);
    } catch (err) {
        console.error('Failed to copy: ', err);
    }
};
</script>

<template>
    <Head title="Check Membership Status — Shaihiyya Amanar Jagora" />

    <div class="min-h-screen bg-slate-50 selection:bg-amber-500 selection:text-white dark:bg-slate-950">
        <!-- Sticky Header -->
        <header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/80 backdrop-blur-md dark:border-slate-800 dark:bg-slate-950/80">
            <div class="mx-auto flex max-w-4xl items-center justify-between px-4 py-4 sm:px-6">
                <Link :href="route('home')" class="flex items-center gap-2.5">
                    <div class="flex size-12 items-center justify-center overflow-hidden rounded-xl bg-white shadow-md shadow-amber-500/10">
                        <img src="/logo.png" alt="Shaihiyya Logo" class="h-full w-full object-cover p-1" />
                    </div>
                    <span class="text-base font-black tracking-tight text-slate-900 dark:text-white">SHAIHIYYA AMANAR JAGORA</span>
                </Link>
                <Link :href="route('register')" class="inline-flex items-center gap-1.5 text-sm font-semibold text-amber-600 hover:underline dark:text-amber-400">
                    Join Now <ChevronRight class="size-3.5" />
                </Link>
            </div>
        </header>

        <!-- Main Content -->
        <div class="flex flex-col items-center justify-center px-4 py-16 sm:px-6">
            <div class="w-full max-w-md">
                <div class="mb-8 text-center">
                    <h1 class="text-3xl font-black tracking-tight text-slate-900 dark:text-white">Membership Status</h1>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Enter your registered phone number to check your status or print your ID badge.</p>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-xl dark:border-slate-800 dark:bg-slate-900 sm:p-8">
                    <!-- Search Form -->
                    <form @submit.prevent="submit" class="flex flex-col gap-4">
                        <div>
                            <label class="sr-only" for="phone">Phone Number</label>
                            <div class="relative">
                                <Search class="absolute left-3 top-3 size-5 text-slate-400" />
                                <input
                                    id="phone"
                                    v-model="form.phone"
                                    type="tel"
                                    required
                                    placeholder="e.g., 08012345678"
                                    class="w-full rounded-xl border border-slate-300 bg-slate-50 py-3 pl-10 pr-4 text-sm focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                />
                            </div>
                            <p v-if="form.errors.phone" class="mt-1 text-xs text-rose-500">{{ form.errors.phone }}</p>
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-slate-900 py-3 text-sm font-bold text-white transition hover:bg-slate-800 disabled:opacity-70 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-white"
                        >
                            <LoaderCircle v-if="form.processing" class="size-4 animate-spin" />
                            Check Status
                        </button>
                    </form>

                    <!-- Error Alert -->
                    <div v-if="$page.props.flash?.error" class="mt-6 rounded-xl border border-rose-200 bg-rose-50 p-4 text-rose-800 dark:border-rose-900/50 dark:bg-rose-900/20 dark:text-rose-300">
                        <div class="flex items-start gap-3">
                            <XCircle class="size-5 shrink-0 text-rose-500" />
                            <p class="text-sm font-medium">{{ $page.props.flash.error }}</p>
                        </div>
                    </div>

                    <!-- Result Card -->
                    <div v-if="$page.props.flash?.memberData" class="mt-8 rounded-2xl border border-slate-100 bg-slate-50 p-5 dark:border-slate-800 dark:bg-slate-800/50">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-bold text-slate-900 dark:text-white">Record Found</h3>
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-bold uppercase tracking-wider"
                                :class="{
                                    'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300': $page.props.flash.memberData.status === 'pending',
                                    'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300': $page.props.flash.memberData.status === 'verified',
                                    'bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-300': $page.props.flash.memberData.status === 'rejected'
                                }"
                            >
                                <span class="relative flex size-2">
                                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full opacity-75"
                                        :class="{
                                            'bg-amber-500': $page.props.flash.memberData.status === 'pending',
                                            'bg-emerald-500': $page.props.flash.memberData.status === 'verified',
                                            'bg-rose-500': $page.props.flash.memberData.status === 'rejected'
                                        }"></span>
                                    <span class="relative inline-flex size-2 rounded-full"
                                        :class="{
                                            'bg-amber-500': $page.props.flash.memberData.status === 'pending',
                                            'bg-emerald-500': $page.props.flash.memberData.status === 'verified',
                                            'bg-rose-500': $page.props.flash.memberData.status === 'rejected'
                                        }"></span>
                                </span>
                                {{ $page.props.flash.memberData.status }}
                            </span>
                        </div>

                        <dl class="grid grid-cols-2 gap-x-4 gap-y-4 text-sm">
                            <div>
                                <dt class="text-xs font-medium text-slate-500 dark:text-slate-400">Name</dt>
                                <dd class="mt-1 font-semibold text-slate-900 dark:text-white">{{ $page.props.flash.memberData.first_name }} {{ $page.props.flash.memberData.last_name }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-slate-500 dark:text-slate-400">Membership No.</dt>
                                <dd class="mt-1 font-mono font-semibold text-slate-900 dark:text-white">{{ $page.props.flash.memberData.membership_number }}</dd>
                            </div>
                        </dl>

                        <div class="mt-6">
                            <template v-if="$page.props.flash.memberData.status !== 'verified'">
                                <div class="mb-4 rounded-xl bg-amber-100/50 p-3 text-xs text-amber-800 dark:bg-amber-900/20 dark:text-amber-300">
                                    <p>Your registration is currently awaiting verification. Your ID badge will be marked as NOT VERIFIED.</p>
                                    <div v-if="$page.props.flash.memberData.coordinator_name" class="mt-2 border-t border-amber-200/50 pt-2 dark:border-amber-900/50">
                                        <p class="font-bold">Contact Your Ward Coordinator for Verification:</p>
                                        <p>Name: {{ $page.props.flash.memberData.coordinator_name }}</p>
                                        <p v-if="$page.props.flash.memberData.coordinator_phone">Email: {{ $page.props.flash.memberData.coordinator_phone }}</p>
                                    </div>
                                </div>
                            </template>
                            
                            <Link
                                :href="route('badge.show', $page.props.flash.memberData.membership_number)"
                                class="flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-amber-600 to-emerald-600 py-3 text-sm font-bold text-white shadow-md transition hover:from-amber-500 hover:to-emerald-500"
                            >
                                <Printer class="size-4" /> View / Print ID Badge
                            </Link>
                        </div>

                        <!-- Referral Program -->
                        <div class="mt-6 border-t border-slate-200 pt-6 dark:border-slate-800">
                            <h4 class="flex items-center gap-2 text-sm font-bold text-slate-900 dark:text-white">
                                <Award class="size-4 text-amber-500" />
                                Your Referral Program
                            </h4>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Invite others to join the movement using your unique link.</p>
                            
                            <div class="mt-4 grid grid-cols-3 gap-3">
                                <div class="rounded-xl border border-slate-200 bg-white p-3 dark:border-slate-800 dark:bg-slate-900">
                                    <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Invited</span>
                                    <span class="mt-1 block text-lg font-black text-slate-900 dark:text-white">{{ $page.props.flash.memberData.referrals_count ?? 0 }}</span>
                                </div>
                                <div class="rounded-xl border border-slate-200 bg-white p-3 dark:border-slate-800 dark:bg-slate-900">
                                    <span class="block text-[10px] font-bold uppercase tracking-wider text-emerald-500">Verified</span>
                                    <span class="mt-1 block text-lg font-black text-emerald-600 dark:text-emerald-400">{{ $page.props.flash.memberData.verified_referrals_count ?? 0 }}</span>
                                </div>
                                <div class="rounded-xl border border-slate-200 bg-white p-3 dark:border-slate-800 dark:bg-slate-900">
                                    <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Current Badge</span>
                                    <span class="mt-1 block text-sm font-bold leading-tight"
                                        :class="{
                                            'text-amber-700 dark:text-amber-500': $page.props.flash.memberData.referral_badge === 'Bronze Ambassador',
                                            'text-slate-500 dark:text-slate-300': $page.props.flash.memberData.referral_badge === 'Silver Veteran',
                                            'text-yellow-600 dark:text-yellow-400': $page.props.flash.memberData.referral_badge === 'Gold Master',
                                            'text-indigo-500 dark:text-indigo-400': $page.props.flash.memberData.referral_badge === 'Platinum Grandmaster',
                                            'text-slate-900 dark:text-white': $page.props.flash.memberData.referral_badge === 'None'
                                        }"
                                    >
                                        {{ $page.props.flash.memberData.referral_badge ?? 'None' }}
                                    </span>
                                </div>
                            </div>
                            <p class="mt-2 text-[10px] text-slate-400 dark:text-slate-500">
                                * Badges are awarded based strictly on the number of <strong class="text-emerald-500">Verified</strong> referrals.
                            </p>

                            <div class="mt-4">
                                <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Your Referral Link</label>
                                <div class="mt-1 flex items-center gap-2">
                                    <input 
                                        type="text" 
                                        readonly 
                                        :value="`${getAppUrl()}/register?ref=${$page.props.flash.memberData.phone ? $page.props.flash.memberData.phone.slice(-8) : ''}`" 
                                        class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-xs font-mono text-slate-600 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-400"
                                        id="referral-link"
                                    />
                                    <button 
                                        @click="copyReferralLink(`${getAppUrl()}/register?ref=${$page.props.flash.memberData.phone ? $page.props.flash.memberData.phone.slice(-8) : ''}`)" 
                                        class="shrink-0 rounded-lg bg-amber-100 px-3 py-2 text-xs font-bold text-amber-800 transition hover:bg-amber-200 dark:bg-amber-900/50 dark:text-amber-300 dark:hover:bg-amber-900"
                                    >
                                        {{ copied ? 'Copied!' : 'Copy' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-8 text-center">
                    <Link :href="route('home')" class="text-sm font-medium text-slate-500 hover:text-slate-800 dark:hover:text-white">
                        ← Back to Home
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
