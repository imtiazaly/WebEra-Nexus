<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { index, update } from '@/routes/internships';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import {
    ArrowLeft,
    Sparkles,
    Pencil,
    Calendar,
    Layers,
    Tag,
    ShieldCheck,
    Wand2,
    Clock,
    Zap,
    GraduationCap,
    Eye,
    Save,
} from '@lucide/vue';
import TrackManagerModal from '@/components/TrackManagerModal.vue';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Internship Academy Portal', href: index.url() },
            { title: 'Edit Batch Studio', href: '#' },
        ],
    },
});

interface Service {
    id: number;
    name: string;
}

interface Internship {
    id: number;
    name: string;
    batch_no: string;
    start_date: string;
    end_date: string;
    status: string;
    service_id: number;
    total_students_count?: number;
}

const props = defineProps<{
    internship: Internship;
    services: Service[];
}>();

const isTrackModalOpen = ref(false);

const form = useForm({
    name: props.internship.name,
    batch_no: props.internship.batch_no,
    service_id: props.internship.service_id,
    start_date: props.internship.start_date,
    end_date: props.internship.end_date,
    status: props.internship.status,
});

// Calculate duration in days & weeks
const durationTelemetry = computed(() => {
    if (!form.start_date || !form.end_date) return null;
    try {
        const start = new Date(form.start_date).getTime();
        const end = new Date(form.end_date).getTime();
        if (isNaN(start) || isNaN(end) || end <= start) return null;
        const diffDays = Math.round((end - start) / (1000 * 60 * 60 * 24));
        const weeks = Math.round((diffDays / 7) * 10) / 10;
        return { days: diffDays, weeks };
    } catch {
        return null;
    }
});

// Selected Track Name
const selectedTrackName = computed(() => {
    if (!form.service_id) return 'General Training Track';
    const found = props.services.find((s) => s.id === Number(form.service_id));
    return found ? found.name : 'Selected Track';
});

const submit = () => {
    form.put(update.url(props.internship.id));
};
</script>

