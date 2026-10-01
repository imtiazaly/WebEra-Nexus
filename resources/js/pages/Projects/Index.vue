<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { index, create, show, edit, destroy } from '@/routes/projects';
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
    X,
} from '@lucide/vue';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Projects Portal', href: index.url() }],
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
    description?: string | null;
    status: string;
    progress: number;
    start_date: string | null;
    deadline: string | null;
    client: Client | null;
    service: Service | null;
    created_at: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginatedProjects {
    data: Project[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    total: number;
    from: number | null;
    to: number | null;
    prev_page_url: string | null;
    next_page_url: string | null;
}

interface ProjectMetrics {
    total: number;
    in_progress: number;
    planning: number;
    completed: number;
    under_review: number;
    on_hold: number;
    cancelled: number;
    overdue: number;
}

const props = defineProps<{
    projects: PaginatedProjects;
    filters?: {
        status?: string;
        type?: string;
        search?: string;
    };
    metrics?: ProjectMetrics;
}>();

// View Mode Toggle (Grid vs Table)
const viewMode = ref<'grid' | 'table'>('table');

// Search & Filter State
const searchQuery = ref(props.filters?.search || '');
const selectedStatus = ref<string>(props.filters?.status || 'all');
const selectedType = ref<string>(props.filters?.type || 'all');

// Helper for Initials
const getInitials = (name: string) => {
    if (!name) return 'PR';
    const parts = name.trim().split(' ');
    if (parts.length >= 2) {
        return `${parts[0][0]}${parts[1][0]}`.toUpperCase();
    }
    return name.slice(0, 2).toUpperCase();
};

// Avatar Color
const getAvatarColor = (name: string) => {
    const colors = [
        'bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300 border-indigo-200',
        'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300 border-blue-200',
        'bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-300 border-purple-200',
        'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300 border-amber-200',
    ];
    let hash = 0;
    for (let i = 0; i < name.length; i++) {
        hash = name.charCodeAt(i) + ((hash << 5) - hash);
    }
    return colors[Math.abs(hash) % colors.length];
};

// Metrics from Server with fallbacks
const metricsCount = computed(() => {
    return (
        props.metrics || {
            total: props.projects.total || 0,
            in_progress: 0,
            planning: 0,
            completed: 0,
            under_review: 0,
            on_hold: 0,
            cancelled: 0,
            overdue: 0,
        }
    );
});

// Status Badge Config
const getStatusConfig = (status: string) => {
    switch (status) {
        case 'planning':
            return {
                label: 'Planning',
                badgeClass:
                    'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800',
                dotClass: 'bg-blue-500',
            };
        case 'in_progress':
            return {
                label: 'In Progress',
                badgeClass:
                    'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800',
                dotClass: 'bg-amber-500',
            };
        case 'under_review':
            return {
                label: 'Under Review',
                badgeClass:
                    'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/40 dark:text-purple-300 dark:border-purple-800',
                dotClass: 'bg-purple-500',
            };
        case 'completed':
            return {
                label: 'Completed',
                badgeClass:
                    'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800',
                dotClass: 'bg-emerald-500',
            };
        case 'on_hold':
            return {
                label: 'On Hold',
                badgeClass:
                    'bg-slate-50 text-slate-700 border-slate-200 dark:bg-slate-900 dark:text-slate-300 dark:border-slate-800',
                dotClass: 'bg-slate-400',
            };
        case 'cancelled':
            return {
                label: 'Cancelled',
                badgeClass:
                    'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800',
                dotClass: 'bg-rose-500',
            };
        default:
            return {
                label: status,
                badgeClass:
                    'bg-slate-50 text-slate-700 border-slate-200 dark:bg-slate-900 dark:text-slate-300 dark:border-slate-800',
                dotClass: 'bg-slate-400',
            };
    }
};

// Progress Bar Color Helper
const getProgressGradient = (progress: number) => {
    if (progress >= 100) return 'bg-emerald-500';
    if (progress >= 50) return 'bg-indigo-600';
    if (progress >= 25) return 'bg-amber-500';
    return 'bg-slate-400';
};

// Database-Driven Server-Side Filtering
let searchDebounceTimer: ReturnType<typeof setTimeout> | null = null;

const applyServerFilters = (
    newStatus?: string,
    newType?: string,
    newSearch?: string,
) => {
    const statusToApply =
        newStatus !== undefined ? newStatus : selectedStatus.value;
    const typeToApply = newType !== undefined ? newType : selectedType.value;
    const searchToApply =
        newSearch !== undefined ? newSearch : searchQuery.value;

    router.get(
        index.url(),
        {
            status: statusToApply !== 'all' ? statusToApply : undefined,
            type: typeToApply !== 'all' ? typeToApply : undefined,
            search: searchToApply.trim() ? searchToApply.trim() : undefined,
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
    applyServerFilters(status, selectedType.value, searchQuery.value);
};

const setType = (type: string) => {
    selectedType.value = type;
    applyServerFilters(selectedStatus.value, type, searchQuery.value);
};

const handleSearchInput = () => {
    if (searchDebounceTimer) {
        clearTimeout(searchDebounceTimer);
    }
    searchDebounceTimer = setTimeout(() => {
        applyServerFilters(
            selectedStatus.value,
            selectedType.value,
            searchQuery.value,
        );
    }, 350);
};

const clearSearch = () => {
    searchQuery.value = '';
    applyServerFilters(selectedStatus.value, selectedType.value, '');
};

const resetAllFilters = () => {
    selectedStatus.value = 'all';
    selectedType.value = 'all';
    searchQuery.value = '';
    applyServerFilters('all', 'all', '');
};

const deleteProject = (id: number) => {
    if (confirm('Are you sure you want to delete this project?')) {
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
    canTableScrollRight.value =
        el.scrollLeft < el.scrollWidth - el.clientWidth - 5;
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
        tabsContainerRef.value.addEventListener('scroll', checkScrollState, {
            passive: true,
        });
    }
    if (tableContainerRef.value) {
        tableContainerRef.value.addEventListener(
            'scroll',
            checkTableScrollState,
            { passive: true },
        );
    }
    window.addEventListener('resize', () => {
        checkScrollState();
        checkTableScrollState();
    });
});

const handleResize = () => {
    checkScrollState();
    checkTableScrollState();
};

onUnmounted(() => {
    if (tabsContainerRef.value) {
        tabsContainerRef.value.removeEventListener('scroll', checkScrollState);
    }
    if (tableContainerRef.value) {
        tableContainerRef.value.removeEventListener(
            'scroll',
            checkTableScrollState,
        );
    }
    window.addEventListener('resize', handleResize);
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
        (el as any)._onScrollCleanup = () =>
            el.removeEventListener('scroll', handleScroll);
    },
    unmounted(el: HTMLElement) {
        if ((el as any)._onScrollCleanup) {
            (el as any)._onScrollCleanup();
        }
    },
};
</script>

<template>
    <Head title="Projects Portal" />

    <div class="w-full space-y-6 p-4 sm:p-6">
        <!-- Page Header -->
        <div
            class="flex flex-col gap-4 border-b border-slate-200/80 pb-5 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800"
        >
            <div>
                <div class="flex flex-wrap items-center gap-2.5">
                    <h1
                        class="text-xl font-extrabold tracking-tight text-slate-900 sm:text-2xl dark:text-slate-50"
                    >
                        Projects Portal
                    </h1>
                    <Badge
                        variant="outline"
                        class="rounded-full border-indigo-200 bg-indigo-50/50 px-2.5 py-0.5 text-xs font-semibold text-indigo-700 dark:border-indigo-900 dark:bg-indigo-950/50 dark:text-indigo-300"
                    >
                        {{ metricsCount.total }} Projects
                    </Badge>
                </div>
                <p
                    class="mt-1 text-xs text-slate-500 sm:text-sm dark:text-slate-400"
                >
                    Manage client deliverables, internal practice tasks, and
                    track real-time completion progress.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <Button
                    as-child
                    size="default"
                    class="w-full bg-indigo-600 font-medium text-white shadow-sm transition-all duration-150 hover:bg-indigo-700 sm:w-auto"
                >
                    <Link :href="create.url()">
                        <Plus class="mr-1.5 h-4 w-4" /> Create Project / Task
                    </Link>
                </Button>
            </div>
        </div>

        <!-- Metric Stat Cards -->
        <div
            class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4 lg:grid-cols-4"
        >
            <div
                class="flex items-center justify-between rounded-xl border border-slate-200/80 bg-white p-4 shadow-xs dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="space-y-0.5">
                    <p
                        class="text-xs font-medium tracking-wider text-slate-500 uppercase dark:text-slate-400"
                    >
                        Total Projects
                    </p>
                    <p
                        class="text-2xl font-extrabold text-slate-900 dark:text-slate-50"
                    >
                        {{ metricsCount.total }}
                    </p>
                </div>
                <div
                    class="rounded-lg bg-slate-100 p-2.5 text-slate-700 dark:bg-slate-800 dark:text-slate-300"
                >
                    <FolderKanban class="h-5 w-5" />
                </div>
            </div>

            <div
                class="flex items-center justify-between rounded-xl border border-slate-200/80 bg-white p-4 shadow-xs dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="space-y-0.5">
                    <p
                        class="text-xs font-medium tracking-wider text-slate-500 uppercase dark:text-slate-400"
                    >
                        In Progress
                    </p>
                    <p
                        class="text-2xl font-extrabold text-amber-600 dark:text-amber-400"
                    >
                        {{ metricsCount.in_progress }}
                    </p>
                </div>
                <div
                    class="rounded-lg bg-amber-50 p-2.5 text-amber-600 dark:bg-amber-950/60 dark:text-amber-400"
                >
                    <PlayCircle class="h-5 w-5" />
                </div>
            </div>

            <div
                class="flex items-center justify-between rounded-xl border border-slate-200/80 bg-white p-4 shadow-xs dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="space-y-0.5">
                    <p
                        class="text-xs font-medium tracking-wider text-slate-500 uppercase dark:text-slate-400"
                    >
                        Completed
                    </p>
                    <p
                        class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400"
                    >
                        {{ metricsCount.completed }}
                    </p>
                </div>
                <div
                    class="rounded-lg bg-emerald-50 p-2.5 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400"
                >
                    <CheckCircle2 class="h-5 w-5" />
                </div>
            </div>

            <div
                class="flex items-center justify-between rounded-xl border border-slate-200/80 bg-white p-4 shadow-xs dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="space-y-0.5">
                    <p
                        class="text-xs font-medium tracking-wider text-slate-500 uppercase dark:text-slate-400"
                    >
                        Overdue / Urgent
                    </p>
                    <p
                        class="text-2xl font-extrabold text-rose-600 dark:text-rose-400"
                    >
                        {{ metricsCount.overdue }}
                    </p>
                </div>
                <div
                    class="rounded-lg bg-rose-50 p-2.5 text-rose-600 dark:bg-rose-950/60 dark:text-rose-400"
                >
                    <AlertTriangle class="h-5 w-5" />
                </div>
            </div>
        </div>

        <!-- Toolbar & Main Card -->
        <Card
            class="overflow-hidden border-slate-200/80 shadow-xs dark:border-slate-800 dark:bg-slate-900"
        >
            <!-- Toolbar -->
            <div
                class="flex flex-col gap-3 border-b border-slate-200/80 p-4 xl:flex-row xl:items-center xl:justify-between dark:border-slate-800"
            >
                <!-- Left: Search Input & Type Filter -->
                <div
                    class="flex w-full flex-col gap-2.5 sm:flex-row sm:items-center xl:w-auto"
                >
                    <div class="relative w-full shrink-0 sm:w-64">
                        <Search
                            class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-slate-400"
                        />
                        <Input
                            v-model="searchQuery"
                            @input="handleSearchInput"
                            type="text"
                            placeholder="Search by project, client, track..."
                            class="h-9 border-slate-200 bg-slate-50/50 pr-8 pl-9 text-xs focus:bg-white dark:border-slate-800 dark:bg-slate-800/40 dark:focus:bg-slate-900"
                        />
                        <button
                            v-if="searchQuery"
                            @click="clearSearch"
                            type="button"
                            class="absolute top-1/2 right-2.5 -translate-y-1/2 rounded-full p-0.5 text-slate-400 hover:bg-slate-200 hover:text-slate-600 dark:hover:bg-slate-700 dark:hover:text-slate-200"
                            title="Clear search"
                        >
                            <X class="h-3.5 w-3.5" />
                        </button>
                    </div>
                    <select
                        v-model="selectedType"
                        @change="
                            setType(($event.target as HTMLSelectElement).value)
                        "
                        class="h-9 w-full shrink-0 rounded-md border border-slate-200 bg-white px-3 text-xs text-slate-700 shadow-xs focus:border-indigo-500 focus:outline-none sm:w-48 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300"
                    >
                        <option value="all">All Project Types</option>
                        <option value="client">Client Projects</option>
                        <option value="internal">
                            Internal Practice Tasks
                        </option>
                    </select>
                </div>

                <!-- Right: Status Filter Tabs & View Mode Switcher -->
                <div
                    class="flex w-full min-w-0 items-center justify-between gap-2.5 xl:w-auto xl:justify-end"
                >
                    <div class="relative min-w-0 flex-1 xl:w-auto">
                        <!-- Left Scroll Arrow Indicator -->
                        <button
                            v-if="canScrollLeft"
                            @click="scrollTabs('left')"
                            type="button"
                            class="absolute top-1/2 -left-2.5 z-20 flex h-6 w-6 -translate-y-1/2 items-center justify-center rounded-full border border-slate-200 bg-white/95 text-slate-700 shadow-md transition-all hover:bg-white hover:text-indigo-600 dark:border-slate-700 dark:bg-slate-800/95 dark:text-slate-200 dark:hover:bg-slate-700"
                            aria-label="Scroll left"
                        >
                            <ChevronLeft class="h-3.5 w-3.5" />
                        </button>

                        <div
                            ref="tabsContainerRef"
                            v-auto-hide-scroll
                            class="scrollbar-auto-hide flex w-full min-w-0 items-center gap-1.5 overflow-x-auto rounded-lg bg-slate-100 p-1 text-xs font-medium dark:bg-slate-800/70"
                        >
                            <button
                                @click="setStatus('all')"
                                type="button"
                                :class="[
                                    'shrink-0 rounded-md px-3 py-1.5 whitespace-nowrap transition-all',
                                    selectedStatus === 'all'
                                        ? 'bg-white font-semibold text-slate-900 shadow-xs dark:bg-slate-900 dark:text-white'
                                        : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200',
                                ]"
                            >
                                All ({{ metricsCount.total }})
                            </button>
                            <button
                                @click="setStatus('in_progress')"
                                type="button"
                                :class="[
                                    'shrink-0 rounded-md px-3 py-1.5 whitespace-nowrap transition-all',
                                    selectedStatus === 'in_progress'
                                        ? 'bg-white font-semibold text-slate-900 shadow-xs dark:bg-slate-900 dark:text-white'
                                        : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200',
                                ]"
                            >
                                In Progress ({{ metricsCount.in_progress }})
                            </button>
                            <button
                                @click="setStatus('planning')"
                                type="button"
                                :class="[
                                    'shrink-0 rounded-md px-3 py-1.5 whitespace-nowrap transition-all',
                                    selectedStatus === 'planning'
                                        ? 'bg-white font-semibold text-slate-900 shadow-xs dark:bg-slate-900 dark:text-white'
                                        : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200',
                                ]"
                            >
                                Planning ({{ metricsCount.planning }})
                            </button>
                            <button
                                @click="setStatus('under_review')"
                                type="button"
                                :class="[
                                    'shrink-0 rounded-md px-3 py-1.5 whitespace-nowrap transition-all',
                                    selectedStatus === 'under_review'
                                        ? 'bg-white font-semibold text-slate-900 shadow-xs dark:bg-slate-900 dark:text-white'
                                        : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200',
                                ]"
                            >
                                Under Review ({{ metricsCount.under_review }})
                            </button>
                            <button
                                @click="setStatus('completed')"
                                type="button"
                                :class="[
                                    'shrink-0 rounded-md px-3 py-1.5 whitespace-nowrap transition-all',
                                    selectedStatus === 'completed'
                                        ? 'bg-white font-semibold text-slate-900 shadow-xs dark:bg-slate-900 dark:text-white'
                                        : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200',
                                ]"
                            >
                                Completed ({{ metricsCount.completed }})
                            </button>
                            <button
                                @click="setStatus('on_hold')"
                                type="button"
                                :class="[
                                    'shrink-0 rounded-md px-3 py-1.5 whitespace-nowrap transition-all',
                                    selectedStatus === 'on_hold'
                                        ? 'bg-white font-semibold text-slate-900 shadow-xs dark:bg-slate-900 dark:text-white'
                                        : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200',
                                ]"
                            >
                                On Hold ({{ metricsCount.on_hold }})
                            </button>
                        </div>

                        <!-- Right Scroll Arrow Indicator -->
                        <button
                            v-if="canScrollRight"
                            @click="scrollTabs('right')"
                            type="button"
                            class="absolute top-1/2 -right-2.5 z-20 flex h-6 w-6 -translate-y-1/2 items-center justify-center rounded-full border border-slate-200 bg-white/95 text-slate-700 shadow-md transition-all hover:bg-white hover:text-indigo-600 dark:border-slate-700 dark:bg-slate-800/95 dark:text-slate-200 dark:hover:bg-slate-700"
                            aria-label="Scroll right"
                        >
                            <ChevronRight class="h-3.5 w-3.5" />
                        </button>
                    </div>

                    <!-- Grid vs Table View Switcher -->
                    <div
                        class="hidden shrink-0 items-center gap-1 rounded-lg border border-slate-200 p-1 sm:flex dark:border-slate-800"
                    >
                        <button
                            @click="viewMode = 'table'"
                            :class="[
                                'rounded-md p-1.5 transition-all',
                                viewMode === 'table'
                                    ? 'bg-slate-200 text-slate-900 dark:bg-slate-800 dark:text-white'
                                    : 'text-slate-400 hover:text-slate-600',
                            ]"
                            title="Table View"
                        >
                            <List class="h-4 w-4" />
                        </button>
                        <button
                            @click="viewMode = 'grid'"
                            :class="[
                                'rounded-md p-1.5 transition-all',
                                viewMode === 'grid'
                                    ? 'bg-slate-200 text-slate-900 dark:bg-slate-800 dark:text-white'
                                    : 'text-slate-400 hover:text-slate-600',
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
                <div
                    v-if="props.projects.data.length === 0"
                    class="py-16 text-center"
                >
                    <div
                        class="mx-auto flex max-w-xs flex-col items-center justify-center space-y-3"
                    >
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400 dark:bg-slate-800"
                        >
                            <FolderKanban class="h-6 w-6" />
                        </div>
                        <div>
                            <p
                                class="text-sm font-bold text-slate-900 dark:text-slate-100"
                            >
                                No projects found
                            </p>
                            <p
                                class="mt-0.5 text-xs text-slate-500 dark:text-slate-400"
                            >
                                {{
                                    searchQuery ||
                                    selectedStatus !== 'all' ||
                                    selectedType !== 'all'
                                        ? 'Try adjusting your search query or filters.'
                                        : 'Get started by creating your first project or task.'
                                }}
                            </p>
                        </div>
                        <Button
                            v-if="
                                searchQuery ||
                                selectedStatus !== 'all' ||
                                selectedType !== 'all'
                            "
                            @click="resetAllFilters"
                            variant="outline"
                            size="sm"
                            class="mt-2"
                        >
                            Reset Filters
                        </Button>
                        <Button
                            v-else
                            as-child
                            size="sm"
                            class="mt-2 bg-indigo-600 text-white hover:bg-indigo-700"
                        >
                            <Link :href="create.url()">
                                <Plus class="mr-1 h-3.5 w-3.5" /> Create Project
                            </Link>
                        </Button>
                    </div>
                </div>

                <!-- 1️⃣ GRID CARDS VIEW -->
                <div
                    v-else-if="viewMode === 'grid'"
                    class="grid grid-cols-1 gap-4 p-6 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <div
                        v-for="project in props.projects.data"
                        :key="project.id"
                        class="group relative flex flex-col justify-between rounded-xl border border-slate-200/80 bg-white p-5 shadow-2xs transition-all hover:shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    >
                        <div class="space-y-3">
                            <div class="flex items-start justify-between gap-2">
                                <span
                                    :class="[
                                        'inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-semibold',
                                        getStatusConfig(project.status)
                                            .badgeClass,
                                    ]"
                                >
                                    <span
                                        :class="[
                                            'h-1.5 w-1.5 rounded-full',
                                            getStatusConfig(project.status)
                                                .dotClass,
                                        ]"
                                    ></span>
                                    {{ getStatusConfig(project.status).label }}
                                </span>

                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button
                                            variant="ghost"
                                            size="icon-sm"
                                            class="h-7 w-7 text-slate-400 hover:text-slate-900 dark:hover:text-slate-100"
                                        >
                                            <MoreHorizontal class="h-4 w-4" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent
                                        align="end"
                                        class="w-44"
                                    >
                                        <DropdownMenuItem as-child>
                                            <Link
                                                :href="show.url(project.id)"
                                                class="flex cursor-pointer items-center gap-2"
                                            >
                                                <Eye
                                                    class="h-4 w-4 text-slate-500"
                                                />
                                                <span>View Details</span>
                                            </Link>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem as-child>
                                            <Link
                                                :href="edit.url(project.id)"
                                                class="flex cursor-pointer items-center gap-2"
                                            >
                                                <Pencil
                                                    class="h-4 w-4 text-slate-500"
                                                />
                                                <span>Edit Project</span>
                                            </Link>
                                        </DropdownMenuItem>
                                        <DropdownMenuSeparator />
                                        <DropdownMenuItem
                                            @click="deleteProject(project.id)"
                                            class="flex cursor-pointer items-center gap-2 text-rose-600 focus:text-rose-600"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                            <span>Delete Project</span>
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>

                            <div>
                                <Link
                                    :href="show.url(project.id)"
                                    class="line-clamp-1 text-base font-bold text-slate-900 transition-colors hover:text-indigo-600 dark:text-slate-100 dark:hover:text-indigo-400"
                                >
                                    {{ project.title }}
                                </Link>
                                <p
                                    v-if="project.client"
                                    class="mt-1 flex items-center gap-1 text-xs font-medium text-slate-500"
                                >
                                    <Building2
                                        class="h-3.5 w-3.5 text-slate-400"
                                    />
                                    {{ project.client.name }}
                                </p>
                                <p
                                    v-else
                                    class="mt-1 flex items-center gap-1 text-xs font-medium text-indigo-600 dark:text-indigo-400"
                                >
                                    <Wrench class="h-3.5 w-3.5" /> Internal
                                    Practice Task
                                </p>
                            </div>

                            <!-- Progress Bar -->
                            <div class="space-y-1.5 pt-2">
                                <div
                                    class="flex justify-between text-xs font-medium"
                                >
                                    <span class="text-slate-500">Progress</span>
                                    <span
                                        class="font-bold text-slate-900 dark:text-slate-100"
                                        >{{ project.progress }}%</span
                                    >
                                </div>
                                <div
                                    class="h-2 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800"
                                >
                                    <div
                                        :class="[
                                            'h-2 rounded-full transition-all duration-300',
                                            getProgressGradient(
                                                project.progress,
                                            ),
                                        ]"
                                        :style="{
                                            width: `${project.progress}%`,
                                        }"
                                    ></div>
                                </div>
                            </div>
                        </div>

                        <div
                            class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3 text-xs text-slate-500 dark:border-slate-800/80"
                        >
                            <span
                                v-if="project.service"
                                class="truncate font-medium text-slate-600 dark:text-slate-400"
                            >
                                {{ project.service.name }}
                            </span>
                            <span v-else class="text-slate-400 italic"
                                >General</span
                            >

                            <span
                                class="flex items-center gap-1 font-mono text-[11px]"
                            >
                                <Clock class="h-3 w-3 text-slate-400" />
                                {{ project.deadline || 'No deadline' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- 2️⃣ ENTERPRISE TABLE VIEW -->
                <div v-else class="relative w-full">
                    <!-- Left Table Scroll Arrow -->
                    <button
                        v-if="canTableScrollLeft"
                        @click="scrollTable('left')"
                        type="button"
                        class="absolute top-1/2 left-2 z-20 hidden h-8 w-8 -translate-y-1/2 items-center justify-center rounded-full border border-slate-200 bg-white/95 text-slate-700 shadow-lg transition-all hover:bg-white hover:text-indigo-600 md:flex dark:border-slate-700 dark:bg-slate-800/95 dark:text-slate-200 dark:hover:bg-slate-700"
                        aria-label="Scroll table left"
                    >
                        <ChevronLeft class="h-4 w-4" />
                    </button>

                    <div
                        ref="tableContainerRef"
                        v-auto-hide-scroll
                        class="scrollbar-auto-hide hidden w-full overflow-x-auto md:block"
                    >
                        <table
                            class="w-full min-w-225 table-fixed border-collapse text-left"
                        >
                            <thead>
                                <tr
                                    class="border-b border-slate-200/80 bg-slate-50/70 text-[11px] font-bold tracking-wider text-slate-500 uppercase dark:border-slate-800 dark:bg-slate-800/50 dark:text-slate-400"
                                >
                                    <th class="w-[24%] px-5 py-3.5">
                                        Project Title
                                    </th>
                                    <th class="w-[22%] px-5 py-3.5">
                                        Client / Assignment
                                    </th>
                                    <th class="w-[16%] px-5 py-3.5">
                                        Service Track
                                    </th>
                                    <th class="w-[16%] px-5 py-3.5">
                                        Completion
                                    </th>
                                    <th class="w-[10%] px-5 py-3.5">Status</th>
                                    <th class="w-[12%] px-5 py-3.5">
                                        Deadline
                                    </th>
                                    <th class="w-[6%] px-5 py-3.5 text-center">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody
                                class="divide-y divide-slate-200/80 text-xs text-slate-700 dark:divide-slate-800 dark:text-slate-300"
                            >
                                <tr
                                    v-for="project in props.projects.data"
                                    :key="project.id"
                                    class="group transition-colors hover:bg-slate-50/80 dark:hover:bg-slate-800/40"
                                >
                                    <!-- Title -->
                                    <td class="px-5 py-4">
                                        <div class="min-w-0">
                                            <Link
                                                :href="show.url(project.id)"
                                                class="block truncate text-sm font-extrabold text-slate-900 transition-colors hover:text-indigo-600 dark:text-slate-100 dark:hover:text-indigo-400"
                                            >
                                                {{ project.title }}
                                            </Link>
                                            <p
                                                class="font-mono text-[11px] text-slate-400"
                                            >
                                                #PRJ-{{ project.id }}
                                            </p>
                                        </div>
                                    </td>

                                    <!-- Client / Assignment -->
                                    <td class="px-5 py-4">
                                        <div
                                            v-if="project.client"
                                            class="flex items-center gap-2"
                                        >
                                            <Avatar
                                                class="h-7 w-7 border border-slate-200 dark:border-slate-700"
                                            >
                                                <AvatarFallback
                                                    :class="[
                                                        'text-[10px] font-bold',
                                                        getAvatarColor(
                                                            project.client.name,
                                                        ),
                                                    ]"
                                                >
                                                    {{
                                                        getInitials(
                                                            project.client.name,
                                                        )
                                                    }}
                                                </AvatarFallback>
                                            </Avatar>
                                            <div class="min-w-0">
                                                <span
                                                    class="block truncate font-semibold text-slate-800 dark:text-slate-200"
                                                >
                                                    {{ project.client.name }}
                                                </span>
                                            </div>
                                        </div>
                                        <div v-else>
                                            <span
                                                class="inline-flex items-center gap-1.5 rounded-md border border-indigo-200/80 bg-indigo-50/60 px-2.5 py-0.5 text-xs font-semibold text-indigo-700 dark:border-indigo-900 dark:bg-indigo-950/60 dark:text-indigo-300"
                                            >
                                                <Wrench class="h-3 w-3" />
                                                Internal Task
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Service Track -->
                                    <td class="px-5 py-4">
                                        <span
                                            class="font-medium text-slate-600 dark:text-slate-400"
                                        >
                                            {{ project.service?.name || '-' }}
                                        </span>
                                    </td>

                                    <!-- Completion Progress Bar -->
                                    <td class="px-5 py-4">
                                        <div class="w-32 space-y-1">
                                            <div
                                                class="flex justify-between text-[11px] font-medium"
                                            >
                                                <span class="text-slate-500"
                                                    >Progress</span
                                                >
                                                <span
                                                    class="font-bold text-slate-900 dark:text-slate-100"
                                                    >{{
                                                        project.progress
                                                    }}%</span
                                                >
                                            </div>
                                            <div
                                                class="h-2 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800"
                                            >
                                                <div
                                                    :class="[
                                                        'h-2 rounded-full transition-all duration-300',
                                                        getProgressGradient(
                                                            project.progress,
                                                        ),
                                                    ]"
                                                    :style="{
                                                        width: `${project.progress}%`,
                                                    }"
                                                ></div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Status Badge -->
                                    <td class="px-5 py-4">
                                        <span
                                            :class="[
                                                'inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-semibold whitespace-nowrap',
                                                getStatusConfig(project.status)
                                                    .badgeClass,
                                            ]"
                                        >
                                            <span
                                                :class="[
                                                    'h-1.5 w-1.5 shrink-0 rounded-full',
                                                    getStatusConfig(
                                                        project.status,
                                                    ).dotClass,
                                                ]"
                                            ></span>
                                            {{
                                                getStatusConfig(project.status)
                                                    .label
                                            }}
                                        </span>
                                    </td>

                                    <!-- Deadline -->
                                    <td class="px-5 py-4">
                                        <div
                                            class="flex items-center gap-1.5 font-mono text-xs text-slate-600 dark:text-slate-400"
                                        >
                                            <Calendar
                                                class="h-3.5 w-3.5 text-slate-400"
                                            />
                                            <span>{{
                                                project.deadline ||
                                                'No deadline'
                                            }}</span>
                                        </div>
                                    </td>

                                    <!-- Actions Dropdown -->
                                    <td class="px-5 py-4 text-center">
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button
                                                    variant="ghost"
                                                    size="icon-sm"
                                                    class="h-8 w-8 text-slate-500 hover:text-slate-900 dark:hover:text-slate-100"
                                                >
                                                    <MoreHorizontal
                                                        class="h-4 w-4"
                                                    />
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent
                                                align="end"
                                                class="w-44"
                                            >
                                                <DropdownMenuLabel
                                                    class="text-xs font-semibold text-slate-500"
                                                    >Actions</DropdownMenuLabel
                                                >
                                                <DropdownMenuSeparator />
                                                <DropdownMenuItem as-child>
                                                    <Link
                                                        :href="
                                                            show.url(project.id)
                                                        "
                                                        class="flex cursor-pointer items-center gap-2"
                                                    >
                                                        <Eye
                                                            class="h-4 w-4 text-slate-500"
                                                        />
                                                        <span
                                                            >View Details</span
                                                        >
                                                    </Link>
                                                </DropdownMenuItem>
                                                <DropdownMenuItem as-child>
                                                    <Link
                                                        :href="
                                                            edit.url(project.id)
                                                        "
                                                        class="flex cursor-pointer items-center gap-2"
                                                    >
                                                        <Pencil
                                                            class="h-4 w-4 text-slate-500"
                                                        />
                                                        <span
                                                            >Edit Project</span
                                                        >
                                                    </Link>
                                                </DropdownMenuItem>
                                                <DropdownMenuSeparator />
                                                <DropdownMenuItem
                                                    @click="
                                                        deleteProject(
                                                            project.id,
                                                        )
                                                    "
                                                    class="flex cursor-pointer items-center gap-2 text-rose-600 focus:text-rose-600"
                                                >
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

                    <!-- Right Table Scroll Arrow -->
                    <button
                        v-if="canTableScrollRight"
                        @click="scrollTable('right')"
                        type="button"
                        class="absolute top-1/2 right-2 z-20 hidden h-8 w-8 -translate-y-1/2 items-center justify-center rounded-full border border-slate-200 bg-white/95 text-slate-700 shadow-lg transition-all hover:bg-white hover:text-indigo-600 md:flex dark:border-slate-700 dark:bg-slate-800/95 dark:text-slate-200 dark:hover:bg-slate-700"
                        aria-label="Scroll table right"
                    >
                        <ChevronRight class="h-4 w-4" />
                    </button>

                    <!-- Mobile View (Cards for mobile < md) -->
                    <div
                        class="block space-y-4 divide-y divide-slate-200 p-4 md:hidden dark:divide-slate-800"
                    >
                        <div
                            v-for="project in props.projects.data"
                            :key="project.id"
                            class="space-y-3 rounded-xl border border-slate-200/80 bg-slate-50/50 p-4 dark:border-slate-800 dark:bg-slate-900/60"
                        >
                            <div class="flex items-center justify-between">
                                <span
                                    :class="[
                                        'inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-semibold',
                                        getStatusConfig(project.status)
                                            .badgeClass,
                                    ]"
                                >
                                    <span
                                        :class="[
                                            'h-1.5 w-1.5 rounded-full',
                                            getStatusConfig(project.status)
                                                .dotClass,
                                        ]"
                                    ></span>
                                    {{ getStatusConfig(project.status).label }}
                                </span>
                                <DropdownMenu>
                                    <DropdownMenuTrigger as-child>
                                        <Button
                                            variant="ghost"
                                            size="icon-sm"
                                            class="h-8 w-8 text-slate-500"
                                        >
                                            <MoreHorizontal class="h-4 w-4" />
                                        </Button>
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent
                                        align="end"
                                        class="w-44"
                                    >
                                        <DropdownMenuItem as-child>
                                            <Link
                                                :href="show.url(project.id)"
                                                class="flex items-center gap-2"
                                            >
                                                <Eye
                                                    class="h-4 w-4 text-slate-500"
                                                />
                                                <span>View Details</span>
                                            </Link>
                                        </DropdownMenuItem>
                                        <DropdownMenuItem as-child>
                                            <Link
                                                :href="edit.url(project.id)"
                                                class="flex items-center gap-2"
                                            >
                                                <Pencil
                                                    class="h-4 w-4 text-slate-500"
                                                />
                                                <span>Edit Project</span>
                                            </Link>
                                        </DropdownMenuItem>
                                        <DropdownMenuSeparator />
                                        <DropdownMenuItem
                                            @click="deleteProject(project.id)"
                                            class="flex items-center gap-2 text-rose-600"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                            <span>Delete Project</span>
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </div>

                            <div>
                                <Link
                                    :href="show.url(project.id)"
                                    class="text-sm font-bold text-slate-900 hover:text-indigo-600 dark:text-slate-100"
                                >
                                    {{ project.title }}
                                </Link>
                                <p
                                    v-if="project.client"
                                    class="mt-0.5 text-xs text-slate-500"
                                >
                                    Client:
                                    <span
                                        class="font-medium text-slate-700 dark:text-slate-300"
                                        >{{ project.client.name }}</span
                                    >
                                </p>
                                <p
                                    v-else
                                    class="mt-0.5 flex items-center gap-1 text-xs font-medium text-indigo-600 dark:text-indigo-400"
                                >
                                    <Wrench class="h-3 w-3" /> Internal Practice
                                    Task
                                </p>
                            </div>

                            <div class="space-y-1 pt-1">
                                <div
                                    class="flex justify-between text-xs font-medium"
                                >
                                    <span class="text-slate-500">Progress</span>
                                    <span
                                        class="font-bold text-slate-900 dark:text-slate-100"
                                        >{{ project.progress }}%</span
                                    >
                                </div>
                                <div
                                    class="h-2 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800"
                                >
                                    <div
                                        :class="[
                                            'h-2 rounded-full transition-all duration-300',
                                            getProgressGradient(
                                                project.progress,
                                            ),
                                        ]"
                                        :style="{
                                            width: `${project.progress}%`,
                                        }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Pagination Bar (Direct Database Pagination) -->
                <div
                    v-if="props.projects.total > 0"
                    class="flex flex-col gap-3 border-t border-slate-200/80 px-5 py-3 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800"
                >
                    <p
                        class="text-center text-xs text-slate-500 sm:text-left dark:text-slate-400"
                    >
                        Showing
                        <span
                            class="font-semibold text-slate-700 dark:text-slate-300"
                            >{{ props.projects.from || 0 }}</span
                        >
                        to
                        <span
                            class="font-semibold text-slate-700 dark:text-slate-300"
                            >{{ props.projects.to || 0 }}</span
                        >
                        of
                        <span
                            class="font-semibold text-slate-700 dark:text-slate-300"
                            >{{ props.projects.total }}</span
                        >
                        projects
                    </p>

                    <!-- Interactive Pagination Navigation Links -->
                    <div
                        v-if="props.projects.last_page > 1"
                        class="flex flex-wrap items-center justify-center gap-1.5"
                    >
                        <!-- Previous Page Button -->
                        <Button
                            v-if="props.projects.prev_page_url"
                            as-child
                            variant="outline"
                            size="sm"
                            class="h-8 gap-1 text-xs"
                        >
                            <Link
                                :href="props.projects.prev_page_url"
                                preserve-scroll
                                preserve-state
                            >
                                <ChevronLeft class="h-3.5 w-3.5" /> Previous
                            </Link>
                        </Button>
                        <Button
                            v-else
                            variant="outline"
                            size="sm"
                            class="h-8 gap-1 text-xs opacity-50"
                            disabled
                        >
                            <ChevronLeft class="h-3.5 w-3.5" /> Previous
                        </Button>

                        <!-- Numbered Page Links -->
                        <template
                            v-for="(link, idx) in props.projects.links.slice(
                                1,
                                -1,
                            )"
                            :key="idx"
                        >
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                preserve-scroll
                                preserve-state
                                :class="[
                                    'inline-flex h-8 min-w-8 items-center justify-center rounded-md px-2.5 text-xs font-semibold transition-all',
                                    link.active
                                        ? 'bg-indigo-600 text-white shadow-xs'
                                        : 'border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800',
                                ]"
                                v-html="link.label"
                            />
                            <span
                                v-else
                                class="inline-flex h-8 min-w-8 items-center justify-center px-1 text-xs text-slate-400"
                                v-html="link.label"
                            />
                        </template>

                        <!-- Next Page Button -->
                        <Button
                            v-if="props.projects.next_page_url"
                            as-child
                            variant="outline"
                            size="sm"
                            class="h-8 gap-1 text-xs"
                        >
                            <Link
                                :href="props.projects.next_page_url"
                                preserve-scroll
                                preserve-state
                            >
                                Next <ChevronRight class="h-3.5 w-3.5" />
                            </Link>
                        </Button>
                        <Button
                            v-else
                            variant="outline"
                            size="sm"
                            class="h-8 gap-1 text-xs opacity-50"
                            disabled
                        >
                            Next <ChevronRight class="h-3.5 w-3.5" />
                        </Button>
                    </div>
                </div>
            </CardContent>
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
