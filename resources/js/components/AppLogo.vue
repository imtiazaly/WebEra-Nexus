<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
</script>

<template>
    <div
        class="relative flex aspect-square size-10 items-center justify-center rounded-xl bg-linear-to-br from-purple-500/10 via-indigo-500/10 to-slate-900/10 dark:from-purple-500/20 dark:via-indigo-500/20 dark:to-blue-500/20 border border-purple-500/20 dark:border-purple-400/30 shadow-sm shadow-purple-500/10 transition-all duration-300 group-hover:scale-105 group-hover:shadow-purple-500/20 group-hover:border-purple-500/40"
    >
        <AppLogoIcon class="size-7 p-0.5" />
    </div>
    <div class="ml-2.5 grid flex-1 text-left leading-none">
        <div class="flex items-center">
            <span class="truncate font-extrabold text-lg text-slate-900 dark:text-white tracking-tight">
                WebEra<span class="ms-1 font-black text-lg bg-linear-to-r from-purple-600 via-indigo-600 to-violet-600 dark:from-purple-400 dark:via-indigo-400 dark:to-violet-400 bg-clip-text text-transparent">Nexus</span>
            </span>
        </div>
        <div class="flex items-center gap-1.5 mt-1">
            <span class="size-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
            <span class="text-[10px] font-semibold tracking-widest text-slate-500 dark:text-slate-400 uppercase">ENTERPRISE</span>
        </div>
    </div>
</template>
