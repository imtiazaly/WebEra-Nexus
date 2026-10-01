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
        class="relative flex min-h-svh flex-col items-center justify-center overflow-hidden bg-slate-50/80 p-6 md:p-10 dark:bg-slate-950"
    >
        <!-- Background Ambient Glow -->
        <div
            class="pointer-events-none absolute -top-40 -left-40 size-96 rounded-full bg-purple-500/10 blur-3xl"
        ></div>
        <div
            class="pointer-events-none absolute -right-40 -bottom-40 size-96 rounded-full bg-indigo-500/10 blur-3xl"
        ></div>

        <div class="relative z-10 flex w-full max-w-md flex-col gap-6">
            <!-- Brand Header & Logo -->
            <Link
                :href="home()"
                class="group flex items-center justify-center gap-3 self-center transition-transform duration-300 hover:scale-105"
            >
                <div
                    class="flex size-11 items-center justify-center rounded-2xl border border-purple-500/20 bg-gradient-to-br from-purple-500/15 via-indigo-500/15 to-slate-900/10 p-1.5 shadow-md dark:border-purple-400/30 dark:from-purple-500/25 dark:via-indigo-500/25"
                >
                    <AppLogoIcon class="size-7" />
                </div>
                <span
                    class="text-xl font-extrabold tracking-tight text-slate-900 dark:text-white"
                >
                    WebEra<span
                        class="ms-1 bg-gradient-to-r from-purple-600 via-indigo-600 to-violet-600 bg-clip-text font-black text-transparent dark:from-purple-400 dark:via-indigo-400 dark:to-violet-400"
                        >Nexus</span
                    >
                </span>
            </Link>

            <div class="flex flex-col gap-6">
                <Card
                    class="rounded-2xl border-slate-200/80 bg-white/90 shadow-xl backdrop-blur-md dark:border-slate-800/80 dark:bg-slate-900/90"
                >
                    <CardHeader class="px-8 pt-7 pb-2 text-center">
                        <CardTitle
                            class="text-xl font-bold tracking-tight text-slate-900 dark:text-white"
                            >{{ title }}</CardTitle
                        >
                        <CardDescription
                            class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                        >
                            {{ description }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="px-8 pt-4 pb-8">
                        <slot />
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>
