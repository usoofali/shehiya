<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { Award, ShieldCheck, ArrowLeft } from 'lucide-vue-next';

const page = usePage();
const name = page.props.name || 'Shaihiyya Amanar Jagora';
const quote = page.props.quote;

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="relative grid min-h-dvh flex-col items-center justify-center bg-slate-50 selection:bg-amber-500 selection:text-white dark:bg-slate-950 lg:max-w-none lg:grid-cols-2 lg:px-0">
        <!-- Left Branding Panel (Desktop Only) -->
        <div class="relative hidden h-full flex-col justify-between overflow-hidden bg-slate-950 p-10 text-white lg:flex">
            <!-- Background Gradients & Glows -->
            <div class="absolute inset-0 bg-[radial-gradient(45rem_50rem_at_top,theme(colors.amber.900/35%),theme(colors.slate.950))]"></div>
            <div class="absolute bottom-0 right-0 h-96 w-96 translate-x-1/3 translate-y-1/3 rounded-full bg-emerald-900/20 blur-3xl"></div>

            <!-- Top Header Logo -->
            <div class="relative z-20 flex items-center justify-between">
                <Link :href="route('home')" class="flex items-center gap-3 transition hover:opacity-90">
                    <div class="flex size-16 items-center justify-center overflow-hidden rounded-full bg-white shadow-lg shadow-amber-500/10">
                        <img src="/logo.png" alt="Shaihiyya Logo" class="h-full w-full object-cover p-1" />
                    </div>
                    <div>
                        <span class="block text-lg font-black tracking-tight text-white">SHAIHIYYA AMANAR JAGORA</span>
                        <span class="block text-[11px] font-bold uppercase tracking-widest text-amber-400">Support Organization</span>
                    </div>
                </Link>

                <Link
                    :href="route('home')"
                    class="inline-flex items-center gap-1.5 rounded-xl border border-slate-800 bg-slate-900/80 px-3.5 py-1.5 text-xs font-semibold text-slate-300 backdrop-blur-md transition hover:border-slate-700 hover:text-white"
                >
                    <ArrowLeft class="size-3.5" /> Back to Home
                </Link>
            </div>

            <!-- Center/Bottom Content Card -->
            <div class="relative z-20 my-auto max-w-lg space-y-6">
                <div class="inline-flex items-center gap-2 rounded-full border border-amber-500/30 bg-amber-500/10 px-3.5 py-1.5 text-xs font-bold uppercase tracking-wider text-amber-400 backdrop-blur-md">
                    <ShieldCheck class="size-4" /> Sen. Abdul'aziz Abubakar Yari Support Organization
                </div>

                <h2 class="text-3xl font-black tracking-tight text-white sm:text-4xl leading-tight">
                    A Permanent Grassroots Support Organization Built on Service, Unity &amp; Accountability
                </h2>

                <p class="text-base leading-relaxed text-slate-300">
                    Shaihiyya is a people-centered support organization connecting citizens with leadership through participation,
                    civic engagement, and community development — from the national level down to every polling unit across Nigeria.
                </p>

                <div class="mt-8 rounded-2xl border border-slate-800/80 bg-slate-900/60 p-5 backdrop-blur-md">
                    <blockquote class="space-y-2">
                        <p class="text-sm italic leading-relaxed text-slate-200">&ldquo;{{ quote ? quote.message : 'Our goal is not only to win elections but to build communities, develop leaders, and leave lasting legacies of service and development for the people of Nigeria.' }}&rdquo;</p>
                        <footer class="text-xs font-semibold uppercase tracking-wider text-amber-400">&mdash; {{ quote ? quote.author : "Senator Abdul'aziz Abubakar Yari, Zamfara West" }}</footer>
                    </blockquote>
                </div>

                <!-- Core Values Mini Tags -->
                <div class="flex flex-wrap gap-2 pt-2">
                    <span
                        v-for="val in ['Service', 'Integrity', 'Unity', 'Discipline', 'Accountability', 'Transparency']"
                        :key="val"
                        class="rounded-full border border-amber-500/20 bg-amber-500/10 px-3 py-0.5 text-[10px] font-bold uppercase tracking-wider text-amber-400"
                    >{{ val }}</span>
                </div>
            </div>

            <!-- Footer Note -->
            <div class="relative z-20 flex items-center justify-between text-xs font-medium text-slate-500">
                <span>&copy; {{ new Date().getFullYear() }} Shaihiyya Amanar Jagora</span>
                <span>Secure Authentication</span>
            </div>
        </div>

        <!-- Right Authentication Form Panel -->
        <div class="flex h-full w-full flex-col justify-center p-6 sm:p-10 lg:p-12">
            <!-- Mobile Logo Header -->
            <div class="mb-8 flex items-center justify-between lg:hidden">
                <Link :href="route('home')" class="flex items-center gap-2 sm:gap-2.5">
                    <div class="flex size-9 sm:size-12 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-white shadow-md shadow-amber-500/10">
                        <img src="/logo.png" alt="Shaihiyya Logo" class="h-full w-full object-cover p-1" />
                    </div>
                    <div>
                        <span class="block text-sm sm:text-base font-black tracking-tight text-slate-900 dark:text-white">SHAIHIYYA AMANAR JAGORA</span>
                        <span class="block text-[10px] sm:text-xs font-semibold uppercase tracking-widest text-amber-600 dark:text-amber-400">Support Organization</span>
                    </div>
                </Link>
                <Link :href="route('home')" class="text-xs font-semibold text-amber-600 hover:underline dark:text-amber-400">
                    &larr; Home
                </Link>
            </div>

            <!-- Form Container Card -->
            <div class="mx-auto w-full max-w-[420px] rounded-3xl border border-slate-200/80 bg-white p-6 shadow-xl shadow-slate-200/50 sm:p-8 dark:border-slate-800 dark:bg-slate-900 dark:shadow-none">
                <div class="mb-6 flex flex-col space-y-2 text-center sm:text-left">
                    <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white" v-if="title">{{ title }}</h1>
                    <p class="text-sm leading-relaxed text-slate-500 dark:text-slate-400" v-if="description">{{ description }}</p>
                </div>

                <slot />
            </div>
        </div>
    </div>
</template>
