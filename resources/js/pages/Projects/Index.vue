<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { index, create, show, edit, destroy } from '@/routes/projects';

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
        case 'planning':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 border-blue-200';
        case 'in_progress':
            return 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 border-amber-200';
        case 'under_review':
            return 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300 border-purple-200';
        case 'completed':
            return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 border-emerald-200';
        case 'on_hold':
            return 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300 border-gray-200';
        case 'cancelled':
            return 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300 border-rose-200';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300 border-gray-200';
    }
};

const formatStatus = (status: string) => {
    return status.replace('_', ' ').replace(/\b\w/g, (l) => l.toUpperCase());
};

const deleteProject = (id: number) => {
    if (confirm('Are you sure you want to delete this project?')) {
        router.delete(destroy.url(id));
    }
};
</script>

<template>
    <Head title="Projects Portal" />

    <div class="space-y-6 p-6">
        <!-- Header -->
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1
                    class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white"
                >
                    Projects Portal
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Manage client projects and company internal practice tasks.
                </p>
            </div>
            <div>
                <Link
                    :href="create.url()"
                    class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none"
                >
                    + Create Project / Task
                </Link>
            </div>
        </div>

        <!-- Table Card -->
        <div
            class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900"
        >
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr
                            class="border-b border-gray-200 bg-gray-50/50 text-xs font-semibold tracking-wider text-gray-500 uppercase dark:border-gray-800 dark:bg-gray-800/50 dark:text-gray-400"
                        >
                            <th class="px-4 py-3.5">Project Title</th>
                            <th class="px-4 py-3.5">Type / Client</th>
                            <th class="px-4 py-3.5">Service</th>
                            <th class="px-4 py-3.5">Progress</th>
                            <th class="px-4 py-3.5">Status</th>
                            <th class="px-4 py-3.5">Deadline</th>
                            <th class="px-4 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-gray-200 text-sm text-gray-700 dark:divide-gray-800 dark:text-gray-300"
                    >
                        <tr v-if="projects.data.length === 0">
                            <td
                                colspan="7"
                                class="py-8 text-center text-gray-500 dark:text-gray-400"
                            >
                                No projects found. Click "+ Create Project" to
                                add your first project or task.
                            </td>
                        </tr>
                        <tr
                            v-for="project in projects.data"
                            :key="project.id"
                            class="transition-colors hover:bg-gray-50/60 dark:hover:bg-gray-800/40"
                        >
                            <td
                                class="px-4 py-4 font-medium text-gray-900 dark:text-white"
                            >
                                <Link
                                    :href="show.url(project.id)"
                                    class="text-indigo-600 hover:underline dark:text-indigo-400"
                                >
                                    {{ project.title }}
                                </Link>
                            </td>
                            <td class="px-4 py-4">
                                <span
                                    v-if="project.client"
                                    class="inline-flex items-center text-xs font-medium text-gray-900 dark:text-gray-200"
                                >
                                    📁 {{ project.client.name }}
                                </span>
                                <span
                                    v-else
                                    class="inline-flex items-center rounded border border-indigo-200 bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700 dark:border-indigo-800 dark:bg-indigo-950/60 dark:text-indigo-300"
                                >
                                    ⚙️ Internal Practice Task
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <span
                                    class="text-xs font-medium text-gray-600 dark:text-gray-400"
                                >
                                    {{ project.service?.name || '-' }}
                                </span>
                            </td>
                            <td class="w-40 px-4 py-4">
                                <div class="space-y-1">
                                    <div
                                        class="flex justify-between text-xs font-medium"
                                    >
                                        <span>{{ project.progress }}%</span>
                                    </div>
                                    <div
                                        class="h-2 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700"
                                    >
                                        <div
                                            class="h-2 rounded-full bg-indigo-600 transition-all duration-300"
                                            :style="{
                                                width: `${project.progress}%`,
                                            }"
                                        ></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <span
                                    :class="[
                                        'rounded-full border px-2.5 py-1 text-xs font-semibold',
                                        getStatusBadge(project.status),
                                    ]"
                                >
                                    {{ formatStatus(project.status) }}
                                </span>
                            </td>
                            <td
                                class="px-4 py-4 text-xs text-gray-500 dark:text-gray-400"
                            >
                                {{ project.deadline || 'No deadline' }}
                            </td>
                            <td class="space-x-2 px-4 py-4 text-right">
                                <Link
                                    :href="show.url(project.id)"
                                    class="font-medium text-gray-600 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400"
                                    >View</Link
                                >
                                <Link
                                    :href="edit.url(project.id)"
                                    class="font-medium text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                    >Edit</Link
                                >
                                <button
                                    @click="deleteProject(project.id)"
                                    class="font-medium text-rose-600 hover:text-rose-900 dark:text-rose-400 dark:hover:text-rose-300"
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
</template>
