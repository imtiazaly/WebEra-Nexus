<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { h } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { index, edit, update } from '@/routes/students';

defineOptions({
    layout: (page: any) =>
        h(
            AppLayout,
            {
                breadcrumbs: [
                    { title: 'Students Portal', href: index.url() },
                    {
                        title: `Edit ${page.props.student.name}`,
                        href: edit.url(page.props.student.id),
                    },
                ],
            },
            () => page,
        ),
});

interface Internship {
    id: number;
    name: string;
    batch_no: string;
}

interface Student {
    id: number;
    internship_id: number;
    name: string;
    email: string;
    phone: string | null;
    status: string;
    overall_progress: number;
}

const props = defineProps<{
    student: Student;
    internships: Internship[];
}>();

interface StudentForm {
    internship_id: number;
    name: string;
    email: string;
    phone: string;
    status: string;
    completion_progress: number;
}

const form = useForm<StudentForm>({
    internship_id: props.student.internship_id,
    name: props.student.name,
    email: props.student.email,
    phone: props.student.phone || '',
    status: props.student.status,
    completion_progress: props.student.overall_progress,
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        overall_progress: data.completion_progress,
    })).put(update.url(props.student.id));
};
</script>

<template>
    <Head title="Edit Student" />

    <div class="mx-auto max-w-3xl space-y-6 p-6">
        <div class="flex items-center justify-between">
            <h1
                class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white"
            >
                Edit Student Record
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
                <div>
                    <label
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >Full Name *</label
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
                        >Email Address *</label
                    >
                    <input
                        v-model="form.email"
                        type="email"
                        required
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 sm:text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    />
                </div>

                <div>
                    <label
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >Phone / WhatsApp</label
                    >
                    <input
                        v-model="form.phone"
                        type="text"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 sm:text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    />
                </div>

                <div>
                    <label
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >Internship Batch *</label
                    >
                    <select
                        v-model="form.internship_id"
                        required
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 sm:text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    >
                        <option
                            v-for="batch in internships"
                            :key="batch.id"
                            :value="batch.id"
                        >
                            {{ batch.name }} ({{ batch.batch_no }})
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
                        <option value="enrolled">Enrolled</option>
                        <option value="active">Active</option>
                        <option value="completed">Completed</option>
                        <option value="dropped_out">Dropped Out</option>
                    </select>
                </div>

                <div>
                    <label
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >Overall Progress (%)</label
                    >
                    <div class="mt-2 flex items-center space-x-4">
                        <input
                            v-model="form.completion_progress"
                            type="range"
                            min="0"
                            max="100"
                            class="w-full accent-indigo-600"
                        />
                        <span
                            class="w-12 text-right font-bold text-indigo-600 dark:text-indigo-400"
                            >{{ form.completion_progress }}%</span
                        >
                    </div>
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
                    Update Student
                </button>
            </div>
        </form>
    </div>
</template>
