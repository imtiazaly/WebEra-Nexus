<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { index, create, show, edit, destroy } from '@/routes/students';
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
    X,
} from '@lucide/vue';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Students Portal', href: index.url() }],
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

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedStudents {
    data: Student[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    total: number;
    from: number | null;
    to: number | null;
    prev_page_url: string | null;
    next_page_url: string | null;
}

interface Stats {
    total_students: number;
    active_students: number;
    enrolled_students?: number;
    completed_students: number;
    dropped_out_students?: number;
    avg_progress: number;
}

interface Filters {
    status?: string;
    search?: string;
}

const props = defineProps<{
    students: PaginatedStudents;
    stats?: Stats;
    filters?: Filters;
}>();

// Filter & Search state initialized from props
const searchQuery = ref(props.filters?.search || '');
const selectedStatus = ref<string>(props.filters?.status || 'all');
let searchTimeout: ReturnType<typeof setTimeout> | null = null;

const applyServerFilters = () => {
    router.get(
        index.url(),
        {
            status: selectedStatus.value !== 'all' ? selectedStatus.value : undefined,
            search: searchQuery.value.trim() !== '' ? searchQuery.value.trim() : undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

const setStatus = (status: string) => {
    selectedStatus.value = status;
    applyServerFilters();
};

const handleSearchInput = () => {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyServerFilters();
    }, 350);
};

const clearSearch = () => {
    searchQuery.value = '';
    applyServerFilters();
};

const resetAllFilters = () => {
    searchQuery.value = '';
    selectedStatus.value = 'all';
    applyServerFilters();
};

const getInitials = (name: string) => {
    if (!name) return 'ST';
    const parts = name.trim().split(' ');
    if (parts.length >= 2) {
        return `${parts[0][0]}${parts[1][0]}`.toUpperCase();
    }
    return name.slice(0, 2).toUpperCase();
};

const getStatusBadge = (status: string) => {
    switch (status) {
        case 'enrolled':
            return 'bg-sky-50 text-sky-700 border-sky-200 dark:bg-sky-950/60 dark:text-sky-300 dark:border-sky-800';
        case 'active':
            return 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800';
        case 'completed':
            return 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/60 dark:text-purple-300 dark:border-purple-800';
        case 'dropped_out':
            return 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/60 dark:text-rose-300 dark:border-rose-800';
        default:
            return 'bg-slate-50 text-slate-700 border-slate-200 dark:bg-slate-950 dark:text-slate-300 dark:border-slate-800';
    }
};

const formatStatus = (status: string) => {
    return status.replace('_', ' ').replace(/\b\w/g, (l) => l.toUpperCase());
};

const deleteStudent = (id: number) => {
    if (confirm('Are you sure you want to delete this student record?')) {
        router.delete(destroy.url(id));
    }
};

// Scroll indicators state & handlers for tab filters & table container
const tabsContainerRef = ref<HTMLElement | null>(null);
const canScrollLeft = ref(false);
const canScrollRight = ref(false);

const tableContainerRef = ref<HTMLElement | null>(null);
const canTableScrollLeft = ref(false);
const canTableScrollRight = ref(false);

const checkScrollState = () => {
    const el = tabsContainerRef.value;
    if (!el) return;
    canScrollLeft.value = el.scrollLeft > 5;
    canScrollRight.value = el.scrollLeft < el.scrollWidth - el.clientWidth - 5;
};

const checkTableScrollState = () => {
    const el = tableContainerRef.value;
    if (!el) return;
    canTableScrollLeft.value = el.scrollLeft > 5;
    canTableScrollRight.value = el.scrollLeft < el.scrollWidth - el.clientWidth - 5;
};

const scrollTabs = (direction: 'left' | 'right') => {
    const el = tabsContainerRef.value;
    if (!el) return;
    const amount = direction === 'left' ? -200 : 200;
    el.scrollBy({ left: amount, behavior: 'smooth' });
};

const scrollTable = (direction: 'left' | 'right') => {
    const el = tableContainerRef.value;
    if (!el) return;
    const amount = direction === 'left' ? -250 : 250;
    el.scrollBy({ left: amount, behavior: 'smooth' });
};

onMounted(() => {
    nextTick(() => {
        checkScrollState();
        checkTableScrollState();
    });
    if (tabsContainerRef.value) {
        tabsContainerRef.value.addEventListener('scroll', checkScrollState, { passive: true });
    }
    if (tableContainerRef.value) {
        tableContainerRef.value.addEventListener('scroll', checkTableScrollState, { passive: true });
    }
    window.addEventListener('resize', () => {
        checkScrollState();
        checkTableScrollState();
    });
});

onUnmounted(() => {
    if (tabsContainerRef.value) {
        tabsContainerRef.value.removeEventListener('scroll', checkScrollState);
    }
    if (tableContainerRef.value) {
        tableContainerRef.value.removeEventListener('scroll', checkTableScrollState);
    }
    window.removeEventListener('resize', () => {
        checkScrollState();
        checkTableScrollState();
    });
});

// Custom directive for smooth auto-hiding thin scrollbar
const vAutoHideScroll = {
    mounted(el: HTMLElement) {
        let scrollTimeout: ReturnType<typeof setTimeout> | null = null;
        const handleScroll = () => {
            el.classList.add('is-scrolling');
            if (scrollTimeout) {
                clearTimeout(scrollTimeout);
            }
            scrollTimeout = setTimeout(() => {
                el.classList.remove('is-scrolling');
            }, 1000);
        };
        el.addEventListener('scroll', handleScroll, { passive: true });
        (el as any)._onScrollCleanup = () => el.removeEventListener('scroll', handleScroll);
    },
    unmounted(el: HTMLElement) {
        if ((el as any)._onScrollCleanup) {
            (el as any)._onScrollCleanup();
        }
    },
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
                <p
                    class="mt-1 text-xs text-slate-500 sm:text-sm dark:text-slate-400"
                >
                    Track student enrollments, batch assignments, client project
                    allocations, and weekly progress.
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
            <Card
                class="border-slate-200/80 shadow-xs dark:border-slate-800 dark:bg-slate-900"
            >
                <CardContent class="p-5">
                    <div class="flex items-center justify-between">
                        <span
                            class="text-xs font-semibold text-slate-500 dark:text-slate-400"
                        >
                            Total Registered
                        </span>
                        <div
                            class="rounded-xl border border-indigo-200 bg-indigo-50/80 p-2 text-indigo-600 dark:border-indigo-900/60 dark:bg-indigo-950/60 dark:text-indigo-400"
                        >
                            <Users class="h-4 w-4" />
                        </div>
                    </div>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span
                            class="text-2xl font-black text-slate-900 dark:text-white"
                        >
                            {{ stats?.total_students ?? students.data.length }}
                        </span>
                        <span class="text-[11px] font-medium text-slate-400"
                            >Total Interns</span
                        >
                    </div>
                </CardContent>
            </Card>

            <!-- 2. Active Candidate -->
            <Card
                class="border-slate-200/80 shadow-xs dark:border-slate-800 dark:bg-slate-900"
            >
                <CardContent class="p-5">
                    <div class="flex items-center justify-between">
                        <span
                            class="text-xs font-semibold text-slate-500 dark:text-slate-400"
                        >
                            Active & Enrolled
                        </span>
                        <div
                            class="rounded-xl border border-emerald-200 bg-emerald-50/80 p-2 text-emerald-600 dark:border-emerald-900/60 dark:bg-emerald-950/60 dark:text-emerald-400"
                        >
                            <UserCheck class="h-4 w-4" />
                        </div>
                    </div>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span
                            class="text-2xl font-black text-slate-900 dark:text-white"
                        >
                            {{ stats?.active_students ?? 0 }}
                        </span>
                        <span
                            class="text-[11px] font-medium text-emerald-600 dark:text-emerald-400"
                            >In Training</span
                        >
                    </div>
                </CardContent>
            </Card>

            <!-- 3. Graduated / Completed -->
            <Card
                class="border-slate-200/80 shadow-xs dark:border-slate-800 dark:bg-slate-900"
            >
                <CardContent class="p-5">
                    <div class="flex items-center justify-between">
                        <span
                            class="text-xs font-semibold text-slate-500 dark:text-slate-400"
                        >
                            Graduated / Completed
                        </span>
                        <div
                            class="rounded-xl border border-purple-200 bg-purple-50/80 p-2 text-purple-600 dark:border-purple-900/60 dark:bg-purple-950/60 dark:text-purple-400"
                        >
                            <GraduationCap class="h-4 w-4" />
                        </div>
                    </div>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span
                            class="text-2xl font-black text-slate-900 dark:text-white"
                        >
                            {{ stats?.completed_students ?? 0 }}
                        </span>
                        <span
                            class="text-[11px] font-medium text-purple-600 dark:text-purple-400"
                            >Completed</span
                        >
                    </div>
                </CardContent>
            </Card>

            <!-- 4. Avg Progress -->
            <Card
                class="border-slate-200/80 shadow-xs dark:border-slate-800 dark:bg-slate-900"
            >
                <CardContent class="p-5">
                    <div class="flex items-center justify-between">
                        <span
                            class="text-xs font-semibold text-slate-500 dark:text-slate-400"
                        >
                            Avg Candidate Progress
                        </span>
                        <div
                            class="rounded-xl border border-amber-200 bg-amber-50/80 p-2 text-amber-600 dark:border-amber-900/60 dark:bg-amber-950/60 dark:text-amber-400"
                        >
                            <Target class="h-4 w-4" />
                        </div>
                    </div>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span
                            class="text-2xl font-black text-slate-900 dark:text-white"
                        >
                            {{ stats?.avg_progress ?? 0 }}%
                        </span>
                        <span class="text-[11px] font-medium text-slate-400"
                            >Overall Completion Rate</span
                        >
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Filter & Search Controls Bar -->
        <div
            class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
        >
            <div class="relative flex-1 sm:max-w-md">
                <Search
                    class="absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-slate-400"
                />
                <Input
                    v-model="searchQuery"
                    @input="handleSearchInput"
                    type="text"
                    placeholder="Search by student name, email, or batch..."
                    class="h-10 border-slate-200 bg-white pl-10 pr-9 text-xs shadow-2xs focus:border-indigo-500 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100"
                />
                <button
                    v-if="searchQuery"
                    @click="clearSearch"
                    class="absolute top-1/2 right-3 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
                >
                    <X class="h-3.5 w-3.5" />
                </button>
            </div>

            <!-- Pipeline Filter Tabs -->
            <div class="relative max-w-full">
                <!-- Left Scroll Arrow Indicator -->
                <button
                    v-if="canScrollLeft"
                    @click="scrollTabs('left')"
                    type="button"
                    class="absolute -left-2.5 top-1/2 -translate-y-1/2 z-20 flex h-6 w-6 items-center justify-center rounded-full border border-slate-200 bg-white/95 text-slate-700 shadow-md transition-all hover:bg-white hover:text-indigo-600 dark:border-slate-700 dark:bg-slate-800/95 dark:text-slate-200 dark:hover:bg-slate-700"
                    aria-label="Scroll left"
                >
                    <ChevronLeft class="h-3.5 w-3.5" />
                </button>

                <div
                    ref="tabsContainerRef"
                    v-auto-hide-scroll
                    class="scrollbar-auto-hide flex max-w-full items-center gap-1.5 overflow-x-auto rounded-xl border border-slate-200/80 bg-slate-100/60 p-1 dark:border-slate-800 dark:bg-slate-900"
                >
                    <button
                        type="button"
                        @click="setStatus('all')"
                        :class="[
                            'rounded-lg px-3 py-1.5 text-xs font-semibold transition-all shrink-0',
                            selectedStatus === 'all'
                                ? 'bg-white text-slate-900 shadow-2xs dark:bg-slate-800 dark:text-white'
                                : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white',
                        ]"
                    >
                        All Statuses
                    </button>
                    <button
                        type="button"
                        @click="setStatus('active')"
                        :class="[
                            'rounded-lg px-3 py-1.5 text-xs font-semibold transition-all shrink-0',
                            selectedStatus === 'active'
                                ? 'bg-emerald-500 text-white shadow-2xs'
                                : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white',
                        ]"
                    >
                        Active
                    </button>
                    <button
                        type="button"
                        @click="setStatus('enrolled')"
                        :class="[
                            'rounded-lg px-3 py-1.5 text-xs font-semibold transition-all shrink-0',
                            selectedStatus === 'enrolled'
                                ? 'bg-sky-600 text-white shadow-2xs'
                                : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white',
                        ]"
                    >
                        Enrolled
                    </button>
                    <button
                        type="button"
                        @click="setStatus('completed')"
                        :class="[
                            'rounded-lg px-3 py-1.5 text-xs font-semibold transition-all shrink-0',
                            selectedStatus === 'completed'
                                ? 'bg-purple-600 text-white shadow-2xs'
                                : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white',
                        ]"
                    >
                        Completed
                    </button>
                    <button
                        type="button"
                        @click="setStatus('dropped_out')"
                        :class="[
                            'rounded-lg px-3 py-1.5 text-xs font-semibold transition-all shrink-0',
                            selectedStatus === 'dropped_out'
                                ? 'bg-rose-600 text-white shadow-2xs'
                                : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white',
                        ]"
                    >
                        Dropped Out
                    </button>
                </div>

                <!-- Right Scroll Arrow Indicator -->
                <button
                    v-if="canScrollRight"
                    @click="scrollTabs('right')"
                    type="button"
                    class="absolute -right-2.5 top-1/2 -translate-y-1/2 z-20 flex h-6 w-6 items-center justify-center rounded-full border border-slate-200 bg-white/95 text-slate-700 shadow-md transition-all hover:bg-white hover:text-indigo-600 dark:border-slate-700 dark:bg-slate-800/95 dark:text-slate-200 dark:hover:bg-slate-700"
                    aria-label="Scroll right"
                >
                    <ChevronRight class="h-3.5 w-3.5" />
                </button>
            </div>
        </div>

        <!-- Main Table Card -->
        <Card
            class="relative overflow-hidden border-slate-200/80 shadow-xs dark:border-slate-800 dark:bg-slate-900"
        >
            <!-- Left Table Scroll Arrow -->
            <button
                v-if="canTableScrollLeft"
                @click="scrollTable('left')"
                type="button"
                class="absolute left-2 top-1/2 -translate-y-1/2 z-20 flex h-8 w-8 items-center justify-center rounded-full border border-slate-200 bg-white/95 text-slate-700 shadow-lg transition-all hover:bg-white hover:text-indigo-600 dark:border-slate-700 dark:bg-slate-800/95 dark:text-slate-200 dark:hover:bg-slate-700"
                aria-label="Scroll table left"
            >
                <ChevronLeft class="h-4 w-4" />
            </button>

            <div
                ref="tableContainerRef"
                v-auto-hide-scroll
                class="scrollbar-auto-hide overflow-x-auto"
            >
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr
                            class="border-b border-slate-200/80 bg-slate-50/80 text-[11px] font-bold tracking-wider text-slate-500 uppercase dark:border-slate-800 dark:bg-slate-950 dark:text-slate-400"
                        >
                            <th class="px-4 py-3.5">Student & Contact Info</th>
                            <th class="px-4 py-3.5">
                                Internship Track / Batch
                            </th>
                            <th class="px-4 py-3.5">Overall Progress</th>
                            <th class="px-4 py-3.5">Status</th>
                            <th class="px-4 py-3.5">Weekly Reports</th>
                            <th class="px-4 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-slate-200/80 dark:divide-slate-800"
                    >
                        <tr v-if="props.students.data.length === 0">
                            <td
                                colspan="6"
                                class="py-12 text-center text-slate-400"
                            >
                                <div
                                    class="flex flex-col items-center justify-center space-y-2"
                                >
                                    <UserX
                                        class="h-8 w-8 text-slate-300 dark:text-slate-700"
                                    />
                                    <p
                                        class="text-sm font-semibold text-slate-600 dark:text-slate-400"
                                    >
                                        No student records found.
                                    </p>
                                    <p class="text-xs text-slate-400">
                                        Try adjusting your search query or status filter.
                                    </p>
                                    <Button
                                        v-if="searchQuery || selectedStatus !== 'all'"
                                        variant="outline"
                                        size="sm"
                                        @click="resetAllFilters"
                                        class="mt-2 text-xs"
                                    >
                                        Reset Filters
                                    </Button>
                                </div>
                            </td>
                        </tr>

                        <tr
                            v-for="student in props.students.data"
                            :key="student.id"
                            class="group transition-colors hover:bg-slate-50/80 dark:hover:bg-slate-800/50"
                        >
                            <!-- Student & Contact Info -->
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-3">
                                    <Avatar
                                        class="h-9 w-9 border border-slate-200 shadow-2xs dark:border-slate-700"
                                    >
                                        <AvatarFallback
                                            class="bg-indigo-600 text-xs font-bold text-white"
                                        >
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
                                        <div
                                            class="flex items-center gap-3 text-[11px] text-slate-500 dark:text-slate-400"
                                        >
                                            <span
                                                class="flex items-center gap-1"
                                            >
                                                <Mail
                                                    class="h-3 w-3 text-slate-400"
                                                />
                                                {{ student.email }}
                                            </span>
                                            <span
                                                v-if="student.phone"
                                                class="flex items-center gap-1"
                                            >
                                                <Phone
                                                    class="h-3 w-3 text-slate-400"
                                                />
                                                {{ student.phone }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Internship Track & Batch -->
                            <td class="px-4 py-3.5">
                                <div
                                    v-if="student.internship"
                                    class="space-y-1"
                                >
                                    <div
                                        class="font-bold text-slate-800 dark:text-slate-200"
                                    >
                                        {{ student.internship.name }}
                                    </div>
                                    <Badge
                                        variant="outline"
                                        class="rounded-md border-indigo-200 bg-indigo-50/50 font-mono text-[10px] font-semibold text-indigo-700 dark:border-indigo-900/60 dark:bg-indigo-950/40 dark:text-indigo-300"
                                    >
                                        Batch {{ student.internship.batch_no }}
                                    </Badge>
                                </div>
                                <span
                                    v-else
                                    class="text-xs text-slate-400 italic"
                                    >No Batch Assigned</span
                                >
                            </td>

                            <!-- Overall Progress Bar -->
                            <td class="w-44 px-4 py-3.5">
                                <div class="space-y-1.5">
                                    <div
                                        class="flex items-center justify-between text-[11px] font-bold"
                                    >
                                        <span
                                            class="text-slate-700 dark:text-slate-300"
                                            >Completion</span
                                        >
                                        <span
                                            class="text-indigo-600 dark:text-indigo-400"
                                            >{{
                                                student.overall_progress
                                            }}%</span
                                        >
                                    </div>
                                    <div
                                        class="h-2 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800"
                                    >
                                        <div
                                            class="h-full rounded-full bg-indigo-600 transition-all duration-300"
                                            :style="{
                                                width: `${student.overall_progress}%`,
                                            }"
                                        ></div>
                                    </div>
                                </div>
                            </td>

                            <!-- Status Badge -->
                            <td class="px-4 py-3.5">
                                <Badge
                                    variant="outline"
                                    :class="[
                                        'rounded-full px-2.5 py-0.5 text-[10px] font-bold tracking-wider uppercase',
                                        getStatusBadge(student.status),
                                    ]"
                                >
                                    {{ formatStatus(student.status) }}
                                </Badge>
                            </td>

                            <!-- Weekly Reports Count -->
                            <td
                                class="px-4 py-3.5 font-medium text-slate-600 dark:text-slate-300"
                            >
                                <div class="flex items-center gap-1.5">
                                    <FileText
                                        class="h-3.5 w-3.5 text-indigo-500"
                                    />
                                    <span
                                        >{{
                                            student.weekly_reports_count || 0
                                        }}
                                        Submissions</span
                                    >
                                </div>
                            </td>

                            <!-- Action Menu Dropdown -->
                            <td class="px-4 py-3.5 text-right">
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
                                    <DropdownMenuContent
                                        align="end"
                                        class="w-44"
                                    >
                                        <DropdownMenuLabel
                                            class="text-xs font-bold text-slate-500"
                                        >
                                            Actions
                                        </DropdownMenuLabel>
                                        <DropdownMenuItem as-child>
                                            <Link
                                                :href="show.url(student.id)"
                                                class="flex cursor-pointer items-center gap-2 text-xs font-semibold"
                                            >
                                                <Eye
                                                    class="h-3.5 w-3.5 text-slate-400"
                                                />
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

            <!-- Right Table Scroll Arrow -->
            <button
                v-if="canTableScrollRight"
                @click="scrollTable('right')"
                type="button"
                class="absolute right-2 top-1/2 -translate-y-1/2 z-20 flex h-8 w-8 items-center justify-center rounded-full border border-slate-200 bg-white/95 text-slate-700 shadow-lg transition-all hover:bg-white hover:text-indigo-600 dark:border-slate-700 dark:bg-slate-800/95 dark:text-slate-200 dark:hover:bg-slate-700"
                aria-label="Scroll table right"
            >
                <ChevronRight class="h-4 w-4" />
            </button>

            <!-- Footer Pagination Controls -->
            <div
                v-if="props.students.total > 0"
                class="flex flex-col items-center justify-between gap-4 border-t border-slate-200/80 px-6 py-4 sm:flex-row dark:border-slate-800"
            >
                <p class="text-sm text-slate-600 dark:text-slate-400">
                    Showing
                    <span class="font-semibold text-slate-900 dark:text-slate-100">{{ props.students.from }}</span>
                    to
                    <span class="font-semibold text-slate-900 dark:text-slate-100">{{ props.students.to }}</span>
                    of
                    <span class="font-semibold text-slate-900 dark:text-slate-100">{{ props.students.total }}</span>
                    candidates
                </p>

                <div class="flex items-center gap-1">
                    <!-- Previous Button -->
                    <button
                        :disabled="!props.students.prev_page_url"
                        @click="props.students.prev_page_url && router.get(props.students.prev_page_url, {}, { preserveState: true, preserveScroll: true, replace: true })"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 transition-all hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400 dark:hover:bg-slate-700"
                    >
                        <ChevronLeft class="h-4 w-4" />
                    </button>

                    <!-- Numbered Page Buttons -->
                    <template v-for="(link, i) in props.students.links.slice(1, -1)" :key="i">
                        <button
                            :disabled="!link.url"
                            @click="link.url && router.get(link.url, {}, { preserveState: true, preserveScroll: true, replace: true })"
                            :class="[
                                'inline-flex h-9 min-w-9 items-center justify-center rounded-lg border px-2 text-sm font-medium transition-all',
                                link.active
                                    ? 'border-indigo-500 bg-indigo-500 text-white shadow-xs'
                                    : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700',
                            ]"
                            v-html="link.label"
                        ></button>
                    </template>

                    <!-- Next Button -->
                    <button
                        :disabled="!props.students.next_page_url"
                        @click="props.students.next_page_url && router.get(props.students.next_page_url, {}, { preserveState: true, preserveScroll: true, replace: true })"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 transition-all hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400 dark:hover:bg-slate-700"
                    >
                        <ChevronRight class="h-4 w-4" />
                    </button>
                </div>
            </div>
        </Card>
    </div>
