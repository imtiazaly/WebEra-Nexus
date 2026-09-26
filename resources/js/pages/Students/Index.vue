<script setup lang="ts">
import { ref, computed } from "vue";
import { Head, Link, router } from "@inertiajs/vue3";
import { index, create, show, edit, destroy } from "@/routes/students";
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
    Search,
    Users,
    UserCheck,
    GraduationCap,
    Target,
    MoreHorizontal,
    Eye,
    Pencil,
    Trash2,
    ChevronLeft,
    ChevronRight,
    Mail,
    Phone,
    FileText,
    Sparkles,
    UserX,
    Briefcase,
} from "@lucide/vue";

defineOptions({
    layout: {
        breadcrumbs: [{ title: "Students Portal", href: index.url() }],
    },
});

interface Internship {
    id: number;
    name: string;
    batch_no: string;
}

interface Project {
    id: number;
    title: string;
}

interface Student {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    status: string;
    overall_progress: number;
    internship: Internship | null;
    projects?: Project[];
    weekly_reports_count?: number;
    created_at: string;
}

interface PaginatedStudents {
    data: Student[];
    links: any[];
    current_page: number;
    last_page: number;
    total?: number;
    from?: number;
    to?: number;
}

interface Stats {
    total_students: number;
    active_students: number;
    completed_students: number;
    avg_progress: number;
}

const props = defineProps<{
    students: PaginatedStudents;
    stats?: Stats;
}>();

// Filter & Search state
const searchQuery = ref("");
const selectedStatus = ref<string>("all");

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

const filteredStudents = computed(() => {
    let result = props.students.data;

    if (selectedStatus.value !== "all") {
        result = result.filter((s) => s.status === selectedStatus.value);
    }

    if (searchQuery.value.trim() !== "") {
        const query = searchQuery.value.toLowerCase().trim();
        result = result.filter(
            (s) =>
                s.name.toLowerCase().includes(query) ||
                s.email.toLowerCase().includes(query) ||
                (s.phone && s.phone.toLowerCase().includes(query)) ||
                (s.internship && s.internship.name.toLowerCase().includes(query)) ||
                (s.internship && s.internship.batch_no.toLowerCase().includes(query)),
        );
    }

    return result;
});

const deleteStudent = (id: number) => {
    if (confirm("Are you sure you want to delete this student record?")) {
        router.delete(destroy.url(id));
    }
};
</script>

