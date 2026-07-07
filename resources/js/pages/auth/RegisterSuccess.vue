<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Award, CheckCircle, Home, Users } from 'lucide-vue-next';
</script>

<template>
    <Head title="Registration Successful — Shaihiyya Amanar Jagora" />

    <div class="flex min-h-screen items-center justify-center bg-slate-50 px-4 dark:bg-slate-950">
        <div class="w-full max-w-md text-center">
            <!-- Logo -->
            <Link :href="route('home')" class="inline-flex items-center gap-2 mb-8">
                <div class="flex size-10 items-center justify-center rounded-xl bg-gradient-to-tr from-amber-600 to-emerald-600 text-white shadow-md">
                    <Award class="size-5" />
                </div>
                <span class="text-base font-black tracking-tight text-slate-900 dark:text-white">SHAIHIYYA AMANAR JAGORA</span>
            </Link>

            <!-- Success Card -->
            <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-xl dark:border-slate-800 dark:bg-slate-900">
                <div class="mx-auto mb-6 flex size-20 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900/40">
                    <CheckCircle class="size-10 text-emerald-600 dark:text-emerald-400" />
                </div>

                <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">Registration Submitted!</h1>
                <p class="mt-3 text-sm leading-relaxed text-slate-500 dark:text-slate-400">
                    Thank you for joining the Shaihiyya Amanar Jagora. Your registration has been received and is now pending verification by your ward coordinator.
                </p>

                <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-left dark:border-amber-900/40 dark:bg-amber-950/30">
                    <p class="text-xs font-semibold text-amber-800 dark:text-amber-300">What happens next?</p>
                    <ul class="mt-2 space-y-1 text-xs text-amber-700 dark:text-amber-400">
                        <li>• Your ward coordinator will review your submission</li>
                        <li>• You will receive your official membership number upon verification</li>
                        <li>• You may be contacted on the phone number you provided</li>
                    </ul>
                </div>

                <div class="mt-8 flex flex-col gap-3">
                    <Link
                        :href="route('home')"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-amber-600 to-emerald-600 px-6 py-3 text-sm font-bold text-white shadow-md transition hover:from-amber-500 hover:to-emerald-500"
                    >
                        <Home class="size-4" /> Return to Home
                    </Link>
                    <Link
                        :href="route('register')"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 px-6 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                    >
                        <Users class="size-4" /> Register Another Member
                    </Link>
                </div>
            </div>

            <p class="mt-6 text-xs text-slate-400">
                Are you a coordinator?
                <Link :href="route('login')" class="font-semibold text-amber-600 hover:underline dark:text-amber-400">Sign in here</Link>
            </p>
        </div>
    </div>
</template>
