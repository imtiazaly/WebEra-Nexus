<script setup lang="ts">
import { ref, computed } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { index, create, show, edit, destroy } from "@/routes/projects";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Avatar, AvatarFallback } from "@/components/ui/avatar";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import {
    Plus,
    Eye,
    Pencil,
    Trash2,
    Building2,
    Search,
    MoreHorizontal,
    PlayCircle,
    CheckCircle2,
    Clock,
    AlertTriangle,
    LayoutGrid,
    List,
    FolderKanban,
    ChevronLeft,
    ChevronRight,
    Wrench,
    Calendar,
    Briefcase,
} from "@lucide/vue";

defineOptions({
    layout: {
        breadcrumbs: [{ title: "Projects Portal", href: index.url() }],
    },
});

interface Client {
    id: number;
    name: string;
    company_name: string | null;
}

interface Service {
    id: number;
    name: string;
}

interface Project {
    id: number;
    title: string;
    status: string;
    progress: number;
    start_date: string | null;
    deadline: string | null;
    client: Client | null;
    service: Service | null;
    created_at: string;
}

interface PaginatedProjects {
    data: Project[];
    links: any[];
    current_page: number;
    last_page: number;
    total?: number;
}

const props = defineProps<{
    projects: PaginatedProjects;
}>();

// View Mode Toggle (Grid vs Table)
const viewMode = ref<"grid" | "table">("table");

// Search & Filter State
const searchQuery = ref("");
const selectedStatus = ref<string>("all");
const selectedType = ref<string>("all");

// Helper for Initials
const getInitials = (name: string) => {
    if (!name) return "PR";
    const parts = name.trim().split(" ");
    if (parts.length >= 2) {
        return `${parts[0][0]}${parts[1][0]}`.toUpperCase();
    }
    return name.slice(0, 2).toUpperCase();
};

// Avatar Color
const getAvatarColor = (name: string) => {
    const colors = [
        "bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300 border-indigo-200",
        "bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300 border-blue-200",
        "bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-300 border-purple-200",
        "bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300 border-amber-200",
    ];
    let hash = 0;
    for (let i = 0; i < name.length; i++) {
        hash = name.charCodeAt(i) + ((hash << 5) - hash);
    }
    return colors[Math.abs(hash) % colors.length];
};

// Metrics
const totalProjectsCount = computed(() => props.projects.total || props.projects.data.length);
const inProgressCount = computed(() => props.projects.data.filter((p) => p.status === "in_progress").length);
const completedCount = computed(() => props.projects.data.filter((p) => p.status === "completed").length);
const overdueCount = computed(() => {
    const today = new Date().toISOString().split("T")[0];
    return props.projects.data.filter((p) => p.deadline && p.deadline < today && p.status !== "completed").length;
});

// Status Badge Config
const getStatusConfig = (status: string) => {
    switch (status) {
        case "planning":
            return {
                label: "Planning",
                badgeClass: "bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800",
                dotClass: "bg-blue-500",
            };
        case "in_progress":
            return {
                label: "In Progress",
                badgeClass: "bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800",
                dotClass: "bg-amber-500",
            };
        case "under_review":
            return {
                label: "Under Review",
                badgeClass: "bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-800",
                dotClass: "bg-purple-500",
            };
        case "completed":
            return {
                label: "Completed",
                badgeClass: "bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800",
                dotClass: "bg-emerald-500",
            };
        case "on_hold":
            return {
                label: "On Hold",
                badgeClass: "bg-slate-50 text-slate-700 border-slate-200 dark:bg-slate-900 dark:text-slate-300 dark:border-slate-800",
                dotClass: "bg-slate-400",
            };
        case "cancelled":
            return {
                label: "Cancelled",
                badgeClass: "bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800",
                dotClass: "bg-rose-500",
            };
        default:
            return {
                label: status,
                badgeClass: "bg-slate-50 text-slate-700 border-slate-200 dark:bg-slate-900 dark:text-slate-300 dark:border-slate-800",
                dotClass: "bg-slate-400",
            };
    }
};

// Progress Bar Color Helper
const getProgressGradient = (progress: number) => {
    if (progress >= 100) return "bg-emerald-500";
    if (progress >= 50) return "bg-indigo-600";
    if (progress >= 25) return "bg-amber-500";
    return "bg-slate-400";
};

