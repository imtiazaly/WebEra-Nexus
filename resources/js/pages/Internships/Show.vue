<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { index, edit } from '@/routes/internships';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Internships Portal', href: index.url() },
            { title: 'Batch Details', href: '#' },
        ],
    },
});

interface Service {
    id: number;
    name: string;
}

interface Student {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    status: string;
    overall_progress: number;
}

interface Internship {
    id: number;
    name: string;
    batch_no: string;
    start_date: string;
    end_date: string;
    status: string;
    service: Service | null;
    students?: Student[];
    created_at: string;
}

const props = defineProps<{
    internship: Internship;
}>();

const formatStatus = (status: string) => {
    return status.replace('_', ' ').replace(/\b\w/g, (l) => l.toUpperCase());
};
</script>

<template>
    <Head :title="internship.name" />

    <div class="mx-auto max-w-5xl space-y-6 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ internship.name }}
                </h1>
                <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400">
                    🏷️ Batch Code: {{ internship.batch_no }}
                </p>
            </div>
            <div class="space-x-3">
                <Link
                    :href="edit.url(internship.id)"
                    class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                    >Edit Batch</Link
                >
                <Link
                    :href="index.url()"
                    class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300"
                    >Back</Link
                >
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
            <div
                class="space-y-2 rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900"
            >
                <div class="text-xs font-semibold uppercase text-gray-500">
                    Track / Service
                </div>
                <div class="font-medium text-gray-900 dark:text-white">
                    {{ internship.service?.name || 'N/A' }}
                </div>
            </div>
            <div
                class="space-y-2 rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900"
            >
                <div class="text-xs font-semibold uppercase text-gray-500">
                    Status
                </div>
                <div class="font-semibold text-indigo-600 dark:text-indigo-400">
                    {{ formatStatus(internship.status) }}
                </div>
            </div>
            <div
                class="space-y-2 rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900"
            >
                <div class="text-xs font-semibold uppercase text-gray-500">
                    Start Date
                </div>
                <div class="font-medium text-gray-900 dark:text-white">
                    {{ internship.start_date }}
                </div>
            </div>
            <div
                class="space-y-2 rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900"
            >
                <div class="text-xs font-semibold uppercase text-gray-500">
                    End Date
                </div>
                <div class="font-medium text-gray-900 dark:text-white">
                    {{ internship.end_date }}
                </div>
            </div>
        </div>

        <div
            class="space-y-4 rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900"
        >
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                Enrolled Interns ({{ internship.students?.length || 0 }})
            </h2>
            <div
                v-if="internship.students && internship.students.length > 0"
                class="divide-y divide-gray-200 dark:divide-gray-800"
            >
                <div
                    v-for="student in internship.students"
                    :key="student.id"
                    class="flex items-center justify-between py-3"
                >
                    <div>
                        <div class="font-semibold text-gray-900 dark:text-white">
                            {{ student.name }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            {{ student.email }} • {{ student.phone || 'No phone' }}
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">
                            Progress: {{ student.overall_progress }}%
                        </span>
                        <span class="rounded bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                            {{ formatStatus(student.status) }}
                        </span>
                    </div>
                </div>
            </div>
            <p v-else class="text-sm text-gray-500">
                No students enrolled in this batch yet.
            </p>
        </div>
    </div>
</template>