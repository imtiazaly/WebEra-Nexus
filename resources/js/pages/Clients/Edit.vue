<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { index, show, update } from '@/routes/clients';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import InputError from '@/components/InputError.vue';
import {
    ArrowLeft,
    User,
    Mail,
    Phone,
    Building2,
    Briefcase,
    DollarSign,
    FileText,
    CheckCircle2,
    Loader2,
    Pencil,
    UserCheck,
} from '@lucide/vue';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Clients & Leads', href: index.url() },
            { title: 'Edit Client', href: '#' },
        ],
    },
});

interface Service {
    id: number;
    name: string;
    pivot?: {
        requirements: string | null;
        estimated_budget: number | null;
    };
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
    created_at: string;
}

const props = defineProps<{
    client: Client;
    services: Service[];
}>();

// Helper for Initials
const getInitials = (name: string) => {
    if (!name) return 'CL';
    const parts = name.trim().split(' ');
    if (parts.length >= 2) {
        return `${parts[0][0]}${parts[1][0]}`.toUpperCase();
    }
    return name.slice(0, 2).toUpperCase();
};

// Pre-populate Selected Services from Client Data
const selectedServices = ref<
    { id: number; requirements: string; estimated_budget: string }[]
>(
    props.client.services
        ? props.client.services.map((s) => ({
              id: s.id,
              requirements: s.pivot?.requirements || '',
              estimated_budget:
                  s.pivot?.estimated_budget !== null &&
                  s.pivot?.estimated_budget !== undefined
                      ? String(s.pivot.estimated_budget)
                      : '',
          }))
        : []
);

// Pre-populate Form Data
const form = useForm({
    name: props.client.name || '',
    email: props.client.email || '',
    phone: props.client.phone || '',
    company_name: props.client.company_name || '',
    status: props.client.status || 'new_lead',
    notes: props.client.notes || '',
    services: [] as {
        id: number;
        requirements: string;
        estimated_budget: string;
    }[],
});

const toggleService = (serviceId: number) => {
    const index = selectedServices.value.findIndex((s) => s.id === serviceId);
    if (index > -1) {
        selectedServices.value.splice(index, 1);
    } else {
        selectedServices.value.push({
            id: serviceId,
            requirements: '',
            estimated_budget: '',
        });
    }
};

const isServiceSelected = (serviceId: number) => {
    return selectedServices.value.some((s) => s.id === serviceId);
};

const getServiceSelection = (serviceId: number) => {
    return selectedServices.value.find((s) => s.id === serviceId);
};

const submit = () => {
    form.services = selectedServices.value;
    form.put(update.url(props.client.id));
};
</script>

