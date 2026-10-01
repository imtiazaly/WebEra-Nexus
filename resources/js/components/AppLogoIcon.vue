<script setup lang="ts">
import { ref, type HTMLAttributes } from 'vue';
import logoImg from '@/components/assets/logo.png';

defineOptions({
    inheritAttrs: false,
});

type Props = {
    className?: HTMLAttributes['class'];
};

const props = defineProps<Props>();
const hasError = ref(false);

const handleError = () => {
    hasError.value = true;
};
</script>

<template>
    <div
        :class="[
            'relative flex items-center justify-center overflow-hidden shrink-0 select-none',
            props.className,
            $attrs.class,
        ]"
    >
        <img
            v-if="!hasError"
            :src="logoImg"
            alt="WebEra Nexus Logo"
            class="h-full w-full object-contain transition-transform duration-300 group-hover:scale-105"
            @error="handleError"
        />
        <div
            v-else
            class="flex h-full w-full items-center justify-center rounded-lg bg-linear-to-br from-purple-700 via-indigo-700 to-blue-700 text-white font-black text-xs shadow-inner"
        >
            WN
        </div>
    </div>
</template>
