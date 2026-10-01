<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="relative flex min-h-svh flex-col items-center justify-center overflow-hidden bg-slate-50/80 p-6 selection:bg-purple-500 selection:text-white md:p-10 dark:bg-slate-950"
    >
        <!-- Background Ambient Glow Effects -->
        <div
            class="pointer-events-none absolute -top-40 -left-40 size-96 rounded-full bg-purple-500/10 blur-3xl dark:bg-purple-600/15"
        ></div>
        <div
            class="pointer-events-none absolute -right-40 -bottom-40 size-96 rounded-full bg-indigo-500/10 blur-3xl dark:bg-indigo-600/15"
        ></div>

        <div class="relative z-10 w-full max-w-md">
            <div class="flex flex-col gap-6">
                <!-- Brand Header & Logo -->
                <div class="flex flex-col items-center gap-3 text-center">
                    <Link
                        :href="home()"
                        class="group flex items-center gap-3 transition-transform duration-300 hover:scale-105"
                    >
                        <div
                            class="flex size-12 items-center justify-center rounded-2xl border border-purple-500/20 bg-linear-to-br from-purple-500/15 via-indigo-500/15 to-slate-900/10 p-1.5 shadow-md shadow-purple-500/10 dark:border-purple-400/30 dark:from-purple-500/25 dark:via-indigo-500/25 dark:to-blue-500/25"
                        >
                            <AppLogoIcon class="size-8" />
                        </div>
                        <div class="text-left leading-none">
                            <span
                                class="text-xl font-extrabold tracking-tight text-slate-900 dark:text-white"
                            >
                                WebEra<span
                                    class="ms-1 bg-linear-to-r from-purple-600 via-indigo-600 to-violet-600 bg-clip-text font-black text-transparent dark:from-purple-400 dark:via-indigo-400 dark:to-violet-400"
                                    >Nexus</span
                                >
                            </span>
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
                    </Link>

                    <div
                        v-if="title || description"
                        class="mt-2 space-y-1 text-center"
                    >
                        <h1
                            v-if="title"
                            class="text-xl font-bold tracking-tight text-slate-900 dark:text-white"
                        >
                            {{ title }}
                        </h1>
                        <p
                            v-if="description"
                            class="mx-auto max-w-xs text-xs text-slate-500 dark:text-slate-400"
                        >
                            {{ description }}
                        </p>
                    </div>
                </div>

                <!-- Main Form Card Container -->
                <div
                    class="rounded-2xl border border-slate-200/80 bg-white/90 p-7 shadow-xl shadow-slate-900/5 backdrop-blur-md transition-all duration-300 md:p-8 dark:border-slate-800/80 dark:bg-slate-900/90"
                >
                    <slot />
                </div>

                <!-- Footer copyright / subtle tagline -->
                <div
                    class="text-center text-xs text-slate-400 dark:text-slate-500"
                >
                    &copy; {{ new Date().getFullYear() }} WebEra Solutions. All
                    rights reserved.
                </div>
            </div>
        </div>
    </div>
</template>
