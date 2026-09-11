<script setup lang="ts">
import { Head, Link, router } from "@inertiajs/vue3";
import AppLayout from "@/layouts/AppLayout.vue";
import { index, create, show, edit, destroy } from "@/routes/projects";

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
    status: string;
    progress: number;
    start_date: string | null;
    deadline: string | null;
    client: Client | null;
    service: Service | null;
    created_at: string;
}

interface PaginatedProjects {
    data: Project[];
    links: any[];
    current_page: number;
    last_page: number;
}

const props = defineProps<{
    projects: PaginatedProjects;
}>();

const getStatusBadge = (status: string) => {
    switch (status) {
        case "planning":
            return "bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 border-blue-200";
        case "in_progress":
            return "bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 border-amber-200";
        case "under_review":
            return "bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300 border-purple-200";
        case "completed":
            return "bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 border-emerald-200";
        case "on_hold":
            return "bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300 border-gray-200";
        case "cancelled":
            return "bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300 border-rose-200";
        default:
            return "bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300 border-gray-200";
    }
};

const formatStatus = (status: string) => {
    return status.replace("_", " ").replace(/\b\w/g, (l) => l.toUpperCase());
};

const deleteProject = (id: number) => {
    if (confirm("Are you sure you want to delete this project?")) {
        router.delete(destroy.url(id));
    }
};
</script>

<template>
    <Head title="Projects Portal" />

    <AppLayout :breadcrumbs="[{ title: 'Projects Portal', href: index.url() }]">
        <div class="p-6 space-y-6">
            <!-- Header -->
            <div
                class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
            >
                <div>
                    <h1
                        class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white"
                    >
                        Projects Portal
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Manage client projects and company internal practice
                        tasks.
                    </p>
                </div>
                <div>
                    <Link
                        :href="create.url()"
                        class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 shadow-sm transition-colors"
                    >
                        + Create Project / Task
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
                                <th class="py-3.5 px-4">Project Title</th>
                                <th class="py-3.5 px-4">Type / Client</th>
                                <th class="py-3.5 px-4">Service</th>
                                <th class="py-3.5 px-4">Progress</th>
                                <th class="py-3.5 px-4">Status</th>
                                <th class="py-3.5 px-4">Deadline</th>
                                <th class="py-3.5 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-gray-200 dark:divide-gray-800 text-sm text-gray-700 dark:text-gray-300"
                        >
                            <tr v-if="projects.data.length === 0">
                                <td
                                    colspan="7"
                                    class="py-8 text-center text-gray-500 dark:text-gray-400"
                                >
                                    No projects found. Click "+ Create Project"
                                    to add your first project or task.
                                </td>
                            </tr>
                            <tr
                                v-for="project in projects.data"
                                :key="project.id"
                                class="hover:bg-gray-50/60 dark:hover:bg-gray-800/40 transition-colors"
                            >
                                <td
                                    class="py-4 px-4 font-medium text-gray-900 dark:text-white"
                                >
                                    <Link
                                        :href="show.url(project.id)"
                                        class="hover:underline text-indigo-600 dark:text-indigo-400"
                                    >
                                        {{ project.title }}
                                    </Link>
                                </td>
                                <td class="py-4 px-4">
                                    <span
                                        v-if="project.client"
                                        class="inline-flex items-center text-xs font-medium text-gray-900 dark:text-gray-200"
                                    >
                                        📁 {{ project.client.name }}
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800"
                                    >
                                        ⚙️ Internal Practice Task
                                    </span>
                                </td>
                                <td class="py-4 px-4">
                                    <span
                                        class="text-xs text-gray-600 dark:text-gray-400 font-medium"
                                    >
                                        {{ project.service?.name || "-" }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 w-40">
                                    <div class="space-y-1">
                                        <div
                                            class="flex justify-between text-xs font-medium"
                                        >
                                            <span>{{ project.progress }}%</span>
                                        </div>
                                        <div
                                            class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden"
                                        >
                                            <div
                                                class="bg-indigo-600 h-2 rounded-full transition-all duration-300"
                                                :style="{
                                                    width: `${project.progress}%`,
                                                }"
                                            ></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-4">
                                    <span
                                        :class="[
                                            'px-2.5 py-1 text-xs font-semibold rounded-full border',
                                            getStatusBadge(project.status),
                                        ]"
                                    >
                                        {{ formatStatus(project.status) }}
                                    </span>
                                </td>
                                <td
                                    class="py-4 px-4 text-xs text-gray-500 dark:text-gray-400"
                                >
                                    {{ project.deadline || "No deadline" }}
                                </td>
                                <td class="py-4 px-4 text-right space-x-2">
                                    <Link
                                        :href="show.url(project.id)"
                                        class="text-gray-600 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 font-medium"
                                        >View</Link
                                    >
                                    <Link
                                        :href="edit.url(project.id)"
                                        class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium"
                                        >Edit</Link
                                    >
                                    <button
                                        @click="deleteProject(project.id)"
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
