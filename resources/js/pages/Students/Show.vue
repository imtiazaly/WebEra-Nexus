<script setup lang="ts">
import { ref, computed } from "vue";
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import { index, edit, show, assignProject } from "@/routes/students";
import {
    store as storeWeeklyReport,
    update as updateWeeklyReport,
    aiSummary as reportAiSummary,
} from "@/routes/weekly-reports";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
    CardDescription,
} from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Avatar, AvatarFallback } from "@/components/ui/avatar";
import InputError from "@/components/InputError.vue";
import {
    ArrowLeft,
    User,
    Mail,
    Phone,
    GraduationCap,
    Target,
    Briefcase,
    FileText,
    Sparkles,
    Bot,
    Pencil,
    Plus,
    CheckCircle2,
    AlertTriangle,
    Clock,
    Award,
    Loader2,
    Send,
    UserCheck,
    Layers,
    MessageSquare,
    ExternalLink,
    Calendar,
} from "@lucide/vue";

defineOptions({
    layout: {
        breadcrumbs: [
            { title: "Students Portal", href: index.url() },
            { title: "Student Profile", href: "#" },
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
    client?: Client | null;
    service?: Service | null;
    pivot: {
        id: number;
        role: string;
        progress: number;
        submission_status: string;
    };
}

interface WeeklyReport {
    id: number;
    week_number: number;
    tasks_completed: string;
    learnings: string | null;
    blockers: string | null;
    status: string;
    feedback: string | null;
    ai_summary: string | null;
    submitted_at: string;
}

interface Internship {
    id: number;
    name: string;
    batch_no: string;
    service?: Service | null;
}

interface EnrolledBatch {
    id: number;
    name: string;
    batch_no: string;
    service?: Service | null;
    pivot: {
        id: number;
        status: string;
        progress: number;
        joined_at: string | null;
        completed_at: string | null;
    };
}

interface Student {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    status: string;
    overall_progress: number;
    internship: Internship | null;
    internships?: EnrolledBatch[];
    weekly_reports?: WeeklyReport[];
    projects?: Project[];
    created_at?: string;
}

interface ProjectOption {
    id: number;
    title: string;
}

interface InternshipOption {
    id: number;
    name: string;
    batch_no: string;
}

const props = defineProps<{
    student: Student;
    availableProjects: ProjectOption[];
    availableBatches?: InternshipOption[];
}>();

// Active tab state
const activeTab = ref<"projects" | "reports" | "batches" | "submit_report">("projects");

// Initials Helper
const getInitials = (name: string) => {
    if (!name) return "ST";
    const parts = name.trim().split(" ");
    if (parts.length >= 2) {
        return `${parts[0][0]}${parts[1][0]}`.toUpperCase();
    }
    return name.slice(0, 2).toUpperCase();
};

const getStatusBadge = (status: string) => {
    switch (status) {
        case "enrolled":
            return "bg-sky-50 text-sky-700 border-sky-200 dark:bg-sky-950/60 dark:text-sky-300 dark:border-sky-800";
        case "active":
            return "bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800";
        case "completed":
            return "bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/60 dark:text-purple-300 dark:border-purple-800";
        case "dropped_out":
            return "bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-800";
        default:
            return "bg-slate-50 text-slate-700 border-slate-200 dark:bg-slate-950 dark:text-slate-300 dark:border-slate-800";
    }
};

const formatStatus = (status: string) => {
    return status.replace("_", " ").replace(/\b\w/g, (l) => l.toUpperCase());
};

const getProjectStatusBadge = (status: string) => {
    switch (status) {
        case "approved":
            return "bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300";
        case "submitted":
            return "bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-950/60 dark:text-indigo-300";
        case "in_progress":
            return "bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/60 dark:text-amber-300";
        case "revision_needed":
            return "bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/60 dark:text-rose-300";
        default:
            return "bg-slate-50 text-slate-700 border-slate-200 dark:bg-slate-950 dark:text-slate-300";
    }
};

// Enroll in New Batch Form & State
const isEnrollModalOpen = ref(false);
const enrollForm = useForm({
    internship_id: "" as string | number,
    status: "enrolled",
    progress_val: 0,
});

const submitEnrollment = () => {
    enrollForm
        .transform((data) => ({
            internship_id: data.internship_id,
            status: data.status,
            progress: data.progress_val,
        }))
        .post(`/students/${props.student.id}/enroll-batch`, {
            onSuccess: () => {
                enrollForm.reset();
                isEnrollModalOpen.value = false;
            },
        });
};

// Assign Project Form & State
const isAssignProjectOpen = ref(false);
const assignForm = useForm({
    project_id: "" as string | number,
    role: "Frontend Developer",
    progress_val: 0,
    submission_status: "assigned",
});

const submitAssignProject = () => {
    assignForm
        .transform((data) => ({
            project_id: data.project_id,
            role: data.role,
            progress: data.progress_val,
            submission_status: data.submission_status,
        }))
        .post(assignProject.url(props.student.id), {
            onSuccess: () => {
                assignForm.reset();
                isAssignProjectOpen.value = false;
            },
        });
};

// Submit Weekly Report Form
const reportForm = useForm({
    week_number: (props.student.weekly_reports?.length || 0) + 1,
    tasks_completed: "",
    learnings: "",
    blockers: "",
    status: "submitted",
});

const submitWeeklyReport = () => {
    reportForm.post(storeWeeklyReport.url(props.student.id), {
        onSuccess: () => {
            reportForm.reset("tasks_completed", "learnings", "blockers");
            reportForm.week_number = (props.student.weekly_reports?.length || 0) + 1;
            activeTab.value = "reports";
        },
    });
};

// Review Weekly Report (Admin Feedback)
const editingReportId = ref<number | null>(null);
const reviewForm = useForm({
    status: "approved",
    feedback: "",
});

const openReview = (report: WeeklyReport) => {
    editingReportId.value = report.id;
    reviewForm.status = report.status;
    reviewForm.feedback = report.feedback || "";
};

const submitReportReview = (reportId: number) => {
    reviewForm.put(updateWeeklyReport.url(reportId), {
        onSuccess: () => {
            editingReportId.value = null;
        },
    });
};

// AI Summary Generation per Weekly Report
const generatingReportAi = ref<number | null>(null);

const generateReportAi = (reportId: number) => {
    generatingReportAi.value = reportId;
    router.post(
        reportAiSummary.url(reportId),
        {},
        {
            onFinish: () => {
                generatingReportAi.value = null;
            },
        },
    );
};

// Computed Performance Tier
const performanceTier = computed(() => {
    const progress = props.student.overall_progress || 0;
    if (progress >= 85) return { label: "Top Performer", color: "text-emerald-600 dark:text-emerald-400", badge: "bg-emerald-50 border-emerald-200 text-emerald-700" };
    if (progress >= 50) return { label: "On Track", color: "text-indigo-600 dark:text-indigo-400", badge: "bg-indigo-50 border-indigo-200 text-indigo-700" };
    if (progress > 0) return { label: "In Progress", color: "text-amber-600 dark:text-amber-400", badge: "bg-amber-50 border-amber-200 text-amber-700" };
    return { label: "Just Enrolled", color: "text-slate-500", badge: "bg-slate-50 border-slate-200 text-slate-700" };
});
</script>

<template>
    <Head :title="`${student.name} - Candidate Profile Studio`" />

    <div class="w-full space-y-6 p-4 sm:p-6">
        <!-- Top Navigation & Profile Command Header -->
        <div class="space-y-4">
            <Button
                variant="ghost"
                size="sm"
                as-child
                class="-ml-2 text-slate-500 hover:text-slate-900 dark:hover:text-slate-100"
            >
                <Link :href="index.url()">
                    <ArrowLeft class="mr-1.5 h-4 w-4" /> Back to Students Portal
                </Link>
            </Button>

            <div
                class="flex flex-col gap-5 rounded-2xl border border-slate-200/80 bg-white p-6 shadow-xs sm:flex-row sm:items-center sm:justify-between dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="flex items-center gap-4">
                    <Avatar class="h-16 w-16 border-2 border-indigo-500/20 shadow-sm">
                        <AvatarFallback class="bg-indigo-600 text-xl font-black text-white">
                            {{ getInitials(student.name) }}
                        </AvatarFallback>
                    </Avatar>

                    <div class="space-y-1">
                        <div class="flex flex-wrap items-center gap-2.5">
                            <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">
                                {{ student.name }}
                            </h1>
                            <Badge variant="outline" class="font-mono text-xs">
                                #STD-{{ student.id }}
                            </Badge>
                            <Badge
                                variant="outline"
                                :class="['rounded-full px-2.5 py-0.5 text-xs font-bold uppercase tracking-wider', getStatusBadge(student.status)]"
                            >
                                {{ formatStatus(student.status) }}
                            </Badge>
                        </div>

                        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500 dark:text-slate-400">
                            <span class="flex items-center gap-1.5">
                                <Mail class="h-3.5 w-3.5 text-slate-400" />
                                {{ student.email }}
                            </span>
                            <span v-if="student.phone" class="flex items-center gap-1.5">
                                <Phone class="h-3.5 w-3.5 text-slate-400" />
                                {{ student.phone }}
                            </span>
                            <span v-if="student.internship" class="flex items-center gap-1.5 font-semibold text-indigo-600 dark:text-indigo-400">
                                <GraduationCap class="h-3.5 w-3.5" />
                                {{ student.internship.name }} (Batch {{ student.internship.batch_no }})
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-wrap items-center gap-2.5">
                    <Button
                        @click="isEnrollModalOpen = !isEnrollModalOpen; activeTab = 'batches'"
                        variant="outline"
                        size="sm"
                        class="h-9 border-indigo-200 text-xs font-semibold text-indigo-700 hover:bg-indigo-50 dark:border-indigo-800 dark:text-indigo-300 dark:hover:bg-indigo-950"
                    >
                        <Plus class="mr-1.5 h-3.5 w-3.5" /> Enroll in New Batch
                    </Button>

                    <Button
                        as-child
                        class="h-9 bg-indigo-600 px-4 text-xs font-semibold text-white shadow-xs hover:bg-indigo-700"
                    >
                        <Link :href="edit.url(student.id)">
                            <Pencil class="mr-1.5 h-3.5 w-3.5" /> Edit Profile
                        </Link>
                    </Button>
                </div>
            </div>
        </div>

        <!-- Metric KPI Cards Bar (4 Metrics) -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- 1. Overall Completion Rating -->
            <Card class="border-slate-200/80 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                <CardContent class="p-5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                            Training Completion
                        </span>
                        <div class="rounded-xl border border-indigo-200 bg-indigo-50/80 p-2 text-indigo-600 dark:border-indigo-900/60 dark:bg-indigo-950/60 dark:text-indigo-400">
                            <Target class="h-4 w-4" />
                        </div>
                    </div>
                    <div class="mt-2 flex items-baseline justify-between">
                        <span class="text-2xl font-black text-slate-900 dark:text-white">
                            {{ student.overall_progress }}%
                        </span>
                        <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400">
                            Overall Score
                        </span>
                    </div>
                    <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                        <div
                            class="h-full rounded-full bg-indigo-600 transition-all duration-500"
                            :style="{ width: `${student.overall_progress}%` }"
                        ></div>
                    </div>
                </CardContent>
            </Card>

            <!-- 2. Enrolled Training Batches -->
            <Card class="border-slate-200/80 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                <CardContent class="p-5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                            Training Batches
                        </span>
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50/80 p-2 text-emerald-600 dark:border-emerald-900/60 dark:bg-emerald-950/60 dark:text-emerald-400">
                            <GraduationCap class="h-4 w-4" />
                        </div>
                    </div>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-2xl font-black text-slate-900 dark:text-white">
                            {{ student.internships?.length || (student.internship ? 1 : 0) }}
                        </span>
                        <span class="text-[11px] font-medium text-emerald-600 dark:text-emerald-400">Enrolled Batches</span>
                    </div>
                </CardContent>
            </Card>

            <!-- 3. Assigned Client Projects -->
            <Card class="border-slate-200/80 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                <CardContent class="p-5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                            Projects & Tasks
                        </span>
                        <div class="rounded-xl border border-sky-200 bg-sky-50/80 p-2 text-sky-600 dark:border-sky-900/60 dark:bg-sky-950/60 dark:text-sky-400">
                            <Briefcase class="h-4 w-4" />
                        </div>
                    </div>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-2xl font-black text-slate-900 dark:text-white">
                            {{ student.projects?.length || 0 }}
                        </span>
                        <span class="text-[11px] font-medium text-slate-400">Allocated Projects</span>
                    </div>
                </CardContent>
            </Card>

            <!-- 4. Weekly Log Reports -->
            <Card class="border-slate-200/80 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                <CardContent class="p-5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                            Weekly Submissions
                        </span>
                        <div class="rounded-xl border border-purple-200 bg-purple-50/80 p-2 text-purple-600 dark:border-purple-900/60 dark:bg-purple-950/60 dark:text-purple-400">
                            <FileText class="h-4 w-4" />
                        </div>
                    </div>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-2xl font-black text-slate-900 dark:text-white">
                            {{ student.weekly_reports?.length || 0 }}
                        </span>
                        <span class="text-[11px] font-medium text-purple-600 dark:text-purple-400">Log Submissions</span>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Navigation Tabs Control Bar -->
        <div class="flex flex-wrap items-center gap-2 border-b border-slate-200/80 pb-3 dark:border-slate-800">
            <button
                type="button"
                @click="activeTab = 'projects'"
                :class="[
                    'flex items-center gap-2 rounded-xl px-4 py-2 text-xs font-bold transition-all',
                    activeTab === 'projects'
                        ? 'bg-indigo-600 text-white shadow-xs'
                        : 'bg-slate-100/80 text-slate-600 hover:bg-slate-200/70 dark:bg-slate-800/80 dark:text-slate-300 dark:hover:bg-slate-800',
                ]"
            >
                <Briefcase class="h-3.5 w-3.5" />
                <span>Assigned Projects & Tasks ({{ student.projects?.length || 0 }})</span>
            </button>

            <button
                type="button"
                @click="activeTab = 'batches'"
                :class="[
                    'flex items-center gap-2 rounded-xl px-4 py-2 text-xs font-bold transition-all',
                    activeTab === 'batches'
                        ? 'bg-indigo-600 text-white shadow-xs'
                        : 'bg-slate-100/80 text-slate-600 hover:bg-slate-200/70 dark:bg-slate-800/80 dark:text-slate-300 dark:hover:bg-slate-800',
                ]"
            >
                <GraduationCap class="h-3.5 w-3.5" />
                <span>Batch Enrollment History ({{ student.internships?.length || (student.internship ? 1 : 0) }})</span>
            </button>

            <button
                type="button"
                @click="activeTab = 'reports'"
                :class="[
                    'flex items-center gap-2 rounded-xl px-4 py-2 text-xs font-bold transition-all',
                    activeTab === 'reports'
                        ? 'bg-indigo-600 text-white shadow-xs'
                        : 'bg-slate-100/80 text-slate-600 hover:bg-slate-200/70 dark:bg-slate-800/80 dark:text-slate-300 dark:hover:bg-slate-800',
                ]"
            >
                <FileText class="h-3.5 w-3.5" />
                <span>Weekly Progress Reports ({{ student.weekly_reports?.length || 0 }})</span>
            </button>

            <button
                type="button"
                @click="activeTab = 'submit_report'"
                :class="[
                    'flex items-center gap-2 rounded-xl px-4 py-2 text-xs font-bold transition-all',
                    activeTab === 'submit_report'
                        ? 'bg-emerald-600 text-white shadow-xs'
                        : 'bg-slate-100/80 text-slate-600 hover:bg-slate-200/70 dark:bg-slate-800/80 dark:text-slate-300 dark:hover:bg-slate-800',
                ]"
            >
                <Plus class="h-3.5 w-3.5" />
                <span>+ Submit Weekly Log</span>
            </button>
        </div>

        <!-- TAB 1: ASSIGNED PROJECTS & PRACTICE TASKS -->
        <div v-if="activeTab === 'projects'" class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-extrabold text-slate-900 dark:text-white">
                        Assigned Client Projects & Tasks
                    </h2>
                    <p class="text-xs text-slate-500">
                        Live project tasks allocated to {{ student.name }} for practical training.
                    </p>
                </div>
                <Button
                    @click="isAssignProjectOpen = !isAssignProjectOpen"
                    size="sm"
                    class="h-9 bg-indigo-600 px-3.5 text-xs font-bold text-white shadow-xs hover:bg-indigo-700"
                >
                    <Plus class="mr-1.5 h-3.5 w-3.5" />
                    {{ isAssignProjectOpen ? "Close Assignment Panel" : "+ Assign New Project" }}
                </Button>
            </div>

            <!-- Inline Assign Project Card Form -->
            <div
                v-if="isAssignProjectOpen"
                class="rounded-2xl border border-indigo-200 bg-indigo-50/40 p-5 dark:border-indigo-900/60 dark:bg-indigo-950/30 space-y-4"
            >
                <div class="flex items-center justify-between border-b border-indigo-200/60 pb-3 dark:border-indigo-900/40">
                    <div class="flex items-center gap-2 font-extrabold text-xs text-indigo-900 dark:text-indigo-200 uppercase tracking-wider">
                        <Briefcase class="h-4 w-4 text-indigo-600" />
                        <span>Allocate Client / Practice Project</span>
                    </div>
                </div>

                <form @submit.prevent="submitAssignProject" class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <!-- Project Selector -->
                    <div class="space-y-1.5">
                        <Label class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                            Select Available Project <span class="text-rose-500">*</span>
                        </Label>
                        <select
                            v-model="assignForm.project_id"
                            required
                            class="h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-xs text-slate-900 shadow-2xs focus:border-indigo-500 dark:border-slate-800 dark:bg-slate-900 dark:text-white"
                        >
                            <option value="" disabled>-- Choose Project from Database --</option>
                            <option
                                v-for="proj in availableProjects"
                                :key="proj.id"
                                :value="proj.id"
                            >
                                {{ proj.title }}
                            </option>
                        </select>
                    </div>

                    <!-- Role Assigned -->
                    <div class="space-y-1.5">
                        <Label class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                            Assigned Role / Title <span class="text-rose-500">*</span>
                        </Label>
                        <Input
                            v-model="assignForm.role"
                            type="text"
                            required
                            placeholder="e.g. Frontend React Developer"
                            class="h-10 rounded-xl border-slate-200 text-xs bg-white focus:border-indigo-500 dark:border-slate-800 dark:bg-slate-900"
                        />
                    </div>

                    <!-- Progress Percentage -->
                    <div class="space-y-1.5">
                        <div class="flex justify-between">
                            <Label class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                                Completion Progress (%)
                            </Label>
                            <span class="text-xs font-bold text-indigo-600">{{ assignForm.progress_val }}%</span>
                        </div>
                        <input
                            v-model.number="assignForm.progress_val"
                            type="range"
                            min="0"
                            max="100"
                            class="h-2 w-full cursor-pointer rounded-lg bg-slate-200 accent-indigo-600 dark:bg-slate-700"
                        />
                    </div>

                    <!-- Submission Status -->
                    <div class="space-y-1.5">
                        <Label class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                            Initial Submission Status
                        </Label>
                        <select
                            v-model="assignForm.submission_status"
                            class="h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-xs text-slate-900 shadow-2xs focus:border-indigo-500 dark:border-slate-800 dark:bg-slate-900 dark:text-white"
                        >
                            <option value="assigned">🔵 Assigned (Not Started)</option>
                            <option value="in_progress">🟡 In Progress</option>
                            <option value="submitted">🟣 Submitted for Review</option>
                            <option value="approved">🟢 Approved</option>
                            <option value="revision_needed">🔴 Revision Needed</option>
                        </select>
                    </div>

                    <div class="md:col-span-2 flex justify-end gap-2 pt-2">
                        <Button
                            type="button"
                            variant="ghost"
                            size="sm"
                            @click="isAssignProjectOpen = false"
                        >
                            Cancel
                        </Button>
                        <Button
                            type="submit"
                            size="sm"
                            :disabled="assignForm.processing"
                            class="bg-indigo-600 font-bold text-white hover:bg-indigo-700"
                        >
                            <Loader2 v-if="assignForm.processing" class="mr-1.5 h-3.5 w-3.5 animate-spin" />
                            Confirm Assignment
                        </Button>
                    </div>
                </form>
            </div>

            <!-- Assigned Projects Grid Cards -->
            <div v-if="!student.projects || student.projects.length === 0" class="py-12 text-center text-slate-400">
                <div class="flex flex-col items-center justify-center space-y-2">
                    <Briefcase class="h-8 w-8 text-slate-300 dark:text-slate-700" />
                    <p class="text-sm font-semibold text-slate-600 dark:text-slate-400">
                        No projects assigned to {{ student.name }} yet.
                    </p>
                    <p class="text-xs text-slate-400">
                        Click "+ Assign New Project" above to link candidate to client project tasks.
                    </p>
                </div>
            </div>

            <div v-else class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <Card
                    v-for="proj in student.projects"
                    :key="proj.id"
                    class="border-slate-200/80 shadow-xs dark:border-slate-800 dark:bg-slate-900 transition-all hover:border-indigo-300 dark:hover:border-indigo-800"
                >
                    <CardHeader class="pb-3 border-b border-slate-200/60 dark:border-slate-800">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <CardTitle class="text-sm font-bold text-slate-900 dark:text-white">
                                    {{ proj.title }}
                                </CardTitle>
                                <CardDescription v-if="proj.client" class="text-xs text-indigo-600 dark:text-indigo-400 font-medium mt-0.5">
                                    Client: {{ proj.client.name }} {{ proj.client.company_name ? `(${proj.client.company_name})` : '' }}
                                </CardDescription>
                            </div>
                            <Badge
                                variant="outline"
                                :class="['rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase', getProjectStatusBadge(proj.pivot.submission_status)]"
                            >
                                {{ formatStatus(proj.pivot.submission_status) }}
                            </Badge>
                        </div>
                    </CardHeader>
                    <CardContent class="p-4 space-y-3">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-500">Assigned Role:</span>
                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ proj.pivot.role }}</span>
                        </div>

                        <!-- Progress Bar -->
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-[11px] font-bold">
                                <span class="text-slate-600 dark:text-slate-400">Task Completion</span>
                                <span class="text-indigo-600 dark:text-indigo-400">{{ proj.pivot.progress }}%</span>
                            </div>
                            <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                <div
                                    class="h-full rounded-full bg-indigo-600 transition-all duration-300"
                                    :style="{ width: `${proj.pivot.progress}%` }"
                                ></div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>

        <!-- TAB 2: BATCH ENROLLMENT HISTORY & MULTI-BATCH STUDIO -->
        <div v-if="activeTab === 'batches'" class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-extrabold text-slate-900 dark:text-white">
                        Batch Enrollment History & Multi-Track Enrollment
                    </h2>
                    <p class="text-xs text-slate-500">
                        View all training batches {{ student.name }} has been enrolled in across programs.
                    </p>
                </div>
                <Button
                    @click="isEnrollModalOpen = !isEnrollModalOpen"
                    size="sm"
                    class="h-9 bg-indigo-600 px-3.5 text-xs font-bold text-white shadow-xs hover:bg-indigo-700"
                >
                    <Plus class="mr-1.5 h-3.5 w-3.5" />
                    {{ isEnrollModalOpen ? "Close Enrollment Panel" : "+ Enroll in New Training Batch" }}
                </Button>
            </div>

            <!-- Inline Enroll in New Batch Card Form -->
            <div
                v-if="isEnrollModalOpen"
                class="rounded-2xl border border-indigo-200 bg-indigo-50/40 p-5 dark:border-indigo-900/60 dark:bg-indigo-950/30 space-y-4"
            >
                <div class="flex items-center justify-between border-b border-indigo-200/60 pb-3 dark:border-indigo-900/40">
                    <div class="flex items-center gap-2 font-extrabold text-xs text-indigo-900 dark:text-indigo-200 uppercase tracking-wider">
                        <GraduationCap class="h-4 w-4 text-indigo-600" />
                        <span>Enroll Candidate into Additional Training Batch</span>
                    </div>
                </div>

                <form @submit.prevent="submitEnrollment" class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <!-- Batch Selector -->
                    <div class="space-y-1.5 md:col-span-2">
                        <Label class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                            Select Training Batch <span class="text-rose-500">*</span>
                        </Label>
                        <select
                            v-model="enrollForm.internship_id"
                            required
                            class="h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-xs text-slate-900 shadow-2xs focus:border-indigo-500 dark:border-slate-800 dark:bg-slate-900 dark:text-white"
                        >
                            <option value="" disabled>-- Choose Training Batch --</option>
                            <option
                                v-for="batch in availableBatches"
                                :key="batch.id"
                                :value="batch.id"
                            >
                                {{ batch.name }} (Batch {{ batch.batch_no }})
                            </option>
                        </select>
                    </div>

                    <!-- Initial Status -->
                    <div class="space-y-1.5">
                        <Label class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                            Enrollment Status
                        </Label>
                        <select
                            v-model="enrollForm.status"
                            class="h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-xs text-slate-900 shadow-2xs focus:border-indigo-500 dark:border-slate-800 dark:bg-slate-900 dark:text-white"
                        >
                            <option value="enrolled">🔵 Enrolled</option>
                            <option value="active">🟢 Active</option>
                            <option value="completed">🟣 Completed</option>
                        </select>
                    </div>

                    <div class="md:col-span-3 flex justify-end gap-2 pt-2">
                        <Button
                            type="button"
                            variant="ghost"
                            size="sm"
                            @click="isEnrollModalOpen = false"
                        >
                            Cancel
                        </Button>
                        <Button
                            type="submit"
                            size="sm"
                            :disabled="enrollForm.processing"
                            class="bg-indigo-600 font-bold text-white hover:bg-indigo-700"
                        >
                            <Loader2 v-if="enrollForm.processing" class="mr-1.5 h-3.5 w-3.5 animate-spin" />
                            Enroll Student
                        </Button>
                    </div>
                </form>
            </div>

            <!-- Enrolled Batches History Timeline List -->
            <div v-if="!student.internships || student.internships.length === 0" class="space-y-4">
                <Card v-if="student.internship" class="border-slate-200/80 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                    <CardHeader class="pb-3 border-b border-slate-200/60 dark:border-slate-800">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <GraduationCap class="h-4 w-4 text-indigo-600" />
                                <CardTitle class="text-sm font-bold text-slate-900 dark:text-white">
                                    {{ student.internship.name }}
                                </CardTitle>
                                <Badge variant="outline" class="font-mono text-[10px]">
                                    Batch {{ student.internship.batch_no }}
                                </Badge>
                            </div>
                            <Badge variant="outline" :class="['rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase', getStatusBadge(student.status)]">
                                {{ formatStatus(student.status) }}
                            </Badge>
                        </div>
                    </CardHeader>
                    <CardContent class="p-4">
                        <p class="text-xs text-slate-500">
                            Primary active training batch.
                        </p>
                    </CardContent>
                </Card>
            </div>

            <div v-else class="space-y-4">
                <Card
                    v-for="batch in student.internships"
                    :key="batch.id"
                    class="border-slate-200/80 shadow-xs dark:border-slate-800 dark:bg-slate-900 transition-all hover:border-indigo-300 dark:hover:border-indigo-800"
                >
                    <CardHeader class="pb-3 border-b border-slate-200/60 dark:border-slate-800">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <GraduationCap class="h-4 w-4 text-indigo-600" />
                                <CardTitle class="text-sm font-bold text-slate-900 dark:text-white">
                                    {{ batch.name }}
                                </CardTitle>
                                <Badge variant="outline" class="font-mono text-[10px]">
                                    Batch {{ batch.batch_no }}
                                </Badge>
                            </div>

                            <Badge
                                variant="outline"
                                :class="['rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase', getStatusBadge(batch.pivot.status)]"
                            >
                                {{ formatStatus(batch.pivot.status) }}
                            </Badge>
                        </div>
                    </CardHeader>
                    <CardContent class="p-4 space-y-3">
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 text-xs">
                            <div class="flex items-center gap-1.5 text-slate-500">
                                <Calendar class="h-3.5 w-3.5 text-slate-400" />
                                <span>Joined Date: <strong class="text-slate-800 dark:text-slate-200">{{ batch.pivot.joined_at || 'Registered' }}</strong></span>
                            </div>
                            <div v-if="batch.pivot.completed_at" class="flex items-center gap-1.5 text-slate-500">
                                <CheckCircle2 class="h-3.5 w-3.5 text-emerald-500" />
                                <span>Completed Date: <strong class="text-slate-800 dark:text-slate-200">{{ batch.pivot.completed_at }}</strong></span>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-[11px] font-bold">
                                <span class="text-slate-600 dark:text-slate-400">Batch Progress</span>
                                <span class="text-indigo-600 dark:text-indigo-400">{{ batch.pivot.progress }}%</span>
                            </div>
                            <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                <div
                                    class="h-full rounded-full bg-indigo-600 transition-all duration-300"
                                    :style="{ width: `${batch.pivot.progress}%` }"
                                ></div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>

        <!-- TAB 3: WEEKLY REPORTS HISTORY -->
        <div v-if="activeTab === 'reports'" class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-extrabold text-slate-900 dark:text-white">
                        Weekly Log Submissions & Feedback History
                    </h2>
                    <p class="text-xs text-slate-500">
                        Review submitted weekly task logs, AI summaries, and mentor reviews.
                    </p>
                </div>
                <Button
                    @click="activeTab = 'submit_report'"
                    size="sm"
                    class="h-9 bg-emerald-600 px-3.5 text-xs font-bold text-white shadow-xs hover:bg-emerald-700"
                >
                    <Plus class="mr-1.5 h-3.5 w-3.5" /> Submit New Log
                </Button>
            </div>

            <div v-if="!student.weekly_reports || student.weekly_reports.length === 0" class="py-12 text-center text-slate-400">
                <div class="flex flex-col items-center justify-center space-y-2">
                    <FileText class="h-8 w-8 text-slate-300 dark:text-slate-700" />
                    <p class="text-sm font-semibold text-slate-600 dark:text-slate-400">
                        No weekly reports submitted yet for {{ student.name }}.
                    </p>
                    <p class="text-xs text-slate-400">
                        Use the "+ Submit Weekly Log" tab above to record weekly candidate progress.
                    </p>
                </div>
            </div>

            <div v-else class="space-y-4">
                <Card
                    v-for="report in student.weekly_reports"
                    :key="report.id"
                    class="border-slate-200/80 shadow-xs dark:border-slate-800 dark:bg-slate-900"
                >
                    <CardHeader class="pb-3 border-b border-slate-200/60 dark:border-slate-800">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <Badge variant="default" class="bg-indigo-600 font-bold text-xs">
                                    Week {{ report.week_number }}
                                </Badge>
                                <span class="text-xs text-slate-400 font-medium">
                                    Submitted {{ report.submitted_at || 'Recently' }}
                                </span>
                            </div>

                            <Badge
                                variant="outline"
                                class="rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider bg-slate-50 text-slate-700 dark:bg-slate-800 dark:text-slate-300"
                            >
                                {{ formatStatus(report.status) }}
                            </Badge>
                        </div>
                    </CardHeader>
                    <CardContent class="p-5 space-y-4">
                        <!-- Tasks Completed -->
                        <div class="space-y-1">
                            <span class="text-xs font-bold text-slate-900 dark:text-slate-100 flex items-center gap-1.5">
                                <CheckCircle2 class="h-3.5 w-3.5 text-emerald-500" /> Tasks Completed:
                            </span>
                            <p class="text-xs text-slate-700 dark:text-slate-300 pl-5 whitespace-pre-line">
                                {{ report.tasks_completed }}
                            </p>
                        </div>

                        <!-- Learnings -->
                        <div v-if="report.learnings" class="space-y-1">
                            <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400 flex items-center gap-1.5">
                                <Sparkles class="h-3.5 w-3.5 text-indigo-500" /> Key Learnings:
                            </span>
                            <p class="text-xs text-slate-700 dark:text-slate-300 pl-5 whitespace-pre-line">
                                {{ report.learnings }}
                            </p>
                        </div>

                        <!-- Blockers -->
                        <div v-if="report.blockers" class="space-y-1">
                            <span class="text-xs font-bold text-rose-600 dark:text-rose-400 flex items-center gap-1.5">
                                <AlertTriangle class="h-3.5 w-3.5 text-rose-500" /> Challenges / Blockers:
                            </span>
                            <p class="text-xs text-rose-700 dark:text-rose-300 pl-5 whitespace-pre-line bg-rose-50/50 p-2.5 rounded-xl border border-rose-200/60 dark:border-rose-900/40 dark:bg-rose-950/20">
                                {{ report.blockers }}
                            </p>
                        </div>

                        <!-- AI Summary Card -->
                        <div
                            v-if="report.ai_summary"
                            class="rounded-2xl border border-purple-200/80 bg-purple-50/60 p-4 space-y-1.5 dark:border-purple-900/40 dark:bg-purple-950/30"
                        >
                            <div class="flex items-center gap-1.5 font-bold text-xs text-purple-900 dark:text-purple-200">
                                <Bot class="h-4 w-4 text-purple-600 dark:text-purple-400" />
                                <span>AI Performance Executive Summary</span>
                            </div>
                            <p class="text-xs text-purple-950 dark:text-purple-100 leading-relaxed">
                                {{ report.ai_summary }}
                            </p>
                        </div>

                        <!-- Admin Mentor Feedback Box -->
                        <div
                            v-if="report.feedback"
                            class="rounded-2xl border border-indigo-200/80 bg-indigo-50/60 p-4 space-y-1.5 dark:border-indigo-900/40 dark:bg-indigo-950/30"
                        >
                            <div class="flex items-center gap-1.5 font-bold text-xs text-indigo-900 dark:text-indigo-200">
                                <MessageSquare class="h-4 w-4 text-indigo-600 dark:text-indigo-400" />
                                <span>Mentor Review & Feedback</span>
                            </div>
                            <p class="text-xs text-indigo-950 dark:text-indigo-100">
                                {{ report.feedback }}
                            </p>
                        </div>

                        <!-- Bottom Card Actions (AI Summary Button & Review Form Toggle) -->
                        <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-200/80 pt-3 dark:border-slate-800">
                            <Button
                                variant="outline"
                                size="sm"
                                @click="generateReportAi(report.id)"
                                :disabled="generatingReportAi === report.id"
                                class="h-8 border-purple-200 text-xs font-bold text-purple-700 hover:bg-purple-50 dark:border-purple-800 dark:text-purple-300 dark:hover:bg-purple-950"
                            >
                                <Loader2 v-if="generatingReportAi === report.id" class="mr-1.5 h-3.5 w-3.5 animate-spin" />
                                <Bot v-else class="mr-1.5 h-3.5 w-3.5 text-purple-600" />
                                <span>{{ generatingReportAi === report.id ? "Generating AI Summary..." : "Generate AI Summary" }}</span>
                            </Button>

                            <Button
                                v-if="editingReportId !== report.id"
                                variant="ghost"
                                size="sm"
                                @click="openReview(report)"
                                class="h-8 text-xs font-bold text-indigo-600 hover:text-indigo-800 dark:text-indigo-400"
                            >
                                <Pencil class="mr-1.5 h-3.5 w-3.5" />
                                Mentor Review & Feedback
                            </Button>
                        </div>

                        <!-- Inline Review / Feedback Form -->
                        <form
                            v-if="editingReportId === report.id"
                            @submit.prevent="submitReportReview(report.id)"
                            class="rounded-xl border border-slate-200 bg-slate-50 p-4 space-y-3 dark:border-slate-800 dark:bg-slate-950"
                        >
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-slate-800 dark:text-slate-200">
                                    Update Review & Feedback for Week {{ report.week_number }}
                                </span>
                            </div>

                            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                                <div class="space-y-1">
                                    <Label class="text-[11px] font-semibold text-slate-700 dark:text-slate-300">
                                        Report Status
                                    </Label>
                                    <select
                                        v-model="reviewForm.status"
                                        class="h-9 w-full rounded-lg border border-slate-200 bg-white px-2.5 text-xs text-slate-900 shadow-2xs dark:border-slate-800 dark:bg-slate-900 dark:text-white"
                                    >
                                        <option value="submitted">Submitted</option>
                                        <option value="under_review">Under Review</option>
                                        <option value="approved">Approved</option>
                                        <option value="revision_requested">Revision Requested</option>
                                    </select>
                                </div>

                                <div class="space-y-1">
                                    <Label class="text-[11px] font-semibold text-slate-700 dark:text-slate-300">
                                        Mentor Comments / Feedback
                                    </Label>
                                    <Input
                                        v-model="reviewForm.feedback"
                                        type="text"
                                        placeholder="Add mentor feedback or notes..."
                                        class="h-9 border-slate-200 text-xs bg-white dark:border-slate-800 dark:bg-slate-900"
                                    />
                                </div>
                            </div>

                            <div class="flex justify-end gap-2 pt-1">
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    @click="editingReportId = null"
                                >
                                    Cancel
                                </Button>
                                <Button
                                    type="submit"
                                    size="sm"
                                    :disabled="reviewForm.processing"
                                    class="bg-indigo-600 font-bold text-white hover:bg-indigo-700"
                                >
                                    Save Review
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </div>

        <!-- TAB 4: SUBMIT NEW WEEKLY REPORT FORM -->
        <div v-if="activeTab === 'submit_report'" class="space-y-6">
            <Card class="border-slate-200/80 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                <CardHeader class="border-b border-slate-200/80 pb-4 dark:border-slate-800">
                    <div class="flex items-center gap-2 text-emerald-600 dark:text-emerald-400">
                        <FileText class="h-5 w-5" />
                        <CardTitle class="text-base font-bold text-slate-900 dark:text-slate-100">
                            Submit Weekly Candidate Progress Log
                        </CardTitle>
                    </div>
                    <CardDescription class="text-xs text-slate-500">
                        Record tasks completed, key learnings, and blockers faced during the week.
                    </CardDescription>
                </CardHeader>
                <CardContent class="p-6">
                    <form @submit.prevent="submitWeeklyReport" class="space-y-5">
                        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                            <!-- Week Number -->
                            <div class="space-y-1.5 md:col-span-2">
                                <Label for="report-week" class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                                    Week Number <span class="text-rose-500">*</span>
                                </Label>
                                <Input
                                    id="report-week"
                                    v-model.number="reportForm.week_number"
                                    type="number"
                                    min="1"
                                    required
                                    class="h-10 w-32 border-slate-200 text-xs shadow-2xs dark:border-slate-800 dark:bg-slate-950"
                                />
                                <InputError :message="reportForm.errors.week_number" />
                            </div>

                            <!-- Tasks Completed -->
                            <div class="space-y-1.5 md:col-span-2">
                                <Label for="report-tasks" class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                                    Tasks Completed This Week <span class="text-rose-500">*</span>
                                </Label>
                                <textarea
                                    id="report-tasks"
                                    v-model="reportForm.tasks_completed"
                                    rows="4"
                                    required
                                    placeholder="Detail major tasks, features built, bug fixes, or modules completed..."
                                    class="w-full rounded-md border border-slate-200 p-3 text-xs text-slate-900 shadow-2xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100"
                                ></textarea>
                                <InputError :message="reportForm.errors.tasks_completed" />
                            </div>

                            <!-- Key Learnings -->
                            <div class="space-y-1.5">
                                <Label for="report-learnings" class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                                    Key Concept Learnings
                                </Label>
                                <textarea
                                    id="report-learnings"
                                    v-model="reportForm.learnings"
                                    rows="3"
                                    placeholder="New tools, technologies, techniques, or frameworks learned..."
                                    class="w-full rounded-md border border-slate-200 p-3 text-xs text-slate-900 shadow-2xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100"
                                ></textarea>
                            </div>

                            <!-- Challenges / Blockers -->
                            <div class="space-y-1.5">
                                <Label for="report-blockers" class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                                    Challenges & Blockers Faced
                                </Label>
                                <textarea
                                    id="report-blockers"
                                    v-model="reportForm.blockers"
                                    rows="3"
                                    placeholder="Any technical issues, missing documentation, or dependencies faced..."
                                    class="w-full rounded-md border border-slate-200 p-3 text-xs text-slate-900 shadow-2xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100"
                                ></textarea>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 border-t border-slate-200/80 pt-4 dark:border-slate-800">
                            <Button
                                type="button"
                                variant="outline"
                                @click="activeTab = 'reports'"
                            >
                                Cancel
                            </Button>
                            <Button
                                type="submit"
                                :disabled="reportForm.processing"
                                class="bg-emerald-600 px-6 font-bold text-white hover:bg-emerald-700"
                            >
                                <Loader2 v-if="reportForm.processing" class="mr-2 h-4 w-4 animate-spin" />
                                <Send v-else class="mr-2 h-3.5 w-3.5" />
                                Submit Weekly Report
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
