<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { dashboard } from '@/routes';
import { index as clientsIndex, create as clientsCreate, show as clientsShow } from '@/routes/clients';
import { index as projectsIndex, create as projectsCreate, show as projectsShow, edit as projectsEdit } from '@/routes/projects';
import { index as internshipsIndex, create as internshipsCreate, show as internshipsShow } from '@/routes/internships';
import { index as studentsIndex, create as studentsCreate, show as studentsShow } from '@/routes/students';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import {
    Activity,
    AlertCircle,
    AlertTriangle,
    ArrowUpRight,
    Award,
    Briefcase,
    Building2,
    Calendar,
    CheckCircle2,
    Clock,
    DollarSign,
    ExternalLink,
    Eye,
    FileText,
    FolderKanban,
    GraduationCap,
    Layers,
    ListFilter,
    Mail,
    MoreHorizontal,
    Pencil,
    Phone,
    PlayCircle,
    Plus,
    RefreshCw,
    Search,
    ShieldAlert,
    ShieldCheck,
    Sparkles,
    Target,
    TrendingUp,
    UserCheck,
    UserPlus,
    Users,
    Wrench,
    Zap,
} from '@lucide/vue';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard.url(),
            },
        ],
    },
});

interface Client {
    id: number;
    name: string;
    company_name?: string | null;
}

interface Service {
    id: number;
    name: string;
}

interface StudentPivot {
    id: number;
    name: string;
}

interface Project {
    id: number;
    title: string;
    status: string;
    progress: number;
    deadline: string | null;
    client: Client | null;
    service: Service | null;
    students?: StudentPivot[];
    created_at?: string;
}

interface Student {
    id: number;
    name: string;
    email: string;
    overall_progress: number;
    status: string;
    internship?: {
        id: number;
        name: string;
        batch_no: string;
    } | null;
    projects_count?: number;
    weekly_reports_count?: number;
    projects?: { id: number; title: string }[];
}

interface WeeklyReport {
    id: number;
    week_number: number;
    status: string;
    submitted_at?: string;
    created_at?: string;
    blockers?: string | null;
    student: {
        id: number;
        name: string;
        email: string;
        internship_id?: number;
    };
}

interface BlockerReport {
    id: number;
    student_id: number;
    week_number: number;
    blockers: string;
    created_at: string;
    student?: {
        id: number;
        name: string;
        email: string;
    };
}

interface Lead {
    id: number;
    name: string;
    email: string;
    phone?: string | null;
    company_name?: string | null;
    created_at: string;
}

interface ActiveBatch {
    id: number;
    name: string;
    batch_no: string;
    service: string;
    start_date: string | null;
    end_date: string | null;
    students_count: number;
    progress_percent: number;
    days_remaining: number;
}

interface ServiceBreakdown {
    id: number;
    name: string;
    type: string;
    projects_count: number;
    clients_count: number;
    internships_count: number;
}

interface ActivityItem {
    type: 'client' | 'project' | 'report';
    title: string;
    subtitle: string;
    status: string;
    timestamp: string;
    link: string;
}

interface Metrics {
    total_pipeline_value: number;
    converted_revenue: number;
    active_deals: number;
    total_leads: number;
    new_leads: number;
    contacted_leads: number;
    active_clients: number;
    total_clients: number;
    conversion_rate: number;
    active_projects: number;
    completed_projects: number;
    client_projects: number;
    internal_tasks: number;
    avg_project_progress: number;
    active_batches: number;
    total_batches: number;
    completed_batches: number;
    total_students: number;
    active_students: number;
    completed_students: number;
    enrolled_students: number;
    graduation_rate: number;
    avg_student_progress: number;
    pending_reports: number;
    total_reports: number;
    approved_reports: number;
    blockers_count: number;
}

const props = defineProps<{
    metrics: Metrics;
    projectsByStatus: Record<string, number>;
    upcomingDeadlines: Project[];
    overdueProjects: Project[];
    recentProjects: Project[];
    activeBatches: ActiveBatch[];
    topStudents: Student[];
    uncontactedLeads: Lead[];
    pendingReports: WeeklyReport[];
    blockerReports: BlockerReport[];
    servicesBreakdown: ServiceBreakdown[];
    activityStream: ActivityItem[];
}>();

// Navigation Tabs
type DashboardTab = 'overview' | 'commercial' | 'projects' | 'academy' | 'action_center';
const activeTab = ref<DashboardTab>('overview');

// Search & Filter State
const globalSearch = ref('');
const isRefreshing = ref(false);

const refreshData = () => {
    isRefreshing.value = true;
    router.reload({
        onFinish: () => {
            isRefreshing.value = false;
        },
    });
};

// Currency Formatter
const formatCurrency = (val: number) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        maximumFractionDigits: 0,
    }).format(val);
};

// Initials Helper
const getInitials = (name: string) => {
    if (!name) return 'WN';
    const parts = name.trim().split(' ');
    if (parts.length >= 2) {
        return `${parts[0][0]}${parts[1][0]}`.toUpperCase();
    }
    return name.slice(0, 2).toUpperCase();
};

// Avatar colors
const getAvatarColor = (name: string) => {
    const colors = [
        'bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300 border-indigo-200',
        'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300 border-blue-200',
        'bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-300 border-purple-200',
        'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300 border-amber-200',
        'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 border-emerald-200',
    ];
    let hash = 0;
    for (let i = 0; i < name.length; i++) {
        hash = name.charCodeAt(i) + ((hash << 5) - hash);
    }
    return colors[Math.abs(hash) % colors.length];
};

// Project Status configuration
const getProjectStatusConfig = (status: string) => {
    switch (status) {
        case 'planning':
            return {
                label: 'Planning',
                badgeClass: 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/60 dark:text-blue-300 dark:border-blue-800',
                dotClass: 'bg-blue-500',
            };
        case 'in_progress':
            return {
                label: 'In Progress',
                badgeClass: 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-800',
                dotClass: 'bg-amber-500',
            };
        case 'under_review':
            return {
                label: 'Under Review',
                badgeClass: 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/60 dark:text-purple-300 dark:border-purple-800',
                dotClass: 'bg-purple-500',
            };
        case 'completed':
            return {
                label: 'Completed',
                badgeClass: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800',
                dotClass: 'bg-emerald-500',
            };
        case 'on_hold':
            return {
                label: 'On Hold',
                badgeClass: 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
                dotClass: 'bg-slate-400',
            };
        case 'cancelled':
            return {
                label: 'Cancelled',
                badgeClass: 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-800',
                dotClass: 'bg-rose-500',
            };
        default:
            return {
                label: status,
                badgeClass: 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
                dotClass: 'bg-slate-400',
            };
    }
};

// Progress bar color
const getProgressGradient = (progress: number) => {
    if (progress >= 100) return 'bg-emerald-500';
    if (progress >= 70) return 'bg-indigo-600';
    if (progress >= 40) return 'bg-blue-600';
    if (progress >= 20) return 'bg-amber-500';
    return 'bg-slate-400';
};

// Dynamic Day Greeting
const greeting = computed(() => {
    const hour = new Date().getHours();
    if (hour < 12) return 'Good morning';
    if (hour < 18) return 'Good afternoon';
    return 'Good evening';
});

// Search filter for recent projects
const filteredRecentProjects = computed(() => {
    if (!globalSearch.value.trim()) return props.recentProjects;
    const q = globalSearch.value.toLowerCase().trim();
    return props.recentProjects.filter((p) =>
        p.title.toLowerCase().includes(q) ||
        (p.client && p.client.name.toLowerCase().includes(q)) ||
        (p.service && p.service.name.toLowerCase().includes(q))
    );
});

// Urgent tasks count
const totalUrgentCount = computed(() => {
    return (
        props.overdueProjects.length +
        props.uncontactedLeads.length +
        props.pendingReports.length +
        props.blockerReports.length
    );
});
</script>

