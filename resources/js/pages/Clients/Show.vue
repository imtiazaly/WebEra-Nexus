<script setup lang="ts">
import { Head, Link } from "@inertiajs/vue3";
import AppLayout from "@/layouts/AppLayout.vue";
import { index, show, edit } from "@/routes/clients";

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
    return status.replace("_", " ").replace(/\b\w/g, (l) => l.toUpperCase());
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
        <div class="max-w-5xl mx-auto p-6 space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1
                        class="text-2xl font-bold text-gray-900 dark:text-white"
                    >
                        {{ client.name }}
                    </h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ client.company_name || "Individual Client" }}
                    </p>
                </div>
                <div class="space-x-3">
                    <Link
                        :href="edit.url(client.id)"
                        class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700"
                        >Edit Details</Link
                    >
                    <Link
                        :href="index.url()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 rounded-lg hover:bg-gray-200"
                        >Back</Link
                    >
                </div>
            </div>

            <!-- Overview Card -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div
                    class="bg-white dark:bg-gray-900 p-6 rounded-xl border border-gray-200 dark:border-gray-800 space-y-2"
                >
                    <div class="text-xs text-gray-500 uppercase font-semibold">
                        Contact Email
                    </div>
                    <div class="font-medium text-gray-900 dark:text-white">
                        {{ client.email }}
                    </div>
                </div>
                <div
                    class="bg-white dark:bg-gray-900 p-6 rounded-xl border border-gray-200 dark:border-gray-800 space-y-2"
                >
                    <div class="text-xs text-gray-500 uppercase font-semibold">
                        Phone / WhatsApp
                    </div>
                    <div class="font-medium text-gray-900 dark:text-white">
                        {{ client.phone || "N/A" }}
                    </div>
                </div>
                <div
                    class="bg-white dark:bg-gray-900 p-6 rounded-xl border border-gray-200 dark:border-gray-800 space-y-2"
                >
                    <div class="text-xs text-gray-500 uppercase font-semibold">
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
                class="bg-white dark:bg-gray-900 p-6 rounded-xl border border-gray-200 dark:border-gray-800 space-y-4"
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
                        class="py-3 flex flex-col md:flex-row md:items-center justify-between gap-2"
                    >
                        <div>
                            <span
                                class="font-semibold text-gray-900 dark:text-white"
                                >{{ service.name }}</span
                            >
                            <p
                                class="text-xs text-gray-500 dark:text-gray-400 mt-1"
                            >
                                {{
                                    service.pivot?.requirements ||
                                    "No specific requirements noted."
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
                                    : "TBD"
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
                class="bg-white dark:bg-gray-900 p-6 rounded-xl border border-gray-200 dark:border-gray-800 space-y-2"
            >
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                    Communication Notes
                </h2>
                <p
                    class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line"
                >
                    {{ client.notes }}
                </p>
            </div>
        </div>
    </AppLayout>
</template>
