<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="relative flex min-h-svh flex-col items-center justify-center overflow-hidden bg-slate-50/80 dark:bg-slate-950 p-6 md:p-10"
    >
        <!-- Background Ambient Glow -->
        <div class="pointer-events-none absolute -top-40 -left-40 size-96 rounded-full bg-purple-500/10 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-40 -right-40 size-96 rounded-full bg-indigo-500/10 blur-3xl"></div>

        <div class="relative z-10 flex w-full max-w-md flex-col gap-6">
            <!-- Brand Header & Logo -->
            <Link
                :href="home()"
                class="group flex items-center justify-center gap-3 self-center transition-transform duration-300 hover:scale-105"
            >
                <div
                    class="flex size-11 items-center justify-center rounded-2xl bg-gradient-to-br from-purple-500/15 via-indigo-500/15 to-slate-900/10 dark:from-purple-500/25 dark:via-indigo-500/25 border border-purple-500/20 dark:border-purple-400/30 shadow-md p-1.5"
                >
                    <AppLogoIcon class="size-7" />
                </div>
                <span class="text-xl font-extrabold tracking-tight text-slate-900 dark:text-white">
                    WebEra<span class="ms-1 font-black bg-gradient-to-r from-purple-600 via-indigo-600 to-violet-600 dark:from-purple-400 dark:via-indigo-400 dark:to-violet-400 bg-clip-text text-transparent">Nexus</span>
                </span>
            </Link>

            <div class="flex flex-col gap-6">
                <Card class="rounded-2xl border-slate-200/80 dark:border-slate-800/80 shadow-xl backdrop-blur-md bg-white/90 dark:bg-slate-900/90">
                    <CardHeader class="px-8 pt-7 pb-2 text-center">
                        <CardTitle class="text-xl font-bold tracking-tight text-slate-900 dark:text-white">{{ title }}</CardTitle>
                        <CardDescription class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                            {{ description }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="px-8 pb-8 pt-4">
                        <slot />
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>
