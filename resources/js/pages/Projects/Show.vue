<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { index, edit, aiSummary } from '@/routes/projects';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import {
    ArrowLeft,
    Pencil,
    Sparkles,
    Wand2,
    Loader2,
    Calendar,
    Clock,
    Briefcase,
    Building2,
    Wrench,
    CheckCircle2,
    FolderKanban,
    FileText,
    TrendingUp,
    Target,
} from '@lucide/vue';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Projects Portal', href: index.url() },
            { title: 'Project Details', href: '#' },
        ],
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
    description: string | null;
    status: string;
    progress: number;
    start_date: string | null;
    deadline: string | null;
    ai_status_summary: string | null;
    client: Client | null;
    service: Service | null;
    created_at: string;
}

const props = defineProps<{
    project: Project;
}>();

const generatingSummary = ref(false);

const generateSummary = () => {
    generatingSummary.value = true;
    router.post(
        aiSummary.url(props.project.id),
        {},
        {
            onFinish: () => {
                generatingSummary.value = false;
            },
        },
    );
};

// Helper for Initials
const getInitials = (name: string) => {
    if (!name) return 'PR';
    const parts = name.trim().split(' ');
    if (parts.length >= 2) {
        return `${parts[0][0]}${parts[1][0]}`.toUpperCase();
    }
    return name.slice(0, 2).toUpperCase();
};

