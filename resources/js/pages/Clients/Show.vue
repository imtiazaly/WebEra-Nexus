<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { index, edit, aiBrief } from '@/routes/clients';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
    CardDescription,
} from '@/components/ui/card';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import {
    ArrowLeft,
    Pencil,
    Mail,
    Phone,
    Building2,
    Calendar,
    Sparkles,
    BrainCircuit,
    Loader2,
    DollarSign,
    Briefcase,
    FileText,
    FolderKanban,
    MessageSquare,
} from '@lucide/vue';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Clients & Leads', href: index.url() },
            { title: 'Client Details', href: '#' },
        ],
    },
});

interface Service {
    id: number;
    name: string;
    pivot?: {
        requirements: string | null;
        estimated_budget: number | null;
    };
}

interface Project {
    id: number;
    title: string;
    status: string;
    progress: number;
}

interface Client {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    company_name: string | null;
    status: string;
    notes: string | null;
    ai_brief: string | null;
    services: Service[];
    projects?: Project[];
    created_at: string;
}

const props = defineProps<{
    client: Client;
}>();

const generatingBrief = ref(false);

const generateBrief = () => {
    generatingBrief.value = true;
    router.post(
        aiBrief.url(props.client.id),
        {},
        {
            onFinish: () => {
                generatingBrief.value = false;
            },
        },
    );
};

// Helper for Initials
const getInitials = (name: string) => {
    if (!name) return 'CL';
    const parts = name.trim().split(' ');
    if (parts.length >= 2) {
        return `${parts[0][0]}${parts[1][0]}`.toUpperCase();
    }
    return name.slice(0, 2).toUpperCase();
};

// Total Calculated Budget
const totalEstimatedBudget = computed(() => {
    if (!props.client.services) return 0;
    return props.client.services.reduce((sum, s) => {
        const val = s.pivot?.estimated_budget;
        return sum + (val ? Number(val) : 0);
    }, 0);
});

