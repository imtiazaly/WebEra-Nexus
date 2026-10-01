<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    Briefcase,
    GraduationCap,
    LayoutGrid,
    UserCheck,
    Users,
    Globe,
} from '@lucide/vue';
import AppLogo from '@/components/AppLogo.vue';
import LinkedInIcon from '@/components/icons/LinkedInIcon.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { index as clientsIndex } from '@/routes/clients';
import { index as projectsIndex } from '@/routes/projects';
import { index as internshipsIndex } from '@/routes/internships';
import { index as studentsIndex } from '@/routes/students';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Clients & Leads',
        href: clientsIndex(),
        icon: Users,
    },
    {
        title: 'Projects Portal',
        href: projectsIndex(),
        icon: Briefcase,
    },
    {
        title: 'Internship Batches',
        href: internshipsIndex(),
        icon: GraduationCap,
    },
    {
        title: 'Students & Reports',
        href: studentsIndex(),
        icon: UserCheck,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'LinkedIn',
        href: 'https://www.linkedin.com/company/webera-solutions',
        icon: LinkedInIcon,
        iconBg: 'bg-[#0A66C2]/10 dark:bg-[#0A66C2]/20 border-[#0A66C2]/25 group-hover:bg-[#0A66C2] group-hover:border-[#0A66C2]',
        iconColor: 'text-[#0A66C2] dark:text-[#389ce0] group-hover:text-white',
    },
    {
        title: 'WebEra Solutions',
        href: 'https://weberasolutionspk.com/',
        icon: Globe,
        iconBg: 'bg-emerald-500/10 dark:bg-emerald-500/20 border-emerald-500/25 group-hover:from-emerald-500 group-hover:to-teal-600 group-hover:border-emerald-500',
        iconColor:
            'text-emerald-600 dark:text-emerald-400 group-hover:text-white',
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton
                        size="lg"
                        as-child
                        class="group rounded-xl p-1.5 transition-all duration-200 hover:bg-slate-100/80 dark:hover:bg-slate-800/80"
                    >
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
