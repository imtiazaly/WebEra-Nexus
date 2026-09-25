<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { index, create, show, edit, destroy } from '@/routes/clients';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Plus,
    Eye,
    Pencil,
    Trash2,
    Building2,
    Mail,
    Phone,
    UserX,
    Search,
    MoreHorizontal,
    Users,
    UserPlus,
    PhoneCall,
    CheckCircle2,
    ChevronLeft,
    ChevronRight,
} from '@lucide/vue';

import ServiceManagerModal from '@/components/ServiceManagerModal.vue';
import { Briefcase } from '@lucide/vue';

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
    total?: number;
    from?: number;
    to?: number;
}

const props = defineProps<{
    clients: PaginatedClients;
    all_client_services?: any[];
}>();

// Search & Filter State
const searchQuery = ref('');
const selectedStatus = ref<string>('all');
const isServiceModalOpen = ref(false);

// Helper for Initials
const getInitials = (name: string) => {
    if (!name) return 'CL';
    const parts = name.trim().split(' ');
    if (parts.length >= 2) {
        return `${parts[0][0]}${parts[1][0]}`.toUpperCase();
    }
    return name.slice(0, 2).toUpperCase();
};

// Avatar background colors generator based on client name
const getAvatarColor = (name: string) => {
    const colors = [
        'bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300 border-indigo-200',
        'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-300 border-blue-200',
        'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300 border-emerald-200',
        'bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-300 border-purple-200',
        'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300 border-amber-200',
    ];
    let hash = 0;
    for (let i = 0; i < name.length; i++) {
        hash = name.charCodeAt(i) + ((hash << 5) - hash);
    }
    return colors[Math.abs(hash) % colors.length];
};

// Metrics
const totalClientsCount = computed(
    () => props.clients.total || props.clients.data.length,
);
const newLeadsCount = computed(
    () => props.clients.data.filter((c) => c.status === 'new_lead').length,
);
const contactedCount = computed(
    () => props.clients.data.filter((c) => c.status === 'contacted').length,
);
const convertedCount = computed(
    () => props.clients.data.filter((c) => c.status === 'converted').length,
);

// Status Badge Config
const getStatusConfig = (status: string) => {
    switch (status) {
        case 'new_lead':
            return {
                label: 'New Lead',
                badgeClass:
                    'bg-blue-50 text-blue-700 border-blue-200/80 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800/60',
                dotClass: 'bg-blue-500',
            };
        case 'contacted':
            return {
                label: 'Contacted',
                badgeClass:
                    'bg-amber-50 text-amber-700 border-amber-200/80 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800/60',
                dotClass: 'bg-amber-500',
            };
        case 'converted':
            return {
                label: 'Converted',
                badgeClass:
                    'bg-emerald-50 text-emerald-700 border-emerald-200/80 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800/60',
                dotClass: 'bg-emerald-500',
            };
        case 'lost':
            return {
                label: 'Lost Lead',
                badgeClass:
                    'bg-rose-50 text-rose-700 border-rose-200/80 dark:bg-rose-950/40 dark:text-rose-300 dark:border-rose-800/60',
                dotClass: 'bg-rose-500',
            };
        default:
            return {
                label: status,
                badgeClass:
                    'bg-slate-50 text-slate-700 border-slate-200 dark:bg-slate-900 dark:text-slate-300 dark:border-slate-800',
                dotClass: 'bg-slate-400',
            };
    }
};

// Filtered Clients List
const filteredClients = computed(() => {
    return props.clients.data.filter((client) => {
        const matchesStatus =
            selectedStatus.value === 'all' ||
            client.status === selectedStatus.value;
        const query = searchQuery.value.toLowerCase().trim();
        const matchesSearch =
            !query ||
            client.name.toLowerCase().includes(query) ||
            client.email.toLowerCase().includes(query) ||
            (client.company_name &&
                client.company_name.toLowerCase().includes(query)) ||
            (client.phone && client.phone.toLowerCase().includes(query));

        return matchesStatus && matchesSearch;
    });
});

const deleteClient = (id: number) => {
    if (confirm('Are you sure you want to delete this client/lead?')) {
        router.delete(destroy.url(id));
    }
};
</script>

