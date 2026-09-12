<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { index, update } from '@/routes/internships';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Internships Portal', href: index.url() },
            { title: 'Edit Batch', href: '#' },
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
}

const props = defineProps<{
    internship: Internship;
    services: Service[];
}>();

const form = useForm({
    name: props.internship.name,
    batch_no: props.internship.batch_no,
    service_id: props.internship.service_id,
    start_date: props.internship.start_date,
    end_date: props.internship.end_date,
    status: props.internship.status,
});

const submit = () => {
    form.put(update.url(props.internship.id));
};
</script>

<template>
    <Head title="Edit Internship Batch" />

    <div class="mx-auto max-w-3xl space-y-6 p-6">
        <div class="flex items-center justify-between">
            <h1
                class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white"
            >
                Edit Internship Batch
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
                        >Program / Batch Name *</label
                    >
                    <input
                        v-model="form.name"
                        type="text"
                        required
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 sm:text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    />
                </div>

                <div>
                    <label
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >Batch Number (Free text code) *</label
                    >
                    <input
                        v-model="form.batch_no"
                        type="text"
                        required
                        placeholder="e.g. BATCH-01, WEB-2026-A"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 sm:text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    />
                </div>

                <div>
                    <label
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >Track / Service Category *</label
                    >
                    <select
                        v-model="form.service_id"
                        required
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 sm:text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    >
                        <option value="" disabled>-- Select Track --</option>
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
                        >Start Date *</label
                    >
                    <input
                        v-model="form.start_date"
                        type="date"
                        required
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 sm:text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    />
                </div>

                <div>
                    <label
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >End Date *</label
                    >
                    <input
                        v-model="form.end_date"
                        type="date"
                        required
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 sm:text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    />
                </div>

                <div class="md:col-span-2">
                    <label
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >Batch Status *</label
                    >
                    <select
                        v-model="form.status"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 sm:text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    >
                        <option value="upcoming">Upcoming</option>
                        <option value="active">Active</option>
                        <option value="completed">Completed</option>
                    </select>
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
                    Update Batch
                </button>
            </div>
        </form>
    </div>
</template>