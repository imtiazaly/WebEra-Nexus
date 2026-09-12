<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { h } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { index, show, edit } from '@/routes/internships';

defineOptions({
    layout: (page: any) =>
        h(
            AppLayout,
            {
                breadcrumbs: [
                    { title: 'Internships Portal', href: index.url() },
                    {
                        title: page.props.internship.name,
                        href: show.url(page.props.internship.id),
                    },
                ],
            },
            () => page,
        ),
});

interface Service {
    id: number;
    name: string;
}

interface Student {
    id: number;
    name: string;
    email: string;
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
                <div class="mt-1 flex items-center space-x-2">
                    <span
                        class="inline-flex items-center rounded border border-gray-300 bg-gray-100 px-2 py-0.5 text-xs font-bold text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                    >
                        🏷️ {{ internship.batch_no }}
                    </span>
                    <span class="text-sm text-gray-500 dark:text-gray-400">
                        Track:
                        <strong class="text-gray-700 dark:text-gray-200">{{
                            internship.service?.name
                        }}</strong>
                    </span>
                </div>
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

        <!-- Overview Grid -->
        <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
            <div
                class="space-y-2 rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900"
            >
                <div class="text-xs font-semibold text-gray-500 uppercase">
                    Start Date
                </div>
                <div class="font-medium text-gray-900 dark:text-white">
                    {{ internship.start_date }}
                </div>
            </div>

            <div
                class="space-y-2 rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900"
            >
                <div class="text-xs font-semibold text-gray-500 uppercase">
                    End Date (~6 Weeks)
                </div>
                <div class="font-medium text-gray-900 dark:text-white">
                    {{ internship.end_date }}
                </div>
            </div>

            <div
                class="space-y-2 rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900"
            >
                <div class="text-xs font-semibold text-gray-500 uppercase">
                    Status
                </div>
                <div class="font-semibold text-indigo-600 dark:text-indigo-400">
                    {{ formatStatus(internship.status) }}
                </div>
            </div>
        </div>

        <!-- Enrolled Students Section -->
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
                        <div class="font-medium text-gray-900 dark:text-white">
                            {{ student.name }}
                        </div>
                        <div class="text-xs text-gray-400">
                            {{ student.email }}
                        </div>
                    </div>
                    <div
                        class="text-sm font-semibold text-indigo-600 dark:text-indigo-400"
                    >
                        {{ student.overall_progress }}% Progress
                    </div>
                </div>
            </div>
            <p v-else class="text-sm text-gray-500 dark:text-gray-400">
                No interns enrolled in this batch yet. (You can enroll students
                in Module 4: Students Portal).
            </p>
        </div>
    </div>
</template>