// Format Date
const formatDate = (dateStr: string | null) => {
    if (!dateStr) return 'Not Scheduled';
    return new Date(dateStr).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

// Check if overdue
const isOverdue = computed(() => {
    if (!props.project.deadline || props.project.status === 'completed') return false;
    const today = new Date().toISOString().split('T')[0];
    return props.project.deadline < today;
});

// Progress Milestone Text
const milestoneLabel = computed(() => {
    const p = props.project.progress;
    if (p >= 100) return '🎉 Fully Completed!';
    if (p >= 75) return '🎯 Near Completion';
    if (p >= 50) return '🚀 Halfway Milestone';
    if (p >= 25) return '⚡ Under Development';
    if (p > 0) return '🌱 Initializing Phase';
    return '📋 Not Started Yet';
});

// Status Badge Config
const getStatusConfig = (status: string) => {
    switch (status) {
        case 'planning':
            return {
                label: 'Planning',
                badgeClass: 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/50 dark:text-blue-300 dark:border-blue-800',
                dotClass: 'bg-blue-500',
            };
        case 'in_progress':
            return {
                label: 'In Progress',
                badgeClass: 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/50 dark:text-amber-300 dark:border-amber-800',
                dotClass: 'bg-amber-500',
            };
        case 'under_review':
            return {
                label: 'Under Review',
                badgeClass: 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/50 dark:text-purple-300 dark:border-purple-800',
                dotClass: 'bg-purple-500',
            };
        case 'completed':
            return {
                label: 'Completed',
                badgeClass: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-300 dark:border-emerald-800',
                dotClass: 'bg-emerald-500',
            };
        case 'on_hold':
            return {
                label: 'On Hold',
                badgeClass: 'bg-slate-50 text-slate-700 border-slate-200 dark:bg-slate-900 dark:text-slate-300 dark:border-slate-800',
                dotClass: 'bg-slate-400',
            };
        case 'cancelled':
            return {
                label: 'Cancelled',
                badgeClass: 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/50 dark:text-rose-300 dark:border-rose-800',
                dotClass: 'bg-rose-500',
            };
        default:
            return {
                label: status,
                badgeClass: 'bg-slate-50 text-slate-700 border-slate-200 dark:bg-slate-900 dark:text-slate-300 dark:border-slate-800',
                dotClass: 'bg-slate-400',
            };
    }
};
</script>

<template>
    <Head :title="`${project.title} — Command Center`" />

    <div class="w-full space-y-6 p-4 sm:p-6">
        <!-- Top Navigation Bar -->
        <div class="flex items-center justify-between">
            <Button variant="ghost" size="sm" as-child class="-ml-2 text-slate-500 hover:text-slate-900 dark:hover:text-slate-100">
                <Link :href="index.url()">
                    <ArrowLeft class="mr-1.5 h-4 w-4" /> Back to Projects Portal
                </Link>
            </Button>
            <Button as-child size="sm" class="bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm gap-1.5 text-sm">
                <Link :href="edit.url(project.id)">
                    <Pencil class="h-4 w-4" /> Edit Project
                </Link>
            </Button>
        </div>

        <!-- 🌟 Executive Hero Header Card -->
        <Card class="overflow-hidden border-slate-200/80 shadow-xs dark:border-slate-800 dark:bg-slate-900">
            <CardContent class="p-6">
                <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                    <div class="flex items-start gap-4">
                        <Avatar class="h-16 w-16 border-2 border-indigo-200 shadow-sm dark:border-indigo-900 shrink-0">
                            <AvatarFallback class="bg-gradient-to-br from-indigo-600 to-purple-700 text-white text-lg font-extrabold">
                                {{ getInitials(project.title) }}
                            </AvatarFallback>
                        </Avatar>
                        <div class="space-y-2 min-w-0">
                            <div class="flex flex-wrap items-center gap-3">
                                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-slate-900 dark:text-slate-50">
                                    {{ project.title }}
                                </h1>
                                <span
                                    :class="[
                                        'inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-sm font-semibold',
                                        getStatusConfig(project.status).badgeClass
                                    ]"
                                >
                                    <span :class="['h-2 w-2 rounded-full', getStatusConfig(project.status).dotClass]"></span>
                                    {{ getStatusConfig(project.status).label }}
                                </span>
                            </div>

                            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-slate-500 dark:text-slate-400">
                                <!-- Client Info -->
                                <span v-if="project.client" class="flex items-center gap-1.5 font-semibold text-slate-800 dark:text-slate-200">
                                    <Building2 class="h-4 w-4 text-slate-400" />
                                    Client: {{ project.client.name }}
                                </span>
                                <span v-else class="inline-flex items-center gap-1.5 font-semibold text-indigo-600 dark:text-indigo-400">
                                    <Wrench class="h-4 w-4" /> Internal Practice Task
                                </span>

                                <span>•</span>
                                <span class="font-mono text-slate-500">ID: #PRJ-{{ project.id }}</span>
                                <span>•</span>
                                <span class="flex items-center gap-1.5 text-slate-500">
                                    <Calendar class="h-4 w-4 text-slate-400" />
                                    Created {{ formatDate(project.created_at) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- 4 Stat Glass Matrix Cards -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Service Category -->
            <div class="flex items-center justify-between rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                <div class="space-y-1 min-w-0 pr-2">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Service Category</p>
                    <p class="text-base font-bold text-slate-900 dark:text-slate-100 truncate">
                        {{ project.service?.name || 'General Project' }}
                    </p>
                </div>
                <div class="rounded-lg bg-indigo-50 p-3 text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-400 shrink-0">
                    <Briefcase class="h-6 w-6" />
                </div>
            </div>

            <!-- Start Date -->
            <div class="flex items-center justify-between rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                <div class="space-y-1">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Start Date</p>
                    <p class="text-sm font-bold text-slate-900 dark:text-slate-100 font-mono">
                        {{ formatDate(project.start_date) }}
                    </p>
                </div>
                <div class="rounded-lg bg-blue-50 p-3 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400 shrink-0">
                    <Calendar class="h-6 w-6" />
                </div>
            </div>

            <!-- Target Deadline -->
            <div class="flex items-center justify-between rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                <div class="space-y-1">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Target Deadline</p>
                    <div class="flex items-center gap-1.5">
                        <p class="text-sm font-bold text-slate-900 dark:text-slate-100 font-mono">
                            {{ formatDate(project.deadline) }}
                        </p>
                        <Badge v-if="isOverdue" variant="destructive" class="text-xs py-0.5 px-2">Overdue</Badge>
                    </div>
                </div>
                <div :class="['rounded-lg p-3 shrink-0', isOverdue ? 'bg-rose-50 text-rose-600 dark:bg-rose-950/60' : 'bg-amber-50 text-amber-600 dark:bg-amber-950/60']">
                    <Clock class="h-6 w-6" />
                </div>
            </div>

            <!-- Milestone Status -->
            <div class="flex items-center justify-between rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                <div class="space-y-1">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Current Milestone</p>
                    <p class="text-sm font-extrabold text-indigo-600 dark:text-indigo-400">
                        {{ milestoneLabel }}
                    </p>
                </div>
                <div class="rounded-lg bg-emerald-50 p-3 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400 shrink-0">
                    <Target class="h-6 w-6" />
                </div>
            </div>
        </div>

        <!-- 📈 Interactive Progress Hero Tracker Card -->
        <Card class="border-slate-200/80 shadow-xs dark:border-slate-800 dark:bg-slate-900">
            <CardContent class="p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <TrendingUp class="h-5 w-5 text-indigo-600 dark:text-indigo-400" />
                        <h2 class="text-lg font-bold text-slate-900 dark:text-slate-100">
                            Execution Progress & Completion Status
                        </h2>
                    </div>
                    <span class="text-4xl font-black text-indigo-600 dark:text-indigo-400 font-mono">
                        {{ project.progress }}%
                    </span>
                </div>

                <!-- Gradient Animated Progress Bar -->
                <div class="space-y-2">
                    <div class="h-4 w-full overflow-hidden rounded-full bg-slate-100 p-0.5 shadow-inner dark:bg-slate-800">
                        <div
                            class="h-3 rounded-full bg-gradient-to-r from-indigo-500 via-purple-500 to-emerald-500 transition-all duration-700 shadow-sm"
                            :style="{ width: `${project.progress}%` }"
                        ></div>
                    </div>
                    <div class="flex justify-between text-xs font-mono text-slate-500">
                        <span>0% Initial Launch</span>
                        <span>50% Mid-Execution</span>
                        <span>100% Final Deliverable</span>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- 🪄 MAGICAL AI Executive Status Summary Card -->
        <Card class="overflow-hidden border-purple-200/80 bg-gradient-to-br from-purple-50/40 via-white to-indigo-50/20 shadow-xs dark:border-purple-900/50 dark:from-purple-950/20 dark:via-slate-900 dark:to-indigo-950/10">
            <CardHeader class="border-b border-purple-100 pb-4 dark:border-purple-900/40">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-tr from-purple-600 to-indigo-600 text-white shadow-xs">
                            <Wand2 class="h-5 w-5 text-purple-200 animate-pulse" />
                        </div>
                        <div>
                            <CardTitle class="text-lg font-extrabold text-purple-950 dark:text-purple-200 flex items-center gap-2">
                                AI Project Status Summary
                                <Sparkles class="h-4 w-4 text-purple-500 animate-spin" />
                            </CardTitle>
                            <CardDescription class="text-xs sm:text-sm text-purple-700/70 dark:text-purple-400/80">
                                AI-synthesized health report, milestone status, and risk evaluation.
                            </CardDescription>
                        </div>
                    </div>
                    <Button
                        @click="generateSummary"
                        :disabled="generatingSummary"
                        size="sm"
                        class="bg-purple-600 hover:bg-purple-700 text-white font-medium shadow-sm text-sm gap-1.5"
                    >
                        <Loader2 v-if="generatingSummary" class="h-4 w-4 animate-spin" />
                        <Wand2 v-else class="h-4 w-4" />
                        <span>{{ generatingSummary ? 'Generating AI Summary...' : project.ai_status_summary ? 'Regenerate AI Summary' : 'Generate AI Summary' }}</span>
                    </Button>
                </div>
            </CardHeader>
            <CardContent class="p-6">
                <div v-if="project.ai_status_summary" class="relative rounded-lg border border-purple-200/60 bg-white/80 p-5 text-sm sm:text-base leading-relaxed text-purple-950 shadow-2xs dark:border-purple-900/40 dark:bg-purple-950/40 dark:text-purple-200 whitespace-pre-line">
                    {{ project.ai_status_summary }}
                </div>
                <div v-else class="flex flex-col items-center justify-center py-6 text-center">
                    <Wand2 class="h-8 w-8 text-purple-300 dark:text-purple-800 mb-2" />
                    <p class="text-sm font-semibold text-purple-900 dark:text-purple-300">No AI Executive Summary generated yet.</p>
                    <p class="text-xs text-purple-600/70 dark:text-purple-400/70 mt-0.5">Click "Generate AI Summary" to create a magical status overview for this project.</p>
                </div>
            </CardContent>
        </Card>

        <!-- 📜 Description & Guidelines Blueprint Room Card -->
        <Card v-if="project.description" class="border-slate-200/80 shadow-xs dark:border-slate-800 dark:bg-slate-900">
            <CardHeader class="border-b border-slate-200/80 pb-4 dark:border-slate-800">
                <div class="flex items-center gap-2 text-indigo-600 dark:text-indigo-400">
                    <FileText class="h-5 w-5" />
                    <CardTitle class="text-lg font-bold text-slate-900 dark:text-slate-100">
                        Project Scope & Operational Guidelines
                    </CardTitle>
                </div>
            </CardHeader>
            <CardContent class="p-6">
                <div class="rounded-xl border border-slate-200/80 bg-slate-50/50 p-5 text-sm sm:text-base leading-relaxed text-slate-800 whitespace-pre-line dark:border-slate-800 dark:bg-slate-900/60 dark:text-slate-200 font-sans">
                    {{ project.description }}
                </div>
            </CardContent>
        </Card>
    </div>
</template>