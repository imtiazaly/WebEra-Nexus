<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { index, create, show, edit, destroy } from '@/routes/internships';

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Internships Portal', href: index.url() }],
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
    service: Service | null;
    total_students_count?: number;
    created_at: string;
}

interface PaginatedInternships {
    data: Internship[];
    links: any[];
    current_page: number;
    last_page: number;
}

const props = defineProps<{
    internships: PaginatedInternships;
}>();

const getStatusBadge = (status: string) => {
    switch (status) {
        case 'upcoming':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 border-blue-200';
        case 'active':
            return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 border-emerald-200';
        case 'completed':
            return 'bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300 border-purple-200';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300 border-gray-200';
    }
};

const formatStatus = (status: string) => {
    return status.replace('_', ' ').replace(/\b\w/g, (l) => l.toUpperCase());
};

const deleteInternship = (id: number) => {
    if (confirm('Are you sure you want to delete this internship batch?')) {
        router.delete(destroy.url(id));
    }
};
</script>

<template>
    <Head title="Internships Portal" />

    <div class="space-y-6 p-6">
        <!-- Header -->
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1
                    class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white"
                >
                    Internships Portal
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Manage WebEra internship batches and student training
                    tracks.
                </p>
            </div>
            <div>
                <Link
                    :href="create.url()"
                    class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none"
                >
                    + Create Internship Batch
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
                            <th class="px-4 py-3.5">Batch Name</th>
                            <th class="px-4 py-3.5">Batch No</th>
                            <th class="px-4 py-3.5">Track / Service</th>
                            <th class="px-4 py-3.5">Duration</th>
                            <th class="px-4 py-3.5">Enrolled Interns</th>
                            <th class="px-4 py-3.5">Status</th>
                            <th class="px-4 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-gray-200 text-sm text-gray-700 dark:divide-gray-800 dark:text-gray-300"
                    >
                        <tr v-if="internships.data.length === 0">
                            <td
                                colspan="7"
                                class="py-8 text-center text-gray-500 dark:text-gray-400"
                            >
                                No internship batches found. Click "+ Create
                                Internship Batch" to get started.
                            </td>
                        </tr>
                        <tr
                            v-for="batch in internships.data"
                            :key="batch.id"
                            class="transition-colors hover:bg-gray-50/60 dark:hover:bg-gray-800/40"
                        >
                            <td
                                class="px-4 py-4 font-medium text-gray-900 dark:text-white"
                            >
                                <Link
                                    :href="show.url(batch.id)"
                                    class="text-indigo-600 hover:underline dark:text-indigo-400"
                                >
                                    {{ batch.name }}
                                </Link>
                            </td>
                            <td class="px-4 py-4">
                                <span
                                    class="inline-flex items-center rounded border border-gray-300 bg-gray-100 px-2 py-0.5 text-xs font-bold text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"
                                >
                                    🏷️ {{ batch.batch_no }}
                                </span>
                            </td>
                            <td
                                class="px-4 py-4 text-xs font-medium text-gray-600 dark:text-gray-300"
                            >
                                {{ batch.service?.name || '-' }}
                            </td>
                            <td
                                class="px-4 py-4 text-xs text-gray-500 dark:text-gray-400"
                            >
                                {{ batch.start_date }} to
                                {{ batch.end_date }}
                            </td>
                            <td
                                class="px-4 py-4 text-sm font-semibold text-gray-900 dark:text-white"
                            >
                                🎓
                                {{ batch.total_students_count || 0 }}
                                Interns
                            </td>
                            <td class="px-4 py-4">
                                <span
                                    :class="[
                                        'rounded-full border px-2.5 py-1 text-xs font-semibold',
                                        getStatusBadge(batch.status),
                                    ]"
                                >
                                    {{ formatStatus(batch.status) }}
                                </span>
                            </td>
                            <td class="space-x-2 px-4 py-4 text-right">
                                <Link
                                    :href="show.url(batch.id)"
                                    class="font-medium text-gray-600 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400"
                                    >View</Link
                                >
                                <Link
                                    :href="edit.url(batch.id)"
                                    class="font-medium text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                    >Edit</Link
                                >
                                <button
                                    @click="deleteInternship(batch.id)"
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
