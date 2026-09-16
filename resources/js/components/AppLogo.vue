<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';

const name = usePage().props.name;
</script>

<template>
    <div
        class="bg-sidebar-primary text-sidebar-primary-foreground flex aspect-square size-10 items-center justify-center rounded"
    >
        <AppLogoIcon class="size-5 fill-current text-white dark:text-black" />
    </div>
    <div class="ml-0.5 grid flex-1 text-left text-xl">
        <span class="truncate leading-tight font-semibold  text-black dark:text-white">WebEra 
            <Span class="font-semibold text-xl text-purple-800 ms-0.8">Nexus</Span>
        </span>
    </div>
</template>
