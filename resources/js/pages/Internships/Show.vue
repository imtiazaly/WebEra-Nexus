<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { index, edit } from '@/routes/internships';
import { show as showStudent, create as createStudent } from '@/routes/students';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import {
    GraduationCap,
    Pencil,
    ArrowLeft,
    Search,
    Calendar,
    Award,
    TrendingUp,
    FileText,
    Clock,
    UserPlus,
    BookOpen,
    CheckCircle2,
    Check,
    Copy,
    Sparkles,
    BarChart2,
    Users,
} from '@lucide/vue';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Internship Academy Portal', href: index.url() },
            { title: 'Cohort Telemetry Hub', href: '#' },
        ],
    },
});

interface Service {
    id: number;
    name: string;
}

interface Student {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    status: string;
    overall_progress: number;
    weekly_reports_count?: number;
}

interface Internship {
    id: number;
    name: string;
    batch_no: string;
    start_date: string;
    end_date: string;
    status: string;
    service: Service | null;
    students?: Student[];
    created_at: string;
}

const props = defineProps<{
    internship: Internship;
}>();

// State
const searchQuery = ref('');
const activeTab = ref<'roster' | 'curriculum' | 'performance'>('roster');
const copiedBatchCode = ref(false);

// Helper for Initials
const getInitials = (name: string) => {
    if (!name) return 'ST';
    const parts = name.trim().split(' ');
    if (parts.length >= 2) {
        return `${parts[0][0]}${parts[1][0]}`.toUpperCase();
    }
    return name.slice(0, 2).toUpperCase();
};

const getAvatarColor = (name: string) => {
    const colors = [
        'bg-indigo-500/20 text-indigo-400 border-indigo-500/30',
        'bg-cyan-500/20 text-cyan-400 border-cyan-500/30',
        'bg-purple-500/20 text-purple-400 border-purple-500/30',
        'bg-emerald-500/20 text-emerald-400 border-emerald-500/30',
        'bg-amber-500/20 text-amber-400 border-amber-500/30',
    ];
    let hash = 0;
    for (let i = 0; i < name.length; i++) {
        hash = name.charCodeAt(i) + ((hash << 5) - hash);
    }
    return colors[Math.abs(hash) % colors.length];
};

const formatDate = (dateStr: string) => {
    if (!dateStr) return 'N/A';
    try {
        const d = new Date(dateStr);
        return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    } catch {
        return dateStr;
    }
};

const getTimelineProgress = (startDateStr: string, endDateStr: string, status: string) => {
    if (status === 'completed') return 100;
    if (status === 'upcoming') return 0;
    try {
        const start = new Date(startDateStr).getTime();
        const end = new Date(endDateStr).getTime();
        const now = new Date().getTime();
        if (isNaN(start) || isNaN(end) || end <= start) return 50;
        if (now <= start) return 0;
        if (now >= end) return 100;
        return Math.min(100, Math.max(0, Math.round(((now - start) / (end - start)) * 100)));
    } catch {
        return 50;
    }
};

const copyCode = () => {
    navigator.clipboard.writeText(props.internship.batch_no);
    copiedBatchCode.value = true;
    setTimeout(() => {
        copiedBatchCode.value = false;
    }, 2000);
};

// Status Badges
const getStatusConfig = (status: string) => {
    switch (status) {
        case 'active':
            return {
                label: 'Active Cohort',
                badgeClass: 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/30',
                dotClass: 'bg-emerald-500 animate-pulse',
            };
        case 'upcoming':
            return {
                label: 'Upcoming Batch',
                badgeClass: 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border-indigo-500/30',
                dotClass: 'bg-indigo-500',
            };
        case 'completed':
            return {
                label: 'Graduated / Completed',
                badgeClass: 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border-purple-500/30',
                dotClass: 'bg-purple-500',
            };
        default:
            return {
                label: status,
                badgeClass: 'bg-slate-500/10 text-slate-600 dark:text-slate-400 border-slate-500/30',
                dotClass: 'bg-slate-400',
            };
    }
};

