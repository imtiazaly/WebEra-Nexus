<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { index, update } from '@/routes/internships';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent } from '@/components/ui/card';
import { ArrowLeft, Sparkles, Pencil, Calendar, Layers, Tag, ShieldCheck } from '@lucide/vue';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Internship Academy Portal', href: index.url() },
            { title: 'Edit Batch', href: '#' },
        ],
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
    service_id: number;
}

const props = defineProps<{
    internship: Internship;
    services: Service[];
}>();

const form = useForm({
    name: props.internship.name,
    batch_no: props.internship.batch_no,
    service_id: props.internship.service_id,
    start_date: props.internship.start_date,
    end_date: props.internship.end_date,
    status: props.internship.status,
});

const submit = () => {
    form.put(update.url(props.internship.id));
};
</script>

<template>
    <Head title="Edit Internship Batch" />

    <div class="mx-auto max-w-3xl space-y-6 p-4 sm:p-6 lg:p-8">
        <!-- Top Title Bar -->
        <div class="flex items-center justify-between">
            <div class="space-y-1">
                <div class="inline-flex items-center gap-2 rounded-full border border-indigo-500/20 bg-indigo-500/10 px-3 py-0.5 text-xs font-semibold text-indigo-600 dark:text-indigo-400">
                    <Sparkles class="h-3.5 w-3.5 text-indigo-500" />
                    <span>Academy Batch Editor</span>
                </div>
                <h1 class="text-2xl font-black tracking-tight text-slate-900 sm:text-3xl dark:text-white">
                    Edit Batch Configuration
                </h1>
            </div>

            <Button as-child variant="ghost" size="sm" class="rounded-xl font-semibold text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white">
                <Link :href="index.url()">
                    <ArrowLeft class="mr-1.5 h-4 w-4" /> Back to Hub
                </Link>
            </Button>
        </div>

        <!-- Form Card -->
        <Card class="overflow-hidden rounded-3xl border border-slate-200/80 shadow-lg dark:border-slate-800 dark:bg-slate-900">
            <CardContent class="p-6 sm:p-8">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <!-- Batch Title -->
                        <div class="md:col-span-2 space-y-2">
                            <label class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                                <Sparkles class="h-3.5 w-3.5 text-indigo-500" />
                                Internship Batch Title *
                            </label>
                            <Input
                                v-model="form.name"
                                type="text"
                                required
                                class="h-11 rounded-xl border-slate-200 text-sm focus:border-indigo-500 dark:border-slate-800 dark:bg-slate-800/50"
                            />
                        </div>

                        <!-- Batch Code -->
                        <div class="space-y-2">
                            <label class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                                <Tag class="h-3.5 w-3.5 text-indigo-500" />
                                Batch Code / Number *
                            </label>
                            <Input
                                v-model="form.batch_no"
                                type="text"
                                required
                                class="h-11 font-mono rounded-xl border-slate-200 text-sm focus:border-indigo-500 dark:border-slate-800 dark:bg-slate-800/50"
                            />
                        </div>

                        <!-- Service Track -->
                        <div class="space-y-2">
                            <label class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                                <Layers class="h-3.5 w-3.5 text-indigo-500" />
                                Learning Track / Category *
                            </label>
                            <select
                                v-model="form.service_id"
                                required
                                class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50/70 px-3 text-sm font-medium focus:border-indigo-500 focus:bg-white dark:border-slate-800 dark:bg-slate-800/50 dark:text-slate-200"
                            >
                                <option value="" disabled>-- Select Track Category --</option>
                                <option v-for="service in services" :key="service.id" :value="service.id">
                                    {{ service.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Start Date -->
                        <div class="space-y-2">
                            <label class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                                <Calendar class="h-3.5 w-3.5 text-indigo-500" />
                                Start Date *
                            </label>
                            <Input
                                v-model="form.start_date"
                                type="date"
                                required
                                class="h-11 rounded-xl border-slate-200 text-sm focus:border-indigo-500 dark:border-slate-800 dark:bg-slate-800/50"
                            />
                        </div>

                        <!-- End Date -->
                        <div class="space-y-2">
                            <label class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                                <Calendar class="h-3.5 w-3.5 text-indigo-500" />
                                Target Completion Date *
                            </label>
                            <Input
                                v-model="form.end_date"
                                type="date"
                                required
                                class="h-11 rounded-xl border-slate-200 text-sm focus:border-indigo-500 dark:border-slate-800 dark:bg-slate-800/50"
                            />
                        </div>

                        <!-- Status -->
                        <div class="md:col-span-2 space-y-2">
                            <label class="flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                                <ShieldCheck class="h-3.5 w-3.5 text-indigo-500" />
                                Batch Status *
                            </label>
                            <select
                                v-model="form.status"
                                class="h-11 w-full rounded-xl border border-slate-200 bg-slate-50/70 px-3 text-sm font-medium focus:border-indigo-500 focus:bg-white dark:border-slate-800 dark:bg-slate-800/50 dark:text-slate-200"
                            >
                                <option value="upcoming">Upcoming Batch</option>
                                <option value="active">Active (Ongoing Now)</option>
                                <option value="completed">Completed / Graduated</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-slate-200/80 pt-6 dark:border-slate-800">
                        <Button as-child variant="outline" class="rounded-xl font-semibold">
                            <Link :href="index.url()">Cancel</Link>
                        </Button>
                        <Button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-xl bg-indigo-600 font-bold text-white shadow-lg hover:bg-indigo-500"
                        >
                            <Pencil class="mr-1.5 h-4 w-4" /> Save Batch Changes
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    </div>
</template>
