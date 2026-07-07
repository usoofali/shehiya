<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';

interface Props {
    class?: string;
}

defineProps<Props>();
</script>

<template>
    <div class="flex aspect-square size-11 items-center justify-center overflow-hidden rounded-xl bg-white shadow-md shadow-amber-500/10">
        <img src="/logo.png" alt="Shaihiyya Logo" class="h-full w-full object-cover p-1" />
    </div>
    <div class="ml-1.5 grid flex-1 text-left text-sm leading-tight">
        <span class="truncate font-black tracking-tight text-foreground">SHAIHIYYA AMANAR JAGORA</span>
        <span class="truncate text-[10px] font-extrabold uppercase tracking-widest text-amber-600 dark:text-amber-400">SA Portal</span>
    </div>
</template>