<template>
    <Head title="Students Portal & Management Studio" />

    <div class="w-full space-y-6 p-4 sm:p-6">
        <!-- Top Header & Primary Action -->
        <div
            class="flex flex-col gap-4 border-b border-slate-200/80 pb-5 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800"
        >
            <div>
                <div class="flex items-center gap-2.5">
                    <h1
                        class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-slate-50"
                    >
                        Students & Interns Portal
                    </h1>
                    <Badge
                        variant="outline"
                        class="rounded-full border-indigo-200 bg-indigo-50/60 px-2.5 py-0.5 text-xs font-semibold text-indigo-700 dark:border-indigo-900 dark:bg-indigo-950/60 dark:text-indigo-300"
                    >
                        <Sparkles class="mr-1 h-3 w-3 text-indigo-500" />
                        {{ students.total || students.data.length }} Candidates
                    </Badge>
                </div>
                <p class="mt-1 text-xs text-slate-500 sm:text-sm dark:text-slate-400">
                    Track student enrollments, batch assignments, client project allocations, and weekly progress.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <Button
                    as-child
                    class="h-10 bg-indigo-600 px-4 text-xs font-semibold text-white shadow-xs hover:bg-indigo-700"
                >
                    <Link :href="create.url()">
                        <Plus class="mr-1.5 h-4 w-4" /> Add Student / Intern
                    </Link>
                </Button>
            </div>
        </div>

        <!-- Metric KPI Cards -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- 1. Total Enrolled -->
            <Card class="border-slate-200/80 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                <CardContent class="p-5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                            Total Registered
                        </span>
                        <div class="rounded-xl border border-indigo-200 bg-indigo-50/80 p-2 text-indigo-600 dark:border-indigo-900/60 dark:bg-indigo-950/60 dark:text-indigo-400">
                            <Users class="h-4 w-4" />
                        </div>
                    </div>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-2xl font-black text-slate-900 dark:text-white">
                            {{ stats?.total_students ?? students.data.length }}
                        </span>
                        <span class="text-[11px] font-medium text-slate-400">Total Interns</span>
                    </div>
                </CardContent>
            </Card>

            <!-- 2. Active Candidate -->
            <Card class="border-slate-200/80 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                <CardContent class="p-5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                            Active & Enrolled
                        </span>
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50/80 p-2 text-emerald-600 dark:border-emerald-900/60 dark:bg-emerald-950/60 dark:text-emerald-400">
                            <UserCheck class="h-4 w-4" />
                        </div>
                    </div>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-2xl font-black text-slate-900 dark:text-white">
                            {{ stats?.active_students ?? 0 }}
                        </span>
                        <span class="text-[11px] font-medium text-emerald-600 dark:text-emerald-400">In Training</span>
                    </div>
                </CardContent>
            </Card>

            <!-- 3. Graduated / Completed -->
            <Card class="border-slate-200/80 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                <CardContent class="p-5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                            Graduated / Completed
                        </span>
                        <div class="rounded-xl border border-purple-200 bg-purple-50/80 p-2 text-purple-600 dark:border-purple-900/60 dark:bg-purple-950/60 dark:text-purple-400">
                            <GraduationCap class="h-4 w-4" />
                        </div>
                    </div>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-2xl font-black text-slate-900 dark:text-white">
                            {{ stats?.completed_students ?? 0 }}
                        </span>
                        <span class="text-[11px] font-medium text-purple-600 dark:text-purple-400">Completed</span>
                    </div>
                </CardContent>
            </Card>

            <!-- 4. Avg Progress -->
            <Card class="border-slate-200/80 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                <CardContent class="p-5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-500 dark:text-slate-400">
                            Avg Candidate Progress
                        </span>
                        <div class="rounded-xl border border-amber-200 bg-amber-50/80 p-2 text-amber-600 dark:border-amber-900/60 dark:bg-amber-950/60 dark:text-amber-400">
                            <Target class="h-4 w-4" />
                        </div>
                    </div>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-2xl font-black text-slate-900 dark:text-white">
                            {{ stats?.avg_progress ?? 0 }}%
                        </span>
                        <span class="text-[11px] font-medium text-slate-400">Overall Completion Rate</span>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Filter & Search Controls Bar -->
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="relative flex-1 sm:max-w-md">
                <Search class="absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-slate-400" />
                <Input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search by student name, email, or batch..."
                    class="h-10 border-slate-200 bg-white pl-10 text-xs shadow-2xs focus:border-indigo-500 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100"
                />
            </div>

            <!-- Pipeline Filter Tabs -->
            <div class="flex flex-wrap items-center gap-1.5 rounded-xl border border-slate-200/80 bg-slate-100/60 p-1 dark:border-slate-800 dark:bg-slate-900">
                <button
                    type="button"
                    @click="selectedStatus = 'all'"
                    :class="[
                        'rounded-lg px-3 py-1.5 text-xs font-semibold transition-all',
                        selectedStatus === 'all'
                            ? 'bg-white text-slate-900 shadow-2xs dark:bg-slate-800 dark:text-white'
                            : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white',
                    ]"
                >
                    All Statuses
                </button>
                <button
                    type="button"
                    @click="selectedStatus = 'active'"
                    :class="[
                        'rounded-lg px-3 py-1.5 text-xs font-semibold transition-all',
                        selectedStatus === 'active'
                            ? 'bg-emerald-500 text-white shadow-2xs'
                            : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white',
                    ]"
                >
                    Active
                </button>
                <button
                    type="button"
                    @click="selectedStatus = 'enrolled'"
                    :class="[
                        'rounded-lg px-3 py-1.5 text-xs font-semibold transition-all',
                        selectedStatus === 'enrolled'
                            ? 'bg-sky-600 text-white shadow-2xs'
                            : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white',
                    ]"
                >
                    Enrolled
                </button>
                <button
                    type="button"
                    @click="selectedStatus = 'completed'"
                    :class="[
                        'rounded-lg px-3 py-1.5 text-xs font-semibold transition-all',
                        selectedStatus === 'completed'
                            ? 'bg-purple-600 text-white shadow-2xs'
                            : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white',
                    ]"
                >
                    Completed
                </button>
                <button
                    type="button"
                    @click="selectedStatus = 'dropped_out'"
                    :class="[
                        'rounded-lg px-3 py-1.5 text-xs font-semibold transition-all',
                        selectedStatus === 'dropped_out'
                            ? 'bg-rose-600 text-white shadow-2xs'
                            : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white',
                    ]"
                >
                    Dropped Out
                </button>
            </div>
        </div>

        <!-- Main Table Card -->
        <Card class="overflow-hidden border-slate-200/80 shadow-xs dark:border-slate-800 dark:bg-slate-900">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-200/80 bg-slate-50/80 text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-400">
                            <th class="py-3.5 px-4">Student & Contact Info</th>
                            <th class="py-3.5 px-4">Internship Track / Batch</th>
                            <th class="py-3.5 px-4">Overall Progress</th>
                            <th class="py-3.5 px-4">Status</th>
                            <th class="py-3.5 px-4">Weekly Reports</th>
                            <th class="py-3.5 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/80 dark:divide-slate-800">
                        <tr v-if="filteredStudents.length === 0">
                            <td colspan="6" class="py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <UserX class="h-8 w-8 text-slate-300 dark:text-slate-700" />
                                    <p class="text-sm font-semibold text-slate-600 dark:text-slate-400">
                                        No student records found.
                                    </p>
                                    <p class="text-xs text-slate-400">
                                        Try adjusting your search query or status filter.
                                    </p>
                                </div>
                            </td>
                        </tr>

                        <tr
                            v-for="student in filteredStudents"
                            :key="student.id"
                            class="group transition-colors hover:bg-slate-50/80 dark:hover:bg-slate-800/50"
                        >
                            <!-- Student & Contact Info -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <Avatar class="h-9 w-9 border border-slate-200 shadow-2xs dark:border-slate-700">
                                        <AvatarFallback class="bg-indigo-600 text-xs font-bold text-white">
                                            {{ getInitials(student.name) }}
                                        </AvatarFallback>
                                    </Avatar>
                                    <div class="space-y-0.5">
                                        <Link
                                            :href="show.url(student.id)"
                                            class="font-bold text-slate-900 hover:text-indigo-600 dark:text-slate-100 dark:hover:text-indigo-400"
                                        >
                                            {{ student.name }}
                                        </Link>
                                        <div class="flex items-center gap-3 text-[11px] text-slate-500 dark:text-slate-400">
                                            <span class="flex items-center gap-1">
                                                <Mail class="h-3 w-3 text-slate-400" />
                                                {{ student.email }}
                                            </span>
                                            <span v-if="student.phone" class="flex items-center gap-1">
                                                <Phone class="h-3 w-3 text-slate-400" />
                                                {{ student.phone }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Internship Track & Batch -->
                            <td class="py-3.5 px-4">
                                <div v-if="student.internship" class="space-y-1">
                                    <div class="font-bold text-slate-800 dark:text-slate-200">
                                        {{ student.internship.name }}
                                    </div>
                                    <Badge
                                        variant="outline"
                                        class="rounded-md border-indigo-200 bg-indigo-50/50 font-mono text-[10px] font-semibold text-indigo-700 dark:border-indigo-900/60 dark:bg-indigo-950/40 dark:text-indigo-300"
                                    >
                                        Batch {{ student.internship.batch_no }}
                                    </Badge>
                                </div>
                                <span v-else class="text-xs text-slate-400 italic">No Batch Assigned</span>
                            </td>

                            <!-- Overall Progress Bar -->
                            <td class="py-3.5 px-4 w-44">
                                <div class="space-y-1.5">
                                    <div class="flex items-center justify-between text-[11px] font-bold">
                                        <span class="text-slate-700 dark:text-slate-300">Completion</span>
                                        <span class="text-indigo-600 dark:text-indigo-400">{{ student.overall_progress }}%</span>
                                    </div>
                                    <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                        <div
                                            class="h-full rounded-full bg-indigo-600 transition-all duration-300"
                                            :style="{ width: `${student.overall_progress}%` }"
                                        ></div>
                                    </div>
                                </div>
                            </td>

                            <!-- Status Badge -->
                            <td class="py-3.5 px-4">
                                <Badge
                                    variant="outline"
                                    :class="[
                                        'rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider',
                                        getStatusBadge(student.status),
                                    ]"
                                >
                                    {{ formatStatus(student.status) }}
                                </Badge>
                            </td>

                            <!-- Weekly Reports Count -->
                            <td class="py-3.5 px-4 font-medium text-slate-600 dark:text-slate-300">
                                <div class="flex items-center gap-1.5">
                                    <FileText class="h-3.5 w-3.5 text-indigo-500" />
                                    <span>{{ student.weekly_reports_count || 0 }} Submissions</span>
                                </div>
                            </td>

                            <!-- Action Menu Dropdown -->
                            <td class="py-3.5 px-4 text-right">
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button
                                            variant="ghost"
                                            size="icon-sm"
                                            class="h-8 w-8 text-slate-500 hover:text-slate-900 dark:hover:text-white"
                                        >
                                            <MoreHorizontal class="h-4 w-4" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent align="end" class="w-44">
                                        <DropdownMenuLabel class="text-xs font-bold text-slate-500">
                                            Actions
                                        </DropdownMenuLabel>
                                        <DropdownMenuItem as-child>
                                            <Link
                                                :href="show.url(student.id)"
                                                class="flex cursor-pointer items-center gap-2 text-xs font-semibold"
                                            >
                                                <Eye class="h-3.5 w-3.5 text-slate-400" />
                                                View Profile
                                            </Link>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem as-child>
                                            <Link
                                                :href="edit.url(student.id)"
                                                class="flex cursor-pointer items-center gap-2 text-xs font-semibold text-indigo-600 dark:text-indigo-400"
                                            >
                                                <Pencil class="h-3.5 w-3.5" />
                                                Edit Profile
                                            </Link>
                                        </DropdownMenuItem>
                                        <DropdownMenuSeparator />
                                        <DropdownMenuItem
                                            @click="deleteStudent(student.id)"
                                            class="flex cursor-pointer items-center gap-2 text-xs font-semibold text-rose-600 dark:text-rose-400"
                                        >
                                            <Trash2 class="h-3.5 w-3.5" />
                                            Delete Record
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Footer Pagination Controls -->
            <div
                v-if="students.links && students.links.length > 3"
                class="flex flex-col items-center justify-between gap-3 border-t border-slate-200/80 px-4 py-3 sm:flex-row dark:border-slate-800"
            >
                <div class="text-xs text-slate-500 dark:text-slate-400">
                    Showing <span class="font-semibold text-slate-700 dark:text-slate-200">{{ students.from || 1 }}</span> to
                    <span class="font-semibold text-slate-700 dark:text-slate-200">{{ students.to || students.data.length }}</span> of
                    <span class="font-semibold text-slate-700 dark:text-slate-200">{{ students.total || students.data.length }}</span> results
                </div>
                <div class="flex items-center gap-1">
                    <template v-for="(link, i) in students.links" :key="i">
                        <Button
                            v-if="link.url || link.label.includes('Previous') || link.label.includes('Next')"
                            :variant="link.active ? 'default' : 'outline'"
                            size="sm"
                            as-child
                            :disabled="!link.url"
                            :class="[
                                'h-8 text-xs',
                                link.active ? 'bg-indigo-600 text-white hover:bg-indigo-700' : 'text-slate-600 dark:text-slate-300',
                            ]"
                        >
                            <Link v-if="link.url" :href="link.url">
                                <span v-html="link.label"></span>
                            </Link>
                            <span v-else v-html="link.label"></span>
                        </Button>
                    </template>
                </div>
            </div>
        </Card>
    </div>
</template>