</template>

<style scoped>
/* Ultra-thin Auto-Hiding Horizontal Scrollbar */
.scrollbar-auto-hide {
    scrollbar-width: thin;
    scrollbar-color: transparent transparent;
    transition: scrollbar-color 0.5s ease-in-out;
}

.scrollbar-auto-hide::-webkit-scrollbar {
    height: 4px;
    width: 4px;
}

.scrollbar-auto-hide::-webkit-scrollbar-track {
    background: transparent;
}

.scrollbar-auto-hide::-webkit-scrollbar-thumb {
    background-color: transparent;
    border-radius: 9999px;
    transition: background-color 0.5s ease-in-out;
}

/* Show thumb animatedly when scrolling or on hover */
.scrollbar-auto-hide.is-scrolling::-webkit-scrollbar-thumb,
.scrollbar-auto-hide:hover::-webkit-scrollbar-thumb {
    background-color: rgba(99, 102, 241, 0.45);
}

.dark .scrollbar-auto-hide.is-scrolling::-webkit-scrollbar-thumb,
.dark .scrollbar-auto-hide:hover::-webkit-scrollbar-thumb {
    background-color: rgba(129, 140, 248, 0.45);
}

.scrollbar-auto-hide.is-scrolling::-webkit-scrollbar-thumb:hover,
.scrollbar-auto-hide:hover::-webkit-scrollbar-thumb:hover {
    background-color: rgba(99, 102, 241, 0.8);
}

.dark .scrollbar-auto-hide.is-scrolling::-webkit-scrollbar-thumb:hover,
.dark .scrollbar-auto-hide:hover::-webkit-scrollbar-thumb:hover {
    background-color: rgba(129, 140, 248, 0.8);
}

.scrollbar-auto-hide.is-scrolling {
    scrollbar-color: rgba(99, 102, 241, 0.45) transparent;
}
</style>