<template>
    <Head title="Clients & Leads Portal" />

    <div class="w-full space-y-6 p-4 sm:p-6">
        <!-- Page Header -->
        <div
            class="flex flex-col gap-4 border-b border-slate-200/80 pb-5 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800"
        >
            <div>
                <div class="flex flex-wrap items-center gap-2.5">
                    <h1
                        class="text-xl font-extrabold tracking-tight text-slate-900 sm:text-2xl dark:text-slate-50"
                    >
                        Clients & Leads Portal
                    </h1>
                    <Badge
                        variant="outline"
                        class="rounded-full border-indigo-200 bg-indigo-50/50 px-2.5 py-0.5 text-xs font-semibold text-indigo-700 dark:border-indigo-900 dark:bg-indigo-950/50 dark:text-indigo-300"
                    >
                        {{ totalClientsCount }} Total
                    </Badge>
                </div>
                <p
                    class="mt-1 text-xs text-slate-500 sm:text-sm dark:text-slate-400"
                >
                    Manage prospective clients, track lead statuses, and review
                    service allocations.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <Button
                    variant="outline"
                    size="default"
                    @click="isServiceModalOpen = true"
                    class="rounded-xl border-slate-200 bg-white font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800"
                >
                    <Briefcase class="mr-1.5 h-4 w-4 text-indigo-500" />
                    <span>Manage Services ⚙️</span>
                </Button>

                <Button
                    as-child
                    size="default"
                    class="w-full bg-indigo-600 font-medium text-white shadow-sm transition-all duration-150 hover:bg-indigo-700 sm:w-auto"
                >
                    <Link :href="create.url()">
                        <Plus class="mr-1.5 h-4 w-4" /> Add New Client
                    </Link>
                </Button>
            </div>
        </div>

        <!-- Metric Stat Cards -->
        <div
            class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4 lg:grid-cols-4"
        >
            <div
                class="flex items-center justify-between rounded-xl border border-slate-200/80 bg-white p-4 shadow-xs dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="space-y-0.5">
                    <p
                        class="text-xs font-medium tracking-wider text-slate-500 uppercase dark:text-slate-400"
                    >
                        Total Clients
                    </p>
                    <p
                        class="text-2xl font-extrabold text-slate-900 dark:text-slate-50"
                    >
                        {{ totalClientsCount }}
                    </p>
                </div>
                <div
                    class="rounded-lg bg-slate-100 p-2.5 text-slate-700 dark:bg-slate-800 dark:text-slate-300"
                >
                    <Users class="h-5 w-5" />
                </div>
            </div>

            <div
                class="flex items-center justify-between rounded-xl border border-slate-200/80 bg-white p-4 shadow-xs dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="space-y-0.5">
                    <p
                        class="text-xs font-medium tracking-wider text-slate-500 uppercase dark:text-slate-400"
                    >
                        New Leads
                    </p>
                    <p
                        class="text-2xl font-extrabold text-blue-600 dark:text-blue-400"
                    >
                        {{ newLeadsCount }}
                    </p>
                </div>
                <div
                    class="rounded-lg bg-blue-50 p-2.5 text-blue-600 dark:bg-blue-950/60 dark:text-blue-400"
                >
                    <UserPlus class="h-5 w-5" />
                </div>
            </div>

            <div
                class="flex items-center justify-between rounded-xl border border-slate-200/80 bg-white p-4 shadow-xs dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="space-y-0.5">
                    <p
                        class="text-xs font-medium tracking-wider text-slate-500 uppercase dark:text-slate-400"
                    >
                        Contacted
                    </p>
                    <p
                        class="text-2xl font-extrabold text-amber-600 dark:text-amber-400"
                    >
                        {{ contactedCount }}
                    </p>
                </div>
                <div
                    class="rounded-lg bg-amber-50 p-2.5 text-amber-600 dark:bg-amber-950/60 dark:text-amber-400"
                >
                    <PhoneCall class="h-5 w-5" />
                </div>
            </div>

            <div
                class="flex items-center justify-between rounded-xl border border-slate-200/80 bg-white p-4 shadow-xs dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="space-y-0.5">
                    <p
                        class="text-xs font-medium tracking-wider text-slate-500 uppercase dark:text-slate-400"
                    >
                        Converted
                    </p>
                    <p
                        class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400"
                    >
                        {{ convertedCount }}
                    </p>
                </div>
                <div
                    class="rounded-lg bg-emerald-50 p-2.5 text-emerald-600 dark:bg-emerald-950/60 dark:text-emerald-400"
                >
                    <CheckCircle2 class="h-5 w-5" />
                </div>
            </div>
        </div>

        <!-- Toolbar & Main Card -->
        <Card
            class="overflow-hidden border-slate-200/80 shadow-xs dark:border-slate-800 dark:bg-slate-900"
        >
            <!-- Toolbar -->
            <div
                class="flex flex-col gap-3 border-b border-slate-200/80 p-4 lg:flex-row lg:items-center lg:justify-between dark:border-slate-800"
            >
                <!-- Search Input -->
                <div class="relative w-full lg:w-80">
                    <Search
                        class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-slate-400"
                    />
                    <Input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search by name, email, company..."
                        class="h-9 border-slate-200 bg-slate-50/50 pl-9 text-xs focus:bg-white dark:border-slate-800 dark:bg-slate-800/40 dark:focus:bg-slate-900"
                    />
                </div>

                <!-- Status Filter Tabs (Scrollable on mobile) -->
                <div
                    class="flex max-w-full items-center gap-1.5 overflow-x-auto rounded-lg bg-slate-100 p-1 text-xs font-medium dark:bg-slate-800/70"
                >
                    <button
                        @click="selectedStatus = 'all'"
                        :class="[
                            'rounded-md px-3 py-1.5 whitespace-nowrap transition-all',
                            selectedStatus === 'all'
                                ? 'bg-white font-semibold text-slate-900 shadow-xs dark:bg-slate-900 dark:text-white'
                                : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200',
                        ]"
                    >
                        All ({{ props.clients.data.length }})
                    </button>
                    <button
                        @click="selectedStatus = 'new_lead'"
                        :class="[
                            'rounded-md px-3 py-1.5 whitespace-nowrap transition-all',
                            selectedStatus === 'new_lead'
                                ? 'bg-white font-semibold text-slate-900 shadow-xs dark:bg-slate-900 dark:text-white'
                                : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200',
                        ]"
                    >
                        New Leads
                    </button>
                    <button
                        @click="selectedStatus = 'contacted'"
                        :class="[
                            'rounded-md px-3 py-1.5 whitespace-nowrap transition-all',
                            selectedStatus === 'contacted'
                                ? 'bg-white font-semibold text-slate-900 shadow-xs dark:bg-slate-900 dark:text-white'
                                : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200',
                        ]"
                    >
                        Contacted
                    </button>
                    <button
                        @click="selectedStatus = 'converted'"
                        :class="[
                            'rounded-md px-3 py-1.5 whitespace-nowrap transition-all',
                            selectedStatus === 'converted'
                                ? 'bg-white font-semibold text-slate-900 shadow-xs dark:bg-slate-900 dark:text-white'
                                : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200',
                        ]"
                    >
                        Converted
                    </button>
                    <button
                        @click="selectedStatus = 'lost'"
                        :class="[
                            'rounded-md px-3 py-1.5 whitespace-nowrap transition-all',
                            selectedStatus === 'lost'
                                ? 'bg-white font-semibold text-slate-900 shadow-xs dark:bg-slate-900 dark:text-white'
                                : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200',
                        ]"
                    >
                        Lost
                    </button>
                </div>
            </div>

            <!-- Card Content -->
            <CardContent class="p-0">
                <!-- Empty State -->
                <div
                    v-if="filteredClients.length === 0"
                    class="py-16 text-center"
                >
                    <div
                        class="mx-auto flex max-w-xs flex-col items-center justify-center space-y-3"
                    >
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400 dark:bg-slate-800"
                        >
                            <UserX class="h-6 w-6" />
                        </div>
                        <div>
                            <p
                                class="text-sm font-bold text-slate-900 dark:text-slate-100"
                            >
                                No clients found
                            </p>
                            <p
                                class="mt-0.5 text-xs text-slate-500 dark:text-slate-400"
                            >
                                {{
                                    searchQuery
                                        ? 'Try adjusting your search query or filter.'
                                        : 'Get started by adding your first client or lead.'
                                }}
                            </p>
                        </div>
                        <Button
                            v-if="!searchQuery"
                            as-child
                            size="sm"
                            class="mt-2 bg-indigo-600 text-white hover:bg-indigo-700"
                        >
                            <Link :href="create.url()">
                                <Plus class="mr-1 h-3.5 w-3.5" />
                                Add New Client
                            </Link>
                        </Button>
                    </div>
                </div>

                <!-- 1️⃣ DESKTOP TABLE VIEW (Visible on md & larger screens) -->
                <div v-else class="hidden w-full overflow-x-auto md:block">
                    <table
                        class="w-full min-w-212.5 table-fixed border-collapse text-left"
                    >
                        <thead>
                            <tr
                                class="border-b border-slate-200/80 bg-slate-50/70 text-[11px] font-bold tracking-wider text-slate-500 uppercase dark:border-slate-800 dark:bg-slate-800/50 dark:text-slate-400"
                            >
                                <th class="w-[22%] px-5 py-3.5">
                                    Client Information
                                </th>
                                <th class="w-[24%] px-5 py-3.5">
                                    Contact Details
                                </th>
                                <th class="w-[20%] px-5 py-3.5">
                                    Company / Organization
                                </th>
                                <th class="w-[18%] px-5 py-3.5">
                                    Services Requested
                                </th>
                                <th class="w-[10%] px-5 py-3.5">Status</th>
                                <th class="w-[6%] px-5 py-3.5 text-center">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-slate-200/80 text-xs text-slate-700 dark:divide-slate-800 dark:text-slate-300"
                        >
                            <tr
                                v-for="client in filteredClients"
                                :key="client.id"
                                class="group transition-colors hover:bg-slate-50/80 dark:hover:bg-slate-800/40"
                            >
                                <!-- Client Info -->
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <Avatar
                                            class="h-9 w-9 shrink-0 border border-slate-200 shadow-xs dark:border-slate-700"
                                        >
                                            <AvatarFallback
                                                :class="[
                                                    'text-xs font-bold',
                                                    getAvatarColor(client.name),
                                                ]"
                                            >
                                                {{ getInitials(client.name) }}
                                            </AvatarFallback>
                                        </Avatar>
                                        <div class="min-w-0">
                                            <Link
                                                :href="show.url(client.id)"
                                                class="block truncate font-semibold text-slate-900 transition-colors hover:text-indigo-600 dark:text-slate-100 dark:hover:text-indigo-400"
                                            >
                                                {{ client.name }}
                                            </Link>
                                            <p
                                                class="truncate font-mono text-[11px] text-slate-400"
                                            >
                                                ID: #CLN-{{ client.id }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Contact -->
                                <td class="px-5 py-4">
                                    <div class="min-w-0 space-y-1">
                                        <div
                                            class="flex items-center gap-1.5 font-medium text-slate-800 dark:text-slate-200"
                                        >
                                            <Mail
                                                class="h-3.5 w-3.5 shrink-0 text-slate-400"
                                            />
                                            <span class="truncate">{{
                                                client.email
                                            }}</span>
                                        </div>
                                        <div
                                            v-if="client.phone"
                                            class="flex items-center gap-1.5 text-slate-500 dark:text-slate-400"
                                        >
                                            <Phone
                                                class="h-3.5 w-3.5 shrink-0 text-slate-400"
                                            />
                                            <span class="truncate">{{
                                                client.phone
                                            }}</span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Company -->
                                <td class="px-5 py-4">
                                    <div
                                        class="inline-flex max-w-full items-center gap-1.5 rounded-md border border-slate-200/80 bg-slate-50 px-2.5 py-1 text-xs font-medium text-slate-700 dark:border-slate-800 dark:bg-slate-800/60 dark:text-slate-300"
                                    >
                                        <Building2
                                            class="h-3.5 w-3.5 shrink-0 text-slate-400"
                                        />
                                        <span class="truncate">{{
                                            client.company_name ||
                                            'Individual / Freelance'
                                        }}</span>
                                    </div>
                                </td>

                                <!-- Services -->
                                <td class="px-5 py-4">
                                    <div class="flex flex-wrap gap-1">
                                        <Badge
                                            v-for="service in client.services"
                                            :key="service.id"
                                            variant="secondary"
                                            class="rounded-md border border-slate-200/60 bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-700 dark:border-slate-700/60 dark:bg-slate-800 dark:text-slate-300"
                                        >
                                            {{ service.name }}
                                        </Badge>
                                        <span
                                            v-if="
                                                !client.services ||
                                                client.services.length === 0
                                            "
                                            class="text-xs text-slate-400 italic"
                                        >
                                            No services attached
                                        </span>
                                    </div>
                                </td>

                                <!-- Status -->
                                <td class="px-5 py-4">
                                    <span
                                        :class="[
                                            'inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-semibold whitespace-nowrap transition-all',
                                            getStatusConfig(client.status)
                                                .badgeClass,
                                        ]"
                                    >
                                        <span
                                            :class="[
                                                'h-1.5 w-1.5 shrink-0 rounded-full',
                                                getStatusConfig(client.status)
                                                    .dotClass,
                                            ]"
                                        ></span>
                                        {{
                                            getStatusConfig(client.status).label
                                        }}
                                    </span>
                                </td>

                                <!-- Actions Dropdown -->
                                <td class="px-5 py-4 text-center">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button
                                                variant="ghost"
                                                size="icon-sm"
                                                class="h-8 w-8 text-slate-500 hover:text-slate-900 dark:hover:text-slate-100"
                                            >
                                                <MoreHorizontal
                                                    class="h-4 w-4"
                                                />
                                                <span class="sr-only"
                                                    >Open menu</span
                                                >
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent
                                            align="end"
                                            class="w-44"
                                        >
                                            <DropdownMenuLabel
                                                class="text-xs font-semibold text-slate-500"
                                                >Actions</DropdownMenuLabel
                                            >
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem as-child>
                                                <Link
                                                    :href="show.url(client.id)"
                                                    class="flex cursor-pointer items-center gap-2"
                                                >
                                                    <Eye
                                                        class="h-4 w-4 text-slate-500"
                                                    />
                                                    <span>View Details</span>
                                                </Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem as-child>
                                                <Link
                                                    :href="edit.url(client.id)"
                                                    class="flex cursor-pointer items-center gap-2"
                                                >
                                                    <Pencil
                                                        class="h-4 w-4 text-slate-500"
                                                    />
                                                    <span>Edit Client</span>
                                                </Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem
                                                @click="deleteClient(client.id)"
                                                class="flex cursor-pointer items-center gap-2 text-rose-600 focus:text-rose-600"
                                            >
                                                <Trash2 class="h-4 w-4" />
                                                <span>Delete Client</span>
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- 2️⃣ MOBILE CARDS VIEW (Visible on mobile screens < md) -->
                <div
                    v-if="filteredClients.length > 0"
                    class="block space-y-4 divide-y divide-slate-200 p-4 md:hidden dark:divide-slate-800"
                >
                    <div
                        v-for="client in filteredClients"
                        :key="client.id"
                        class="space-y-3 rounded-xl border border-slate-200/80 bg-slate-50/50 p-4 dark:border-slate-800 dark:bg-slate-900/60"
                    >
                        <!-- Mobile Header: Avatar + Name + Dropdown -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <Avatar
                                    class="h-9 w-9 border border-slate-200 shadow-xs dark:border-slate-700"
                                >
                                    <AvatarFallback
                                        :class="[
                                            'text-xs font-bold',
                                            getAvatarColor(client.name),
                                        ]"
                                    >
                                        {{ getInitials(client.name) }}
                                    </AvatarFallback>
                                </Avatar>
                                <div>
                                    <Link
                                        :href="show.url(client.id)"
                                        class="font-semibold text-slate-900 hover:text-indigo-600 dark:text-slate-100"
                                    >
                                        {{ client.name }}
                                    </Link>
                                    <p
                                        class="font-mono text-[11px] text-slate-400"
                                    >
                                        #CLN-{{ client.id }}
                                    </p>
                                </div>
                            </div>
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button
                                        variant="ghost"
                                        size="icon-sm"
                                        class="h-8 w-8 text-slate-500"
                                    >
                                        <MoreHorizontal class="h-4 w-4" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end" class="w-44">
                                    <DropdownMenuItem as-child>
                                        <Link
                                            :href="show.url(client.id)"
                                            class="flex items-center gap-2"
                                        >
                                            <Eye
                                                class="h-4 w-4 text-slate-500"
                                            />
                                            <span>View Details</span>
                                        </Link>
                                    </DropdownMenuItem>
                                    <DropdownMenuItem as-child>
                                        <Link
                                            :href="edit.url(client.id)"
                                            class="flex items-center gap-2"
                                        >
                                            <Pencil
                                                class="h-4 w-4 text-slate-500"
                                            />
                                            <span>Edit Client</span>
                                        </Link>
                                    </DropdownMenuItem>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem
                                        @click="deleteClient(client.id)"
                                        class="flex items-center gap-2 text-rose-600"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                        <span>Delete Client</span>
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>

                        <!-- Status & Company Bar -->
                        <div
                            class="flex flex-wrap items-center justify-between gap-2 border-y border-slate-200/60 py-2 dark:border-slate-800"
                        >
                            <span
                                :class="[
                                    'inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-semibold',
                                    getStatusConfig(client.status).badgeClass,
                                ]"
                            >
                                <span
                                    :class="[
                                        'h-1.5 w-1.5 rounded-full',
                                        getStatusConfig(client.status).dotClass,
                                    ]"
                                ></span>
                                {{ getStatusConfig(client.status).label }}
                            </span>

                            <div
                                class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-600 dark:text-slate-400"
                            >
                                <Building2 class="h-3.5 w-3.5 text-slate-400" />
                                <span>{{
                                    client.company_name || 'Individual'
                                }}</span>
                            </div>
                        </div>

                        <!-- Contact Details -->
                        <div
                            class="space-y-1 text-xs text-slate-600 dark:text-slate-400"
                        >
                            <div class="flex items-center gap-2">
                                <Mail class="h-3.5 w-3.5 text-slate-400" />
                                <span class="truncate">{{ client.email }}</span>
                            </div>
                            <div
                                v-if="client.phone"
                                class="flex items-center gap-2"
                            >
                                <Phone class="h-3.5 w-3.5 text-slate-400" />
                                <span>{{ client.phone }}</span>
                            </div>
                        </div>

                        <!-- Services -->
                        <div
                            v-if="client.services && client.services.length > 0"
                            class="flex flex-wrap gap-1 pt-1"
                        >
                            <Badge
                                v-for="service in client.services"
                                :key="service.id"
                                variant="secondary"
                                class="rounded-md border border-slate-200/60 bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-700 dark:border-slate-700/60 dark:bg-slate-800 dark:text-slate-300"
                            >
                                {{ service.name }}
                            </Badge>
                        </div>
                    </div>
                </div>

                <!-- Footer Pagination Bar -->
                <div
                    v-if="filteredClients.length > 0"
                    class="flex flex-col gap-3 border-t border-slate-200/80 px-5 py-3 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800"
                >
                    <p
                        class="text-center text-xs text-slate-500 sm:text-left dark:text-slate-400"
                    >
                        Showing
                        <span
                            class="font-semibold text-slate-700 dark:text-slate-300"
                            >1</span
                        >
                        to
                        <span
                            class="font-semibold text-slate-700 dark:text-slate-300"
                            >{{ filteredClients.length }}</span
                        >
                        of
                        <span
                            class="font-semibold text-slate-700 dark:text-slate-300"
                            >{{ totalClientsCount }}</span
                        >
                        clients
                    </p>
                    <div class="flex items-center justify-center gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            class="h-8 gap-1 text-xs"
                            disabled
                        >
                            <ChevronLeft class="h-3.5 w-3.5" /> Previous
                        </Button>
                        <Button
                            variant="outline"
                            size="sm"
                            class="h-8 gap-1 text-xs"
                            disabled
                        >
                            Next <ChevronRight class="h-3.5 w-3.5" />
                        </Button>
                    </div>
                </div>
            </CardContent>
        </Card>

        <!-- In-Context Service Manager Modal -->
        <ServiceManagerModal
            :is-open="isServiceModalOpen"
            :services="all_client_services || []"
            @close="isServiceModalOpen = false"
        />
    </div>
</template>