<template>
    <Head title="Executive Command Center — WebEra-Nexus" />

    <div class="w-full space-y-6 p-4 sm:p-6 lg:p-8">
        <!-- 🌟 TOP MASTER EXECUTIVE HEADER -->
        <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 p-6 text-white shadow-xl dark:border-slate-800">
            <!-- Background Decorative Lighting -->
            <div class="pointer-events-none absolute -top-24 -right-24 h-72 w-72 rounded-full bg-indigo-500/15 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-24 -left-24 h-72 w-72 rounded-full bg-blue-500/15 blur-3xl"></div>

            <div class="relative z-10 flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <!-- Left Title & Subtext -->
                <div class="space-y-1.5">
                    <div class="flex flex-wrap items-center gap-2.5">
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-400/30 bg-emerald-500/10 px-2.5 py-0.5 text-xs font-semibold text-emerald-300">
                            <span class="relative flex h-2 w-2">
                                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                            </span>
                            NEXUS INTELLIGENCE ENGINE ACTIVE
                        </span>

                        <span class="text-xs font-medium text-slate-400">
                            Enterprise Operations Platform
                        </span>
                    </div>

                    <h1 class="text-2xl font-black tracking-tight text-white sm:text-3xl">
                        {{ greeting }}, Executive Command Center
                    </h1>

                    <p class="max-w-2xl text-xs text-slate-300 sm:text-sm">
                        Panoramic performance telemetry across commercial pipeline, client deliverables, internship academy, and active mentorship streams.
                    </p>
                </div>

                <!-- Right Quick Launch Hub -->
                <div class="flex flex-wrap items-center gap-2.5">
                    <Button
                        as-child
                        size="sm"
                        class="bg-indigo-600 font-semibold text-white shadow-md hover:bg-indigo-500 hover:shadow-indigo-500/20"
                    >
                        <Link :href="clientsCreate.url()">
                            <UserPlus class="mr-1.5 h-3.5 w-3.5" /> New Lead
                        </Link>
                    </Button>

                    <Button
                        as-child
                        size="sm"
                        class="bg-emerald-600 font-semibold text-white shadow-md hover:bg-emerald-500 hover:shadow-emerald-500/20"
                    >
                        <Link :href="projectsCreate.url()">
                            <Plus class="mr-1.5 h-3.5 w-3.5" /> Create Project
                        </Link>
                    </Button>

                    <Button
                        as-child
                        size="sm"
                        variant="outline"
                        class="border-slate-700 bg-slate-800/80 text-xs font-semibold text-white hover:bg-slate-700"
                    >
                        <Link :href="internshipsCreate.url()">
                            <GraduationCap class="mr-1.5 h-3.5 w-3.5 text-purple-400" /> Launch Batch
                        </Link>
                    </Button>

                    <Button
                        as-child
                        size="sm"
                        variant="outline"
                        class="border-slate-700 bg-slate-800/80 text-xs font-semibold text-white hover:bg-slate-700"
                    >
                        <Link :href="studentsCreate.url()">
                            <UserCheck class="mr-1.5 h-3.5 w-3.5 text-sky-400" /> Enroll Intern
                        </Link>
                    </Button>

                    <button
                        @click="refreshData"
                        :disabled="isRefreshing"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-700 bg-slate-800/80 text-slate-300 transition-all hover:bg-slate-700 hover:text-white"
                        title="Reload Telemetry"
                    >
                        <RefreshCw :class="['h-3.5 w-3.5', isRefreshing ? 'animate-spin text-indigo-400' : '']" />
                    </button>
                </div>
            </div>
        </div>

        <!-- 🚀 TOP TIER 5 EXECUTIVE KPI BENTO CARDS -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <!-- 1. Revenue & Commercial Pipeline -->
            <div class="group relative flex flex-col justify-between overflow-hidden rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs transition-all hover:border-indigo-300 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-indigo-800">
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-xs font-bold tracking-wider text-slate-500 uppercase dark:text-slate-400">
                        <span>Commercial Pipeline</span>
                        <div class="rounded-lg bg-emerald-50 p-2 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400">
                            <DollarSign class="h-4 w-4" />
                        </div>
                    </div>

                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl font-black text-slate-900 dark:text-white">
                            {{ formatCurrency(metrics.total_pipeline_value) }}
                        </span>
                    </div>

                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Converted: <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ formatCurrency(metrics.converted_revenue) }}</span>
                    </p>
                </div>

                <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-3 text-[11px] text-slate-400 dark:border-slate-800">
                    <span class="font-medium text-slate-600 dark:text-slate-300">{{ metrics.active_deals }} Active Deals</span>
                    <span class="inline-flex items-center gap-0.5 font-bold text-emerald-600 dark:text-emerald-400">
                        <TrendingUp class="h-3 w-3" /> Healthy
                    </span>
                </div>
            </div>

            <!-- 2. Leads & Conversion Radar -->
            <div class="group relative flex flex-col justify-between overflow-hidden rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs transition-all hover:border-blue-300 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-blue-800">
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-xs font-bold tracking-wider text-slate-500 uppercase dark:text-slate-400">
                        <span>Client Conversion</span>
                        <div class="rounded-lg bg-blue-50 p-2 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400">
                            <Target class="h-4 w-4" />
                        </div>
                    </div>

                    <div class="flex items-baseline justify-between">
                        <span class="text-2xl font-black text-slate-900 dark:text-white">
                            {{ metrics.conversion_rate }}%
                        </span>
                        <Badge variant="outline" class="rounded-full border-blue-200 bg-blue-50 text-[11px] font-bold text-blue-700 dark:border-blue-900 dark:bg-blue-950/50 dark:text-blue-300">
                            {{ metrics.active_clients }} Converted
                        </Badge>
                    </div>

                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        {{ metrics.total_leads }} Leads ({{ metrics.new_leads }} New • {{ metrics.contacted_leads }} Contacted)
                    </p>
                </div>

                <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-3 text-[11px] text-slate-400 dark:border-slate-800">
                    <span>Total Clients: {{ metrics.total_clients }}</span>
                    <Link :href="clientsIndex.url()" class="font-semibold text-blue-600 hover:underline dark:text-blue-400">View CRM →</Link>
                </div>
            </div>

            <!-- 3. Projects Delivery Velocity -->
            <div class="group relative flex flex-col justify-between overflow-hidden rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs transition-all hover:border-indigo-300 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-indigo-800">
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-xs font-bold tracking-wider text-slate-500 uppercase dark:text-slate-400">
                        <span>Active Projects</span>
                        <div class="rounded-lg bg-indigo-50 p-2 text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-400">
                            <FolderKanban class="h-4 w-4" />
                        </div>
                    </div>

                    <div class="flex items-baseline justify-between">
                        <span class="text-2xl font-black text-indigo-600 dark:text-indigo-400">
                            {{ metrics.active_projects }} Active
                        </span>
                        <span class="text-xs font-bold text-slate-500 dark:text-slate-400">
                            Avg {{ metrics.avg_project_progress }}%
                        </span>
                    </div>

                    <div class="h-1.5 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                        <div
                            class="h-1.5 rounded-full bg-indigo-600 transition-all duration-500"
                            :style="{ width: `${metrics.avg_project_progress}%` }"
                        ></div>
                    </div>
                </div>

                <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-3 text-[11px] text-slate-400 dark:border-slate-800">
                    <span>{{ metrics.client_projects }} Client • {{ metrics.internal_tasks }} Internal</span>
                    <span class="font-bold text-emerald-600 dark:text-emerald-400">{{ metrics.completed_projects }} Done</span>
                </div>
            </div>

            <!-- 4. Academy & Talent Engine -->
            <div class="group relative flex flex-col justify-between overflow-hidden rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs transition-all hover:border-purple-300 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-purple-800">
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-xs font-bold tracking-wider text-slate-500 uppercase dark:text-slate-400">
                        <span>Academy Engine</span>
                        <div class="rounded-lg bg-purple-50 p-2 text-purple-600 dark:bg-purple-950/60 dark:text-purple-400">
                            <GraduationCap class="h-4 w-4" />
                        </div>
                    </div>

                    <div class="flex items-baseline justify-between">
                        <span class="text-2xl font-black text-purple-600 dark:text-purple-400">
                            {{ metrics.active_batches }} Batches
                        </span>
                        <span class="text-xs font-bold text-purple-700 dark:text-purple-300">
                            {{ metrics.graduation_rate }}% Grad.
                        </span>
                    </div>

                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        {{ metrics.active_students }} Active Interns • {{ metrics.completed_students }} Graduated
                    </p>
                </div>

                <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-3 text-[11px] text-slate-400 dark:border-slate-800">
                    <span>{{ metrics.total_students }} Total Students</span>
                    <Link :href="internshipsIndex.url()" class="font-semibold text-purple-600 hover:underline dark:text-purple-400">View Batches →</Link>
                </div>
            </div>

            <!-- 5. Quality Assurance & Mentorship -->
            <div class="group relative flex flex-col justify-between overflow-hidden rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs transition-all hover:border-amber-300 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-amber-800">
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-xs font-bold tracking-wider text-slate-500 uppercase dark:text-slate-400">
                        <span>Quality & Reports</span>
                        <div class="rounded-lg bg-amber-50 p-2 text-amber-600 dark:bg-amber-950/60 dark:text-amber-400">
                            <FileText class="h-4 w-4" />
                        </div>
                    </div>

                    <div class="flex items-baseline justify-between">
                        <span class="text-2xl font-black text-amber-600 dark:text-amber-400">
                            {{ metrics.pending_reports }}
                        </span>
                        <span v-if="metrics.blockers_count > 0" class="inline-flex items-center gap-1 rounded-full bg-rose-50 px-2 py-0.5 text-[10px] font-extrabold text-rose-600 dark:bg-rose-950/60 dark:text-rose-400">
                            <AlertCircle class="h-3 w-3" /> {{ metrics.blockers_count }} Blockers
                        </span>
                        <span v-else class="text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                            All Clear
                        </span>
                    </div>

                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Weekly Reports Needing Review
                    </p>
                </div>

                <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-3 text-[11px] text-slate-400 dark:border-slate-800">
                    <span>{{ metrics.approved_reports }} of {{ metrics.total_reports }} Approved</span>
                    <Link :href="studentsIndex.url()" class="font-semibold text-amber-600 hover:underline dark:text-amber-400">Review →</Link>
                </div>
            </div>
        </div>

        <!-- 🧭 PERSPECTIVE CONTROLS & LIVE SEARCH BAR -->
        <div class="flex flex-col gap-3 rounded-xl border border-slate-200/80 bg-white p-3 shadow-xs lg:flex-row lg:items-center lg:justify-between dark:border-slate-800 dark:bg-slate-900">
            <!-- Tabs -->
            <div class="flex max-w-full items-center gap-1.5 overflow-x-auto">
                <button
                    @click="activeTab = 'overview'"
                    :class="[
                        'flex items-center gap-2 rounded-lg px-3.5 py-2 text-xs font-bold whitespace-nowrap transition-all',
                        activeTab === 'overview'
                            ? 'bg-indigo-600 text-white shadow-xs'
                            : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-100',
                    ]"
                >
                    <Layers class="h-3.5 w-3.5" /> Command Center
                </button>

                <button
                    @click="activeTab = 'commercial'"
                    :class="[
                        'flex items-center gap-2 rounded-lg px-3.5 py-2 text-xs font-bold whitespace-nowrap transition-all',
                        activeTab === 'commercial'
                            ? 'bg-indigo-600 text-white shadow-xs'
                            : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-100',
                    ]"
                >
                    <DollarSign class="h-3.5 w-3.5" /> Clients & Revenue
                </button>

                <button
                    @click="activeTab = 'projects'"
                    :class="[
                        'flex items-center gap-2 rounded-lg px-3.5 py-2 text-xs font-bold whitespace-nowrap transition-all',
                        activeTab === 'projects'
                            ? 'bg-indigo-600 text-white shadow-xs'
                            : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-100',
                    ]"
                >
                    <Briefcase class="h-3.5 w-3.5" /> Projects & Delivery
                </button>

                <button
                    @click="activeTab = 'academy'"
                    :class="[
                        'flex items-center gap-2 rounded-lg px-3.5 py-2 text-xs font-bold whitespace-nowrap transition-all',
                        activeTab === 'academy'
                            ? 'bg-indigo-600 text-white shadow-xs'
                            : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-100',
                    ]"
                >
                    <GraduationCap class="h-3.5 w-3.5" /> Academy & Talent
                </button>

                <button
                    @click="activeTab = 'action_center'"
                    :class="[
                        'flex items-center gap-2 rounded-lg px-3.5 py-2 text-xs font-bold whitespace-nowrap transition-all',
                        activeTab === 'action_center'
                            ? 'bg-rose-600 text-white shadow-xs'
                            : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-100',
                    ]"
                >
                    <ShieldAlert class="h-3.5 w-3.5" /> Action Center
                    <span v-if="totalUrgentCount > 0" class="rounded-full bg-rose-200 px-1.5 py-0.2 text-[10px] font-black text-rose-800 dark:bg-rose-900 dark:text-rose-200">
                        {{ totalUrgentCount }}
                    </span>
                </button>
            </div>

            <!-- Live Filter / Quick Lookup -->
            <div class="relative w-full sm:w-64">
                <Search class="absolute top-1/2 left-3 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" />
                <Input
                    v-model="globalSearch"
                    placeholder="Quick search dashboard..."
                    class="h-8 border-slate-200 bg-slate-50/50 pl-8 text-xs focus:bg-white dark:border-slate-800 dark:bg-slate-800/40"
                />
            </div>
        </div>

        <!-- 🚨 CRITICAL ATTENTION / URGENT RADAR STRIP (Always visible if urgent items exist) -->
        <div v-if="totalUrgentCount > 0" class="rounded-xl border border-rose-200/80 bg-rose-50/60 p-4 dark:border-rose-900/60 dark:bg-rose-950/20">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-rose-600 text-white shadow-xs">
                        <AlertTriangle class="h-5 w-5" />
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-rose-950 dark:text-rose-100">
                            Immediate Operational Action Required ({{ totalUrgentCount }} items)
                        </h2>
                        <p class="text-xs text-rose-700 dark:text-rose-300">
                            {{ overdueProjects.length }} overdue deliverables • {{ uncontactedLeads.length }} uncontacted leads • {{ pendingReports.length }} pending reports • {{ blockerReports.length }} intern blockers
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <Button
                        @click="activeTab = 'action_center'"
                        size="sm"
                        class="bg-rose-600 text-xs font-semibold text-white hover:bg-rose-700"
                    >
                        Resolve in Action Center →
                    </Button>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- TAB 1: 🌟 COMMAND CENTER (FULL PANORAMIC VIEW) -->
        <!-- ========================================================================= -->
        <div v-if="activeTab === 'overview'" class="space-y-6">
            <!-- Row 1: Interactive Pipeline Funnel & Project Status Radar -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- 1. Enterprise Conversion & Delivery Funnel (2 Cols) -->
                <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs lg:col-span-2 dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                        <div>
                            <h2 class="text-base font-extrabold text-slate-900 dark:text-white">
                                🔄 Commercial & Delivery Pipeline Funnel
                            </h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                End-to-end journey from initial inquiry to final project completion
                            </p>
                        </div>
                        <Badge variant="outline" class="font-mono text-xs">
                            Nexus Conversion Engine
                        </Badge>
                    </div>

                    <!-- 4-Stage Interactive Visual Pipeline -->
                    <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-4">
                        <!-- Stage 1: Inquiries / Leads -->
                        <div class="relative rounded-xl border border-blue-200/70 bg-blue-50/40 p-4 transition-all hover:bg-blue-50 dark:border-blue-900/60 dark:bg-blue-950/20">
                            <span class="text-[10px] font-bold tracking-wider text-blue-600 uppercase dark:text-blue-400">Step 1 • Inbound</span>
                            <div class="mt-2 text-2xl font-black text-blue-950 dark:text-blue-100">{{ metrics.total_leads }}</div>
                            <div class="mt-1 text-xs font-medium text-blue-800 dark:text-blue-300">Total Leads</div>
                            <div class="mt-3 text-[11px] text-blue-600 dark:text-blue-400">
                                {{ metrics.new_leads }} New • {{ metrics.contacted_leads }} Contacted
                            </div>
                        </div>

                        <!-- Stage 2: Qualified & Contacted -->
                        <div class="relative rounded-xl border border-indigo-200/70 bg-indigo-50/40 p-4 transition-all hover:bg-indigo-50 dark:border-indigo-900/60 dark:bg-indigo-950/20">
                            <span class="text-[10px] font-bold tracking-wider text-indigo-600 uppercase dark:text-indigo-400">Step 2 • Negotiation</span>
                            <div class="mt-2 text-2xl font-black text-indigo-950 dark:text-indigo-100">{{ metrics.active_deals }}</div>
                            <div class="mt-1 text-xs font-medium text-indigo-800 dark:text-indigo-300">Active Deals</div>
                            <div class="mt-3 text-[11px] text-indigo-600 dark:text-indigo-400">
                                Estimated: {{ formatCurrency(metrics.total_pipeline_value) }}
                            </div>
                        </div>

                        <!-- Stage 3: Converted & Active Delivery -->
                        <div class="relative rounded-xl border border-purple-200/70 bg-purple-50/40 p-4 transition-all hover:bg-purple-50 dark:border-purple-900/60 dark:bg-purple-950/20">
                            <span class="text-[10px] font-bold tracking-wider text-purple-600 uppercase dark:text-purple-400">Step 3 • In Production</span>
                            <div class="mt-2 text-2xl font-black text-purple-950 dark:text-purple-100">{{ metrics.active_projects }}</div>
                            <div class="mt-1 text-xs font-medium text-purple-800 dark:text-purple-300">Active Projects</div>
                            <div class="mt-3 text-[11px] text-purple-600 dark:text-purple-400">
                                Conv. Rate: <span class="font-bold">{{ metrics.conversion_rate }}%</span>
                            </div>
                        </div>

                        <!-- Stage 4: Completed Deliverables -->
                        <div class="relative rounded-xl border border-emerald-200/70 bg-emerald-50/40 p-4 transition-all hover:bg-emerald-50 dark:border-emerald-900/60 dark:bg-emerald-950/20">
                            <span class="text-[10px] font-bold tracking-wider text-emerald-600 uppercase dark:text-emerald-400">Step 4 • Delivered</span>
                            <div class="mt-2 text-2xl font-black text-emerald-950 dark:text-emerald-100">{{ metrics.completed_projects }}</div>
                            <div class="mt-1 text-xs font-medium text-emerald-800 dark:text-emerald-300">Delivered Success</div>
                            <div class="mt-3 text-[11px] text-emerald-600 dark:text-emerald-400">
                                Revenue: <span class="font-bold">{{ formatCurrency(metrics.converted_revenue) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Visual Funnel Progress Bar -->
                    <div class="mt-5 space-y-1.5">
                        <div class="flex justify-between text-xs font-semibold text-slate-600 dark:text-slate-400">
                            <span>Pipeline Velocity & Conversion Efficiency</span>
                            <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ metrics.conversion_rate }}% Client Conversion Ratio</span>
                        </div>
                        <div class="h-2.5 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                            <div
                                class="h-2.5 rounded-full bg-gradient-to-r from-blue-500 via-indigo-500 to-emerald-500 transition-all duration-700"
                                :style="{ width: `${Math.min(100, Math.max(10, metrics.conversion_rate))}%` }"
                            ></div>
                        </div>
                    </div>
                </div>

                <!-- 2. Project Status Distribution Chart (1 Col) -->
                <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                    <div class="border-b border-slate-100 pb-3 dark:border-slate-800">
                        <h2 class="text-base font-extrabold text-slate-900 dark:text-white">
                            📊 Project Workload Distribution
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Current operational status across all projects
                        </p>
                    </div>

                    <div class="mt-4 space-y-3">
                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2">
                                <span class="h-3 w-3 rounded-full bg-amber-500"></span>
                                <span class="font-medium text-slate-700 dark:text-slate-300">In Progress</span>
                            </div>
                            <span class="font-extrabold text-slate-900 dark:text-white">{{ projectsByStatus.in_progress || 0 }}</span>
                        </div>

                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2">
                                <span class="h-3 w-3 rounded-full bg-blue-500"></span>
                                <span class="font-medium text-slate-700 dark:text-slate-300">Planning & Prep</span>
                            </div>
                            <span class="font-extrabold text-slate-900 dark:text-white">{{ projectsByStatus.planning || 0 }}</span>
                        </div>

                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2">
                                <span class="h-3 w-3 rounded-full bg-purple-500"></span>
                                <span class="font-medium text-slate-700 dark:text-slate-300">Under Review</span>
                            </div>
                            <span class="font-extrabold text-slate-900 dark:text-white">{{ projectsByStatus.under_review || 0 }}</span>
                        </div>

                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2">
                                <span class="h-3 w-3 rounded-full bg-emerald-500"></span>
                                <span class="font-medium text-slate-700 dark:text-slate-300">Completed</span>
                            </div>
                            <span class="font-extrabold text-slate-900 dark:text-white">{{ projectsByStatus.completed || 0 }}</span>
                        </div>

                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center gap-2">
                                <span class="h-3 w-3 rounded-full bg-slate-400"></span>
                                <span class="font-medium text-slate-700 dark:text-slate-300">On Hold</span>
                            </div>
                            <span class="font-extrabold text-slate-900 dark:text-white">{{ projectsByStatus.on_hold || 0 }}</span>
                        </div>
                    </div>

                    <div class="mt-5 rounded-lg border border-slate-100 bg-slate-50 p-3 text-xs dark:border-slate-800 dark:bg-slate-800/40">
                        <div class="flex items-center justify-between font-medium text-slate-600 dark:text-slate-300">
                            <span>Active Project Health:</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400">
                                {{ metrics.active_projects - overdueProjects.length }} of {{ metrics.active_projects }} On-Track
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Row 2: Live Projects Execution Matrix & Urgent Deadlines -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Live Projects Matrix (2 Cols) -->
                <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs lg:col-span-2 dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                        <div>
                            <h2 class="text-base font-extrabold text-slate-900 dark:text-white">
                                🚀 Active Delivery Matrix
                            </h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Real-time completion progress and team allocations
                            </p>
                        </div>
                        <Link :href="projectsIndex.url()" class="text-xs font-bold text-indigo-600 hover:underline dark:text-indigo-400">
                            View All Projects ({{ metrics.active_projects }}) →
                        </Link>
                    </div>

                    <div class="mt-4 divide-y divide-slate-100 dark:divide-slate-800">
                        <div
                            v-for="project in filteredRecentProjects"
                            :key="project.id"
                            class="group flex flex-col justify-between gap-3 py-3.5 transition-colors sm:flex-row sm:items-center"
                        >
                            <div class="min-w-0 flex-1 space-y-1">
                                <div class="flex items-center gap-2">
                                    <Link
                                        :href="projectsShow.url(project.id)"
                                        class="truncate text-sm font-extrabold text-slate-900 transition-colors hover:text-indigo-600 dark:text-white dark:hover:text-indigo-400"
                                    >
                                        {{ project.title }}
                                    </Link>
                                    <span
                                        :class="[
                                            'inline-flex items-center gap-1 rounded-full border px-2 py-0.2 text-[10px] font-semibold',
                                            getProjectStatusConfig(project.status).badgeClass,
                                        ]"
                                    >
                                        <span :class="['h-1.5 w-1.5 rounded-full', getProjectStatusConfig(project.status).dotClass]"></span>
                                        {{ getProjectStatusConfig(project.status).label }}
                                    </span>
                                </div>

                                <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500 dark:text-slate-400">
                                    <span v-if="project.client" class="flex items-center gap-1 font-medium text-slate-700 dark:text-slate-300">
                                        <Building2 class="h-3 w-3 text-slate-400" /> {{ project.client.name }}
                                    </span>
                                    <span v-else class="flex items-center gap-1 font-medium text-indigo-600 dark:text-indigo-400">
                                        <Wrench class="h-3 w-3" /> Internal Task
                                    </span>

                                    <span v-if="project.service" class="text-slate-400">• {{ project.service.name }}</span>

                                    <span v-if="project.deadline" class="flex items-center gap-1 font-mono text-[11px]">
                                        <Clock class="h-3 w-3 text-slate-400" /> Due {{ project.deadline }}
                                    </span>
                                </div>
                            </div>

                            <!-- Progress & Action -->
                            <div class="flex items-center gap-4 sm:w-48 sm:shrink-0">
                                <div class="w-full space-y-1">
                                    <div class="flex justify-between text-[11px] font-bold">
                                        <span class="text-slate-400">Progress</span>
                                        <span class="text-slate-900 dark:text-white">{{ project.progress }}%</span>
                                    </div>
                                    <div class="h-1.5 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                        <div
                                            :class="['h-1.5 rounded-full transition-all', getProgressGradient(project.progress)]"
                                            :style="{ width: `${project.progress}%` }"
                                        ></div>
                                    </div>
                                </div>

                                <Link
                                    :href="projectsShow.url(project.id)"
                                    class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                                >
                                    <Eye class="h-4 w-4" />
                                </Link>
                            </div>
                        </div>

                        <div v-if="filteredRecentProjects.length === 0" class="py-8 text-center text-xs text-slate-500">
                            No matching projects found.
                        </div>
                    </div>
                </div>

                <!-- Upcoming & Overdue Deadlines Radar (1 Col) -->
                <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                    <div class="border-b border-slate-100 pb-3 dark:border-slate-800">
                        <h2 class="text-base font-extrabold text-slate-900 dark:text-white">
                            ⏰ Deadlines Radar
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Critical milestones due in the next 14 days
                        </p>
                    </div>

                    <!-- Overdue items -->
                    <div class="mt-4 space-y-3">
                        <div v-if="overdueProjects.length > 0" class="space-y-2">
                            <span class="text-[10px] font-bold tracking-wider text-rose-600 uppercase">⚠️ Overdue Projects</span>
                            <div
                                v-for="proj in overdueProjects.slice(0, 3)"
                                :key="proj.id"
                                class="rounded-lg border border-rose-200 bg-rose-50/50 p-2.5 text-xs dark:border-rose-900 dark:bg-rose-950/30"
                            >
                                <div class="flex items-center justify-between">
                                    <Link :href="projectsShow.url(proj.id)" class="font-bold text-rose-950 hover:underline dark:text-rose-100">
                                        {{ proj.title }}
                                    </Link>
                                    <span class="font-mono text-[10px] font-bold text-rose-600">Past Due</span>
                                </div>
                                <div class="mt-1 flex items-center justify-between text-[11px] text-rose-700 dark:text-rose-400">
                                    <span>{{ proj.client ? proj.client.name : 'Internal Practice' }}</span>
                                    <span>{{ proj.deadline }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Upcoming 14 days -->
                        <div v-if="upcomingDeadlines.length > 0" class="space-y-2">
                            <span class="text-[10px] font-bold tracking-wider text-amber-600 uppercase">📅 Due in Next 14 Days</span>
                            <div
                                v-for="proj in upcomingDeadlines.slice(0, 3)"
                                :key="proj.id"
                                class="rounded-lg border border-amber-200 bg-amber-50/50 p-2.5 text-xs dark:border-amber-900 dark:bg-amber-950/30"
                            >
                                <div class="flex items-center justify-between">
                                    <Link :href="projectsShow.url(proj.id)" class="font-bold text-amber-950 hover:underline dark:text-amber-100">
                                        {{ proj.title }}
                                    </Link>
                                    <span class="font-mono text-[10px] font-bold text-amber-600">{{ proj.progress }}%</span>
                                </div>
                                <div class="mt-1 flex items-center justify-between text-[11px] text-amber-700 dark:text-amber-400">
                                    <span>{{ proj.client ? proj.client.name : 'Internal Practice' }}</span>
                                    <span>{{ proj.deadline }}</span>
                                </div>
                            </div>
                        </div>

                        <div v-if="overdueProjects.length === 0 && upcomingDeadlines.length === 0" class="py-10 text-center">
                            <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-emerald-50 text-emerald-600 dark:bg-emerald-950 dark:text-emerald-400">
                                <CheckCircle2 class="h-6 w-6" />
                            </div>
                            <p class="mt-2 text-xs font-bold text-slate-700 dark:text-slate-300">All Project Deadlines Clear</p>
                            <p class="text-[11px] text-slate-400">No overdue or immediate delivery emergencies.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Row 3: Academy Batches, Top Talent & Real-time Activity Feed -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- 1. Active Academy Batches Lifecycle (1 Col) -->
                <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                        <div>
                            <h2 class="text-base font-extrabold text-slate-900 dark:text-white">
                                🎓 Active Batches
                            </h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Running training cohorts
                            </p>
                        </div>
                        <Link :href="internshipsIndex.url()" class="text-xs font-bold text-purple-600 hover:underline dark:text-purple-400">
                            All Batches →
                        </Link>
                    </div>

                    <div class="mt-4 space-y-3">
                        <div
                            v-for="batch in activeBatches.slice(0, 3)"
                            :key="batch.id"
                            class="rounded-xl border border-purple-100 bg-purple-50/30 p-3.5 text-xs dark:border-purple-900/60 dark:bg-purple-950/20"
                        >
                            <div class="flex items-center justify-between">
                                <Link :href="internshipsShow.url(batch.id)" class="font-extrabold text-purple-950 hover:underline dark:text-purple-100">
                                    {{ batch.name }}
                                </Link>
                                <span class="rounded bg-purple-200 px-1.5 py-0.5 font-mono text-[10px] font-bold text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                    {{ batch.batch_no }}
                                </span>
                            </div>

                            <p class="mt-1 text-[11px] text-purple-700 dark:text-purple-400">
                                Track: {{ batch.service }} • {{ batch.students_count }} Interns Enrolled
                            </p>

                            <!-- Batch Progress -->
                            <div class="mt-3 space-y-1">
                                <div class="flex justify-between text-[10px] font-bold">
                                    <span class="text-slate-500">Cohort Timeline</span>
                                    <span class="text-purple-700 dark:text-purple-300">{{ batch.days_remaining }} days left ({{ batch.progress_percent }}%)</span>
                                </div>
                                <div class="h-1.5 w-full overflow-hidden rounded-full bg-purple-100 dark:bg-purple-950">
                                    <div
                                        class="h-1.5 rounded-full bg-purple-600 transition-all"
                                        :style="{ width: `${batch.progress_percent}%` }"
                                    ></div>
                                </div>
                            </div>
                        </div>

                        <div v-if="activeBatches.length === 0" class="py-6 text-center text-xs text-slate-500">
                            No active batches currently running.
                        </div>
                    </div>
                </div>

                <!-- 2. Star Interns & Talent Leaders (1 Col) -->
                <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                        <div>
                            <h2 class="text-base font-extrabold text-slate-900 dark:text-white">
                                ⭐ Star Interns
                            </h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                High-velocity student talent
                            </p>
                        </div>
                        <Link :href="studentsIndex.url()" class="text-xs font-bold text-indigo-600 hover:underline dark:text-indigo-400">
                            All Students →
                        </Link>
                    </div>

                    <div class="mt-4 divide-y divide-slate-100 dark:divide-slate-800">
                        <div
                            v-for="student in topStudents"
                            :key="student.id"
                            class="flex items-center justify-between py-2.5 text-xs"
                        >
                            <div class="flex items-center gap-2.5">
                                <Avatar class="h-8 w-8 border border-slate-200 dark:border-slate-700">
                                    <AvatarFallback :class="['text-[11px] font-bold', getAvatarColor(student.name)]">
                                        {{ getInitials(student.name) }}
                                    </AvatarFallback>
                                </Avatar>
                                <div>
                                    <Link :href="studentsShow.url(student.id)" class="font-bold text-slate-900 hover:text-indigo-600 dark:text-white dark:hover:text-indigo-400">
                                        {{ student.name }}
                                    </Link>
                                    <p class="text-[11px] text-slate-500">
                                        {{ student.internship ? student.internship.batch_no : 'General' }} • {{ student.projects_count || 0 }} Projects
                                    </p>
                                </div>
                            </div>

                            <div class="text-right">
                                <span class="font-extrabold text-indigo-600 dark:text-indigo-400">
                                    {{ student.overall_progress }}%
                                </span>
                                <p class="text-[10px] text-slate-400">Overall Progress</p>
                            </div>
                        </div>

                        <div v-if="topStudents.length === 0" class="py-6 text-center text-xs text-slate-500">
                            No students registered yet.
                        </div>
                    </div>
                </div>

                <!-- 3. Unified Real-Time Activity Feed (1 Col) -->
                <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                    <div class="border-b border-slate-100 pb-3 dark:border-slate-800">
                        <h2 class="text-base font-extrabold text-slate-900 dark:text-white">
                            ⚡ Real-Time Audit Stream
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Chronological events across WebEra-Nexus
                        </p>
                    </div>

                    <div class="mt-4 space-y-3">
                        <div
                            v-for="(item, idx) in activityStream.slice(0, 5)"
                            :key="idx"
                            class="flex items-start gap-2.5 rounded-lg border border-slate-100 bg-slate-50/50 p-2.5 text-xs transition-colors hover:bg-slate-100 dark:border-slate-800 dark:bg-slate-800/40 dark:hover:bg-slate-800"
                        >
                            <!-- Icon -->
                            <div class="mt-0.5 shrink-0">
                                <span v-if="item.type === 'client'" class="flex h-6 w-6 items-center justify-center rounded-md bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300">
                                    <Users class="h-3.5 w-3.5" />
                                </span>
                                <span v-else-if="item.type === 'project'" class="flex h-6 w-6 items-center justify-center rounded-md bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300">
                                    <FolderKanban class="h-3.5 w-3.5" />
                                </span>
                                <span v-else class="flex h-6 w-6 items-center justify-center rounded-md bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300">
                                    <FileText class="h-3.5 w-3.5" />
                                </span>
                            </div>

                            <div class="min-w-0 flex-1">
                                <a :href="item.link" class="block truncate font-bold text-slate-900 hover:text-indigo-600 dark:text-white dark:hover:text-indigo-400">
                                    {{ item.title }}
                                </a>
                                <p class="truncate text-[11px] text-slate-500 dark:text-slate-400">
                                    {{ item.subtitle }}
                                </p>
                            </div>

                            <span class="shrink-0 text-[10px] text-slate-400">
                                {{ item.timestamp }}
                            </span>
                        </div>

                        <div v-if="activityStream.length === 0" class="py-6 text-center text-xs text-slate-500">
                            No recent activity recorded.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- TAB 2: 💼 CLIENTS & REVENUE PIPELINE -->
        <!-- ========================================================================= -->
        <div v-else-if="activeTab === 'commercial'" class="space-y-6">
            <!-- Commercial Metrics Summary -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                    <span class="text-xs font-bold text-slate-500 uppercase">Total Estimated Deal Pipeline</span>
                    <div class="mt-2 text-3xl font-black text-emerald-600 dark:text-emerald-400">{{ formatCurrency(metrics.total_pipeline_value) }}</div>
                    <p class="mt-1 text-xs text-slate-400">Combined budget across client requirements</p>
                </div>

                <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                    <span class="text-xs font-bold text-slate-500 uppercase">Converted Client Contracts</span>
                    <div class="mt-2 text-3xl font-black text-indigo-600 dark:text-indigo-400">{{ formatCurrency(metrics.converted_revenue) }}</div>
                    <p class="mt-1 text-xs text-slate-400">{{ metrics.active_clients }} Converted paying enterprise clients</p>
                </div>

                <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                    <span class="text-xs font-bold text-slate-500 uppercase">Conversion Efficiency</span>
                    <div class="mt-2 text-3xl font-black text-blue-600 dark:text-blue-400">{{ metrics.conversion_rate }}%</div>
                    <p class="mt-1 text-xs text-slate-400">{{ metrics.active_clients }} converted out of {{ metrics.total_clients }} total contacts</p>
                </div>
            </div>

            <!-- Service Domain Demand Breakdown -->
            <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900 dark:text-white">
                            🌐 Service Track Market Demand
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Active projects and client requirements mapped across service tracks
                        </p>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="svc in servicesBreakdown"
                        :key="svc.id"
                        class="rounded-xl border border-slate-200/80 bg-slate-50/50 p-4 transition-all hover:bg-white hover:shadow-xs dark:border-slate-800 dark:bg-slate-800/40 dark:hover:bg-slate-800"
                    >
                        <div class="flex items-center justify-between">
                            <span class="font-extrabold text-slate-900 dark:text-white">{{ svc.name }}</span>
                            <Badge variant="outline" class="font-mono text-[10px] capitalize">
                                {{ svc.type }}
                            </Badge>
                        </div>

                        <div class="mt-3 flex items-center justify-between text-xs text-slate-600 dark:text-slate-300">
                            <span>Projects: <strong class="text-slate-900 dark:text-white">{{ svc.projects_count }}</strong></span>
                            <span>Clients: <strong class="text-slate-900 dark:text-white">{{ svc.clients_count }}</strong></span>
                            <span>Batches: <strong class="text-slate-900 dark:text-white">{{ svc.internships_count }}</strong></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cold Leads Action Hub -->
            <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900 dark:text-white">
                            🎯 Uncontacted Leads Awaiting Response
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Inquiries needing qualification to prevent lead leakage
                        </p>
                    </div>
                    <Button as-child size="sm" class="bg-indigo-600 text-white">
                        <Link :href="clientsCreate.url()">+ Add New Lead</Link>
                    </Button>
                </div>

                <div class="mt-4 divide-y divide-slate-100 dark:divide-slate-800">
                    <div
                        v-for="lead in uncontactedLeads"
                        :key="lead.id"
                        class="flex flex-col justify-between gap-3 py-3 sm:flex-row sm:items-center"
                    >
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-900 dark:text-white">{{ lead.name }}</span>
                                <Badge variant="outline" class="border-rose-200 bg-rose-50 text-[10px] font-bold text-rose-700 dark:border-rose-900 dark:bg-rose-950/50 dark:text-rose-300">
                                    New Lead
                                </Badge>
                            </div>
                            <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500">
                                <span v-if="lead.company_name" class="font-medium text-slate-700 dark:text-slate-300">
                                    {{ lead.company_name }}
                                </span>
                                <span class="flex items-center gap-1"><Mail class="h-3 w-3" /> {{ lead.email }}</span>
                                <span v-if="lead.phone" class="flex items-center gap-1"><Phone class="h-3 w-3" /> {{ lead.phone }}</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <a
                                :href="`mailto:${lead.email}`"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-xs hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200"
                            >
                                <Mail class="h-3.5 w-3.5 text-slate-400" /> Send Email
                            </a>
                            <Link
                                :href="clientsShow.url(lead.id)"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-xs hover:bg-indigo-500"
                            >
                                Contact / Convert →
                            </Link>
                        </div>
                    </div>

                    <div v-if="uncontactedLeads.length === 0" class="py-8 text-center text-xs text-slate-500">
                        🎉 All leads have been contacted promptly!
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- TAB 3: 🚀 PROJECTS & OPERATIONAL RADAR -->
        <!-- ========================================================================= -->
        <div v-else-if="activeTab === 'projects'" class="space-y-6">
            <!-- Project Execution Stats Grid -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                    <span class="text-xs font-bold text-slate-500 uppercase">Active Engagements</span>
                    <div class="mt-2 text-3xl font-black text-indigo-600 dark:text-indigo-400">{{ metrics.active_projects }}</div>
                    <p class="mt-1 text-xs text-slate-400">{{ metrics.client_projects }} Client • {{ metrics.internal_tasks }} Internal</p>
                </div>

                <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                    <span class="text-xs font-bold text-slate-500 uppercase">Avg Delivery Progress</span>
                    <div class="mt-2 text-3xl font-black text-blue-600 dark:text-blue-400">{{ metrics.avg_project_progress }}%</div>
                    <p class="mt-1 text-xs text-slate-400">Across all running project phases</p>
                </div>

                <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                    <span class="text-xs font-bold text-slate-500 uppercase">Overdue Items</span>
                    <div class="mt-2 text-3xl font-black text-rose-600 dark:text-rose-400">{{ overdueProjects.length }}</div>
                    <p class="mt-1 text-xs text-slate-400">Past target completion date</p>
                </div>

                <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                    <span class="text-xs font-bold text-slate-500 uppercase">Completed Deliverables</span>
                    <div class="mt-2 text-3xl font-black text-emerald-600 dark:text-emerald-400">{{ metrics.completed_projects }}</div>
                    <p class="mt-1 text-xs text-slate-400">Successfully signed off</p>
                </div>
            </div>

            <!-- Overdue Projects Table -->
            <div v-if="overdueProjects.length > 0" class="rounded-xl border border-rose-200 bg-white p-5 shadow-xs dark:border-rose-900/60 dark:bg-slate-900">
                <div class="flex items-center justify-between border-b border-rose-100 pb-3 dark:border-rose-900/40">
                    <div>
                        <h2 class="text-base font-extrabold text-rose-900 dark:text-rose-200">
                            🚨 Overdue Projects ({{ overdueProjects.length }})
                        </h2>
                        <p class="text-xs text-rose-600 dark:text-rose-400">
                            Deliverables that require immediate schedule re-alignment or blocker removal
                        </p>
                    </div>
                </div>

                <div class="mt-4 divide-y divide-rose-100 dark:divide-rose-950">
                    <div
                        v-for="proj in overdueProjects"
                        :key="proj.id"
                        class="flex flex-col justify-between gap-3 py-3 sm:flex-row sm:items-center"
                    >
                        <div>
                            <Link :href="projectsShow.url(proj.id)" class="text-sm font-bold text-rose-950 hover:underline dark:text-rose-100">
                                {{ proj.title }}
                            </Link>
                            <p class="text-xs text-slate-500">
                                Client: {{ proj.client ? proj.client.name : 'Internal Practice' }} • Service: {{ proj.service?.name || 'General' }}
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="font-mono text-xs font-bold text-rose-600">Due: {{ proj.deadline }}</span>
                            <Button as-child size="sm" class="bg-rose-600 text-xs text-white hover:bg-rose-700">
                                <Link :href="projectsEdit.url(proj.id)">Edit / Reschedule</Link>
                            </Button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- All Active Projects Detailed List -->
            <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900 dark:text-white">
                            📋 Projects Execution Roster
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Active projects with progress breakdown and quick actions
                        </p>
                    </div>
                    <Button as-child size="sm" class="bg-indigo-600 text-white">
                        <Link :href="projectsCreate.url()">+ Create Project</Link>
                    </Button>
                </div>

                <div class="mt-4 divide-y divide-slate-100 dark:divide-slate-800">
                    <div
                        v-for="project in filteredRecentProjects"
                        :key="project.id"
                        class="flex flex-col justify-between gap-3 py-4 sm:flex-row sm:items-center"
                    >
                        <div class="min-w-0 flex-1 space-y-1">
                            <div class="flex items-center gap-2">
                                <Link :href="projectsShow.url(project.id)" class="font-extrabold text-slate-900 hover:text-indigo-600 dark:text-white">
                                    {{ project.title }}
                                </Link>
                                <span :class="['inline-flex items-center gap-1 rounded-full border px-2 py-0.2 text-[10px] font-semibold', getProjectStatusConfig(project.status).badgeClass]">
                                    {{ getProjectStatusConfig(project.status).label }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-500">
                                {{ project.client ? `Client: ${project.client.name}` : 'Internal Task' }} • Track: {{ project.service?.name || 'General' }}
                            </p>
                        </div>

                        <div class="flex items-center gap-4 sm:w-60">
                            <div class="w-full space-y-1">
                                <div class="flex justify-between text-xs font-semibold">
                                    <span class="text-slate-400">Progress</span>
                                    <span>{{ project.progress }}%</span>
                                </div>
                                <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                    <div
                                        :class="['h-2 rounded-full transition-all', getProgressGradient(project.progress)]"
                                        :style="{ width: `${project.progress}%` }"
                                    ></div>
                                </div>
                            </div>
                            <Button as-child variant="outline" size="sm" class="shrink-0 text-xs">
                                <Link :href="projectsShow.url(project.id)">View</Link>
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- TAB 4: 🎓 ACADEMY & TALENT ENGINE -->
        <!-- ========================================================================= -->
        <div v-else-if="activeTab === 'academy'" class="space-y-6">
            <!-- Academy Stats Grid -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-4">
                <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                    <span class="text-xs font-bold text-slate-500 uppercase">Active Cohorts</span>
                    <div class="mt-2 text-3xl font-black text-purple-600 dark:text-purple-400">{{ metrics.active_batches }}</div>
                    <p class="mt-1 text-xs text-slate-400">{{ metrics.total_batches }} total batches launched</p>
                </div>

                <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                    <span class="text-xs font-bold text-slate-500 uppercase">Active Interns</span>
                    <div class="mt-2 text-3xl font-black text-indigo-600 dark:text-indigo-400">{{ metrics.active_students }}</div>
                    <p class="mt-1 text-xs text-slate-400">{{ metrics.total_students }} total enrolled students</p>
                </div>

                <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                    <span class="text-xs font-bold text-slate-500 uppercase">Graduation Rate</span>
                    <div class="mt-2 text-3xl font-black text-emerald-600 dark:text-emerald-400">{{ metrics.graduation_rate }}%</div>
                    <p class="mt-1 text-xs text-slate-400">{{ metrics.completed_students }} graduated interns</p>
                </div>

                <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                    <span class="text-xs font-bold text-slate-500 uppercase">Avg Intern Progress</span>
                    <div class="mt-2 text-3xl font-black text-blue-600 dark:text-blue-400">{{ metrics.avg_student_progress }}%</div>
                    <p class="mt-1 text-xs text-slate-400">Across curriculum milestones</p>
                </div>
            </div>

            <!-- Active Batches Milestones -->
            <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900 dark:text-white">
                            🎓 Batch Lifecycle Progress
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Active internship batches with timeline progress and enrolled headcount
                        </p>
                    </div>
                    <Button as-child size="sm" class="bg-purple-600 text-white hover:bg-purple-700">
                        <Link :href="internshipsCreate.url()">+ Launch Batch</Link>
                    </Button>
                </div>

                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="batch in activeBatches"
                        :key="batch.id"
                        class="rounded-xl border border-purple-200/80 bg-purple-50/20 p-5 dark:border-purple-900/60 dark:bg-purple-950/20"
                    >
                        <div class="flex items-start justify-between">
                            <div>
                                <Link :href="internshipsShow.url(batch.id)" class="text-base font-extrabold text-purple-950 hover:underline dark:text-purple-100">
                                    {{ batch.name }}
                                </Link>
                                <p class="text-xs text-purple-700 dark:text-purple-400">{{ batch.service }}</p>
                            </div>
                            <span class="rounded bg-purple-200 px-2 py-0.5 font-mono text-xs font-bold text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                {{ batch.batch_no }}
                            </span>
                        </div>

                        <div class="mt-4 space-y-2">
                            <div class="flex justify-between text-xs text-slate-600 dark:text-slate-300">
                                <span>Interns: <strong>{{ batch.students_count }}</strong></span>
                                <span>{{ batch.days_remaining }} days remaining</span>
                            </div>

                            <div class="h-2 w-full overflow-hidden rounded-full bg-purple-100 dark:bg-purple-950">
                                <div
                                    class="h-2 rounded-full bg-purple-600 transition-all"
                                    :style="{ width: `${batch.progress_percent}%` }"
                                ></div>
                            </div>
                        </div>

                        <div class="mt-4 flex items-center justify-between border-t border-purple-100 pt-3 text-[11px] text-slate-500 dark:border-purple-900/40">
                            <span>{{ batch.start_date }} → {{ batch.end_date }}</span>
                            <Link :href="internshipsShow.url(batch.id)" class="font-bold text-purple-600 hover:underline dark:text-purple-400">View Roster →</Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Students Leaderboard -->
            <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                    <div>
                        <h2 class="text-base font-extrabold text-slate-900 dark:text-white">
                            ⭐ Star Interns Leaderboard
                        </h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Top performing interns ranked by overall progress and client project assignments
                        </p>
                    </div>
                    <Button as-child size="sm" class="bg-indigo-600 text-white">
                        <Link :href="studentsCreate.url()">+ Enroll Intern</Link>
                    </Button>
                </div>

                <div class="mt-4 divide-y divide-slate-100 dark:divide-slate-800">
                    <div
                        v-for="(student, rank) in topStudents"
                        :key="student.id"
                        class="flex flex-col justify-between gap-3 py-3 sm:flex-row sm:items-center"
                    >
                        <div class="flex items-center gap-3">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-slate-100 font-mono text-xs font-bold text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                #{{ rank + 1 }}
                            </span>
                            <Avatar class="h-9 w-9 border border-slate-200 dark:border-slate-700">
                                <AvatarFallback :class="['text-xs font-bold', getAvatarColor(student.name)]">
                                    {{ getInitials(student.name) }}
                                </AvatarFallback>
                            </Avatar>
                            <div>
                                <Link :href="studentsShow.url(student.id)" class="font-bold text-slate-900 hover:text-indigo-600 dark:text-white">
                                    {{ student.name }}
                                </Link>
                                <p class="text-xs text-slate-500">
                                    Batch: {{ student.internship ? student.internship.name : 'Unassigned' }} • {{ student.projects_count || 0 }} Live Projects
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <div class="w-32 text-right">
                                <div class="text-xs font-bold text-slate-900 dark:text-white">{{ student.overall_progress }}% Progress</div>
                                <div class="mt-1 h-1.5 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                    <div
                                        :class="['h-1.5 rounded-full', getProgressGradient(student.overall_progress)]"
                                        :style="{ width: `${student.overall_progress}%` }"
                                    ></div>
                                </div>
                            </div>
                            <Button as-child variant="outline" size="sm" class="text-xs">
                                <Link :href="studentsShow.url(student.id)">View Profile</Link>
                            </Button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- TAB 5: 🚨 ACTION CENTER & MENTORSHIP HUB -->
        <!-- ========================================================================= -->
        <div v-else-if="activeTab === 'action_center'" class="space-y-6">
            <!-- Action Center Banner -->
            <div class="rounded-xl border border-rose-200 bg-rose-50 p-5 dark:border-rose-900 dark:bg-rose-950/30">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-rose-600 text-white shadow-md">
                        <ShieldAlert class="h-6 w-6" />
                    </div>
                    <div>
                        <h2 class="text-base font-extrabold text-rose-950 dark:text-rose-100">
                            Executive Action & Mentorship Resolution Center
                        </h2>
                        <p class="text-xs text-rose-700 dark:text-rose-300">
                            Prioritized operations requiring admin decisions: unblock interns, review pending reports, rescue overdue deliverables, and contact incoming leads.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Grid: Intern Blockers & Weekly Reports Needing Review -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- 1. Critical Intern Blockers -->
                <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                        <div>
                            <h2 class="text-base font-extrabold text-rose-600 dark:text-rose-400">
                                ⚡ Active Intern Blockers ({{ blockerReports.length }})
                            </h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Obstacles reported in weekly reports needing mentor rescue
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 space-y-3">
                        <div
                            v-for="block in blockerReports"
                            :key="block.id"
                            class="rounded-xl border border-rose-200/80 bg-rose-50/40 p-4 dark:border-rose-900/60 dark:bg-rose-950/20"
                        >
                            <div class="flex items-center justify-between">
                                <div class="font-extrabold text-slate-900 dark:text-white">
                                    {{ block.student ? block.student.name : `Intern #${block.student_id}` }}
                                </div>
                                <span class="rounded bg-rose-100 px-2 py-0.5 text-[10px] font-bold text-rose-800 dark:bg-rose-900 dark:text-rose-200">
                                    Week {{ block.week_number }} Blocker
                                </span>
                            </div>

                            <p class="mt-2 rounded-lg border border-rose-200/50 bg-white/70 p-2.5 text-xs text-rose-900 dark:border-rose-900/40 dark:bg-slate-900/70 dark:text-rose-200">
                                "{{ block.blockers }}"
                            </p>

                            <div class="mt-3 flex items-center justify-between">
                                <span class="text-[10px] text-slate-400">{{ block.created_at }}</span>
                                <Link
                                    v-if="block.student"
                                    :href="studentsShow.url(block.student.id)"
                                    class="inline-flex items-center gap-1 text-xs font-bold text-rose-600 hover:underline dark:text-rose-400"
                                >
                                    Unblock Intern →
                                </Link>
                            </div>
                        </div>

                        <div v-if="blockerReports.length === 0" class="py-8 text-center text-xs text-slate-500">
                            🎉 No active blockers reported by students!
                        </div>
                    </div>
                </div>

                <!-- 2. Weekly Reports Awaiting Review -->
                <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3 dark:border-slate-800">
                        <div>
                            <h2 class="text-base font-extrabold text-amber-600 dark:text-amber-400">
                                📝 Weekly Reports Awaiting Review ({{ pendingReports.length }})
                            </h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Student submissions pending admin assessment
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 space-y-3">
                        <div
                            v-for="rep in pendingReports"
                            :key="rep.id"
                            class="flex items-center justify-between rounded-xl border border-amber-200/80 bg-amber-50/40 p-3.5 text-xs dark:border-amber-900/60 dark:bg-amber-950/20"
                        >
                            <div>
                                <div class="font-extrabold text-amber-950 dark:text-amber-100">
                                    {{ rep.student.name }}
                                </div>
                                <div class="text-[11px] text-amber-800 dark:text-amber-300">
                                    Week {{ rep.week_number }} Report • Status: {{ rep.status }}
                                </div>
                            </div>

                            <Link
                                :href="studentsShow.url(rep.student.id)"
                                class="rounded-lg bg-amber-600 px-3 py-1.5 font-bold text-white shadow-xs hover:bg-amber-500"
                            >
                                Review Now
                            </Link>
                        </div>

                        <div v-if="pendingReports.length === 0" class="py-8 text-center text-xs text-slate-500">
                            ✨ All weekly reports have been reviewed!
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grid: Overdue Deliverables & Cold Leads -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <!-- Overdue Deliverables -->
                <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                    <div class="border-b border-slate-100 pb-3 dark:border-slate-800">
                        <h2 class="text-base font-extrabold text-rose-600 dark:text-rose-400">
                            ⏰ Overdue Deliverables ({{ overdueProjects.length }})
                        </h2>
                    </div>

                    <div class="mt-4 space-y-3">
                        <div
                            v-for="proj in overdueProjects"
                            :key="proj.id"
                            class="flex items-center justify-between rounded-xl border border-rose-200 bg-rose-50/40 p-3.5 text-xs dark:border-rose-900 dark:bg-rose-950/20"
                        >
                            <div>
                                <div class="font-extrabold text-rose-950 dark:text-rose-100">{{ proj.title }}</div>
                                <div class="text-[11px] text-rose-700 dark:text-rose-400">
                                    {{ proj.client ? proj.client.name : 'Internal Practice' }} • Due: {{ proj.deadline }}
                                </div>
                            </div>
                            <Button as-child size="sm" class="bg-rose-600 text-xs text-white hover:bg-rose-700">
                                <Link :href="projectsEdit.url(proj.id)">Fix / Reschedule</Link>
                            </Button>
                        </div>

                        <div v-if="overdueProjects.length === 0" class="py-8 text-center text-xs text-slate-500">
                            🎉 No projects overdue!
                        </div>
                    </div>
                </div>

                <!-- Uncontacted Leads -->
                <div class="rounded-xl border border-slate-200/80 bg-white p-5 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                    <div class="border-b border-slate-100 pb-3 dark:border-slate-800">
                        <h2 class="text-base font-extrabold text-blue-600 dark:text-blue-400">
                            📞 Leads Pending Contact ({{ uncontactedLeads.length }})
                        </h2>
                    </div>

                    <div class="mt-4 space-y-3">
                        <div
                            v-for="lead in uncontactedLeads"
                            :key="lead.id"
                            class="flex items-center justify-between rounded-xl border border-blue-200 bg-blue-50/40 p-3.5 text-xs dark:border-blue-900 dark:bg-blue-950/20"
                        >
                            <div>
                                <div class="font-extrabold text-blue-950 dark:text-blue-100">{{ lead.name }}</div>
                                <div class="text-[11px] text-blue-700 dark:text-blue-400">
                                    {{ lead.company_name || lead.email }}
                                </div>
                            </div>
                            <Button as-child size="sm" class="bg-blue-600 text-xs text-white hover:bg-blue-500">
                                <Link :href="clientsShow.url(lead.id)">Contact Now</Link>
                            </Button>
                        </div>

                        <div v-if="uncontactedLeads.length === 0" class="py-8 text-center text-xs text-slate-500">
                            🎉 All leads have been contacted!
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
