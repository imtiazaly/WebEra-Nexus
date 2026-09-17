<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { index, create, store } from '@/routes/projects';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import {
    ArrowLeft,
    FolderPlus,
    FolderKanban,
    Building2,
    Briefcase,
    Calendar,
    Clock,
    SlidersHorizontal,
    FileText,
    Loader2,
    Sparkles,
} from '@lucide/vue';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Projects Portal', href: index.url() },
            { title: 'Create Project', href: create.url() },
        ],
    },
});

interface Client {
    id: number;
    name: string;
    company_name: string | null;
}

interface Service {
    id: number;
    name: string;
}

const props = defineProps<{
    clients: Client[];
    services: Service[];
}>();

interface ProjectForm {
    title: string;
    client_id: number | null;
    service_id: number | null;
    description: string;
    status: string;
    start_date: string;
    deadline: string;
    completion_progress: number;
}

const form = useForm<ProjectForm>({
    title: '',
    client_id: null,
    service_id: null,
    description: '',
    status: 'planning',
    start_date: '',
    deadline: '',
    completion_progress: 0,
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        progress: data.completion_progress,
    })).post(store.url());
};
</script>

<template>
    <Head title="Create New Project" />

    <div class="w-full space-y-6 p-4 sm:p-6">
        <form @submit.prevent="submit" class="space-y-6">
            <!-- Top Navigation Bar & Action Bar -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b border-slate-200/80 pb-5 dark:border-slate-800">
                <div>
                    <Button variant="ghost" size="sm" as-child class="-ml-2 mb-2 text-slate-500 hover:text-slate-900 dark:hover:text-slate-100">
                        <Link :href="index.url()">
                            <ArrowLeft class="mr-1.5 h-4 w-4" /> Back to Projects Portal
                        </Link>
                    </Button>
                    <div class="flex items-center gap-2.5">
                        <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-slate-50">
                            Create New Project / Practice Task
                        </h1>
                        <Badge variant="outline" class="rounded-full border-indigo-200 bg-indigo-50/60 px-2.5 py-0.5 text-xs font-semibold text-indigo-700 dark:border-indigo-900 dark:bg-indigo-950/60 dark:text-indigo-300">
                            <Sparkles class="mr-1 h-3.5 w-3.5 text-indigo-500" /> New Project
                        </Badge>
                    </div>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                        Define project title, assign to a client or internal task track, and configure initial progress.
                    </p>
                </div>

                <!-- Single Primary Top Action Bar -->
                <div class="flex items-center gap-3">
                    <Button variant="outline" type="button" as-child class="h-10 text-sm">
                        <Link :href="index.url()">Cancel</Link>
                    </Button>
                    <Button
                        type="submit"
                        :disabled="form.processing"
                        class="h-10 bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm font-semibold px-6 text-sm"
                    >
                        <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                        <FolderPlus v-else class="mr-1.5 h-4 w-4" />
                        <span>{{ form.processing ? 'Creating Project...' : 'Create Project / Task' }}</span>
                    </Button>
                </div>
            </div>

            <!-- 2-Column Enterprise Form Body -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
                <!-- Left Column (7 cols): Project Scope & Overview Card -->
                <div class="lg:col-span-7">
                    <Card class="h-full border-slate-200/80 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                        <CardHeader class="border-b border-slate-200/80 pb-4 dark:border-slate-800">
                            <div class="flex items-center gap-2 text-indigo-600 dark:text-indigo-400">
                                <FolderKanban class="h-5 w-5" />
                                <CardTitle class="text-lg font-bold text-slate-900 dark:text-slate-100">
                                    Project Scope & Schedule
                                </CardTitle>
                            </div>
                            <CardDescription class="text-xs sm:text-sm text-slate-500">
                                Set project title, operational description, and start/deadline schedule.
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="p-6 space-y-5">
                            <!-- Project Title Field -->
                            <div class="space-y-1.5">
                                <Label for="project-title" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    Project / Task Title <span class="text-rose-500">*</span>
                                </Label>
                                <div class="relative">
                                    <FolderPlus class="absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                                    <Input
                                        id="project-title"
                                        v-model="form.title"
                                        type="text"
                                        placeholder="e.g. E-Commerce Redesign or Web Development Task"
                                        required
                                        class="pl-10 h-10 text-sm border-slate-200 focus:border-indigo-500 dark:border-slate-800 dark:bg-slate-950"
                                    />
                                </div>
                                <InputError :message="form.errors.title" />
                            </div>

                            <!-- Schedule Dates Grid -->
                            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                                <!-- Start Date -->
                                <div class="space-y-1.5">
                                    <Label for="project-start-date" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                        Start Date
                                    </Label>
                                    <div class="relative">
                                        <Calendar class="absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                                        <Input
                                            id="project-start-date"
                                            v-model="form.start_date"
                                            type="date"
                                            class="pl-10 h-10 text-sm border-slate-200 focus:border-indigo-500 dark:border-slate-800 dark:bg-slate-950"
                                        />
                                    </div>
                                    <InputError :message="form.errors.start_date" />
                                </div>

                                <!-- Deadline -->
                                <div class="space-y-1.5">
                                    <Label for="project-deadline" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                        Target Deadline
                                    </Label>
                                    <div class="relative">
                                        <Clock class="absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                                        <Input
                                            id="project-deadline"
                                            v-model="form.deadline"
                                            type="date"
                                            class="pl-10 h-10 text-sm border-slate-200 focus:border-indigo-500 dark:border-slate-800 dark:bg-slate-950"
                                        />
                                    </div>
                                    <InputError :message="form.errors.deadline" />
                                </div>
                            </div>

                            <!-- Description Guidelines -->
                            <div class="space-y-1.5">
                                <Label for="project-description" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    Description & Guidelines
                                </Label>
                                <textarea
                                    id="project-description"
                                    v-model="form.description"
                                    rows="5"
                                    placeholder="Enter detailed project requirements, deliverables, or student practice task guidelines..."
                                    class="w-full rounded-md border border-slate-200 p-3 text-sm text-slate-900 shadow-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100"
                                ></textarea>
                                <InputError :message="form.errors.description" />
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Right Column (5 cols): Allocation & Configuration Card -->
                <div class="lg:col-span-5">
                    <Card class="h-full border-slate-200/80 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                        <CardHeader class="border-b border-slate-200/80 pb-4 dark:border-slate-800">
                            <div class="flex items-center gap-2 text-indigo-600 dark:text-indigo-400">
                                <SlidersHorizontal class="h-5 w-5" />
                                <CardTitle class="text-lg font-bold text-slate-900 dark:text-slate-100">
                                    Allocation & Status Settings
                                </CardTitle>
                            </div>
                            <CardDescription class="text-xs sm:text-sm text-slate-500">
                                Link to a client or tag as an internal practice task.
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="p-6 space-y-5">
                            <!-- Client Selection -->
                            <div class="space-y-1.5">
                                <Label for="project-client" class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                    <Building2 class="h-4 w-4 text-slate-400" /> Client Assignment
                                </Label>
                                <select
                                    id="project-client"
                                    v-model="form.client_id"
                                    class="h-10 w-full rounded-md border border-slate-200 bg-white px-3 text-sm text-slate-900 shadow-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100"
                                >
                                    <option :value="null">⚙️ Internal Practice Task (No Client)</option>
                                    <option
                                        v-for="client in clients"
                                        :key="client.id"
                                        :value="client.id"
                                    >
                                        📁 {{ client.name }} {{ client.company_name ? `(${client.company_name})` : '' }}
                                    </option>
                                </select>
                                <p class="text-xs text-slate-400">
                                    Select client if this is a paid deliverable, or leave as internal task.
                                </p>
                                <InputError :message="form.errors.client_id" />
                            </div>

                            <!-- Service Category Selection -->
                            <div class="space-y-1.5">
                                <Label for="project-service" class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                    <Briefcase class="h-4 w-4 text-slate-400" /> Service Track
                                </Label>
                                <select
                                    id="project-service"
                                    v-model="form.service_id"
                                    class="h-10 w-full rounded-md border border-slate-200 bg-white px-3 text-sm text-slate-900 shadow-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100"
                                >
                                    <option :value="null">-- Select Service Track --</option>
                                    <option
                                        v-for="service in services"
                                        :key="service.id"
                                        :value="service.id"
                                    >
                                        {{ service.name }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.service_id" />
                            </div>

                            <!-- Initial Status -->
                            <div class="space-y-1.5">
                                <Label for="project-status" class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                    Initial Status <span class="text-rose-500">*</span>
                                </Label>
                                <select
                                    id="project-status"
                                    v-model="form.status"
                                    class="h-10 w-full rounded-md border border-slate-200 bg-white px-3 text-sm text-slate-900 shadow-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100"
                                >
                                    <option value="planning">🔵 Planning</option>
                                    <option value="in_progress">🟡 In Progress</option>
                                    <option value="under_review">🟣 Under Review</option>
                                    <option value="on_hold">⚪ On Hold</option>
                                    <option value="completed">🟢 Completed</option>
                                    <option value="cancelled">🔴 Cancelled</option>
                                </select>
                                <InputError :message="form.errors.status" />
                            </div>

                            <!-- Interactive Progress Slider -->
                            <div class="space-y-2 pt-2 border-t border-slate-200/60 dark:border-slate-800">
                                <div class="flex items-center justify-between">
                                    <Label class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                        Initial Completion Progress
                                    </Label>
                                    <Badge variant="outline" class="font-extrabold text-sm text-indigo-600 dark:text-indigo-400">
                                        {{ form.completion_progress }}%
                                    </Badge>
                                </div>
                                <input
                                    v-model.number="form.completion_progress"
                                    type="range"
                                    min="0"
                                    max="100"
                                    class="w-full accent-indigo-600 h-2 bg-slate-200 rounded-lg cursor-pointer dark:bg-slate-800"
                                />
                                <div class="flex justify-between text-xs text-slate-400 font-mono">
                                    <span>0% (Not Started)</span>
                                    <span>50% (In Mid)</span>
                                    <span>100% (Done)</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </form>
    </div>
</template>