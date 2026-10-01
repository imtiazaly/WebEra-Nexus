<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
</script>

<template>
    <div
        class="relative flex aspect-square size-10 items-center justify-center rounded-xl border border-purple-500/20 bg-linear-to-br from-purple-500/10 via-indigo-500/10 to-slate-900/10 shadow-sm shadow-purple-500/10 transition-all duration-300 group-hover:scale-105 group-hover:border-purple-500/40 group-hover:shadow-purple-500/20 dark:border-purple-400/30 dark:from-purple-500/20 dark:via-indigo-500/20 dark:to-blue-500/20"
    >
        <AppLogoIcon class="size-7 p-0.5" />
    </div>
    <div class="ml-2.5 grid flex-1 text-left leading-none">
        <div class="flex items-center">
            <span
                class="truncate text-lg font-extrabold tracking-tight text-slate-900 dark:text-white"
            >
                WebEra<span
                    class="ms-1 bg-linear-to-r from-purple-600 via-indigo-600 to-violet-600 bg-clip-text text-lg font-black text-transparent dark:from-purple-400 dark:via-indigo-400 dark:to-violet-400"
                    >Nexus</span
                >
            </span>
        </div>
        <div class="mt-1 flex items-center gap-1.5">
            <span
                class="size-1.5 animate-pulse rounded-full bg-emerald-500"
            ></span>
            <span
                class="text-[10px] font-semibold tracking-widest text-slate-500 uppercase dark:text-slate-400"
                >ENTERPRISE</span
            >
        </div>
    </div>
</template>
