<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { index, show, edit } from '@/routes/projects';

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
    description: string | null;
    status: string;
    progress: number;
    start_date: string | null;
    deadline: string | null;
    client: Client | null;
    service: Service | null;
    created_at: string;
}

const props = defineProps<{
    project: Project;
}>();

const formatStatus = (status: string) => {
    return status.replace('_', ' ').replace(/\b\w/g, (l) => l.toUpperCase());
};
</script>

<template>
    <Head :title="project.title" />

    <AppLayout
        :breadcrumbs="[
            { title: 'Projects Portal', href: index.url() },
            { title: project.title, href: show.url(project.id) },
        ]"
    >
        <div class="mx-auto max-w-5xl space-y-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1
                        class="text-2xl font-bold text-gray-900 dark:text-white"
                    >
                        {{ project.title }}
                    </h1>
                    <div class="mt-1 flex items-center space-x-2">
                        <span
                            v-if="project.client"
                            class="text-sm text-gray-500 dark:text-gray-400"
                        >
                            Client:
                            <strong class="text-gray-700 dark:text-gray-200">{{
                                project.client.name
                            }}</strong>
                        </span>
                        <span
                            v-else
                            class="inline-flex items-center rounded bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300"
                        >
                            ⚙️ Internal Practice Task
                        </span>
                    </div>
                </div>
                <div class="space-x-3">
                    <Link
                        :href="edit.url(project.id)"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                        >Edit Project</Link
                    >
                    <Link
                        :href="index.url()"
                        class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300"
                        >Back</Link
                    >
                </div>
            </div>

            <!-- Overview Grid -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
                <div
                    class="space-y-2 rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900"
                >
                    <div class="text-xs font-semibold text-gray-500 uppercase">
                        Service Category
                    </div>
                    <div class="font-medium text-gray-900 dark:text-white">
                        {{ project.service?.name || 'N/A' }}
                    </div>
                </div>

                <div
                    class="space-y-2 rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900"
                >
                    <div class="text-xs font-semibold text-gray-500 uppercase">
                        Status
                    </div>
                    <div
                        class="font-semibold text-indigo-600 dark:text-indigo-400"
                    >
                        {{ formatStatus(project.status) }}
                    </div>
                </div>

                <div
                    class="space-y-2 rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900"
                >
                    <div class="text-xs font-semibold text-gray-500 uppercase">
                        Start Date
                    </div>
                    <div class="font-medium text-gray-900 dark:text-white">
                        {{ project.start_date || 'Not set' }}
                    </div>
                </div>

                <div
                    class="space-y-2 rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900"
                >
                    <div class="text-xs font-semibold text-gray-500 uppercase">
                        Deadline
                    </div>
                    <div class="font-medium text-gray-900 dark:text-white">
                        {{ project.deadline || 'Not set' }}
                    </div>
                </div>
            </div>

            <!-- Progress Bar Card -->
            <div
                class="space-y-3 rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900"
            >
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                        Overall Completion Progress
                    </h2>
                    <span
                        class="text-xl font-extrabold text-indigo-600 dark:text-indigo-400"
                        >{{ project.progress }}%</span
                    >
                </div>
                <div
                    class="h-3 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700"
                >
                    <div
                        class="h-3 rounded-full bg-indigo-600 transition-all duration-500"
                        :style="{ width: `${project.progress}%` }"
                    ></div>
                </div>
            </div>

            <!-- Description -->
            <div
                v-if="project.description"
                class="space-y-2 rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900"
            >
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                    Description / Scope
                </h2>
                <p
                    class="text-sm whitespace-pre-line text-gray-700 dark:text-gray-300"
                >
                    {{ project.description }}
                </p>
            </div>
        </div>
    </AppLayout>
</template>
