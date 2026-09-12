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
import {
    Plus,
    Eye,
    Pencil,
    Trash2,
    UserCheck,
    Mail,
    Phone,
    Building,
} from '@lucide/vue';

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
            return {
                variant: 'default' as const,
                label: 'New Lead',
                class: 'bg-blue-600 text-white',
            };
        case 'contacted':
            return {
                variant: 'secondary' as const,
                label: 'Contacted',
                class: 'bg-amber-500 text-white',
            };
        case 'converted':
            return {
                variant: 'default' as const,
                label: 'Converted Client',
                class: 'bg-emerald-600 text-white',
            };
        case 'lost':
            return {
                variant: 'destructive' as const,
                label: 'Lost',
                class: '',
            };
        default:
            return { variant: 'outline' as const, label: status, class: '' };
    }
};

const deleteClient = (id: number) => {
    if (confirm('Are you sure you want to delete this client/lead?')) {
        router.delete(destroy.url(id));
    }
};
</script>

<template>
    <Head title="Clients & Leads" />

    <div class="mx-auto max-w-7xl space-y-6 p-6">
        <!-- Header -->
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
        >
            <Heading
                title="Clients & Leads Portal"
                description="Manage potential leads, client conversions, and service requests."
            />
            <div>
                <Button as-child>
                    <Link :href="create.url()" class="flex items-center gap-2">
                        <Plus class="h-4 w-4" />
                        <span>Add New Client / Lead</span>
                    </Link>
                </Button>
            </div>
        </div>

        <!-- Table Card -->
        <Card>
            <CardContent class="p-0">
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-left">
                        <thead>
                            <tr
                                class="border-border bg-muted/50 text-muted-foreground border-b text-xs font-semibold tracking-wider uppercase"
                            >
                                <th class="px-4 py-3.5">Client Name</th>
                                <th class="px-4 py-3.5">Contact</th>
                                <th class="px-4 py-3.5">Company</th>
                                <th class="px-4 py-3.5">Services Requested</th>
                                <th class="px-4 py-3.5">Status</th>
                                <th class="px-4 py-3.5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-border divide-y text-sm">
                            <tr v-if="clients.data.length === 0">
                                <td
                                    colspan="6"
                                    class="text-muted-foreground py-8 text-center"
                                >
                                    No clients or leads found. Click "+ Add New
                                    Client" to get started.
                                </td>
                            </tr>
                            <tr
                                v-for="client in clients.data"
                                :key="client.id"
                                class="hover:bg-muted/40 transition-colors"
                            >
                                <td class="px-4 py-4 font-medium">
                                    <Link
                                        :href="show.url(client.id)"
                                        class="text-primary hover:underline"
                                    >
                                        {{ client.name }}
                                    </Link>
                                </td>
                                <td class="px-4 py-4">
                                    <div
                                        class="text-foreground flex items-center gap-1.5 text-xs"
                                    >
                                        <Mail
                                            class="text-muted-foreground h-3.5 w-3.5"
                                        />
                                        <span>{{ client.email }}</span>
                                    </div>
                                    <div
                                        v-if="client.phone"
                                        class="text-muted-foreground mt-0.5 flex items-center gap-1.5 text-xs"
                                    >
                                        <Phone class="h-3.5 w-3.5" />
                                        <span>{{ client.phone }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <div
                                        class="text-muted-foreground flex items-center gap-1.5 text-xs"
                                    >
                                        <Building class="h-3.5 w-3.5" />
                                        <span>{{
                                            client.company_name || 'Individual'
                                        }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex flex-wrap gap-1">
                                        <Badge
                                            v-for="service in client.services"
                                            :key="service.id"
                                            variant="outline"
                                            class="text-[11px]"
                                        >
                                            {{ service.name }}
                                        </Badge>
                                        <span
                                            v-if="
                                                !client.services ||
                                                client.services.length === 0
                                            "
                                            class="text-muted-foreground text-xs"
                                            >-</span
                                        >
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <Badge
                                        :variant="
                                            getStatusBadge(client.status)
                                                .variant
                                        "
                                        :class="
                                            getStatusBadge(client.status).class
                                        "
                                    >
                                        {{
                                            getStatusBadge(client.status).label
                                        }}
                                    </Badge>
                                </td>
                                <td class="space-x-1 px-4 py-4 text-right">
                                    <Button variant="ghost" size="sm" as-child>
                                        <Link :href="show.url(client.id)">
                                            <Eye class="h-4 w-4" />
                                        </Link>
                                    </Button>
                                    <Button variant="ghost" size="sm" as-child>
                                        <Link :href="edit.url(client.id)">
                                            <Pencil
                                                class="text-primary h-4 w-4"
                                            />
                                        </Link>
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        @click="deleteClient(client.id)"
                                    >
                                        <Trash2
                                            class="text-destructive h-4 w-4"
                                        />
                                    </Button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
