<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { index, create, show, edit, destroy } from '@/routes/internships';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    GraduationCap,
    Plus,
    Eye,
    Pencil,
    Trash2,
    Search,
    MoreHorizontal,
    Sparkles,
    Calendar,
    Users,
    TrendingUp,
    Zap,
    Clock,
    CheckCircle2,
    Award,
    Layers,
    LayoutGrid,
    ListTree,
    ArrowUpRight,
    BookOpen,
    Copy,
    Check,
} from '@lucide/vue';

import TrackManagerModal from '@/components/TrackManagerModal.vue';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Internship Academy Portal', href: index.url() }],
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
    phone?: string | null;
    status: string;
    overall_progress: number;
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
    total_students_count?: number;
    created_at: string;
}

interface PaginatedInternships {
    data: Internship[];
    links: any[];
    current_page: number;
    last_page: number;
    total?: number;
}

const props = defineProps<{
    internships: PaginatedInternships;
    all_tracks?: any[];
}>();

// State
const searchQuery = ref('');
const isTrackModalOpen = ref(false);
const selectedStatus = ref<string>('active');
const selectedTrack = ref<string>('all');
const viewMode = ref<'grid' | 'timeline'>('grid');
const copiedBatchId = ref<number | null>(null);

// Helper for Initials
const getInitials = (name: string) => {
    if (!name) return 'IN';
    const parts = name.trim().split(' ');
    if (parts.length >= 2) {
        return `${parts[0][0]}${parts[1][0]}`.toUpperCase();
    }
    return name.slice(0, 2).toUpperCase();
};

// Avatar background colors
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

// Calculate Date Timeline Progress (% time elapsed between start_date & end_date)
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

        const total = end - start;
        const current = now - start;
        return Math.min(100, Math.max(0, Math.round((current / total) * 100)));
    } catch {
        return 50;
    }
};

// Format Date nicely
const formatDate = (dateStr: string) => {
    if (!dateStr) return 'N/A';
    try {
        const d = new Date(dateStr);
        return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    } catch {
        return dateStr;
    }
};

// Copy Batch Code to Clipboard
const copyBatchCode = (batchNo: string, id: number) => {
    navigator.clipboard.writeText(batchNo);
    copiedBatchId.value = id;
    setTimeout(() => {
        if (copiedBatchId.value === id) copiedBatchId.value = null;
    }, 2000);
};

// Status Config with Glowing Cyber Colors
const getStatusConfig = (status: string) => {
    switch (status) {
        case 'active':
            return {
                label: 'Active Cohort',
                badgeClass: 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/30 shadow-emerald-500/10',
                dotClass: 'bg-emerald-500 animate-pulse',
                cardBorder: 'border-emerald-500/30 hover:border-emerald-500/60',
                glowGradient: 'from-emerald-500/20 via-teal-500/5 to-transparent',
            };
        case 'upcoming':
            return {
                label: 'Upcoming Batch',
                badgeClass: 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border-indigo-500/30 shadow-indigo-500/10',
                dotClass: 'bg-indigo-500',
                cardBorder: 'border-indigo-500/30 hover:border-indigo-500/60',
                glowGradient: 'from-indigo-500/20 via-purple-500/5 to-transparent',
            };
        case 'completed':
            return {
                label: 'Graduated / Completed',
                badgeClass: 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border-purple-500/30 shadow-purple-500/10',
                dotClass: 'bg-purple-500',
                cardBorder: 'border-purple-500/30 hover:border-purple-500/60',
                glowGradient: 'from-purple-500/20 via-violet-500/5 to-transparent',
            };
        default:
            return {
                label: status,
                badgeClass: 'bg-slate-500/10 text-slate-600 dark:text-slate-400 border-slate-500/30',
                dotClass: 'bg-slate-400',
                cardBorder: 'border-slate-500/20',
                glowGradient: 'from-slate-500/10 to-transparent',
            };
    }
};

// Computed Summary Metrics
const totalBatches = computed(() => props.internships.total || props.internships.data.length);