// Filtered Projects List
const filteredProjects = computed(() => {
    return props.projects.data.filter((project) => {
        const matchesStatus = selectedStatus.value === "all" || project.status === selectedStatus.value;
        const matchesType =
            selectedType.value === "all" ||
            (selectedType.value === "client" && project.client !== null) ||
            (selectedType.value === "internal" && project.client === null);

        const query = searchQuery.value.toLowerCase().trim();
        const matchesSearch =
            !query ||
            project.title.toLowerCase().includes(query) ||
            (project.client && project.client.name.toLowerCase().includes(query)) ||
            (project.service && project.service.name.toLowerCase().includes(query));

        return matchesStatus && matchesType && matchesSearch;
    });
});

const deleteProject = (id: number) => {
    if (confirm("Are you sure you want to delete this project?")) {
        router.delete(destroy.url(id));
    }
};
</script>

<template>
    <Head title="Projects Portal" />

    <div class="w-full space-y-6 p-4 sm:p-6">
        <!-- Page Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-slate-200/80 pb-5 dark:border-slate-800">
            <div>
                <div class="flex flex-wrap items-center gap-2.5">
                    <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight text-slate-900 dark:text-slate-50">
                        Projects Portal
                    </h1>
                    <Badge variant="outline" class="rounded-full px-2.5 py-0.5 text-xs font-semibold border-indigo-200 bg-indigo-50/50 text-indigo-700 dark:border-indigo-900 dark:bg-indigo-950/50 dark:text-indigo-300">
                        {{ totalProjectsCount }} Projects
                    </Badge>
                </div>
                <p class="mt-1 text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                    Manage client deliverables, internal practice tasks, and track real-time completion progress.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <Button
                    as-child
                    size="default"
                    class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm font-medium transition-all duration-150"
                >
                    <Link :href="create.url()">
                        <Plus class="mr-1.5 h-4 w-4" /> Create Project / Task
                    </Link>
                </Button>
            </div>
        </div>

        <!-- Metric Stat Cards -->
        <div class="grid grid-cols-1 gap-3 sm:gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="flex items-center justify-between rounded-xl border border-slate-200/80 bg-white p-4 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                <div class="space-y-0.5">
                    <p class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Projects</p>
                    <p class="text-2xl font-extrabold text-slate-900 dark:text-slate-50">{{ totalProjectsCount }}</p>
                </div>
                <div class="rounded-lg bg-slate-100 p-2.5 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                    <FolderKanban class="h-5 w-5" />
                </div>
            </div>

            <div class="flex items-center justify-between rounded-xl border border-slate-200/80 bg-white p-4 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                <div class="space-y-0.5">
                    <p class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">In Progress</p>
                    <p class="text-2xl font-extrabold text-amber-600 dark:text-amber-400">{{ inProgressCount }}</p>
                </div>
                <div class="rounded-lg bg-amber-50 p-2.5 text-amber-600 dark:bg-amber-950/60 dark:text-amber-400">
                    <PlayCircle class="h-5 w-5" />
                </div>
            </div>

            <div class="flex items-center justify-between rounded-xl border border-slate-200/80 bg-white p-4 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                <div class="space-y-0.5">
                    <p class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">Completed</p>
                    <p class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400">{{ completedCount }}</p>
                </div>
                <div class="rounded-lg bg-emerald-50 p-2.5 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400">
                    <CheckCircle2 class="h-5 w-5" />
                </div>
            </div>

            <div class="flex items-center justify-between rounded-xl border border-slate-200/80 bg-white p-4 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                <div class="space-y-0.5">
                    <p class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">Overdue / Urgent</p>
                    <p class="text-2xl font-extrabold text-rose-600 dark:text-rose-400">{{ overdueCount }}</p>
                </div>
                <div class="rounded-lg bg-rose-50 p-2.5 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400">
                    <AlertTriangle class="h-5 w-5" />
                </div>
            </div>
        </div>

        <!-- Toolbar & Main Card -->
        <Card class="overflow-hidden border-slate-200/80 shadow-xs dark:border-slate-800 dark:bg-slate-900">
            <!-- Toolbar -->
            <div class="flex flex-col gap-3 border-b border-slate-200/80 p-4 lg:flex-row lg:items-center lg:justify-between dark:border-slate-800">
                <!-- Left: Search Input & Type Filter -->
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center w-full lg:w-auto">
                    <div class="relative w-full sm:w-72">
                        <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                        <Input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search by project, client, track..."
                            class="pl-9 h-9 text-xs border-slate-200 bg-slate-50/50 focus:bg-white dark:border-slate-800 dark:bg-slate-800/40 dark:focus:bg-slate-900"
                        />
                    </div>
                    <select
                        v-model="selectedType"
                        class="h-9 rounded-md border border-slate-200 bg-white px-3 text-xs text-slate-700 shadow-xs dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300"
                    >
                        <option value="all">All Project Types</option>
                        <option value="client">📁 Client Projects</option>
                        <option value="internal">⚙️ Internal Tasks</option>
                    </select>
                </div>

                <!-- Right: Status Filter Tabs & View Mode Switcher -->
                <div class="flex flex-wrap items-center justify-between gap-3 lg:justify-end">
                    <div class="flex items-center gap-1.5 overflow-x-auto rounded-lg bg-slate-100 p-1 dark:bg-slate-800/70 text-xs font-medium max-w-full">
                        <button
                            @click="selectedStatus = 'all'"
                            :class="[
                                'whitespace-nowrap rounded-md px-3 py-1.5 transition-all',
                                selectedStatus === 'all'
                                    ? 'bg-white text-slate-900 shadow-xs font-semibold dark:bg-slate-900 dark:text-white'
                                    : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200'
                            ]"
                        >
                            All ({{ props.projects.data.length }})
                        </button>
                        <button
                            @click="selectedStatus = 'in_progress'"
                            :class="[
                                'whitespace-nowrap rounded-md px-3 py-1.5 transition-all',
                                selectedStatus === 'in_progress'
                                    ? 'bg-white text-slate-900 shadow-xs font-semibold dark:bg-slate-900 dark:text-white'
                                    : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200'
                            ]"
                        >
                            In Progress
                        </button>
                        <button
                            @click="selectedStatus = 'planning'"
                            :class="[
                                'whitespace-nowrap rounded-md px-3 py-1.5 transition-all',
                                selectedStatus === 'planning'
                                    ? 'bg-white text-slate-900 shadow-xs font-semibold dark:bg-slate-900 dark:text-white'
                                    : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200'
                            ]"
                        >
                            Planning
                        </button>
                        <button
                            @click="selectedStatus = 'completed'"
                            :class="[
                                'whitespace-nowrap rounded-md px-3 py-1.5 transition-all',
                                selectedStatus === 'completed'
                                    ? 'bg-white text-slate-900 shadow-xs font-semibold dark:bg-slate-900 dark:text-white'
                                    : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200'
                            ]"
                        >
                            Completed
                        </button>
                    </div>

                    <!-- Grid vs Table View Switcher -->
                    <div class="hidden sm:flex items-center gap-1 rounded-lg border border-slate-200 p-1 dark:border-slate-800">
                        <button
                            @click="viewMode = 'table'"
                            :class="[
                                'p-1.5 rounded-md transition-all',
                                viewMode === 'table' ? 'bg-slate-200 text-slate-900 dark:bg-slate-800 dark:text-white' : 'text-slate-400 hover:text-slate-600'
                            ]"
                            title="Table View"
                        >
                            <List class="h-4 w-4" />
                        </button>
                        <button
                            @click="viewMode = 'grid'"
                            :class="[
                                'p-1.5 rounded-md transition-all',
                                viewMode === 'grid' ? 'bg-slate-200 text-slate-900 dark:bg-slate-800 dark:text-white' : 'text-slate-400 hover:text-slate-600'
                            ]"
                            title="Grid Cards View"
                        >
                            <LayoutGrid class="h-4 w-4" />
                        </button>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <CardContent class="p-0">
                <!-- Empty State -->
                <div v-if="filteredProjects.length === 0" class="py-16 text-center">
                    <div class="mx-auto flex max-w-xs flex-col items-center justify-center space-y-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800 text-slate-400">
                            <FolderKanban class="h-6 w-6" />
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-900 dark:text-slate-100">No projects found</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                {{ searchQuery ? 'Try adjusting your search query or filters.' : 'Get started by creating your first project or task.' }}
                            </p>
                        </div>
                        <Button v-if="!searchQuery" as-child size="sm" class="mt-2 bg-indigo-600 hover:bg-indigo-700 text-white">
                            <Link :href="create.url()">
                                <Plus class="mr-1 h-3.5 w-3.5" /> Create Project
                            </Link>
                        </Button>
                    </div>
                </div>

                <!-- 1️⃣ GRID CARDS VIEW -->
                <div v-else-if="viewMode === 'grid'" class="p-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="project in filteredProjects"
                        :key="project.id"
                        class="group relative flex flex-col justify-between rounded-xl border border-slate-200/80 bg-white p-5 shadow-2xs hover:shadow-sm transition-all dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div class="space-y-3">
                            <div class="flex items-start justify-between gap-2">
                                <span
                                    :class="[
                                        'inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-semibold',
                                        getStatusConfig(project.status).badgeClass
                                    ]"
                                >
                                    <span :class="['h-1.5 w-1.5 rounded-full', getStatusConfig(project.status).dotClass]"></span>
                                    {{ getStatusConfig(project.status).label }}
                                </span>

                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="ghost" size="icon-sm" class="h-7 w-7 text-slate-400 hover:text-slate-900 dark:hover:text-slate-100">
                                            <MoreHorizontal class="h-4 w-4" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end" class="w-44">
                                        <DropdownMenuItem as-child>
                                            <Link :href="show.url(project.id)" class="flex items-center gap-2 cursor-pointer">
                                                <Eye class="h-4 w-4 text-slate-500" />
                                                <span>View Details</span>
                                            </Link>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem as-child>
                                            <Link :href="edit.url(project.id)" class="flex items-center gap-2 cursor-pointer">
                                                <Pencil class="h-4 w-4 text-slate-500" />
                                                <span>Edit Project</span>
                                            </Link>
                                        </DropdownMenuItem>
                                        <DropdownMenuSeparator />
                                        <DropdownMenuItem @click="deleteProject(project.id)" class="flex items-center gap-2 text-rose-600 focus:text-rose-600 cursor-pointer">
                                            <Trash2 class="h-4 w-4" />
                                            <span>Delete Project</span>
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>

                            <div>
                                <Link
                                    :href="show.url(project.id)"
                                    class="font-bold text-slate-900 hover:text-indigo-600 dark:text-slate-100 dark:hover:text-indigo-400 text-base transition-colors line-clamp-1"
                                >
                                    {{ project.title }}
                                </Link>
                                <p v-if="project.client" class="text-xs font-medium text-slate-500 mt-1 flex items-center gap-1">
                                    <Building2 class="h-3.5 w-3.5 text-slate-400" /> {{ project.client.name }}
                                </p>
                                <p v-else class="text-xs font-medium text-indigo-600 dark:text-indigo-400 mt-1 flex items-center gap-1">
                                    <Wrench class="h-3.5 w-3.5" /> Internal Practice Task
                                </p>
                            </div>

                            <!-- Progress Bar -->
                            <div class="space-y-1.5 pt-2">
                                <div class="flex justify-between text-xs font-medium">
                                    <span class="text-slate-500">Progress</span>
                                    <span class="font-bold text-slate-900 dark:text-slate-100">{{ project.progress }}%</span>
                                </div>
                                <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                    <div
                                        :class="['h-2 rounded-full transition-all duration-300', getProgressGradient(project.progress)]"
                                        :style="{ width: `${project.progress}%` }"
                                    ></div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between border-t border-slate-100 pt-3 mt-4 dark:border-slate-800/80 text-xs text-slate-500">
                            <span v-if="project.service" class="truncate font-medium text-slate-600 dark:text-slate-400">
                                {{ project.service.name }}
                            </span>
                            <span v-else class="text-slate-400 italic">General</span>

                            <span class="flex items-center gap-1 text-[11px] font-mono">
                                <Clock class="h-3 w-3 text-slate-400" /> {{ project.deadline || 'No deadline' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- 2️⃣ ENTERPRISE TABLE VIEW -->
                <div v-else class="w-full">
                    <!-- Desktop Table (md & larger) -->
                    <div class="hidden md:block w-full overflow-x-auto">
                        <table class="w-full min-w-[900px] table-fixed border-collapse text-left">
                            <thead>
                                <tr class="border-b border-slate-200/80 bg-slate-50/70 text-[11px] font-bold tracking-wider text-slate-500 uppercase dark:border-slate-800 dark:bg-slate-800/50 dark:text-slate-400">
                                    <th class="w-[24%] px-5 py-3.5">Project Title</th>
                                    <th class="w-[22%] px-5 py-3.5">Client / Assignment</th>
                                    <th class="w-[16%] px-5 py-3.5">Service Track</th>
                                    <th class="w-[16%] px-5 py-3.5">Completion</th>
                                    <th class="w-[10%] px-5 py-3.5">Status</th>
                                    <th class="w-[12%] px-5 py-3.5">Deadline</th>
                                    <th class="w-[6%] px-5 py-3.5 text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200/80 text-xs text-slate-700 dark:divide-slate-800 dark:text-slate-300">
                                <tr
                                    v-for="project in filteredProjects"
                                    :key="project.id"
                                    class="group transition-colors hover:bg-slate-50/80 dark:hover:bg-slate-800/40"
                                >
                                    <!-- Title -->
                                    <td class="px-5 py-4">
                                        <div class="min-w-0">
                                            <Link
                                                :href="show.url(project.id)"
                                                class="font-extrabold text-slate-900 hover:text-indigo-600 dark:text-slate-100 dark:hover:text-indigo-400 transition-colors truncate block text-sm"
                                            >
                                                {{ project.title }}
                                            </Link>
                                            <p class="text-[11px] text-slate-400 font-mono">#PRJ-{{ project.id }}</p>
                                        </div>
                                    </td>

                                    <!-- Client / Assignment -->
                                    <td class="px-5 py-4">
                                        <div v-if="project.client" class="flex items-center gap-2">
                                            <Avatar class="h-7 w-7 border border-slate-200 dark:border-slate-700">
                                                <AvatarFallback :class="['text-[10px] font-bold', getAvatarColor(project.client.name)]">
                                                    {{ getInitials(project.client.name) }}
                                                </AvatarFallback>
                                            </Avatar>
                                            <div class="min-w-0">
                                                <span class="font-semibold text-slate-800 dark:text-slate-200 truncate block">
                                                    {{ project.client.name }}
                                                </span>
                                            </div>
                                        </div>
                                        <div v-else>
                                            <span class="inline-flex items-center gap-1 rounded-md border border-indigo-200/80 bg-indigo-50/60 px-2.5 py-0.5 text-xs font-semibold text-indigo-700 dark:border-indigo-900 dark:bg-indigo-950/60 dark:text-indigo-300">
                                                ⚙️ Internal Task
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Service Track -->
                                    <td class="px-5 py-4">
                                        <span class="font-medium text-slate-600 dark:text-slate-400">
                                            {{ project.service?.name || '-' }}
                                        </span>
                                    </td>

                                    <!-- Completion Progress Bar -->
                                    <td class="px-5 py-4">
                                        <div class="space-y-1 w-32">
                                            <div class="flex justify-between text-[11px] font-medium">
                                                <span class="text-slate-500">Progress</span>
                                                <span class="font-bold text-slate-900 dark:text-slate-100">{{ project.progress }}%</span>
                                            </div>
                                            <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                                <div
                                                    :class="['h-2 rounded-full transition-all duration-300', getProgressGradient(project.progress)]"
                                                    :style="{ width: `${project.progress}%` }"
                                                ></div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Status Badge -->
                                    <td class="px-5 py-4">
                                        <span
                                            :class="[
                                                'inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-semibold whitespace-nowrap',
                                                getStatusConfig(project.status).badgeClass
                                            ]"
                                        >
                                            <span :class="['h-1.5 w-1.5 rounded-full shrink-0', getStatusConfig(project.status).dotClass]"></span>
                                            {{ getStatusConfig(project.status).label }}
                                        </span>
                                    </td>

                                    <!-- Deadline -->
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-1.5 text-xs text-slate-600 dark:text-slate-400 font-mono">
                                            <Calendar class="h-3.5 w-3.5 text-slate-400" />
                                            <span>{{ project.deadline || 'No deadline' }}</span>
                                        </div>
                                    </td>

                                    <!-- Actions Dropdown -->
                                    <td class="px-5 py-4 text-center">
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button variant="ghost" size="icon-sm" class="h-8 w-8 text-slate-500 hover:text-slate-900 dark:hover:text-slate-100">
                                                    <MoreHorizontal class="h-4 w-4" />
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent align="end" class="w-44">
                                                <DropdownMenuLabel class="text-xs font-semibold text-slate-500">Actions</DropdownMenuLabel>
                                                <DropdownMenuSeparator />
                                                <DropdownMenuItem as-child>
                                                    <Link :href="show.url(project.id)" class="flex items-center gap-2 cursor-pointer">
                                                        <Eye class="h-4 w-4 text-slate-500" />
                                                        <span>View Details</span>
                                                    </Link>
                                                </DropdownMenuItem>
                                                <DropdownMenuItem as-child>
                                                    <Link :href="edit.url(project.id)" class="flex items-center gap-2 cursor-pointer">
                                                        <Pencil class="h-4 w-4 text-slate-500" />
                                                        <span>Edit Project</span>
                                                    </Link>
                                                </DropdownMenuItem>
                                                <DropdownMenuSeparator />
                                                <DropdownMenuItem @click="deleteProject(project.id)" class="flex items-center gap-2 text-rose-600 focus:text-rose-600 cursor-pointer">
                                                    <Trash2 class="h-4 w-4" />
                                                    <span>Delete Project</span>
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile View (Cards for mobile < md) -->
                    <div class="block md:hidden divide-y divide-slate-200 dark:divide-slate-800 p-4 space-y-4">
                        <div
                            v-for="project in filteredProjects"
                            :key="project.id"
                            class="rounded-xl border border-slate-200/80 bg-slate-50/50 p-4 space-y-3 dark:border-slate-800 dark:bg-slate-900/60"
                        >
                            <div class="flex items-center justify-between">
                                <span
                                    :class="[
                                        'inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-semibold',
                                        getStatusConfig(project.status).badgeClass
                                    ]"
                                >
                                    <span :class="['h-1.5 w-1.5 rounded-full', getStatusConfig(project.status).dotClass]"></span>
                                    {{ getStatusConfig(project.status).label }}
                                </span>
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button variant="ghost" size="icon-sm" class="h-8 w-8 text-slate-500">
                                            <MoreHorizontal class="h-4 w-4" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end" class="w-44">
                                        <DropdownMenuItem as-child>
                                            <Link :href="show.url(project.id)" class="flex items-center gap-2">
                                                <Eye class="h-4 w-4 text-slate-500" />
                                                <span>View Details</span>
                                            </Link>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem as-child>
                                            <Link :href="edit.url(project.id)" class="flex items-center gap-2">
                                                <Pencil class="h-4 w-4 text-slate-500" />
                                                <span>Edit Project</span>
                                            </Link>
                                        </DropdownMenuItem>
                                        <DropdownMenuSeparator />
                                        <DropdownMenuItem @click="deleteProject(project.id)" class="flex items-center gap-2 text-rose-600">
                                            <Trash2 class="h-4 w-4" />
                                            <span>Delete Project</span>
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>

                            <div>
                                <Link
                                    :href="show.url(project.id)"
                                    class="font-bold text-slate-900 dark:text-slate-100 hover:text-indigo-600 text-sm"
                                >
                                    {{ project.title }}
                                </Link>
                                <p v-if="project.client" class="text-xs text-slate-500 mt-0.5">
                                    Client: <span class="font-medium text-slate-700 dark:text-slate-300">{{ project.client.name }}</span>
                                </p>
                                <p v-else class="text-xs text-indigo-600 dark:text-indigo-400 mt-0.5 font-medium">
                                    ⚙️ Internal Practice Task
                                </p>
                            </div>

                            <div class="space-y-1 pt-1">
                                <div class="flex justify-between text-xs font-medium">
                                    <span class="text-slate-500">Progress</span>
                                    <span class="font-bold text-slate-900 dark:text-slate-100">{{ project.progress }}%</span>
                                </div>
                                <div class="h-2 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800">
                                    <div
                                        :class="['h-2 rounded-full transition-all duration-300', getProgressGradient(project.progress)]"
                                        :style="{ width: `${project.progress}%` }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Pagination Bar -->
                <div v-if="filteredProjects.length > 0" class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between border-t border-slate-200/80 px-5 py-3 dark:border-slate-800">
                    <p class="text-xs text-slate-500 dark:text-slate-400 text-center sm:text-left">
                        Showing <span class="font-semibold text-slate-700 dark:text-slate-300">1</span> to
                        <span class="font-semibold text-slate-700 dark:text-slate-300">{{ filteredProjects.length }}</span> of
                        <span class="font-semibold text-slate-700 dark:text-slate-300">{{ totalProjectsCount }}</span> projects
                    </p>
                    <div class="flex items-center justify-center gap-2">
                        <Button variant="outline" size="sm" class="h-8 text-xs gap-1" disabled>
                            <ChevronLeft class="h-3.5 w-3.5" /> Previous
                        </Button>
                        <Button variant="outline" size="sm" class="h-8 text-xs gap-1" disabled>
                            Next <ChevronRight class="h-3.5 w-3.5" />
                        </Button>
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>
</template>