<script setup lang="ts">
import { Link } from "@inertiajs/vue3";
import {
    BookOpen,
    Briefcase,
    FolderGit2,
    GraduationCap,
    LayoutGrid,
    UserCheck,
    Users,
    Globe,
} from "@lucide/vue";
import AppLogo from "@/components/AppLogo.vue";
import NavFooter from "@/components/NavFooter.vue";
import NavMain from "@/components/NavMain.vue";
import NavUser from "@/components/NavUser.vue";
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from "@/components/ui/sidebar";
import { dashboard } from "@/routes";
import { index as clientsIndex } from "@/routes/clients";
import { index as projectsIndex } from "@/routes/projects";
import { index as internshipsIndex } from "@/routes/internships";
import { index as studentsIndex } from "@/routes/students";
import type { NavItem } from "@/types";

const mainNavItems: NavItem[] = [
    {
        title: "Dashboard",
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: "Clients & Leads",
        href: clientsIndex(),
        icon: Users,
    },
    {
        title: "Projects Portal",
        href: projectsIndex(),
        icon: Briefcase,
    },
    {
        title: "Internship Batches",
        href: internshipsIndex(),
        icon: GraduationCap,
    },
    {
        title: "Students & Reports",
        href: studentsIndex(),
        icon: UserCheck,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: "Documentation",
        href: "https://laravel.com/docs/starter-kits#vue",
        icon: BookOpen,
    },
    {
        title: "WebEra Solutions",
        href: "https://weberasolutionspk.com/",
        icon: Globe,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
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