// Format Date
const formatDate = (dateStr: string) => {
    if (!dateStr) return 'N/A';
    return new Date(dateStr).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

// Status Badge Config
const getStatusConfig = (status: string) => {
    switch (status) {
        case 'new_lead':
            return {
                label: 'New Lead',
                badgeClass:
                    'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/50 dark:text-blue-300 dark:border-blue-800',
                dotClass: 'bg-blue-500',
            };
        case 'contacted':
            return {
                label: 'Contacted',
                badgeClass:
                    'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/50 dark:text-amber-300 dark:border-amber-800',
                dotClass: 'bg-amber-500',
            };
        case 'converted':
            return {
                label: 'Converted Client',
                badgeClass:
                    'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-300 dark:border-emerald-800',
                dotClass: 'bg-emerald-500',
            };
        case 'lost':
            return {
                label: 'Lost Lead',
                badgeClass:
                    'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/50 dark:text-rose-300 dark:border-rose-800',
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

const formatProjectStatus = (status: string) => {
    return status.replace('_', ' ').replace(/\b\w/g, (l) => l.toUpperCase());
};
</script>

<template>
    <Head :title="`${client.name} — Details`" />

    <div class="w-full space-y-6 p-4 sm:p-6">
        <!-- Top Navigation Bar -->
        <div class="flex items-center justify-between">
            <Button
                variant="ghost"
                size="sm"
                as-child
                class="-ml-2 text-slate-500 hover:text-slate-900 dark:hover:text-slate-100"
            >
                <Link :href="index.url()">
                    <ArrowLeft class="mr-1.5 h-4 w-4" /> Back to Clients Portal
                </Link>
            </Button>
            <Button
                as-child
                size="sm"
                class="gap-1.5 bg-indigo-600 text-white shadow-sm hover:bg-indigo-700"
            >
                <Link :href="edit.url(client.id)">
                    <Pencil class="h-3.5 w-3.5" /> Edit Profile
                </Link>
            </Button>
        </div>

        <!-- Main Executive Profile Header Card -->
        <Card
            class="overflow-hidden border-slate-200/80 shadow-xs dark:border-slate-800 dark:bg-slate-900"
        >
            <CardContent class="p-6">
                <div
                    class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between"
                >
                    <div class="flex items-start gap-4">
                        <Avatar
                            class="h-16 w-16 border-2 border-indigo-200 shadow-sm dark:border-indigo-900"
                        >
                            <AvatarFallback
                                class="bg-indigo-600 text-lg font-bold text-white"
                            >
                                {{ getInitials(client.name) }}
                            </AvatarFallback>
                        </Avatar>
                        <div class="space-y-1">
                            <div class="flex flex-wrap items-center gap-3">
                                <h1
                                    class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-slate-50"
                                >
                                    {{ client.name }}
                                </h1>
                                <span
                                    :class="[
                                        'inline-flex items-center gap-1.5 rounded-full border px-3 py-0.5 text-xs font-semibold',
                                        getStatusConfig(client.status)
                                            .badgeClass,
                                    ]"
                                >
                                    <span
                                        :class="[
                                            'h-2 w-2 rounded-full',
                                            getStatusConfig(client.status)
                                                .dotClass,
                                        ]"
                                    ></span>
                                    {{ getStatusConfig(client.status).label }}
                                </span>
                            </div>
                            <div
                                class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500 dark:text-slate-400"
                            >
                                <span
                                    class="flex items-center gap-1 font-medium text-slate-700 dark:text-slate-300"
                                >
                                    <Building2
                                        class="h-3.5 w-3.5 text-slate-400"
                                    />
                                    {{
                                        client.company_name ||
                                        'Individual Client'
                                    }}
                                </span>
                                <span>•</span>
                                <span class="flex items-center gap-1">
                                    <Calendar
                                        class="h-3.5 w-3.5 text-slate-400"
                                    />
                                    Joined {{ formatDate(client.created_at) }}
                                </span>
                                <span>•</span>
                                <span class="font-mono text-slate-400"
                                    >ID: #CLN-{{ client.id }}</span
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- Quick Metrics Grid -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Email -->
            <div
                class="flex items-center justify-between rounded-xl border border-slate-200/80 bg-white p-4 shadow-xs dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="min-w-0 space-y-0.5 pr-2">
                    <p
                        class="text-[11px] font-semibold tracking-wider text-slate-400 uppercase"
                    >
                        Email Address
                    </p>
                    <a
                        :href="`mailto:${client.email}`"
                        class="block truncate text-xs font-bold text-indigo-600 hover:underline dark:text-indigo-400"
                    >
                        {{ client.email }}
                    </a>
                </div>
                <div
                    class="shrink-0 rounded-lg bg-indigo-50 p-2.5 text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-400"
                >
                    <Mail class="h-5 w-5" />
                </div>
            </div>

            <!-- Phone -->
            <div
                class="flex items-center justify-between rounded-xl border border-slate-200/80 bg-white p-4 shadow-xs dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="space-y-0.5">
                    <p
                        class="text-[11px] font-semibold tracking-wider text-slate-400 uppercase"
                    >
                        Phone / WhatsApp
                    </p>
                    <p
                        class="text-xs font-bold text-slate-900 dark:text-slate-100"
                    >
                        {{ client.phone || 'Not Provided' }}
                    </p>
                </div>
                <div
                    class="shrink-0 rounded-lg bg-emerald-50 p-2.5 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400"
                >
                    <Phone class="h-5 w-5" />
                </div>
            </div>

            <!-- Total Budget -->
            <div
                class="flex items-center justify-between rounded-xl border border-slate-200/80 bg-white p-4 shadow-xs dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="space-y-0.5">
                    <p
                        class="text-[11px] font-semibold tracking-wider text-slate-400 uppercase"
                    >
                        Total Est. Budget
                    </p>
                    <p
                        class="text-lg font-extrabold text-emerald-600 dark:text-emerald-400"
                    >
                        ${{ totalEstimatedBudget.toLocaleString() }}
                    </p>
                </div>
                <div
                    class="shrink-0 rounded-lg bg-amber-50 p-2.5 text-amber-600 dark:bg-amber-950/60 dark:text-amber-400"
                >
                    <DollarSign class="h-5 w-5" />
                </div>
            </div>

            <!-- Services Count -->
            <div
                class="flex items-center justify-between rounded-xl border border-slate-200/80 bg-white p-4 shadow-xs dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="space-y-0.5">
                    <p
                        class="text-[11px] font-semibold tracking-wider text-slate-400 uppercase"
                    >
                        Requested Tracks
                    </p>
                    <p
                        class="text-lg font-extrabold text-slate-900 dark:text-slate-50"
                    >
                        {{ client.services?.length || 0 }} Services
                    </p>
                </div>
                <div
                    class="shrink-0 rounded-lg bg-purple-50 p-2.5 text-purple-600 dark:bg-purple-950/60 dark:text-purple-400"
                >
                    <Briefcase class="h-5 w-5" />
                </div>
            </div>
        </div>

        <!-- 🤖 AI Strategic Lead Brief Card -->
        <Card
            class="overflow-hidden border-purple-200/80 bg-gradient-to-br from-purple-50/40 via-white to-indigo-50/20 shadow-xs dark:border-purple-900/50 dark:from-purple-950/20 dark:via-slate-900 dark:to-indigo-950/10"
        >
            <CardHeader
                class="border-b border-purple-100 pb-4 dark:border-purple-900/40"
            >
                <div
                    class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div class="flex items-center gap-2.5">
                        <div
                            class="flex h-9 w-9 items-center justify-center rounded-lg bg-purple-600 text-white shadow-xs"
                        >
                            <BrainCircuit class="h-5 w-5" />
                        </div>
                        <div>
                            <CardTitle
                                class="flex items-center gap-1.5 text-base font-extrabold text-purple-950 dark:text-purple-200"
                            >
                                AI Strategic Executive Brief
                                <Sparkles
                                    class="h-4 w-4 animate-pulse text-purple-500"
                                />
                            </CardTitle>
                            <CardDescription
                                class="text-xs text-purple-700/70 dark:text-purple-400/80"
                            >
                                AI-generated executive intelligence summary &
                                conversion strategy.
                            </CardDescription>
                        </div>
                    </div>
                    <Button
                        @click="generateBrief"
                        :disabled="generatingBrief"
                        size="sm"
                        class="gap-1.5 bg-purple-600 text-xs font-medium text-white shadow-sm hover:bg-purple-700"
                    >
                        <Loader2
                            v-if="generatingBrief"
                            class="h-3.5 w-3.5 animate-spin"
                        />
                        <Sparkles v-else class="h-3.5 w-3.5" />
                        <span>{{
                            generatingBrief
                                ? 'Generating AI Brief...'
                                : client.ai_brief
                                  ? 'Regenerate AI Brief'
                                  : 'Generate AI Brief'
                        }}</span>
                    </Button>
                </div>
            </CardHeader>
            <CardContent class="p-6">
                <div
                    v-if="client.ai_brief"
                    class="relative rounded-lg border border-purple-200/60 bg-white/80 p-4 text-xs leading-relaxed whitespace-pre-line text-purple-950 shadow-2xs sm:text-sm dark:border-purple-900/40 dark:bg-purple-950/40 dark:text-purple-200"
                >
                    {{ client.ai_brief }}
                </div>
                <div
                    v-else
                    class="flex flex-col items-center justify-center py-6 text-center"
                >
                    <BrainCircuit
                        class="mb-2 h-8 w-8 text-purple-300 dark:text-purple-800"
                    />
                    <p
                        class="text-xs font-semibold text-purple-900 dark:text-purple-300"
                    >
                        No AI Strategic Brief generated yet.
                    </p>
                    <p
                        class="mt-0.5 text-[11px] text-purple-600/70 dark:text-purple-400/70"
                    >
                        Click "Generate AI Brief" to create an executive
                        synthesis for this lead.
                    </p>
                </div>
            </CardContent>
        </Card>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
            <!-- Left Column (7 Cols): Services Requested Breakdown -->
            <div class="space-y-6 lg:col-span-7">
                <Card
                    class="border-slate-200/80 shadow-xs dark:border-slate-800 dark:bg-slate-900"
                >
                    <CardHeader
                        class="border-b border-slate-200/80 pb-4 dark:border-slate-800"
                    >
                        <div
                            class="flex items-center gap-2 text-indigo-600 dark:text-indigo-400"
                        >
                            <Briefcase class="h-5 w-5" />
                            <CardTitle
                                class="text-base font-bold text-slate-900 dark:text-slate-100"
                            >
                                Services Requested & Specifications
                            </CardTitle>
                        </div>
                    </CardHeader>
                    <CardContent class="p-6">
                        <div
                            v-if="
                                !client.services || client.services.length === 0
                            "
                            class="py-8 text-center text-xs text-slate-400"
                        >
                            No services attached to this client yet.
                        </div>
                        <div v-else class="space-y-4">
                            <div
                                v-for="service in client.services"
                                :key="service.id"
                                class="space-y-3 rounded-xl border border-slate-200/80 bg-slate-50/50 p-4 dark:border-slate-800 dark:bg-slate-800/40"
                            >
                                <div class="flex items-center justify-between">
                                    <span
                                        class="text-sm font-extrabold text-slate-900 dark:text-slate-100"
                                    >
                                        {{ service.name }}
                                    </span>
                                    <Badge
                                        variant="outline"
                                        class="border-emerald-200 bg-emerald-50 text-xs font-bold text-emerald-700 dark:border-emerald-900 dark:bg-emerald-950/60 dark:text-emerald-400"
                                    >
                                        Budget:
                                        {{
                                            service.pivot?.estimated_budget
                                                ? `$${Number(service.pivot.estimated_budget).toLocaleString()}`
                                                : 'TBD'
                                        }}
                                    </Badge>
                                </div>
                                <div
                                    class="rounded-lg border border-slate-200/60 bg-white p-3 text-xs text-slate-600 dark:border-slate-700/60 dark:bg-slate-900 dark:text-slate-300"
                                >
                                    <p
                                        class="mb-0.5 flex items-center gap-1 text-[11px] font-semibold text-slate-500"
                                    >
                                        <FileText
                                            class="h-3 w-3 text-slate-400"
                                        />
                                        Requirements:
                                    </p>
                                    <p class="leading-relaxed">
                                        {{
                                            service.pivot?.requirements ||
                                            'No specific requirements documented.'
                                        }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Communication Notes -->
                <Card
                    v-if="client.notes"
                    class="border-slate-200/80 shadow-xs dark:border-slate-800 dark:bg-slate-900"
                >
                    <CardHeader
                        class="border-b border-slate-200/80 pb-4 dark:border-slate-800"
                    >
                        <div
                            class="flex items-center gap-2 text-indigo-600 dark:text-indigo-400"
                        >
                            <MessageSquare class="h-5 w-5" />
                            <CardTitle
                                class="text-base font-bold text-slate-900 dark:text-slate-100"
                            >
                                Communication History & Notes
                            </CardTitle>
                        </div>
                    </CardHeader>
                    <CardContent class="p-6">
                        <div
                            class="rounded-xl border border-slate-200/80 bg-slate-50/50 p-4 font-sans text-xs leading-relaxed whitespace-pre-line text-slate-700 sm:text-sm dark:border-slate-800 dark:bg-slate-900/60 dark:text-slate-300"
                        >
                            {{ client.notes }}
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Right Column (5 Cols): Active Client Projects -->
            <div class="space-y-6 lg:col-span-5">
                <Card
                    class="border-slate-200/80 shadow-xs dark:border-slate-800 dark:bg-slate-900"
                >
                    <CardHeader
                        class="border-b border-slate-200/80 pb-4 dark:border-slate-800"
                    >
                        <div class="flex items-center justify-between">
                            <div
                                class="flex items-center gap-2 text-indigo-600 dark:text-indigo-400"
                            >
                                <FolderKanban class="h-5 w-5" />
                                <CardTitle
                                    class="text-base font-bold text-slate-900 dark:text-slate-100"
                                >
                                    Associated Projects
                                </CardTitle>
                            </div>
                            <Badge variant="secondary" class="text-xs">
                                {{ client.projects?.length || 0 }} Active
                            </Badge>
                        </div>
                    </CardHeader>
                    <CardContent class="p-6">
                        <div
                            v-if="
                                !client.projects || client.projects.length === 0
                            "
                            class="py-8 text-center text-xs text-slate-400"
                        >
                            No projects currently linked to this client.
                        </div>
                        <div v-else class="space-y-4">
                            <div
                                v-for="proj in client.projects"
                                :key="proj.id"
                                class="space-y-3 rounded-xl border border-slate-200/80 bg-white p-4 shadow-2xs dark:border-slate-800 dark:bg-slate-950/60"
                            >
                                <div class="flex items-center justify-between">
                                    <p
                                        class="text-xs font-bold text-slate-900 dark:text-slate-100"
                                    >
                                        {{ proj.title }}
                                    </p>
                                    <Badge
                                        variant="outline"
                                        class="text-[10px]"
                                    >
                                        {{ formatProjectStatus(proj.status) }}
                                    </Badge>
                                </div>
                                <div class="space-y-1">
                                    <div
                                        class="flex justify-between text-[11px] font-medium text-slate-500"
                                    >
                                        <span>Completion Progress</span>
                                        <span
                                            class="font-bold text-indigo-600 dark:text-indigo-400"
                                            >{{ proj.progress }}%</span
                                        >
                                    </div>
                                    <div
                                        class="h-2 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800"
                                    >
                                        <div
                                            class="h-2 rounded-full bg-indigo-600 transition-all duration-300"
                                            :style="{
                                                width: `${proj.progress}%`,
                                            }"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>
