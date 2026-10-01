<script setup lang="ts">
import { ArrowUpRight } from '@lucide/vue';
import {
    SidebarGroup,
    SidebarGroupContent,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { toUrl } from '@/lib/utils';
import type { NavItem } from '@/types';

type Props = {
    items: NavItem[];
    class?: string;
};

defineProps<Props>();
</script>

<template>
    <SidebarGroup
        :class="`group-data-[collapsible=icon]:p-0 ${$props.class || ''}`"
    >
        <SidebarGroupContent>
            <SidebarMenu class="gap-1.5">
                <SidebarMenuItem v-for="item in items" :key="item.title">
                    <SidebarMenuButton
                        as-child
                        :tooltip="item.title"
                        class="group relative h-10 w-full rounded-xl px-2.5 transition-all duration-200 hover:bg-slate-100 dark:hover:bg-slate-800/80"
                    >
                        <a
                            :href="toUrl(item.href)"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="flex w-full items-center justify-between"
                        >
                            <div class="flex items-center gap-2.5">
                                <div
                                    :class="[
                                        'flex size-7 shrink-0 items-center justify-center rounded-lg border shadow-xs transition-all duration-300 group-hover:scale-110',
                                        item.iconBg ||
                                            'border-slate-200 bg-slate-100 dark:border-slate-700 dark:bg-slate-800',
                                        item.iconColor ||
                                            'text-slate-600 dark:text-slate-300',
                                    ]"
                                >
                                    <component
                                        :is="item.icon"
                                        class="size-4 shrink-0 transition-transform duration-300"
                                    />
                                </div>
                                <span
                                    class="truncate text-xs font-medium text-slate-700 transition-colors group-hover:text-slate-900 dark:text-slate-200 dark:group-hover:text-white"
                                >
                                    {{ item.title }}
                                </span>
                            </div>
                            <ArrowUpRight
                                class="ms-1 size-3.5 shrink-0 -translate-x-1 text-slate-400 opacity-0 transition-all duration-200 group-hover:translate-x-0 group-hover:opacity-100 group-data-[collapsible=icon]:hidden"
                            />
                        </a>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarGroupContent>
    </SidebarGroup>
</template>
