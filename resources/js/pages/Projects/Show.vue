<script setup lang="ts">
import { Head, Link } from "@inertiajs/vue3";
import AppLayout from "@/layouts/AppLayout.vue";
import { index, show, edit } from "@/routes/projects";

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
    return status.replace("_", " ").replace(/\b\w/g, (l) => l.toUpperCase());
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
        <div class="max-w-5xl mx-auto p-6 space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1
                        class="text-2xl font-bold text-gray-900 dark:text-white"
                    >
                        {{ project.title }}
                    </h1>
                    <div class="flex items-center space-x-2 mt-1">
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
                            class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-300"
                        >
                            ⚙️ Internal Practice Task
                        </span>
                    </div>
                </div>
                <div class="space-x-3">
                    <Link
                        :href="edit.url(project.id)"
                        class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700"
                        >Edit Project</Link
                    >
                    <Link
                        :href="index.url()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 rounded-lg hover:bg-gray-200"
                        >Back</Link
                    >
                </div>
            </div>

            <!-- Overview Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div
                    class="bg-white dark:bg-gray-900 p-6 rounded-xl border border-gray-200 dark:border-gray-800 space-y-2"
                >
                    <div class="text-xs text-gray-500 uppercase font-semibold">
                        Service Category
                    </div>
                    <div class="font-medium text-gray-900 dark:text-white">
                        {{ project.service?.name || "N/A" }}
                    </div>
                </div>

                <div
                    class="bg-white dark:bg-gray-900 p-6 rounded-xl border border-gray-200 dark:border-gray-800 space-y-2"
                >
                    <div class="text-xs text-gray-500 uppercase font-semibold">
                        Status
                    </div>
                    <div
                        class="font-semibold text-indigo-600 dark:text-indigo-400"
                    >
                        {{ formatStatus(project.status) }}
                    </div>
                </div>

                <div
                    class="bg-white dark:bg-gray-900 p-6 rounded-xl border border-gray-200 dark:border-gray-800 space-y-2"
                >
                    <div class="text-xs text-gray-500 uppercase font-semibold">
                        Start Date
                    </div>
                    <div class="font-medium text-gray-900 dark:text-white">
                        {{ project.start_date || "Not set" }}
                    </div>
                </div>

                <div
                    class="bg-white dark:bg-gray-900 p-6 rounded-xl border border-gray-200 dark:border-gray-800 space-y-2"
                >
                    <div class="text-xs text-gray-500 uppercase font-semibold">
                        Deadline
                    </div>
                    <div class="font-medium text-gray-900 dark:text-white">
                        {{ project.deadline || "Not set" }}
                    </div>
                </div>
            </div>

            <!-- Progress Bar Card -->
            <div
                class="bg-white dark:bg-gray-900 p-6 rounded-xl border border-gray-200 dark:border-gray-800 space-y-3"
            >
                <div class="flex justify-between items-center">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                        Overall Completion Progress
                    </h2>
                    <span
                        class="text-xl font-extrabold text-indigo-600 dark:text-indigo-400"
                        >{{ project.progress }}%</span
                    >
                </div>
                <div
                    class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3 overflow-hidden"
                >
                    <div
                        class="bg-indigo-600 h-3 rounded-full transition-all duration-500"
                        :style="{ width: `${project.progress}%` }"
                    ></div>
                </div>
            </div>

            <!-- Description -->
            <div
                v-if="project.description"
                class="bg-white dark:bg-gray-900 p-6 rounded-xl border border-gray-200 dark:border-gray-800 space-y-2"
            >
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                    Description / Scope
                </h2>
                <p
                    class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line"
                >
                    {{ project.description }}
                </p>
            </div>
        </div>
    </AppLayout>
</template>
