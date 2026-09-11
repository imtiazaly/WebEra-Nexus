<script setup lang="ts">
import { Head, Link, useForm } from "@inertiajs/vue3";
import AppLayout from "@/layouts/AppLayout.vue";
import { index, edit, update } from "@/routes/internships";

interface Service {
    id: number;
    name: string;
}

interface Internship {
    id: number;
    service_id: number;
    name: string;
    batch_no: string;
    start_date: string;
    end_date: string;
    status: string;
}

const props = defineProps<{
    internship: Internship;
    services: Service[];
}>();

interface InternshipForm {
    service_id: number;
    name: string;
    batch_no: string;
    start_date: string;
    end_date: string;
    status: string;
}

const form = useForm<InternshipForm>({
    service_id: props.internship.service_id,
    name: props.internship.name,
    batch_no: props.internship.batch_no,
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

    <AppLayout
        :breadcrumbs="[
            { title: 'Internships Portal', href: index.url() },
            { title: `Edit ${internship.name}`, href: edit.url(internship.id) },
        ]"
    >
        <div class="max-w-3xl mx-auto p-6 space-y-6">
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
                class="bg-white dark:bg-gray-900 shadow-sm rounded-xl border border-gray-200 dark:border-gray-800 p-6 space-y-6"
            >
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >Internship Batch Title *</label
                        >
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white shadow-sm focus:border-indigo-500 sm:text-sm"
                        />
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >Batch Number (Free Text) *</label
                        >
                        <input
                            v-model="form.batch_no"
                            type="text"
                            required
                            class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white shadow-sm focus:border-indigo-500 sm:text-sm"
                        />
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >Service Track *</label
                        >
                        <select
                            v-model="form.service_id"
                            required
                            class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white shadow-sm focus:border-indigo-500 sm:text-sm"
                        >
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
                            class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white shadow-sm focus:border-indigo-500 sm:text-sm"
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
                            class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white shadow-sm focus:border-indigo-500 sm:text-sm"
                        />
                    </div>

                    <div class="md:col-span-2">
                        <label
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >Batch Status *</label
                        >
                        <select
                            v-model="form.status"
                            class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white shadow-sm focus:border-indigo-500 sm:text-sm"
                        >
                            <option value="upcoming">Upcoming</option>
                            <option value="active">Active (Ongoing)</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                </div>

                <div
                    class="flex justify-end space-x-3 border-t border-gray-200 dark:border-gray-800 pt-6"
                >
                    <Link
                        :href="index.url()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 rounded-lg hover:bg-gray-200"
                        >Cancel</Link
                    >
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        Update Batch
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