<template>
    <Head title="Edit Internship Batch Studio" />

    <div class="w-full space-y-8 p-4 sm:p-6 lg:p-8">
        <!-- 🌟 HERO HEADER BANNER -->
        <div
            class="relative overflow-hidden rounded-3xl border border-indigo-200/80 bg-gradient-to-br from-indigo-50/90 via-purple-50/60 to-slate-50/90 p-6 sm:p-8 text-slate-900 shadow-xl backdrop-blur-xl dark:border-indigo-500/20 dark:from-indigo-950 dark:via-slate-900 dark:to-purple-950 dark:text-white"
        >
            <div class="pointer-events-none absolute -top-20 -right-20 h-80 w-80 rounded-full bg-indigo-500/15 blur-3xl dark:bg-indigo-500/20"></div>
            <div class="pointer-events-none absolute -bottom-20 -left-20 h-80 w-80 rounded-full bg-purple-500/15 blur-3xl dark:bg-purple-500/20"></div>

            <div class="relative z-10 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="space-y-2">
                    <div class="inline-flex items-center gap-2 rounded-full border border-indigo-200 bg-indigo-50 px-3.5 py-1 text-xs font-semibold tracking-wide text-indigo-700 backdrop-blur-md dark:border-indigo-400/30 dark:bg-indigo-500/10 dark:text-indigo-300">
                        <Pencil class="h-3.5 w-3.5 text-indigo-600 dark:text-indigo-400" />
                        <span>Academy Batch Configurator</span>
                    </div>
                    <h1 class="text-2xl font-black tracking-tight text-slate-900 sm:text-3xl lg:text-4xl dark:text-white">
                        Edit Batch: {{ internship.name }}
                    </h1>
                    <p class="max-w-xl text-xs font-medium text-slate-600 sm:text-sm dark:text-slate-300">
                        Update batch track details, dates, and live status allocations with real-time card preview.
                    </p>
                </div>

                <Button
                    as-child
                    variant="outline"
                    class="rounded-xl border-indigo-200/80 bg-white/80 font-semibold text-indigo-700 hover:bg-white dark:border-indigo-400/30 dark:bg-slate-900/60 dark:text-indigo-300 dark:hover:bg-slate-800 dark:hover:text-white"
                >
                    <Link :href="index.url()">
                        <ArrowLeft class="mr-2 h-4 w-4" /> Back to Academy Hub
                    </Link>
                </Button>
            </div>
        </div>

        <!-- 🎛️ MAIN STUDIO GRID (Form Studio + Live Preview Card) -->
        <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
            <!-- LEFT COLUMN: FORM STUDIO (7 COLS) -->
            <div class="lg:col-span-7">
                <Card class="overflow-hidden rounded-3xl border border-slate-200/80 shadow-lg dark:border-slate-800 dark:bg-slate-900">
                    <CardContent class="p-6 sm:p-8">
                        <form @submit.prevent="submit" class="space-y-8">
                            <!-- SECTION 1: PROGRAM & IDENTITY -->
                            <div class="space-y-5">
                                <div class="flex items-center gap-2 border-b border-slate-200/80 pb-3 dark:border-slate-800">
                                    <Sparkles class="h-4 w-4 text-indigo-600 dark:text-indigo-400" />
                                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider dark:text-slate-100">
                                        1. Identity & Track Category
                                    </h3>
                                </div>

                                <!-- Batch Title Input -->
                                <div class="space-y-2">
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                                        Internship Program Title *
                                    </label>
                                    <Input
                                        v-model="form.name"
                                        type="text"
                                        required
                                        class="h-11 rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-800 dark:bg-slate-800/50"
                                    />
                                    <p v-if="form.errors.name" class="text-xs font-semibold text-rose-500">
                                        {{ form.errors.name }}
                                    </p>
                                </div>

                                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                    <!-- Batch Code -->
                                    <div class="space-y-2">
                                        <label class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                                            <Tag class="h-3.5 w-3.5 text-indigo-500" />
                                            Batch Number / Code *
                                        </label>
                                        <Input
                                            v-model="form.batch_no"
                                            type="text"
                                            required
                                            class="h-11 font-mono rounded-xl border-slate-200 text-sm focus:border-indigo-500 dark:border-slate-800 dark:bg-slate-800/50"
                                        />
                                        <p v-if="form.errors.batch_no" class="text-xs font-semibold text-rose-500">
                                            {{ form.errors.batch_no }}
                                        </p>
                                    </div>

                                    <!-- Service Track -->
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between">
                                            <label class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                                                <Layers class="h-3.5 w-3.5 text-indigo-500" />
                                                Learning Track *
                                            </label>
                                            <button
                                                type="button"
                                                @click="isTrackModalOpen = true"
                                                class="text-[11px] font-bold text-indigo-600 hover:underline dark:text-indigo-400"
                                            >
                                                + Manage Tracks
                                            </button>
                                        </div>
                                        <select
                                            v-model="form.service_id"
                                            required
                                            class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm font-semibold text-slate-900 shadow-xs focus:border-indigo-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100"
                                        >
                                            <option value="" disabled class="bg-white text-slate-900 dark:bg-slate-900 dark:text-slate-100">-- Select Learning Track --</option>
                                            <option v-for="service in services" :key="service.id" :value="service.id" class="bg-white text-slate-900 dark:bg-slate-900 dark:text-slate-100">
                                                {{ service.name }}
                                            </option>
                                        </select>
                                        <p v-if="form.errors.service_id" class="text-xs font-semibold text-rose-500">
                                            {{ form.errors.service_id }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- SECTION 2: DURATION TELEMETRY -->
                            <div class="space-y-5">
                                <div class="flex items-center gap-2 border-b border-slate-200/80 pb-3 dark:border-slate-800">
                                    <Calendar class="h-4 w-4 text-indigo-600 dark:text-indigo-400" />
                                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider dark:text-slate-100">
                                        2. Schedule & Duration Telemetry
                                    </h3>
                                </div>

                                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                    <!-- Start Date -->
                                    <div class="space-y-2">
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                                            Start Date *
                                        </label>
                                        <Input
                                            v-model="form.start_date"
                                            type="date"
                                            required
                                            class="h-11 rounded-xl border-slate-200 text-sm focus:border-indigo-500 dark:border-slate-800 dark:bg-slate-800/50"
                                        />
                                    </div>

                                    <!-- End Date -->
                                    <div class="space-y-2">
                                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                                            Target Completion Date *
                                        </label>
                                        <Input
                                            v-model="form.end_date"
                                            type="date"
                                            required
                                            class="h-11 rounded-xl border-slate-200 text-sm focus:border-indigo-500 dark:border-slate-800 dark:bg-slate-800/50"
                                        />
                                    </div>
                                </div>

                                <!-- Calculated Duration Banner -->
                                <div
                                    v-if="durationTelemetry"
                                    class="flex items-center justify-between rounded-2xl border border-indigo-200/80 bg-indigo-50/70 p-4 text-xs text-indigo-900 dark:border-indigo-900/60 dark:bg-indigo-950/40 dark:text-indigo-300"
                                >
                                    <div class="flex items-center gap-2 font-semibold">
                                        <Clock class="h-4 w-4 text-indigo-600 dark:text-indigo-400" />
                                        <span>Calculated Cohort Duration:</span>
                                    </div>
                                    <span class="font-mono font-bold text-indigo-700 dark:text-indigo-300 text-sm">
                                        {{ durationTelemetry.days }} Days (~{{ durationTelemetry.weeks }} Weeks)
                                    </span>
                                </div>
                            </div>

                            <!-- SECTION 3: VISUAL STATUS PILLS -->
                            <div class="space-y-4">
                                <div class="flex items-center gap-2 border-b border-slate-200/80 pb-3 dark:border-slate-800">
                                    <ShieldCheck class="h-4 w-4 text-indigo-600 dark:text-indigo-400" />
                                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider dark:text-slate-100">
                                        3. Batch Lifecycle State
                                    </h3>
                                </div>

                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                                    <!-- Upcoming Option -->
                                    <label
                                        @click="form.status = 'upcoming'"
                                        :class="[
                                            'flex cursor-pointer flex-col justify-between rounded-2xl border p-4 transition-all',
                                            form.status === 'upcoming'
                                                ? 'border-indigo-600 bg-indigo-50/70 ring-2 ring-indigo-500/20 dark:border-indigo-500 dark:bg-indigo-950/40'
                                                : 'border-slate-200 bg-slate-50/50 hover:bg-slate-100 dark:border-slate-800 dark:bg-slate-800/40',
                                        ]"
                                    >
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400">Upcoming</span>
                                            <div :class="['h-3.5 w-3.5 rounded-full border-2', form.status === 'upcoming' ? 'border-indigo-600 bg-indigo-600' : 'border-slate-400']"></div>
                                        </div>
                                        <p class="mt-2 text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                                            Scheduled for future trainee onboarding.
                                        </p>
                                    </label>

                                    <!-- Active Option -->
                                    <label
                                        @click="form.status = 'active'"
                                        :class="[
                                            'flex cursor-pointer flex-col justify-between rounded-2xl border p-4 transition-all',
                                            form.status === 'active'
                                                ? 'border-emerald-600 bg-emerald-50/70 ring-2 ring-emerald-500/20 dark:border-emerald-500 dark:bg-emerald-950/40'
                                                : 'border-slate-200 bg-slate-50/50 hover:bg-slate-100 dark:border-slate-800 dark:bg-slate-800/40',
                                        ]"
                                    >
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                                                <Zap class="h-3.5 w-3.5" /> Active Live
                                            </span>
                                            <div :class="['h-3.5 w-3.5 rounded-full border-2', form.status === 'active' ? 'border-emerald-600 bg-emerald-600' : 'border-slate-400']"></div>
                                        </div>
                                        <p class="mt-2 text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                                            Currently running live batch track.
                                        </p>
                                    </label>

                                    <!-- Completed Option -->
                                    <label
                                        @click="form.status = 'completed'"
                                        :class="[
                                            'flex cursor-pointer flex-col justify-between rounded-2xl border p-4 transition-all',
                                            form.status === 'completed'
                                                ? 'border-purple-600 bg-purple-50/70 ring-2 ring-purple-500/20 dark:border-purple-500 dark:bg-purple-950/40'
                                                : 'border-slate-200 bg-slate-50/50 hover:bg-slate-100 dark:border-slate-800 dark:bg-slate-800/40',
                                        ]"
                                    >
                                        <div class="flex items-center justify-between">
                                            <span class="text-xs font-bold text-purple-600 dark:text-purple-400">Completed</span>
                                            <div :class="['h-3.5 w-3.5 rounded-full border-2', form.status === 'completed' ? 'border-purple-600 bg-purple-600' : 'border-slate-400']"></div>
                                        </div>
                                        <p class="mt-2 text-[11px] text-slate-500 dark:text-slate-400 font-medium">
                                            Graduated / Archived training track.
                                        </p>
                                    </label>
                                </div>
                            </div>

                            <!-- FORM SUBMIT CONTROLS -->
                            <div class="flex items-center justify-end gap-3 border-t border-slate-200/80 pt-6 dark:border-slate-800">
                                <Button as-child variant="outline" class="rounded-xl font-semibold">
                                    <Link :href="index.url()">Cancel</Link>
                                </Button>
                                <Button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="rounded-xl bg-indigo-600 px-6 font-bold text-white shadow-lg hover:bg-indigo-500"
                                >
                                    <Save class="mr-2 h-4 w-4" /> Save Batch Changes
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>

            <!-- RIGHT COLUMN: LIVE HOLOGRAM CARD PREVIEW (5 COLS) -->
            <div class="lg:col-span-5 space-y-4">
                <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-slate-500">
                    <Eye class="h-4 w-4 text-indigo-500" />
                    <span>Live Batch Card Telemetry Preview</span>
                </div>

                <!-- Preview Hologram Card -->
                <div class="relative overflow-hidden rounded-3xl border border-indigo-500/30 bg-white p-6 shadow-xl dark:bg-slate-900">
                    <div class="pointer-events-none absolute -top-12 -right-12 h-36 w-36 rounded-full bg-gradient-to-br from-indigo-500/20 to-purple-500/10 blur-2xl"></div>

                    <div class="relative z-10 space-y-5">
                        <!-- Top Row Code + Status -->
                        <div class="flex items-center justify-between">
                            <span class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-mono font-bold text-slate-800 dark:border-slate-800 dark:bg-slate-800 dark:text-slate-200">
                                🏷️ {{ form.batch_no || 'BATCH-CODE' }}
                            </span>

                            <Badge
                                variant="outline"
                                class="rounded-full px-2.5 py-0.5 text-xs font-bold capitalize border-indigo-500/30 text-indigo-600 dark:text-indigo-400"
                            >
                                {{ form.status }}
                            </Badge>
                        </div>

                        <!-- Title & Track -->
                        <div class="space-y-1.5">
                            <h4 class="text-lg font-bold text-slate-900 dark:text-slate-100">
                                {{ form.name || 'Batch Title Will Appear Here' }}
                            </h4>
                            <Badge variant="secondary" class="rounded-md text-[11px] font-semibold text-indigo-700 bg-indigo-50 dark:bg-indigo-950 dark:text-indigo-300">
                                Track: {{ selectedTrackName }}
                            </Badge>
                        </div>

                        <!-- Duration Progress Gauge Preview -->
                        <div class="space-y-2 rounded-2xl border border-slate-100 bg-slate-50/80 p-3.5 dark:border-slate-800/80 dark:bg-slate-800/40">
                            <div class="flex items-center justify-between text-xs font-semibold">
                                <span class="text-slate-600 dark:text-slate-300">Duration Radar</span>
                                <span class="font-mono text-indigo-600 dark:text-indigo-400 font-bold">
                                    {{ durationTelemetry ? `${durationTelemetry.days} Days` : 'Dates Not Set' }}
                                </span>
                            </div>
                            <div class="h-2 w-full overflow-hidden rounded-full bg-slate-200 dark:bg-slate-700">
                                <div class="h-full rounded-full bg-gradient-to-r from-indigo-500 via-cyan-500 to-emerald-500 w-3/4"></div>
                            </div>
                            <div class="flex items-center justify-between text-[11px] text-slate-400">
                                <span>Start: {{ form.start_date || 'YYYY-MM-DD' }}</span>
                                <span>End: {{ form.end_date || 'YYYY-MM-DD' }}</span>
                            </div>
                        </div>

                        <!-- Enrolled Interns Mock -->
                        <div class="flex items-center justify-between border-t border-slate-100 pt-4 dark:border-slate-800">
                            <div class="flex items-center gap-2">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100 text-indigo-700 font-bold text-xs dark:bg-indigo-950 dark:text-indigo-300">
                                    🎓
                                </div>
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300">
                                    {{ internship.total_students_count || 0 }} Trainees Enrolled
                                </span>
                            </div>
                            <span class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400">Hub Configured →</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <TrackManagerModal
            :is-open="isTrackModalOpen"
            :tracks="services || []"
            @close="isTrackModalOpen = false"
        />
    </div>
</template>
