<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { index, create, show, edit, destroy } from '@/routes/clients';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Clients & Leads',
                href: index.url(),
            },
        ],
    },
});

interface Service {
    id: number;
    name: string;
}

interface Client {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    company_name: string | null;
    status: string;
    services: Service[];
    created_at: string;
}

interface PaginatedClients {
    data: Client[];
    links: any[];
    current_page: number;
    last_page: number;
}

const props = defineProps<{
    clients: PaginatedClients;
}>();

const getStatusBadge = (status: string) => {
    switch (status) {
        case 'new_lead':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 border-blue-200';
        case 'contacted':
            return 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 border-amber-200';
        case 'converted':
            return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 border-emerald-200';
        case 'lost':
            return 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300 border-rose-200';
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300 border-gray-200';
    }
};

const formatStatus = (status: string) => {
    return status.replace('_', ' ').replace(/\b\w/g, (l) => l.toUpperCase());
};

const deleteClient = (id: number) => {
    if (confirm('Are you sure you want to delete this client/lead?')) {
        router.delete(destroy.url(id));
    }
};
</script>

<template>
    <Head title="Clients & Leads" />

    <div class="space-y-6 p-6">
        <!-- Header -->
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                <h1
                    class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white"
                >
                    Clients & Leads Portal
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Manage potential leads, client conversions, and service requests.
                </p>
            </div>
            <div>
                <Link
                    :href="create.url()"
                    class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none"
                >
                    + Add New Client / Lead
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
                            <th class="px-4 py-3.5">Client Name</th>
                            <th class="px-4 py-3.5">Contact</th>
                            <th class="px-4 py-3.5">Company</th>
                            <th class="px-4 py-3.5">Services Requested</th>
                            <th class="px-4 py-3.5">Status</th>
                            <th class="px-4 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody
                        class="divide-y divide-gray-200 text-sm text-gray-700 dark:divide-gray-800 dark:text-gray-300"
                    >
                        <tr v-if="clients.data.length === 0">
                            <td
                                colspan="6"
                                class="py-8 text-center text-gray-500 dark:text-gray-400"
                            >
                                No clients or leads found. Click "+ Add New Client" to get started.
                            </td>
                        </tr>
                        <tr
                            v-for="client in clients.data"
                            :key="client.id"
                            class="transition-colors hover:bg-gray-50/60 dark:hover:bg-gray-800/40"
                        >
                            <td
                                class="px-4 py-4 font-medium text-gray-900 dark:text-white"
                            >
                                <Link
                                    :href="show.url(client.id)"
                                    class="text-indigo-600 hover:underline dark:text-indigo-400"
                                >
                                    {{ client.name }}
                                </Link>
                            </td>
                            <td class="px-4 py-4">
                                <div class="text-xs font-medium text-gray-900 dark:text-white">
                                    {{ client.email }}
                                </div>
                                <div
                                    v-if="client.phone"
                                    class="text-xs text-gray-400"
                                >
                                    {{ client.phone }}
                                </div>
                            </td>
                            <td class="px-4 py-4 text-xs text-gray-600 dark:text-gray-300">
                                {{ client.company_name || 'Individual' }}
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex flex-wrap gap-1">
                                    <span
                                        v-for="service in client.services"
                                        :key="service.id"
                                        class="rounded bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300"
                                    >
                                        {{ service.name }}
                                    </span>
                                    <span
                                        v-if="!client.services || client.services.length === 0"
                                        class="text-xs text-gray-400"
                                        >-</span
                                    >
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <span
                                    :class="[
                                        'rounded-full border px-2.5 py-1 text-xs font-semibold',
                                        getStatusBadge(client.status),
                                    ]"
                                >
                                    {{ formatStatus(client.status) }}
                                </span>
                            </td>
                            <td class="space-x-2 px-4 py-4 text-right">
                                <Link
                                    :href="show.url(client.id)"
                                    class="font-medium text-gray-600 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400"
                                    >View</Link
                                >
                                <Link
                                    :href="edit.url(client.id)"
                                    class="font-medium text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                                    >Edit</Link
                                >
                                <button
                                    @click="deleteClient(client.id)"
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