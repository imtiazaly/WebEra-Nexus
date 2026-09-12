<script setup lang="ts">
import { Head, Link, router } from "@inertiajs/vue3";
import AppLayout from "@/layouts/AppLayout.vue";
import { index, create, show, edit, destroy } from "@/routes/students";

interface Internship {
    id: number;
    name: string;
    batch_no: string;
}

interface Student {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    status: string;
    overall_progress: number;
    internship: Internship | null;
    weekly_reports_count?: number;
    created_at: string;
}

interface PaginatedStudents {
    data: Student[];
    links: any[];
    current_page: number;
    last_page: number;
}

const props = defineProps<{
    students: PaginatedStudents;
}>();

const getStatusBadge = (status: string) => {
    switch (status) {
        case "enrolled":
            return "bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 border-blue-200";
        case "active":
            return "bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 border-emerald-200";
        case "completed":
            return "bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300 border-purple-200";
        case "dropped_out":
            return "bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300 border-rose-200";
        default:
            return "bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300 border-gray-200";
    }
};

const formatStatus = (status: string) => {
    return status.replace("_", " ").replace(/\b\w/g, (l) => l.toUpperCase());
};

const deleteStudent = (id: number) => {
    if (confirm("Are you sure you want to delete this student record?")) {
        router.delete(destroy.url(id));
    }
};
</script>

<template>
    <Head title="Students Portal" />

    <AppLayout :breadcrumbs="[{ title: 'Students Portal', href: index.url() }]">
        <div class="p-6 space-y-6">
            <!-- Header -->
            <div
                class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
            >
                <div>
                    <h1
                        class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white"
                    >
                        Students Portal
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Manage enrolled interns, project assignments, and weekly
                        progress reports.
                    </p>
                </div>
                <div>
                    <Link
                        :href="create.url()"
                        class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 shadow-sm transition-colors"
                    >
                        + Add New Student / Intern
                    </Link>
                </div>
            </div>

            <!-- Table Card -->
            <div
                class="bg-white dark:bg-gray-900 shadow-sm rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden"
            >
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr
                                class="border-b border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/50 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400"
                            >
                                <th class="py-3.5 px-4">Student Name</th>
                                <th class="py-3.5 px-4">Contact</th>
                                <th class="py-3.5 px-4">Internship Batch</th>
                                <th class="py-3.5 px-4">Overall Progress</th>
                                <th class="py-3.5 px-4">Status</th>
                                <th class="py-3.5 px-4">Weekly Reports</th>
                                <th class="py-3.5 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-gray-200 dark:divide-gray-800 text-sm text-gray-700 dark:text-gray-300"
                        >
                            <tr v-if="students.data.length === 0">
                                <td
                                    colspan="7"
                                    class="py-8 text-center text-gray-500 dark:text-gray-400"
                                >
                                    No student records found. Click "+ Add New
                                    Student" to get started.
                                </td>
                            </tr>
                            <tr
                                v-for="student in students.data"
                                :key="student.id"
                                class="hover:bg-gray-50/60 dark:hover:bg-gray-800/40 transition-colors"
                            >
                                <td
                                    class="py-4 px-4 font-medium text-gray-900 dark:text-white"
                                >
                                    <Link
                                        :href="show.url(student.id)"
                                        class="hover:underline text-indigo-600 dark:text-indigo-400"
                                    >
                                        {{ student.name }}
                                    </Link>
                                </td>
                                <td class="py-4 px-4">
                                    <div>{{ student.email }}</div>
                                    <div class="text-xs text-gray-400">
                                        {{ student.phone || "-" }}
                                    </div>
                                </td>
                                <td class="py-4 px-4">
                                    <div
                                        v-if="student.internship"
                                        class="space-y-0.5"
                                    >
                                        <div
                                            class="font-medium text-xs text-gray-900 dark:text-white"
                                        >
                                            {{ student.internship.name }}
                                        </div>
                                        <span
                                            class="inline-block text-[10px] font-bold px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400"
                                        >
                                            🏷️ {{ student.internship.batch_no }}
                                        </span>
                                    </div>
                                    <span v-else class="text-gray-400 text-xs"
                                        >-</span
                                    >
                                </td>
                                <td class="py-4 px-4 w-36">
                                    <div class="space-y-1">
                                        <div
                                            class="flex justify-between text-xs font-medium"
                                        >
                                            <span
                                                >{{
                                                    student.overall_progress
                                                }}%</span
                                            >
                                        </div>
                                        <div
                                            class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden"
                                        >
                                            <div
                                                class="bg-indigo-600 h-2 rounded-full transition-all duration-300"
                                                :style="{
                                                    width: `${student.overall_progress}%`,
                                                }"
                                            ></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-4">
                                    <span
                                        :class="[
                                            'px-2.5 py-1 text-xs font-semibold rounded-full border',
                                            getStatusBadge(student.status),
                                        ]"
                                    >
                                        {{ formatStatus(student.status) }}
                                    </span>
                                </td>
                                <td
                                    class="py-4 px-4 text-xs font-semibold text-gray-700 dark:text-gray-300"
                                >
                                    📝
                                    {{
                                        student.weekly_reports_count || 0
                                    }}
                                    Reports
                                </td>
                                <td class="py-4 px-4 text-right space-x-2">
                                    <Link
                                        :href="show.url(student.id)"
                                        class="text-gray-600 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 font-medium"
                                        >View</Link
                                    >
                                    <Link
                                        :href="edit.url(student.id)"
                                        class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium"
                                        >Edit</Link
                                    >
                                    <button
                                        @click="deleteStudent(student.id)"
                                        class="text-rose-600 hover:text-rose-900 dark:text-rose-400 dark:hover:text-rose-300 font-medium"
                                    >
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