const activeBatches = computed(() => props.internships.data.filter((b) => b.status === 'active'));
const upcomingBatches = computed(() => props.internships.data.filter((b) => b.status === 'upcoming'));
const completedBatches = computed(() => props.internships.data.filter((b) => b.status === 'completed'));

const totalInternsEnrolled = computed(() => {
    return props.internships.data.reduce((sum, b) => sum + (b.total_students_count || 0), 0);
});

const globalAverageProgress = computed(() => {
    let allStudents: Student[] = [];
    props.internships.data.forEach((b) => {
        if (b.students) allStudents.push(...b.students);
    });
    if (allStudents.length === 0) return 0;
    const sum = allStudents.reduce((acc, s) => acc + (s.overall_progress || 0), 0);
    return Math.round(sum / allStudents.length);
});

// Unique Tracks list for filter
const uniqueTracks = computed(() => {
    const set = new Set<string>();
    props.internships.data.forEach((b) => {
        if (b.service?.name) set.add(b.service.name);
    });
    return Array.from(set);
});

// Filtered Internships
const filteredInternships = computed(() => {
    return props.internships.data.filter((batch) => {
        const matchesStatus = selectedStatus.value === 'all' || batch.status === selectedStatus.value;
        const matchesTrack = selectedTrack.value === 'all' || batch.service?.name === selectedTrack.value;

        const query = searchQuery.value.toLowerCase().trim();
        const matchesSearch =
            !query ||
            batch.name.toLowerCase().includes(query) ||
            batch.batch_no.toLowerCase().includes(query) ||
            (batch.service && batch.service.name.toLowerCase().includes(query));

        return matchesStatus && matchesTrack && matchesSearch;
    });
});

const deleteInternship = (id: number) => {
    if (confirm('Are you sure you want to delete this internship batch?')) {
        router.delete(destroy.url(id));
    }
};
</script>

