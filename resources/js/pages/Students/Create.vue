<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { index, create, store } from '@/routes/students';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Students Portal', href: index.url() },
            { title: 'Add Student', href: create.url() },
        ],
    },
});

interface Internship {
    id: number;
    name: string;
    batch_no: string;
}

const props = defineProps<{
    internships: Internship[];
}>();

interface StudentForm {
    internship_id: number | string;
    name: string;
    email: string;
    phone: string;
    status: string;
    completion_progress: number;
}

const form = useForm<StudentForm>({
    internship_id: '',
    name: '',
    email: '',
    phone: '',
    status: 'enrolled',
    completion_progress: 0,
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        overall_progress: data.completion_progress,
    })).post(store.url());
};
</script>

<template>
    <Head title="Add New Student" />

    <div class="mx-auto max-w-3xl space-y-6 p-6">
        <div class="flex items-center justify-between">
            <h1
                class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white"
            >
                Add New Student / Intern
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
                        placeholder="e.g. Ali Raza"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 sm:text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    />
                    <span
                        v-if="form.errors.name"
                        class="mt-1 block text-xs text-rose-500"
                        >{{ form.errors.name }}</span
                    >
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
                        placeholder="ali@example.com"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 sm:text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    />
                    <span
                        v-if="form.errors.email"
                        class="mt-1 block text-xs text-rose-500"
                        >{{ form.errors.email }}</span
                    >
                </div>

                <div>
                    <label
                        class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                        >Phone / WhatsApp</label
                    >
                    <input
                        v-model="form.phone"
                        type="text"
                        placeholder="+92 300 1234567"
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
                        <option value="" disabled>
                            -- Select Internship Batch --
                        </option>
                        <option
                            v-for="batch in internships"
                            :key="batch.id"
                            :value="batch.id"
                        >
                            {{ batch.name }} ({{ batch.batch_no }})
                        </option>
                    </select>
                    <span
                        v-if="form.errors.internship_id"
                        class="mt-1 block text-xs text-rose-500"
                        >{{ form.errors.internship_id }}</span
                    >
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
                    Save Student
                </button>
            </div>
        </form>
    </div>
</template>
