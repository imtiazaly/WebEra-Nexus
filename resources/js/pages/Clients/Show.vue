<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { index, show, edit } from '@/routes/clients';

interface Service {
    id: number;
    name: string;
    pivot?: {
        requirements: string | null;
        estimated_budget: number | null;
    };
}

interface Project {
    id: number;
    title: string;
    status: string;
    progress: number;
}

interface Client {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    company_name: string | null;
    status: string;
    notes: string | null;
    services: Service[];
    projects?: Project[];
    created_at: string;
}

const props = defineProps<{
    client: Client;
}>();

const formatStatus = (status: string) => {
    return status.replace('_', ' ').replace(/\b\w/g, (l) => l.toUpperCase());
};
</script>

<template>
    <Head :title="client.name" />

    <AppLayout
        :breadcrumbs="[
            { title: 'Clients & Leads', href: index.url() },
            { title: client.name, href: show.url(client.id) },
        ]"
    >
        <div class="mx-auto max-w-5xl space-y-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1
                        class="text-2xl font-bold text-gray-900 dark:text-white"
                    >
                        {{ client.name }}
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ client.company_name || 'Individual Client' }}
                    </p>
                </div>
                <div class="space-x-3">
                    <Link
                        :href="edit.url(client.id)"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                        >Edit Details</Link
                    >
                    <Link
                        :href="index.url()"
                        class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300"
                        >Back</Link
                    >
                </div>
            </div>

            <!-- Overview Card -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                <div
                    class="space-y-2 rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900"
                >
                    <div class="text-xs font-semibold text-gray-500 uppercase">
                        Contact Email
                    </div>
                    <div class="font-medium text-gray-900 dark:text-white">
                        {{ client.email }}
                    </div>
                </div>
                <div
                    class="space-y-2 rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900"
                >
                    <div class="text-xs font-semibold text-gray-500 uppercase">
                        Phone / WhatsApp
                    </div>
                    <div class="font-medium text-gray-900 dark:text-white">
                        {{ client.phone || 'N/A' }}
                    </div>
                </div>
                <div
                    class="space-y-2 rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900"
                >
                    <div class="text-xs font-semibold text-gray-500 uppercase">
                        Current Status
                    </div>
                    <div
                        class="font-semibold text-indigo-600 dark:text-indigo-400"
                    >
                        {{ formatStatus(client.status) }}
                    </div>
                </div>
            </div>

            <!-- Requested Services -->
            <div
                class="space-y-4 rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900"
            >
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                    Services Requested
                </h2>
                <div
                    v-if="client.services && client.services.length > 0"
                    class="divide-y divide-gray-200 dark:divide-gray-800"
                >
                    <div
                        v-for="service in client.services"
                        :key="service.id"
                        class="flex flex-col justify-between gap-2 py-3 md:flex-row md:items-center"
                    >
                        <div>
                            <span
                                class="font-semibold text-gray-900 dark:text-white"
                                >{{ service.name }}</span
                            >
                            <p
                                class="mt-1 text-xs text-gray-500 dark:text-gray-400"
                            >
                                {{
                                    service.pivot?.requirements ||
                                    'No specific requirements noted.'
                                }}
                            </p>
                        </div>
                        <div
                            class="text-sm font-medium text-emerald-600 dark:text-emerald-400"
                        >
                            Budget:
                            {{
                                service.pivot?.estimated_budget
                                    ? `$${service.pivot.estimated_budget}`
                                    : 'TBD'
                            }}
                        </div>
                    </div>
                </div>
                <p v-else class="text-sm text-gray-500">
                    No services attached to this client yet.
                </p>
            </div>

            <!-- Notes -->
            <div
                v-if="client.notes"
                class="space-y-2 rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900"
            >
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                    Communication Notes
                </h2>
                <p
                    class="text-sm whitespace-pre-line text-gray-700 dark:text-gray-300"
                >
                    {{ client.notes }}
                </p>
            </div>
        </div>
    </AppLayout>
</template>