<template>
    <Head :title="`Edit ${client.name}`" />

    <div class="w-full space-y-6 p-4 sm:p-6">
        <form @submit.prevent="submit" class="space-y-6">
            <!-- Back Navigation & Top Header Bar -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-slate-200/80 pb-5 dark:border-slate-800">
                <div class="space-y-1">
                    <Button variant="ghost" size="sm" as-child class="-ml-2 mb-1 text-slate-500 hover:text-slate-900 dark:hover:text-slate-100">
                        <Link :href="show.url(client.id)">
                            <ArrowLeft class="mr-1.5 h-4 w-4" /> Back to Client Profile
                        </Link>
                    </Button>
                    <div class="flex items-center gap-3">
                        <Avatar class="h-10 w-10 border border-slate-200 shadow-xs dark:border-slate-700">
                            <AvatarFallback class="bg-indigo-600 text-white font-bold text-sm">
                                {{ getInitials(client.name) }}
                            </AvatarFallback>
                        </Avatar>
                        <div>
                            <div class="flex items-center gap-2">
                                <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-slate-50">
                                    Edit Client Profile
                                </h1>
                                <Badge variant="outline" class="text-xs font-mono">
                                    #CLN-{{ client.id }}
                                </Badge>
                            </div>
                            <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                                Update record details, lead status, and requested service requirements for <span class="font-semibold text-slate-700 dark:text-slate-300">{{ client.name }}</span>.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Single Primary Top Action Bar -->
                <div class="flex items-center gap-3">
                    <Button variant="outline" type="button" as-child class="h-10 text-xs">
                        <Link :href="show.url(client.id)">Cancel</Link>
                    </Button>
                    <Button
                        type="submit"
                        :disabled="form.processing"
                        class="h-10 bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm font-semibold px-6 text-xs"
                    >
                        <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                        <Pencil v-else class="mr-1.5 h-3.5 w-3.5" />
                        <span>{{ form.processing ? 'Updating Profile...' : 'Update Client Profile' }}</span>
                    </Button>
                </div>
            </div>

            <!-- 2-Column Main Form Body -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
                <!-- Left Column (7 cols): Client Profile & Contact Info -->
                <div class="lg:col-span-7">
                    <Card class="h-full border-slate-200/80 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                        <CardHeader class="border-b border-slate-200/80 pb-4 dark:border-slate-800">
                            <div class="flex items-center gap-2 text-indigo-600 dark:text-indigo-400">
                                <UserCheck class="h-5 w-5" />
                                <CardTitle class="text-base font-bold text-slate-900 dark:text-slate-100">
                                    Client & Organization Information
                                </CardTitle>
                            </div>
                            <CardDescription class="text-xs text-slate-500">
                                Modify contact numbers, company details, and current pipeline status.
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="p-6 space-y-5">
                            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                                <!-- Name Field -->
                                <div class="space-y-1.5">
                                    <Label for="client-name" class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                                        Full Name <span class="text-rose-500">*</span>
                                    </Label>
                                    <div class="relative">
                                        <User class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                                        <Input
                                            id="client-name"
                                            v-model="form.name"
                                            type="text"
                                            placeholder="e.g. John Doe"
                                            required
                                            class="pl-9 h-10 text-xs border-slate-200 focus:border-indigo-500 dark:border-slate-800 dark:bg-slate-950"
                                        />
                                    </div>
                                    <InputError :message="form.errors.name" />
                                </div>

                                <!-- Email Field -->
                                <div class="space-y-1.5">
                                    <Label for="client-email" class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                                        Email Address <span class="text-rose-500">*</span>
                                    </Label>
                                    <div class="relative">
                                        <Mail class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                                        <Input
                                            id="client-email"
                                            v-model="form.email"
                                            type="email"
                                            placeholder="e.g. john@example.com"
                                            required
                                            class="pl-9 h-10 text-xs border-slate-200 focus:border-indigo-500 dark:border-slate-800 dark:bg-slate-950"
                                        />
                                    </div>
                                    <InputError :message="form.errors.email" />
                                </div>

                                <!-- Phone Field -->
                                <div class="space-y-1.5">
                                    <Label for="client-phone" class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                                        Phone / WhatsApp Number
                                    </Label>
                                    <div class="relative">
                                        <Phone class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                                        <Input
                                            id="client-phone"
                                            v-model="form.phone"
                                            type="text"
                                            placeholder="e.g. +92 300 1234567"
                                            class="pl-9 h-10 text-xs border-slate-200 focus:border-indigo-500 dark:border-slate-800 dark:bg-slate-950"
                                        />
                                    </div>
                                    <InputError :message="form.errors.phone" />
                                </div>

                                <!-- Company Name Field -->
                                <div class="space-y-1.5">
                                    <Label for="client-company" class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                                        Company / Organization
                                    </Label>
                                    <div class="relative">
                                        <Building2 class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                                        <Input
                                            id="client-company"
                                            v-model="form.company_name"
                                            type="text"
                                            placeholder="e.g. Acme Software Corp."
                                            class="pl-9 h-10 text-xs border-slate-200 focus:border-indigo-500 dark:border-slate-800 dark:bg-slate-950"
                                        />
                                    </div>
                                    <InputError :message="form.errors.company_name" />
                                </div>

                                <!-- Initial Status Field -->
                                <div class="space-y-1.5 md:col-span-2">
                                    <Label for="client-status" class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                                        Lead Pipeline Status <span class="text-rose-500">*</span>
                                    </Label>
                                    <select
                                        id="client-status"
                                        v-model="form.status"
                                        class="h-10 w-full rounded-md border border-slate-200 bg-white px-3 text-xs text-slate-900 shadow-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100"
                                    >
                                        <option value="new_lead">🔵 New Lead (Initial Inquiry)</option>
                                        <option value="contacted">🟡 Contacted (In Discussion)</option>
                                        <option value="converted">🟢 Converted Client (Active Project)</option>
                                        <option value="lost">🔴 Lost (Closed / Inactive)</option>
                                    </select>
                                    <InputError :message="form.errors.status" />
                                </div>

                                <!-- Notes Field -->
                                <div class="space-y-1.5 md:col-span-2">
                                    <Label for="client-notes" class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                                        Notes & Communication History
                                    </Label>
                                    <textarea
                                        id="client-notes"
                                        v-model="form.notes"
                                        rows="4"
                                        placeholder="Add meeting logs, requirement changes, or communication context..."
                                        class="w-full rounded-md border border-slate-200 p-3 text-xs text-slate-900 shadow-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100"
                                    ></textarea>
                                    <InputError :message="form.errors.notes" />
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Right Column (5 cols): Services Selection Card -->
                <div class="lg:col-span-5">
                    <Card class="h-full border-slate-200/80 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                        <CardHeader class="border-b border-slate-200/80 pb-4 dark:border-slate-800">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2 text-indigo-600 dark:text-indigo-400">
                                    <Briefcase class="h-5 w-5" />
                                    <CardTitle class="text-base font-bold text-slate-900 dark:text-slate-100">
                                        Services Requested
                                    </CardTitle>
                                </div>
                                <Badge variant="secondary" class="text-xs font-semibold">
                                    {{ selectedServices.length }} Selected
                                </Badge>
                            </div>
                            <CardDescription class="text-xs text-slate-500">
                                Select or adjust services requested and update budget/requirements.
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="p-6">
                            <div v-if="!services || services.length === 0" class="py-12 text-center text-xs text-slate-400">
                                No active services found in database.
                            </div>
                            <div v-else class="space-y-4">
                                <div
                                    v-for="service in services"
                                    :key="service.id"
                                    :class="[
                                        'rounded-xl border transition-all duration-200 p-4 space-y-3',
                                        isServiceSelected(service.id)
                                            ? 'border-indigo-500 bg-indigo-50/30 dark:border-indigo-600 dark:bg-indigo-950/20 shadow-xs'
                                            : 'border-slate-200/80 bg-slate-50/40 hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-800/30 dark:hover:bg-slate-800/50'
                                    ]"
                                >
                                    <label class="flex cursor-pointer items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <input
                                                type="checkbox"
                                                :checked="isServiceSelected(service.id)"
                                                @change="toggleService(service.id)"
                                                class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900"
                                            />
                                            <span class="text-sm font-bold text-slate-900 dark:text-slate-100">
                                                {{ service.name }}
                                            </span>
                                        </div>
                                        <Badge v-if="isServiceSelected(service.id)" variant="default" class="bg-indigo-600 text-[10px]">
                                            <CheckCircle2 class="mr-1 h-3 w-3" /> Active Track
                                        </Badge>
                                    </label>

                                    <!-- Requirement & Budget Details (Shown when selected) -->
                                    <div
                                        v-if="isServiceSelected(service.id)"
                                        class="space-y-3 pt-3 border-t border-indigo-200/60 dark:border-indigo-900/40"
                                    >
                                        <div class="space-y-1.5">
                                            <Label class="text-[11px] font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-1">
                                                <FileText class="h-3.5 w-3.5 text-slate-400" /> Requirements
                                            </Label>
                                            <textarea
                                                v-model="getServiceSelection(service.id)!.requirements"
                                                rows="2"
                                                placeholder="Detail client requirements..."
                                                class="w-full rounded-md border border-slate-200 p-2 text-xs text-slate-900 shadow-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100"
                                            ></textarea>
                                        </div>
                                        <div class="space-y-1.5">
                                            <Label class="text-[11px] font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-1">
                                                <DollarSign class="h-3.5 w-3.5 text-slate-400" /> Estimated Budget ($)
                                            </Label>
                                            <div class="relative">
                                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">$</span>
                                                <Input
                                                    v-model="getServiceSelection(service.id)!.estimated_budget"
                                                    type="number"
                                                    step="0.01"
                                                    placeholder="e.g. 1500.00"
                                                    class="pl-7 h-9 text-xs border-slate-200 focus:border-indigo-500 dark:border-slate-800 dark:bg-slate-950"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </form>
    </div>
</template>