const getStudentStatusBadge = (status: string) => {
    switch (status) {
        case 'active':
            return 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/30';
        case 'completed':
            return 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border-purple-500/30';
        case 'dropped':
            return 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/30';
        default:
            return 'bg-slate-500/10 text-slate-600 dark:text-slate-400 border-slate-500/30';
    }
};

// Computed Stats
const studentsList = computed(() => props.internship.students || []);

const activeStudentsCount = computed(() => {
    return studentsList.value.filter((s) => s.status === 'active').length;
});

const cohortAvgProgress = computed(() => {
    if (studentsList.value.length === 0) return 0;
    const sum = studentsList.value.reduce((acc, s) => acc + (s.overall_progress || 0), 0);
    return Math.round(sum / studentsList.value.length);
});

const totalWeeklyReportsCount = computed(() => {
    return studentsList.value.reduce((acc, s) => acc + (s.weekly_reports_count || 0), 0);
});

// Performance Distribution Groups
const topPerformers = computed(() => studentsList.value.filter((s) => s.overall_progress >= 75));
const onTrackStudents = computed(() => studentsList.value.filter((s) => s.overall_progress >= 30 && s.overall_progress < 75));
const needsSupportStudents = computed(() => studentsList.value.filter((s) => s.overall_progress < 30));

// Filtered Trainees
const filteredStudents = computed(() => {
    const query = searchQuery.value.toLowerCase().trim();
    if (!query) return studentsList.value;
    return studentsList.value.filter(
        (s) =>
            s.name.toLowerCase().includes(query) ||
            s.email.toLowerCase().includes(query) ||
            (s.phone && s.phone.includes(query))
    );
});
</script>

