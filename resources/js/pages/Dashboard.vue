<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { dashboard } from '@/routes';
import { index as clientsIndex } from '@/routes/clients';
import { index as projectsIndex } from '@/routes/projects';
import {
    index as studentsIndex,
    show as studentsShow,
} from '@/routes/students';

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
}

interface Service {
    id: number;
    name: string;
}

interface Project {
    id: number;
    title: string;
    status: string;
    deadline: string;
    client: Client | null;
    service: Service | null;
}

interface Student {
    id: number;
    name: string;
}

interface WeeklyReport {
    id: number;
    week_number: number;
    status: string;
    student: Student;
}

interface Lead {
    id: number;
    name: string;
    email: string;
    created_at: string;
}

interface Metrics {
    total_leads: number;
    active_clients: number;
    conversion_rate: number;
    active_projects: number;
    client_projects: number;
    internal_tasks: number;
    active_batches: number;
    total_students: number;
    active_students: number;
    completed_students: number;
    pending_reports: number;
}

const props = defineProps<{
    metrics: Metrics;
    upcomingDeadlines: Project[];
    overdueProjects: Project[];
    uncontactedLeads: Lead[];
    pendingReports: WeeklyReport[];
}>();
</script>

<template>
    <Head title="Admin Dashboard" />

    <div class="mx-auto max-w-7xl space-y-6 p-6">
        <!-- Header -->
        <div>
            <h1
                class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white"
            >
                WebEra Solutions PK — Admin Dashboard
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Real-time business performance analytics across clients,
                projects, internships, and student reports.
            </p>
        </div>

        <!-- KPI Cards Grid -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Card 1: Leads & Conversion -->
            <div
                class="space-y-2 rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900"
            >
                <div
                    class="flex items-center justify-between text-xs font-semibold text-gray-500 uppercase"
                >
                    <span>Leads & Conversion</span>
                    <span class="text-lg">🎯</span>
                </div>
                <div class="flex items-baseline justify-between">
                    <div
                        class="text-2xl font-extrabold text-gray-900 dark:text-white"
                    >
                        {{ metrics.total_leads }} Leads
                    </div>
                    <div
                        class="text-sm font-bold text-emerald-600 dark:text-emerald-400"
                    >
                        {{ metrics.conversion_rate }}% Conv.
                    </div>
                </div>
                <div class="text-xs text-gray-400">
                    Active Clients: {{ metrics.active_clients }}
                </div>
            </div>

            <!-- Card 2: Active Projects -->
            <div
                class="space-y-2 rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900"
            >
                <div
                    class="flex items-center justify-between text-xs font-semibold text-gray-500 uppercase"
                >
                    <span>Active Projects</span>
                    <span class="text-lg">🚀</span>
                </div>
                <div class="flex items-baseline justify-between">
                    <div
                        class="text-2xl font-extrabold text-indigo-600 dark:text-indigo-400"
                    >
                        {{ metrics.active_projects }} Active
                    </div>
                </div>
                <div class="flex space-x-2 text-xs text-gray-400">
                    <span>Client: {{ metrics.client_projects }}</span>
                    <span>•</span>
                    <span>Internal: {{ metrics.internal_tasks }}</span>
                </div>
            </div>

            <!-- Card 3: Active Internships -->
            <div
                class="space-y-2 rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900"
            >
                <div
                    class="flex items-center justify-between text-xs font-semibold text-gray-500 uppercase"
                >
                    <span>Internship Batches</span>
                    <span class="text-lg">🎓</span>
                </div>
                <div class="flex items-baseline justify-between">
                    <div
                        class="text-2xl font-extrabold text-purple-600 dark:text-purple-400"
                    >
                        {{ metrics.active_batches }} Batches
                    </div>
                    <div
                        class="text-sm font-bold text-gray-700 dark:text-gray-300"
                    >
                        {{ metrics.total_students }} Total
                    </div>
                </div>
                <div class="text-xs text-gray-400">
                    Active Interns: {{ metrics.active_students }} | Graduated:
                    {{ metrics.completed_students }}
                </div>
            </div>

            <!-- Card 4: Pending Reports -->
            <div
                class="space-y-2 rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900"
            >
                <div
                    class="flex items-center justify-between text-xs font-semibold text-gray-500 uppercase"
                >
                    <span>Pending Weekly Reports</span>
                    <span class="text-lg">📝</span>
                </div>
                <div class="flex items-baseline justify-between">
                    <div
                        class="text-2xl font-extrabold text-amber-600 dark:text-amber-400"
                    >
                        {{ metrics.pending_reports }} Pending
                    </div>
                </div>
                <div class="text-xs text-gray-400">Requires Admin Review</div>
            </div>
        </div>

        <!-- Dashboard Main Sections (Alerts & Quick Action Streams) -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Section 1: Overdue & Upcoming Deadlines -->
            <div
                class="space-y-4 rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900"
            >
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                        ⏰ Deadline Alerts
                    </h2>
                    <Link
                        :href="projectsIndex.url()"
                        class="text-xs font-semibold text-indigo-600 hover:underline"
                        >View All Projects →</Link
                    >
                </div>

                <!-- Overdue List -->
                <div v-if="overdueProjects.length > 0" class="space-y-2">
                    <div class="text-xs font-bold text-rose-600 uppercase">
                        Overdue Projects ({{ overdueProjects.length }})
                    </div>
                    <div
                        v-for="proj in overdueProjects"
                        :key="proj.id"
                        class="flex items-center justify-between rounded-lg border border-rose-200 bg-rose-50 p-3 text-xs dark:border-rose-900 dark:bg-rose-950/40"
                    >
                        <div>
                            <div
                                class="font-bold text-rose-900 dark:text-rose-200"
                            >
                                {{ proj.title }}
                            </div>
                            <div class="text-rose-700 dark:text-rose-400">
                                {{
                                    proj.client
                                        ? proj.client.name
                                        : 'Internal Task'
                                }}
                            </div>
                        </div>
                        <div class="font-bold text-rose-600">
                            Due: {{ proj.deadline }}
                        </div>
                    </div>
                </div>

                <!-- Upcoming List -->
                <div v-if="upcomingDeadlines.length > 0" class="space-y-2">
                    <div class="text-xs font-bold text-amber-600 uppercase">
                        Due in Next 7 Days ({{ upcomingDeadlines.length }})
                    </div>
                    <div
                        v-for="proj in upcomingDeadlines"
                        :key="proj.id"
                        class="flex items-center justify-between rounded-lg border border-amber-200 bg-amber-50 p-3 text-xs dark:border-amber-900 dark:bg-amber-950/40"
                    >
                        <div>
                            <div
                                class="font-bold text-amber-900 dark:text-amber-200"
                            >
                                {{ proj.title }}
                            </div>
                            <div class="text-amber-700 dark:text-amber-400">
                                {{
                                    proj.client
                                        ? proj.client.name
                                        : 'Internal Task'
                                }}
                            </div>
                        </div>
                        <div class="font-bold text-amber-600">
                            Due: {{ proj.deadline }}
                        </div>
                    </div>
                </div>

                <p
                    v-if="
                        overdueProjects.length === 0 &&
                        upcomingDeadlines.length === 0
                    "
                    class="py-4 text-center text-sm text-gray-500"
                >
                    🎉 No overdue or urgent project deadlines!
                </p>
            </div>

            <!-- Section 2: Actionable Student Reports & Uncontacted Leads -->
            <div
                class="space-y-4 rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900"
            >
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                        🔔 Action Required
                    </h2>
                    <Link
                        :href="studentsIndex.url()"
                        class="text-xs font-semibold text-indigo-600 hover:underline"
                        >View All Students →</Link
                    >
                </div>

                <!-- Pending Reports List -->
                <div v-if="pendingReports.length > 0" class="space-y-2">
                    <div class="text-xs font-bold text-indigo-600 uppercase">
                        Pending Weekly Reports for Review
                    </div>
                    <div
                        v-for="report in pendingReports"
                        :key="report.id"
                        class="flex items-center justify-between rounded-lg border border-indigo-200 bg-indigo-50 p-3 text-xs dark:border-indigo-900 dark:bg-indigo-950/40"
                    >
                        <div>
                            <div
                                class="font-bold text-indigo-900 dark:text-indigo-200"
                            >
                                {{ report.student.name }}
                            </div>
                            <div class="text-indigo-700 dark:text-indigo-400">
                                Week {{ report.week_number }} Report
                            </div>
                        </div>
                        <Link
                            :href="studentsShow.url(report.student.id)"
                            class="rounded bg-indigo-600 px-2.5 py-1 text-xs font-medium text-white"
                            >Review</Link
                        >
                    </div>
                </div>

                <!-- Uncontacted Leads List -->
                <div v-if="uncontactedLeads.length > 0" class="space-y-2">
                    <div class="text-xs font-bold text-rose-600 uppercase">
                        Uncontacted Leads (>48 Hours)
                    </div>
                    <div
                        v-for="lead in uncontactedLeads"
                        :key="lead.id"
                        class="flex items-center justify-between rounded-lg border border-rose-200 bg-rose-50 p-3 text-xs dark:border-rose-900 dark:bg-rose-950/40"
                    >
                        <div>
                            <div
                                class="font-bold text-rose-900 dark:text-rose-200"
                            >
                                {{ lead.name }}
                            </div>
                            <div class="text-rose-700 dark:text-rose-400">
                                {{ lead.email }}
                            </div>
                        </div>
                        <Link
                            :href="clientsIndex.url()"
                            class="rounded bg-rose-600 px-2.5 py-1 text-xs font-medium text-white"
                            >Contact Lead</Link
                        >
                    </div>
                </div>

                <p
                    v-if="
                        pendingReports.length === 0 &&
                        uncontactedLeads.length === 0
                    "
                    class="py-4 text-center text-sm text-gray-500"
                >
                    ✨ All weekly reports reviewed and all leads contacted!
                </p>
            </div>
        </div>
    </div>
</template>