<template>
    <Head title="Internship Academy Portal" />

    <div class="w-full space-y-8 p-4 sm:p-6 lg:p-8">
        <!-- 🚀 MAGICAL HERO COMMAND CONSOLE (Light & Dark Responsive) -->
        <div
            class="relative overflow-hidden rounded-3xl border border-indigo-200/80 bg-gradient-to-br from-indigo-50/90 via-purple-50/60 to-slate-50/90 p-6 sm:p-8 text-slate-900 shadow-xl backdrop-blur-xl dark:border-indigo-500/20 dark:from-indigo-950/90 dark:via-slate-900 dark:to-purple-950/90 dark:text-white"
        >
            <!-- Background Glow Effects -->
            <div
                class="pointer-events-none absolute -top-24 -right-24 h-96 w-96 rounded-full bg-indigo-500/15 blur-3xl dark:bg-indigo-500/20"
            ></div>
            <div
                class="pointer-events-none absolute -bottom-24 -left-24 h-96 w-96 rounded-full bg-purple-500/15 blur-3xl dark:bg-purple-500/20"
            ></div>
            <div
                class="pointer-events-none absolute top-1/2 left-1/2 h-64 w-64 -translate-x-1/2 -translate-y-1/2 rounded-full bg-cyan-500/10 blur-3xl"
            ></div>

            <div class="relative z-10 space-y-6">
                <!-- Top Title Row -->
                <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                    <div class="space-y-2">
                        <div class="inline-flex items-center gap-2 rounded-full border border-indigo-200 bg-indigo-50 px-3.5 py-1 text-xs font-semibold tracking-wide text-indigo-700 backdrop-blur-md dark:border-indigo-400/30 dark:bg-indigo-500/10 dark:text-indigo-300">
                            <Sparkles class="h-3.5 w-3.5 animate-spin text-indigo-600 dark:text-indigo-400" />
                            <span>WebEra Academy & Internship Portal</span>
                        </div>
                        <h1 class="text-2xl font-black tracking-tight text-slate-900 sm:text-3xl lg:text-4xl dark:text-white">
                            Internship Command Hub
                        </h1>
                        <p class="max-w-2xl text-xs font-medium text-slate-600 sm:text-sm dark:text-slate-300">
                            Real-time batch telemetry, trainee progress metrics, track allocations, and cohort velocity summaries at a glance.
                        </p>
                    </div>

                    <!-- Create Batch & Manage Tracks Actions -->
                    <div class="flex flex-wrap items-center gap-3">
                        <Button
                            variant="outline"
                            size="lg"
                            @click="isTrackModalOpen = true"
                            class="rounded-xl border-indigo-200/80 bg-white/80 font-bold text-indigo-700 hover:bg-white dark:border-indigo-400/30 dark:bg-slate-900/60 dark:text-indigo-300 dark:hover:bg-slate-800 dark:hover:text-white"
                        >
                            <Layers class="mr-2 h-4 w-4 text-indigo-500" />
                            <span>Manage Tracks ⚙️</span>
                        </Button>

                        <Button
                            as-child
                            size="lg"
                            class="group relative overflow-hidden rounded-xl border border-indigo-500/30 bg-gradient-to-r from-indigo-600 via-indigo-500 to-purple-600 px-5 py-2.5 font-bold text-white shadow-lg transition-all duration-300 hover:scale-[1.02] hover:shadow-indigo-500/25"
                        >
                            <Link :href="create.url()">
                                <Plus class="mr-2 h-5 w-5 transition-transform duration-300 group-hover:rotate-90" />
                                <span>Create Internship Batch</span>
                            </Link>
                        </Button>
                    </div>
                </div>

                <!-- 📊 HERO SUMMARY METRICS HUB -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 pt-2">
                    <!-- Widget 1: Active Cohorts Radar -->
                    <div
                        class="group relative overflow-hidden rounded-2xl border border-emerald-500/30 bg-white/90 p-4 shadow-sm transition-all duration-300 hover:border-emerald-500/60 hover:bg-white dark:bg-slate-900/60 dark:hover:bg-slate-900/80"
                    >
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold tracking-wider text-emerald-600 dark:text-emerald-400 uppercase">
                                Active Cohorts
                            </span>
                            <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30">
                                <Zap class="h-4 w-4 animate-pulse" />
                            </div>
                        </div>
                        <div class="mt-3 flex items-baseline gap-2">
                            <span class="text-3xl font-black text-slate-900 dark:text-white">
                                {{ activeBatches.length }}
                            </span>
                            <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                / {{ totalBatches }} Batches
                            </span>
                        </div>
                        <!-- Micro Progress Pill -->
                        <div class="mt-3 flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                            <span>Upcoming: <strong class="text-indigo-600 dark:text-indigo-300">{{ upcomingBatches.length }}</strong></span>
                            <span>Completed: <strong class="text-purple-600 dark:text-purple-300">{{ completedBatches.length }}</strong></span>
                        </div>
                    </div>

                    <!-- Widget 2: Enrolled Trainees -->
                    <div
                        class="group relative overflow-hidden rounded-2xl border border-indigo-500/30 bg-white/90 p-4 shadow-sm transition-all duration-300 hover:border-indigo-500/60 hover:bg-white dark:bg-slate-900/60 dark:hover:bg-slate-900/80"
                    >
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold tracking-wider text-indigo-600 dark:text-indigo-300 uppercase">
                                Total Enrolled Interns
                            </span>
                            <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 border border-indigo-500/30">
                                <GraduationCap class="h-4 w-4" />
                            </div>
                        </div>
                        <div class="mt-3 flex items-baseline gap-2">
                            <span class="text-3xl font-black text-slate-900 dark:text-white">
                                {{ totalInternsEnrolled }}
                            </span>
                            <span class="text-xs font-bold text-slate-600 dark:text-slate-300">
                                Active Trainees
                            </span>
                        </div>
                        <div class="mt-3 flex items-center gap-1 text-[11px] text-indigo-600 dark:text-indigo-300 font-semibold">
                            <Users class="h-3.5 w-3.5 text-indigo-500 dark:text-indigo-400" />
                            <span>Distributed across {{ totalBatches }} batches</span>
                        </div>
                    </div>

                    <!-- Widget 3: Global Cohort Progress -->
                    <div
                        class="group relative overflow-hidden rounded-2xl border border-cyan-500/30 bg-white/90 p-4 shadow-sm transition-all duration-300 hover:border-cyan-500/60 hover:bg-white dark:bg-slate-900/60 dark:hover:bg-slate-900/80"
                    >
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold tracking-wider text-cyan-600 dark:text-cyan-300 uppercase">
                                Avg Cohort Velocity
                            </span>
                            <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-cyan-500/20 text-cyan-600 dark:text-cyan-400 border border-cyan-500/30">
                                <TrendingUp class="h-4 w-4" />
                            </div>
                        </div>
                        <div class="mt-3 flex items-baseline gap-2">
                            <span class="text-3xl font-black text-slate-900 dark:text-white">
                                {{ globalAverageProgress }}%
                            </span>
                            <span class="text-xs font-bold text-cyan-600 dark:text-cyan-300">
                                Overall Completion
                            </span>
                        </div>
                        <div class="mt-3 w-full bg-slate-200 dark:bg-slate-800 rounded-full h-1.5 overflow-hidden border border-slate-300/50 dark:border-slate-700">
                            <div
                                class="bg-gradient-to-r from-cyan-500 to-indigo-500 h-full rounded-full transition-all duration-500"
                                :style="{ width: `${globalAverageProgress}%` }"
                            ></div>
                        </div>
                    </div>

                    <!-- Widget 4: Active Tracks Breakdown -->
                    <div
                        class="group relative overflow-hidden rounded-2xl border border-purple-500/30 bg-white/90 p-4 shadow-sm transition-all duration-300 hover:border-purple-500/60 hover:bg-white dark:bg-slate-900/60 dark:hover:bg-slate-900/80"
                    >
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold tracking-wider text-purple-600 dark:text-purple-300 uppercase">
                                Learning Tracks
                            </span>
                            <div class="flex h-8 w-8 items-center justify-center rounded-xl bg-purple-500/20 text-purple-600 dark:text-purple-400 border border-purple-500/30">
                                <Layers class="h-4 w-4" />
                            </div>
                        </div>
                        <div class="mt-3 flex items-baseline gap-2">
                            <span class="text-3xl font-black text-slate-900 dark:text-white">
                                {{ uniqueTracks.length }}
                            </span>
                            <span class="text-xs font-bold text-purple-600 dark:text-purple-300">
                                Specialized Tracks
                            </span>
                        </div>
                        <div class="mt-2.5 flex flex-wrap gap-1">
                            <span
                                v-for="track in uniqueTracks.slice(0, 3)"
                                :key="track"
                                class="inline-block rounded-md bg-purple-100 text-purple-700 border border-purple-200 dark:bg-purple-500/20 dark:text-purple-200 dark:border-purple-500/30 px-2 py-0.5 text-[10px] font-bold"
                            >
                                {{ track }}
                            </span>
                            <span v-if="uniqueTracks.length > 3" class="text-[10px] text-slate-500 dark:text-slate-400 font-semibold">
                                +{{ uniqueTracks.length - 3 }} more
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Lifecycle Progress Bar (Visual Summary of Batch States) -->
                <div class="space-y-1.5 pt-2">
                    <div class="flex items-center justify-between text-xs font-bold text-slate-700 dark:text-slate-300">
                        <span class="flex items-center gap-1.5">
                            <Award class="h-3.5 w-3.5 text-indigo-600 dark:text-indigo-400" />
                            Batch Lifecycle Allocation Status
                        </span>
                        <span class="text-slate-500 dark:text-slate-400">
                            {{ activeBatches.length }} Active • {{ upcomingBatches.length }} Upcoming • {{ completedBatches.length }} Graduated
                        </span>
                    </div>
                    <div class="flex h-2.5 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800 p-0.5 border border-slate-300/60 dark:border-slate-700">
                        <div
                            class="bg-emerald-500 rounded-l-full transition-all duration-500"
                            :style="{ width: `${totalBatches ? (activeBatches.length / totalBatches) * 100 : 0}%` }"
                            title="Active Batches"
                        ></div>
                        <div
                            class="bg-indigo-500 transition-all duration-500"
                            :style="{ width: `${totalBatches ? (upcomingBatches.length / totalBatches) * 100 : 0}%` }"
                            title="Upcoming Batches"
                        ></div>
                        <div
                            class="bg-purple-500 rounded-r-full transition-all duration-500"
                            :style="{ width: `${totalBatches ? (completedBatches.length / totalBatches) * 100 : 0}%` }"
                            title="Completed Batches"
                        ></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 🎛️ CONTROLS & FILTERING TOOLBAR -->
        <div
            class="flex flex-col gap-4 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm lg:flex-row lg:items-center lg:justify-between dark:border-slate-800 dark:bg-slate-900"
        >
            <!-- Search Bar -->
            <div class="relative w-full lg:w-80">
                <Search class="absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <Input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search batch name, code, track..."
                    class="h-10 border-slate-200 bg-slate-50/70 pl-10 text-xs focus:bg-white dark:border-slate-800 dark:bg-slate-800/50 dark:focus:bg-slate-900"
                />
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <!-- Track Filter Dropdown -->
                <select
                    v-model="selectedTrack"
                    class="h-10 rounded-xl border border-slate-200/80 bg-slate-50/70 px-3 text-xs font-semibold text-slate-700 dark:border-slate-800 dark:bg-slate-800/60 dark:text-slate-300"
                >
                    <option value="all">All Learning Tracks</option>
                    <option v-for="track in uniqueTracks" :key="track" :value="track">
                        {{ track }}
                    </option>
                </select>

                <!-- Status Filter Pills -->
                <div class="flex items-center rounded-xl bg-slate-100 p-1 text-xs font-semibold dark:bg-slate-800/80">
                    <button
                        @click="selectedStatus = 'all'"
                        :class="[
                            'rounded-lg px-3 py-1.5 transition-all',
                            selectedStatus === 'all'
                                ? 'bg-white font-bold text-slate-900 shadow-sm dark:bg-slate-900 dark:text-white'
                                : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200',
                        ]"
                    >
                        All ({{ totalBatches }})
                    </button>
                    <button
                        @click="selectedStatus = 'active'"
                        :class="[
                            'rounded-lg px-3 py-1.5 transition-all',
                            selectedStatus === 'active'
                                ? 'bg-emerald-500 font-bold text-white shadow-sm'
                                : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200',
                        ]"
                    >
                        Active ({{activeBatches.length}})
                    </button>
                    <button
                        @click="selectedStatus = 'upcoming'"
                        :class="[
                            'rounded-lg px-3 py-1.5 transition-all',
                            selectedStatus === 'upcoming'
                                ? 'bg-indigo-600 font-bold text-white shadow-sm'
                                : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200',
                        ]"
                    >
                        Upcoming
                    </button>
                    <button
                        @click="selectedStatus = 'completed'"
                        :class="[
                            'rounded-lg px-3 py-1.5 transition-all',
                            selectedStatus === 'completed'
                                ? 'bg-purple-600 font-bold text-white shadow-sm'
                                : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200',
                        ]"
                    >
                        Completed
                    </button>
                </div>

                <!-- View Switcher (Grid vs Pipeline Timeline) -->
                <div class="flex items-center rounded-xl border border-slate-200/80 bg-slate-50 p-1 dark:border-slate-800 dark:bg-slate-800/50">
                    <button
                        @click="viewMode = 'grid'"
                        :class="[
                            'flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-semibold transition-all',
                            viewMode === 'grid'
                                ? 'bg-white text-indigo-600 shadow-sm dark:bg-slate-900 dark:text-indigo-400'
                                : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200',
                        ]"
                        title="Grid View"
                    >
                        <LayoutGrid class="h-4 w-4" />
                        <span class="hidden sm:inline">Grid</span>
                    </button>
                    <button
                        @click="viewMode = 'timeline'"
                        :class="[
                            'flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-semibold transition-all',
                            viewMode === 'timeline'
                                ? 'bg-white text-indigo-600 shadow-sm dark:bg-slate-900 dark:text-indigo-400'
                                : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200',
                        ]"
                        title="Pipeline Timeline View"
                    >
                        <ListTree class="h-4 w-4" />
                        <span class="hidden sm:inline">Pipeline</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- 📦 EMPTY STATE -->
        <div v-if="filteredInternships.length === 0" class="rounded-3xl border border-dashed border-slate-300 p-12 text-center dark:border-slate-800">
            <div class="mx-auto flex max-w-sm flex-col items-center justify-center space-y-4">
                <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-indigo-50/80 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400">
                    <BookOpen class="h-8 w-8" />
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">
                        No internship batches found
                    </h3>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                        {{ searchQuery ? 'Try adjusting your search filters.' : 'Get started by creating your first internship training batch.' }}
                    </p>
                </div>
                <Button v-if="!searchQuery" as-child class="bg-indigo-600 text-white hover:bg-indigo-700">
                    <Link :href="create.url()">
                        <Plus class="mr-1.5 h-4 w-4" /> Create Internship Batch
                    </Link>
                </Button>
            </div>
        </div>

        <!-- 🔮 MODE 1: MATRIX CARD GRID (Magical Glass Cards) -->
        <div
            v-else-if="viewMode === 'grid'"
            class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3"
        >
            <div
                v-for="batch in filteredInternships"
                :key="batch.id"
                :class="[
                    'group relative overflow-hidden rounded-3xl border bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl dark:bg-slate-900/90 dark:backdrop-blur-md',
                    getStatusConfig(batch.status).cardBorder,
                ]"
            >
                <!-- Glowing Corner Mesh -->
                <div
                    :class="[
                        'pointer-events-none absolute -top-12 -right-12 h-36 w-36 rounded-full bg-gradient-to-br blur-2xl transition-opacity duration-300 group-hover:opacity-100 opacity-60',
                        getStatusConfig(batch.status).glowGradient,
                    ]"
                ></div>

                <div class="relative z-10 space-y-5">
                    <!-- Top Row: Hologram Batch Pill + Actions -->
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex flex-wrap items-center gap-2">
                            <!-- Batch Code Hologram Pill -->
                            <button
                                @click="copyBatchCode(batch.batch_no, batch.id)"
                                title="Click to copy batch code"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-mono font-bold text-slate-800 transition-all hover:bg-slate-100 dark:border-slate-800 dark:bg-slate-800/80 dark:text-slate-200 dark:hover:bg-slate-800"
                            >
                                <span class="text-indigo-500">🏷️</span>
                                <span>{{ batch.batch_no }}</span>
                                <Check v-if="copiedBatchId === batch.id" class="h-3 w-3 text-emerald-500" />
                                <Copy v-else class="h-3 w-3 text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-300" />
                            </button>

                            <!-- Status Badge -->
                            <span
                                :class="[
                                    'inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-bold transition-all',
                                    getStatusConfig(batch.status).badgeClass,
                                ]"
                            >
                                <span :class="['h-2 w-2 rounded-full', getStatusConfig(batch.status).dotClass]"></span>
                                {{ getStatusConfig(batch.status).label }}
                            </span>
                        </div>

                        <!-- Action Dropdown -->
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button
                                    variant="ghost"
                                    size="icon-sm"
                                    class="h-8 w-8 text-slate-400 hover:text-slate-900 dark:hover:text-slate-100"
                                >
                                    <MoreHorizontal class="h-4 w-4" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" class="w-48">
                                <DropdownMenuLabel class="text-xs text-slate-500">Batch Options</DropdownMenuLabel>
                                <DropdownMenuSeparator />
                                <DropdownMenuItem as-child>
                                    <Link :href="show.url(batch.id)" class="flex cursor-pointer items-center gap-2">
                                        <Eye class="h-4 w-4 text-indigo-500" />
                                        <span>View Academy Portal</span>
                                    </Link>
                                </DropdownMenuItem>
                                <DropdownMenuItem as-child>
                                    <Link :href="edit.url(batch.id)" class="flex cursor-pointer items-center gap-2">
                                        <Pencil class="h-4 w-4 text-slate-500" />
                                        <span>Edit Batch Info</span>
                                    </Link>
                                </DropdownMenuItem>
                                <DropdownMenuSeparator />
                                <DropdownMenuItem @click="deleteInternship(batch.id)" class="flex cursor-pointer items-center gap-2 text-rose-600 focus:text-rose-600">
                                    <Trash2 class="h-4 w-4" />
                                    <span>Delete Batch</span>
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>

                    <!-- Batch Title & Track -->
                    <div class="space-y-1.5">
                        <Link
                            :href="show.url(batch.id)"
                            class="group/title flex items-center gap-2 text-lg font-bold tracking-tight text-slate-900 transition-colors hover:text-indigo-600 dark:text-slate-100 dark:hover:text-indigo-400"
                        >
                            <span>{{ batch.name }}</span>
                            <ArrowUpRight class="h-4 w-4 opacity-0 transition-opacity group-hover/title:opacity-100 text-indigo-500" />
                        </Link>
                        
                        <div class="flex items-center gap-2">
                            <Badge
                                variant="outline"
                                class="rounded-md border-indigo-200/80 bg-indigo-50/50 px-2 py-0.5 text-[11px] font-semibold text-indigo-700 dark:border-indigo-900/60 dark:bg-indigo-950/40 dark:text-indigo-300"
                            >
                                Track: {{ batch.service?.name || 'General Training' }}
                            </Badge>
                        </div>
                    </div>

                    <!-- ⏱️ REAL-TIME BATCH DURATION GAUGE -->
                    <div class="space-y-2 rounded-2xl border border-slate-100 bg-slate-50/80 p-3.5 dark:border-slate-800/80 dark:bg-slate-800/40">
                        <div class="flex items-center justify-between text-xs font-semibold text-slate-600 dark:text-slate-300">
                            <span class="flex items-center gap-1.5">
                                <Clock class="h-3.5 w-3.5 text-indigo-500" />
                                <span>Duration Progress</span>
                            </span>
                            <span class="font-mono text-indigo-600 dark:text-indigo-400 font-bold">
                                {{ getTimelineProgress(batch.start_date, batch.end_date, batch.status) }}% Elapsed
                            </span>
                        </div>
                        <div class="h-2 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                            <div
                                class="h-full rounded-full bg-gradient-to-r from-indigo-500 via-cyan-500 to-emerald-500 transition-all duration-500"
                                :style="{ width: `${getTimelineProgress(batch.start_date, batch.end_date, batch.status)}%` }"
                            ></div>
                        </div>
                        <div class="flex items-center justify-between text-[11px] font-medium text-slate-400">
                            <span>Start: {{ formatDate(batch.start_date) }}</span>
                            <span>End: {{ formatDate(batch.end_date) }}</span>
                        </div>
                    </div>

                    <!-- 🎓 ENROLLED INTERNS COHORT STACK -->
                    <div class="flex items-center justify-between border-t border-slate-100 pt-4 dark:border-slate-800">
                        <div class="flex items-center gap-3">
                            <!-- Avatar Stack -->
                            <div v-if="batch.students && batch.students.length > 0" class="flex -space-x-2.5 overflow-hidden">
                                <Avatar
                                    v-for="student in batch.students.slice(0, 4)"
                                    :key="student.id"
                                    class="h-8 w-8 border-2 border-white ring-1 ring-slate-100 dark:border-slate-900 dark:ring-slate-800"
                                >
                                    <AvatarFallback :class="['text-[10px] font-bold', getAvatarColor(student.name)]">
                                        {{ getInitials(student.name) }}
                                    </AvatarFallback>
                                </Avatar>
                                <div
                                    v-if="batch.students.length > 4"
                                    class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-white bg-slate-800 text-[10px] font-bold text-white dark:border-slate-900"
                                >
                                    +{{ batch.students.length - 4 }}
                                </div>
                            </div>
                            <div v-else class="text-xs text-slate-400 italic">
                                No interns enrolled yet
                            </div>

                            <div v-if="batch.students && batch.students.length > 0" class="text-xs">
                                <p class="font-bold text-slate-900 dark:text-slate-100">
                                    {{ batch.total_students_count || batch.students.length }} Interns
                                </p>
                                <p class="text-[10px] text-slate-400">
                                    Cohort Enrolled
                                </p>
                            </div>
                        </div>

                        <!-- View Hub Button -->
                        <Button
                            as-child
                            size="sm"
                            variant="secondary"
                            class="rounded-xl font-semibold transition-colors hover:bg-indigo-600 hover:text-white"
                        >
                            <Link :href="show.url(batch.id)">
                                View Hub →
                            </Link>
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <!-- 🌐 MODE 2: PIPELINE TIMELINE VIEW -->
        <div v-else class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="space-y-6">
                <div class="flex items-center justify-between border-b border-slate-200/80 pb-4 dark:border-slate-800">
                    <h3 class="text-base font-bold text-slate-900 dark:text-slate-100">
                        Chronological Batch Pipeline
                    </h3>
                    <span class="text-xs text-slate-500">
                        Showing {{ filteredInternships.length }} batches
                    </span>
                </div>

                <div class="relative border-l-2 border-indigo-500/30 ml-4 space-y-8 pl-6">
                    <div
                        v-for="batch in filteredInternships"
                        :key="batch.id"
                        class="relative group"
                    >
                        <!-- Timeline Node Bullet -->
                        <div
                            :class="[
                                'absolute -left-[31px] top-1.5 h-4 w-4 rounded-full border-2 border-white shadow-md transition-transform group-hover:scale-125 dark:border-slate-900',
                                batch.status === 'active'
                                    ? 'bg-emerald-500 animate-pulse'
                                    : batch.status === 'upcoming'
                                    ? 'bg-indigo-500'
                                    : 'bg-purple-500',
                            ]"
                        ></div>

                        <!-- Pipeline Row Content -->
                        <div class="flex flex-col gap-4 rounded-2xl border border-slate-200/80 bg-slate-50/50 p-4 transition-all hover:bg-slate-50 md:flex-row md:items-center md:justify-between dark:border-slate-800 dark:bg-slate-800/40 dark:hover:bg-slate-800/70">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-xs font-bold text-indigo-500">
                                        {{ batch.batch_no }}
                                    </span>
                                    <h4 class="text-sm font-bold text-slate-900 dark:text-slate-100">
                                        {{ batch.name }}
                                    </h4>
                                    <span
                                        :class="[
                                            'rounded-full px-2 py-0.5 text-[10px] font-bold border',
                                            getStatusConfig(batch.status).badgeClass,
                                        ]"
                                    >
                                        {{ getStatusConfig(batch.status).label }}
                                    </span>
                                </div>

                                <div class="flex flex-wrap items-center gap-4 text-xs text-slate-500 dark:text-slate-400">
                                    <span>Track: <strong class="text-slate-700 dark:text-slate-300">{{ batch.service?.name || 'N/A' }}</strong></span>
                                    <span>Duration: <strong>{{ formatDate(batch.start_date) }} → {{ formatDate(batch.end_date) }}</strong></span>
                                    <span>Interns: <strong>{{ batch.total_students_count || 0 }}</strong></span>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <div class="w-32 hidden sm:block">
                                    <div class="flex justify-between text-[10px] font-semibold text-slate-500">
                                        <span>Progress</span>
                                        <span>{{ getTimelineProgress(batch.start_date, batch.end_date, batch.status) }}%</span>
                                    </div>
                                    <div class="h-1.5 w-full bg-slate-200 rounded-full overflow-hidden dark:bg-slate-700 mt-1">
                                        <div
                                            class="h-full bg-indigo-500 rounded-full"
                                            :style="{ width: `${getTimelineProgress(batch.start_date, batch.end_date, batch.status)}%` }"
                                        ></div>
                                    </div>
                                </div>

                                <Button as-child size="sm" class="bg-indigo-600 text-white hover:bg-indigo-700">
                                    <Link :href="show.url(batch.id)">
                                        View Portal →
                                    </Link>
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- In-Context Track Manager Modal -->
        <TrackManagerModal
            :is-open="isTrackModalOpen"
            :tracks="all_tracks || []"
            @close="isTrackModalOpen = false"
        />
    </div>
</template>