<template>
    <Head :title="`${internship.name} • Cohort Hub`" />

    <div class="w-full space-y-8 p-4 sm:p-6 lg:p-8">
        <!-- ⚡ HERO CYBER BANNER -->
        <div
            class="relative overflow-hidden rounded-3xl border border-indigo-500/20 bg-gradient-to-br from-indigo-950 via-slate-900 to-purple-950 p-6 sm:p-8 text-white shadow-2xl backdrop-blur-xl"
        >
            <div class="pointer-events-none absolute -top-20 -right-20 h-80 w-80 rounded-full bg-indigo-500/20 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-20 -left-20 h-80 w-80 rounded-full bg-purple-500/20 blur-3xl"></div>

            <div class="relative z-10 space-y-6">
                <!-- Top Navigation & Action Controls -->
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <Button
                        as-child
                        variant="outline"
                        size="sm"
                        class="rounded-xl border-indigo-400/30 bg-slate-900/60 text-indigo-300 hover:bg-slate-800 hover:text-white"
                    >
                        <Link :href="index.url()">
                            <ArrowLeft class="mr-1.5 h-4 w-4" /> Back to Internship Hub
                        </Link>
                    </Button>

                    <div class="flex items-center gap-3">
                        <Button
                            as-child
                            variant="outline"
                            size="sm"
                            class="rounded-xl border-slate-700 bg-slate-900/60 font-semibold text-slate-200 hover:bg-slate-800 hover:text-white"
                        >
                            <Link :href="edit.url(internship.id)">
                                <Pencil class="mr-1.5 h-3.5 w-3.5 text-indigo-400" /> Edit Batch
                            </Link>
                        </Button>

                        <Button
                            as-child
                            size="sm"
                            class="rounded-xl bg-indigo-600 font-bold text-white shadow-lg hover:bg-indigo-500"
                        >
                            <Link :href="createStudent.url()">
                                <UserPlus class="mr-1.5 h-4 w-4" /> Register New Trainee
                            </Link>
                        </Button>
                    </div>
                </div>

                <!-- Main Batch Info Header -->
                <div class="space-y-3">
                    <div class="flex flex-wrap items-center gap-3">
                        <!-- Hologram Code Box -->
                        <button
                            @click="copyCode"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-indigo-400/30 bg-indigo-500/10 px-3 py-1 font-mono text-xs font-bold text-indigo-300 backdrop-blur-md transition-all hover:bg-indigo-500/20"
                            title="Copy Batch Code"
                        >
                            <span>🏷️ {{ internship.batch_no }}</span>
                            <Check v-if="copiedBatchCode" class="h-3.5 w-3.5 text-emerald-400" />
                            <Copy v-else class="h-3.5 w-3.5 text-indigo-400" />
                        </button>

                        <!-- Status Badge -->
                        <span
                            :class="[
                                'inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-bold',
                                getStatusConfig(internship.status).badgeClass,
                            ]"
                        >
                            <span :class="['h-2 w-2 rounded-full', getStatusConfig(internship.status).dotClass]"></span>
                            {{ getStatusConfig(internship.status).label }}
                        </span>

                        <!-- Track Tag -->
                        <Badge
                            variant="secondary"
                            class="rounded-lg border border-purple-400/30 bg-purple-500/10 px-3 py-1 text-xs font-semibold text-purple-300"
                        >
                            Track: {{ internship.service?.name || 'General Training' }}
                        </Badge>
                    </div>

                    <h1 class="text-2xl font-black tracking-tight text-white sm:text-4xl">
                        {{ internship.name }}
                    </h1>
                </div>

                <!-- Timeline Bar inside Hero -->
                <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-4 space-y-2">
                    <div class="flex items-center justify-between text-xs font-semibold">
                        <span class="flex items-center gap-2 text-slate-300">
                            <Clock class="h-4 w-4 text-indigo-400" />
                            <span>Batch Duration Progress</span>
                        </span>
                        <span class="font-mono text-indigo-400 font-bold">
                            {{ getTimelineProgress(internship.start_date, internship.end_date, internship.status) }}% Duration Elapsed
                        </span>
                    </div>
                    <div class="h-2.5 w-full bg-slate-800 rounded-full overflow-hidden border border-slate-700">
                        <div
                            class="h-full bg-gradient-to-r from-indigo-500 via-cyan-500 to-emerald-500 rounded-full transition-all duration-500"
                            :style="{ width: `${getTimelineProgress(internship.start_date, internship.end_date, internship.status)}%` }"
                        ></div>
                    </div>
                    <div class="flex items-center justify-between text-xs text-slate-400 font-medium">
                        <span>Started: <strong>{{ formatDate(internship.start_date) }}</strong></span>
                        <span>Target Completion: <strong>{{ formatDate(internship.end_date) }}</strong></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 📊 TELEMETRY WIDGET PODS -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase">Enrolled Interns</span>
                    <div class="rounded-xl bg-indigo-50 p-2.5 text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-400">
                        <GraduationCap class="h-5 w-5" />
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-black text-slate-900 dark:text-white">{{ studentsList.length }}</span>
                    <span class="text-xs text-slate-500">Trainees</span>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase">Active Trainees Ratio</span>
                    <div class="rounded-xl bg-emerald-50 p-2.5 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400">
                        <CheckCircle2 class="h-5 w-5" />
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-black text-emerald-600 dark:text-emerald-400">{{ activeStudentsCount }}</span>
                    <span class="text-xs text-slate-500">/ {{ studentsList.length }} Active</span>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase">Cohort Avg Progress</span>
                    <div class="rounded-xl bg-cyan-50 p-2.5 text-cyan-600 dark:bg-cyan-950/60 dark:text-cyan-400">
                        <TrendingUp class="h-5 w-5" />
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-black text-slate-900 dark:text-white">{{ cohortAvgProgress }}%</span>
                    <span class="text-xs text-slate-500">Score</span>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-slate-500 uppercase">Weekly Reports</span>
                    <div class="rounded-xl bg-purple-50 p-2.5 text-purple-600 dark:bg-purple-950/60 dark:text-purple-400">
                        <FileText class="h-5 w-5" />
                    </div>
                </div>
                <div class="mt-3 flex items-baseline gap-2">
                    <span class="text-3xl font-black text-purple-600 dark:text-purple-400">{{ totalWeeklyReportsCount }}</span>
                    <span class="text-xs text-slate-500">Submissions</span>
                </div>
            </div>
        </div>

        <!-- 📑 INTERACTIVE TABS HUB -->
        <div class="space-y-6">
            <div class="flex border-b border-slate-200 text-sm font-semibold dark:border-slate-800">
                <button
                    @click="activeTab = 'roster'"
                    :class="[
                        'flex items-center gap-2 border-b-2 px-5 py-3 transition-colors',
                        activeTab === 'roster'
                            ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400'
                            : 'border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200',
                    ]"
                >
                    <Users class="h-4 w-4" />
                    <span>Enrolled Trainees Roster ({{ studentsList.length }})</span>
                </button>
                <button
                    @click="activeTab = 'curriculum'"
                    :class="[
                        'flex items-center gap-2 border-b-2 px-5 py-3 transition-colors',
                        activeTab === 'curriculum'
                            ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400'
                            : 'border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200',
                    ]"
                >
                    <BookOpen class="h-4 w-4" />
                    <span>Track Curriculum & Timeline</span>
                </button>
                <button
                    @click="activeTab = 'performance'"
                    :class="[
                        'flex items-center gap-2 border-b-2 px-5 py-3 transition-colors',
                        activeTab === 'performance'
                            ? 'border-indigo-600 text-indigo-600 dark:border-indigo-400 dark:text-indigo-400'
                            : 'border-transparent text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200',
                    ]"
                >
                    <BarChart2 class="h-4 w-4" />
                    <span>Cohort Performance Tiers</span>
                </button>
            </div>

            <!-- TAB 1: TRAINEE ROSTER -->
            <div v-if="activeTab === 'roster'" class="space-y-4">
                <!-- Toolbar -->
                <div class="flex items-center justify-between gap-4">
                    <div class="relative w-full max-w-xs">
                        <Search class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-slate-400" />
                        <Input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Filter trainees by name, email..."
                            class="h-9 border-slate-200 pl-9 text-xs dark:border-slate-800 dark:bg-slate-900"
                        />
                    </div>
                </div>

                <!-- Roster Table Card -->
                <Card class="overflow-hidden border-slate-200/80 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <CardContent class="p-0">
                        <div v-if="filteredStudents.length === 0" class="py-12 text-center text-slate-500 dark:text-slate-400">
                            No enrolled trainees match your filter.
                        </div>
                        <div v-else class="overflow-x-auto">
                            <table class="w-full border-collapse text-left text-xs">
                                <thead>
                                    <tr class="border-b border-slate-200/80 bg-slate-50/70 font-bold text-slate-500 uppercase dark:border-slate-800 dark:bg-slate-800/50 dark:text-slate-400">
                                        <th class="px-5 py-3.5">Trainee Name</th>
                                        <th class="px-5 py-3.5">Contact Details</th>
                                        <th class="px-5 py-3.5">Overall Progress</th>
                                        <th class="px-5 py-3.5">Weekly Reports</th>
                                        <th class="px-5 py-3.5">Status</th>
                                        <th class="px-5 py-3.5 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200/80 text-slate-700 dark:divide-slate-800 dark:text-slate-300">
                                    <tr
                                        v-for="student in filteredStudents"
                                        :key="student.id"
                                        class="transition-colors hover:bg-slate-50/80 dark:hover:bg-slate-800/40"
                                    >
                                        <td class="px-5 py-4">
                                            <div class="flex items-center gap-3">
                                                <Avatar class="h-9 w-9 shrink-0 border border-slate-200 dark:border-slate-700">
                                                    <AvatarFallback :class="['text-xs font-bold', getAvatarColor(student.name)]">
                                                        {{ getInitials(student.name) }}
                                                    </AvatarFallback>
                                                </Avatar>
                                                <div>
                                                    <Link
                                                        :href="showStudent(student.id).url"
                                                        class="font-bold text-slate-900 hover:text-indigo-600 dark:text-slate-100 dark:hover:text-indigo-400"
                                                    >
                                                        {{ student.name }}
                                                    </Link>
                                                    <p class="font-mono text-[10px] text-slate-400">
                                                        #STD-{{ student.id }}
                                                    </p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-4">
                                            <div class="space-y-0.5">
                                                <p class="font-medium text-slate-800 dark:text-slate-200">{{ student.email }}</p>
                                                <p v-if="student.phone" class="text-[11px] text-slate-400">{{ student.phone }}</p>
                                            </div>
                                        </td>
                                        <td class="px-5 py-4">
                                            <div class="w-36 space-y-1">
                                                <div class="flex justify-between text-[11px] font-bold">
                                                    <span class="text-slate-600 dark:text-slate-400">Progress</span>
                                                    <span class="text-indigo-600 dark:text-indigo-400">{{ student.overall_progress }}%</span>
                                                </div>
                                                <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                                    <div
                                                        class="h-full rounded-full bg-indigo-600"
                                                        :style="{ width: `${student.overall_progress}%` }"
                                                    ></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-5 py-4">
                                            <Badge variant="outline" class="rounded-lg border-slate-200 bg-slate-50 font-semibold dark:border-slate-700 dark:bg-slate-800">
                                                📝 {{ student.weekly_reports_count || 0 }} Reports
                                            </Badge>
                                        </td>
                                        <td class="px-5 py-4">
                                            <span
                                                :class="[
                                                    'inline-flex items-center gap-1 rounded-full border px-2.5 py-0.5 text-[11px] font-bold capitalize',
                                                    getStudentStatusBadge(student.status),
                                                ]"
                                            >
                                                {{ student.status }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-4 text-right">
                                            <Button as-child size="sm" variant="ghost" class="font-bold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">
                                                <Link :href="showStudent(student.id).url">
                                                    View Profile →
                                                </Link>
                                            </Button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- TAB 2: CURRICULUM TIMELINE -->
            <div v-else-if="activeTab === 'curriculum'" class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="space-y-6">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">
                            6-Week {{ internship.service?.name || 'Internship' }} Milestone Roadmap
                        </h3>
                        <p class="text-xs text-slate-500">
                            Standard training syllabus execution plan for this batch track.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                        <div v-for="week in 6" :key="week" class="rounded-2xl border border-slate-200/80 bg-slate-50/60 p-4 space-y-2 dark:border-slate-800 dark:bg-slate-800/40">
                            <div class="flex items-center justify-between">
                                <span class="rounded-md bg-indigo-500/20 px-2 py-0.5 text-xs font-bold text-indigo-600 dark:text-indigo-400">
                                    Week 0{{ week }}
                                </span>
                                <CheckCircle2 v-if="week <= Math.ceil((getTimelineProgress(internship.start_date, internship.end_date, internship.status) / 100) * 6)" class="h-4 w-4 text-emerald-500" />
                                <Clock v-else class="h-4 w-4 text-slate-400" />
                            </div>
                            <h4 class="text-xs font-bold text-slate-900 dark:text-slate-100">
                                {{ week === 1 ? 'Orientation & Fundamentals' : week === 2 ? 'Core Component Engineering' : week === 3 ? 'API Integration & State Management' : week === 4 ? 'Database Optimization & Testing' : week === 5 ? 'Capstone Project Development' : 'Code Review & Graduation Prep' }}
                            </h4>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                Trainees work on practical hands-on tasks and submit weekly progress logs.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 3: PERFORMANCE TIERS -->
            <div v-else class="grid grid-cols-1 gap-6 md:grid-cols-3">
                <!-- Top Performers -->
                <div class="space-y-4 rounded-3xl border border-emerald-500/30 bg-emerald-500/5 p-5 dark:bg-emerald-950/20">
                    <div class="flex items-center justify-between border-b border-emerald-500/20 pb-3">
                        <h4 class="text-sm font-bold text-emerald-700 dark:text-emerald-400 flex items-center gap-1.5">
                            <Award class="h-4 w-4" /> Top Performers (>75%)
                        </h4>
                        <span class="rounded-full bg-emerald-500/20 px-2.5 py-0.5 text-xs font-bold text-emerald-600 dark:text-emerald-400">
                            {{ topPerformers.length }}
                        </span>
                    </div>
                    <div v-if="topPerformers.length === 0" class="text-xs text-slate-400 italic">No trainees in this tier yet.</div>
                    <div v-else class="space-y-2">
                        <div v-for="s in topPerformers" :key="s.id" class="flex items-center justify-between rounded-xl bg-white p-3 text-xs shadow-xs dark:bg-slate-900">
                            <span class="font-bold text-slate-900 dark:text-white">{{ s.name }}</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ s.overall_progress }}%</span>
                        </div>
                    </div>
                </div>

                <!-- On Track -->
                <div class="space-y-4 rounded-3xl border border-indigo-500/30 bg-indigo-500/5 p-5 dark:bg-indigo-950/20">
                    <div class="flex items-center justify-between border-b border-indigo-500/20 pb-3">
                        <h4 class="text-sm font-bold text-indigo-700 dark:text-indigo-400 flex items-center gap-1.5">
                            <TrendingUp class="h-4 w-4" /> On Track (30%-75%)
                        </h4>
                        <span class="rounded-full bg-indigo-500/20 px-2.5 py-0.5 text-xs font-bold text-indigo-600 dark:text-indigo-400">
                            {{ onTrackStudents.length }}
                        </span>
                    </div>
                    <div v-if="onTrackStudents.length === 0" class="text-xs text-slate-400 italic">No trainees in this tier.</div>
                    <div v-else class="space-y-2">
                        <div v-for="s in onTrackStudents" :key="s.id" class="flex items-center justify-between rounded-xl bg-white p-3 text-xs shadow-xs dark:bg-slate-900">
                            <span class="font-bold text-slate-900 dark:text-white">{{ s.name }}</span>
                            <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ s.overall_progress }}%</span>
                        </div>
                    </div>
                </div>

                <!-- Needs Support -->
                <div class="space-y-4 rounded-3xl border border-amber-500/30 bg-amber-500/5 p-5 dark:bg-amber-950/20">
                    <div class="flex items-center justify-between border-b border-amber-500/20 pb-3">
                        <h4 class="text-sm font-bold text-amber-700 dark:text-amber-400 flex items-center gap-1.5">
                            <Clock class="h-4 w-4" /> Needs Mentorship (<30%)
                        </h4>
                        <span class="rounded-full bg-amber-500/20 px-2.5 py-0.5 text-xs font-bold text-amber-600 dark:text-amber-400">
                            {{ needsSupportStudents.length }}
                        </span>
                    </div>
                    <div v-if="needsSupportStudents.length === 0" class="text-xs text-slate-400 italic">All trainees are performing well!</div>
                    <div v-else class="space-y-2">
                        <div v-for="s in needsSupportStudents" :key="s.id" class="flex items-center justify-between rounded-xl bg-white p-3 text-xs shadow-xs dark:bg-slate-900">
                            <span class="font-bold text-slate-900 dark:text-white">{{ s.name }}</span>
                            <span class="font-bold text-amber-600 dark:text-amber-400">{{ s.overall_progress }}%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
