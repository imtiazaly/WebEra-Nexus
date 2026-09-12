<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { index, update } from '@/routes/projects';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Projects Portal', href: index.url() },
            { title: 'Edit Project', href: '#' },
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
    description: string | null;
    status: string;
    progress: number;
    start_date: string | null;
    deadline: string | null;
    client_id: number | null;
    service_id: number | null;
}

const props = defineProps<{
    project: Project;
    clients: Client[];
    services: Service[];
}>();

const form = useForm({
    title: props.project.title,
    client_id: props.project.client_id || '',
    service_id: props.project.service_id || '',
    description: props.project.description || '',
    status: props.project.status,
    start_date: props.project.start_date || '',
    deadline: props.project.deadline || '',
    completion_progress: props.project.progress || 0,
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        client_id: data.client_id || null,
        service_id: data.service_id || null,
        progress: data.completion_progress,
    })).put(update.url(props.project.id));
};
</script>

<template>
    <Head title="Edit Project" />

    <div class="mx-auto max-w-4xl space-y-6 p-6">
        <div class="flex items-center justify-between">
            <h1
                class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white"
            >
                Edit Project / Practice Task
            </h1>
            <Link
                :href="index.url()"
                class="text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
                >← Back to List</Link
            >
        </div>

        <form
            @submit.prevent="submit"
            class="space-y-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900"
        >
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >Project Title *</label
                    >
                    <input
                        v-model="form.title"
                        type="text"
                        required
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 sm:text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    />
                </div>

                <div>
                    <label
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >Attached Client (Optional)</label
                    >
                    <select
                        v-model="form.client_id"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 sm:text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    >
                        <option value="">
                            -- Internal Practice Project (No Client) --
                        </option>
                        <option
                            v-for="client in clients"
                            :key="client.id"
                            :value="client.id"
                        >
                            {{ client.name }}
                        </option>
                    </select>
                </div>

                <div>
                    <label
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >Service Category</label
                    >
                    <select
                        v-model="form.service_id"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 sm:text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    >
                        <option value="">-- Select Service --</option>
                        <option
                            v-for="service in services"
                            :key="service.id"
                            :value="service.id"
                        >
                            {{ service.name }}
                        </option>
                    </select>
                </div>

                <div>
                    <label
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >Status *</label
                    >
                    <select
                        v-model="form.status"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 sm:text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    >
                        <option value="planning">Planning</option>
                        <option value="in_progress">In Progress</option>
                        <option value="on_hold">On Hold</option>
                        <option value="under_review">Under Review</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>

                <div>
                    <label
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >Completion Progress (%)</label
                    >
                    <input
                        v-model="form.completion_progress"
                        type="number"
                        min="0"
                        max="100"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 sm:text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    />
                </div>

                <div>
                    <label
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >Start Date</label
                    >
                    <input
                        v-model="form.start_date"
                        type="date"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 sm:text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    />
                </div>

                <div>
                    <label
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >Deadline</label
                    >
                    <input
                        v-model="form.deadline"
                        type="date"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 sm:text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    />
                </div>

                <div class="md:col-span-2">
                    <label
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >Description / Requirements Scope</label
                    >
                    <textarea
                        v-model="form.description"
                        rows="3"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 sm:text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    ></textarea>
                </div>
            </div>

            <div
                class="flex justify-end space-x-3 border-t border-gray-200 pt-6 dark:border-gray-800"
            >
                <Link
                    :href="index.url()"
                    class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300"
                    >Cancel</Link
                >
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                >
                    Update Project
                </button>
            </div>
        </form>
    </div>
</template>