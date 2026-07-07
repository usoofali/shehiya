<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Award, ChevronLeft, Calendar, User, MapPin } from 'lucide-vue-next';

defineProps<{
    announcement: {
        id: number;
        title: string;
        content: string;
        type: string;
        target_level: string;
        created_at: string;
        image_path?: string;
        published_by?: { name: string };
        state?: { name: string };
        lga?: { name: string };
        ward?: { name: string };
    };
}>();
</script>

<template>
    <Head :title="announcement.title + ' — Shaihiyya Announcements'" />

    <div class="min-h-screen bg-slate-50 dark:bg-slate-950">
        <!-- Header -->
        <header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/80 backdrop-blur-md dark:border-slate-800 dark:bg-slate-950/80">
            <div class="mx-auto flex max-w-4xl items-center justify-between px-4 py-4 sm:px-6">
                <Link :href="route('home')" class="flex items-center gap-2.5">
                    <div class="flex size-12 items-center justify-center overflow-hidden rounded-xl bg-white shadow-md shadow-amber-500/10">
                        <img src="/logo.png" alt="Shaihiyya Logo" class="h-full w-full object-cover p-1" />
                    </div>
                    <span class="text-base font-black tracking-tight text-slate-900 dark:text-white">SHAIHIYYA AMANAR JAGORA</span>
                </Link>
                <Link :href="route('home')" class="inline-flex items-center gap-1.5 text-sm font-semibold text-slate-500 hover:text-slate-900 dark:hover:text-white">
                    <ChevronLeft class="size-4" /> Back to Home
                </Link>
            </div>
        </header>

        <main class="mx-auto max-w-3xl px-4 py-12 sm:px-6">
            <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-lg dark:border-slate-800 dark:bg-slate-900">
                <div v-if="announcement.image_path" class="aspect-[21/9] w-full overflow-hidden border-b border-slate-200 dark:border-slate-800">
                    <img :src="`/storage/${announcement.image_path}`" alt="Announcement Image" class="h-full w-full object-cover" />
                </div>
                
                <div class="p-6 sm:p-10">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center rounded-full bg-amber-100 px-3 py-1 text-xs font-bold uppercase tracking-wider text-amber-800 dark:bg-amber-900/30 dark:text-amber-400">
                            {{ announcement.type }}
                        </span>
                        <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                            <MapPin class="size-3" />
                            <span class="uppercase">{{ announcement.target_level }}</span>
                            <span v-if="announcement.state"> • {{ announcement.state.name }}</span>
                            <span v-if="announcement.lga"> • {{ announcement.lga.name }}</span>
                            <span v-if="announcement.ward"> • {{ announcement.ward.name }}</span>
                        </span>
                    </div>

                    <h1 class="mt-6 text-3xl font-black tracking-tight text-slate-900 dark:text-white sm:text-4xl">
                        {{ announcement.title }}
                    </h1>

                    <div class="mt-6 flex flex-wrap items-center gap-6 border-y border-slate-100 py-4 text-sm text-slate-500 dark:border-slate-800">
                        <div class="flex items-center gap-2">
                            <Calendar class="size-4" />
                            {{ new Date(announcement.created_at).toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}
                        </div>
                        <div class="flex items-center gap-2">
                            <User class="size-4" />
                            Published by {{ announcement.published_by?.name || 'Administrator' }}
                        </div>
                    </div>

                    <div class="prose prose-slate mt-8 max-w-none prose-p:leading-relaxed prose-a:text-amber-600 dark:prose-invert dark:prose-a:text-amber-400 sm:text-lg">
                        <p class="whitespace-pre-line">{{ announcement.content }}</p>
                    </div>
                </div>
            </article>
        </main>
    </div>
</template>
