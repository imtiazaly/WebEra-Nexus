<script setup lang="ts">
import { computed } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import { index, show, update } from "@/routes/students";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
    CardDescription,
} from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Avatar, AvatarFallback } from "@/components/ui/avatar";
import InputError from "@/components/InputError.vue";
import {
    ArrowLeft,
    User,
    Mail,
    Phone,
    GraduationCap,
    Target,
    Loader2,
    Pencil,
    UserCheck,
    CheckCircle2,
} from "@lucide/vue";

defineOptions({
    layout: {
        breadcrumbs: [
            { title: "Students Portal", href: index.url() },
            { title: "Edit Student", href: "#" },
        ],
    },
});

interface Internship {
    id: number;
    name: string;
    batch_no: string;
}

interface Student {
    id: number;
    name: string;
    email: string;
    phone: string | null;
    status: string;
    overall_progress: number;
    internship_id: number;
    created_at?: string;
    internships?: Internship[];
}

const props = defineProps<{
    student: Student;
    internships: Internship[];
}>();

const getInitials = (name: string) => {
    if (!name) return "ST";
    const parts = name.trim().split(" ");
    if (parts.length >= 2) {
        return `${parts[0][0]}${parts[1][0]}`.toUpperCase();
    }
    return name.slice(0, 2).toUpperCase();
};

const initialSelectedInternships = computed(() => {
    const list = (props.student.internships || []).map((b) => b.id);
    if (props.student.internship_id && !list.includes(props.student.internship_id)) {
        list.push(props.student.internship_id);
    }
    return list;
});

const form = useForm({
    name: props.student.name || "",
    email: props.student.email || "",
    phone: props.student.phone || "",
    internship_id: props.student.internship_id || "",
    selected_internships: [...initialSelectedInternships.value],
    status: props.student.status || "enrolled",
    overall_progress: props.student.overall_progress || 0,
});

const selectedBatch = computed(() => {
    if (!form.internship_id) return null;
    return props.internships.find((b) => b.id === Number(form.internship_id));
});

const toggleBatchSelection = (batchId: number) => {
    const idx = form.selected_internships.indexOf(batchId);
    if (idx > -1) {
        form.selected_internships.splice(idx, 1);
    } else {
        form.selected_internships.push(batchId);
    }
};

const onPrimaryBatchChange = () => {
    if (form.internship_id && !form.selected_internships.includes(Number(form.internship_id))) {
        form.selected_internships.push(Number(form.internship_id));
    }
};

const submit = () => {
    form.put(update.url(props.student.id));
};
</script>

<template>
    <Head :title="`Edit ${student.name}`" />

    <div class="w-full space-y-6 p-4 sm:p-6">
        <form @submit.prevent="submit" class="space-y-6">
            <!-- Back Navigation & Top Header Bar -->
            <div
                class="flex flex-col gap-4 border-b border-slate-200/80 pb-5 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800"
            >
                <div class="space-y-1">
                    <Button
                        variant="ghost"
                        size="sm"
                        as-child
                        class="mb-1 -ml-2 text-slate-500 hover:text-slate-900 dark:hover:text-slate-100"
                    >
                        <Link :href="show.url(student.id)">
                            <ArrowLeft class="mr-1.5 h-4 w-4" /> Back to Student Profile
                        </Link>
                    </Button>
                    <div class="flex items-center gap-3">
                        <Avatar
                            class="h-10 w-10 border border-slate-200 shadow-xs dark:border-slate-700"
                        >
                            <AvatarFallback
                                class="bg-indigo-600 text-sm font-bold text-white"
                            >
                                {{ getInitials(student.name) }}
                            </AvatarFallback>
                        </Avatar>
                        <div>
                            <div class="flex items-center gap-2">
                                <h1
                                    class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-slate-50"
                                >
                                    Edit Student Record
                                </h1>
                                <Badge
                                    variant="outline"
                                    class="font-mono text-xs"
                                >
                                    #STD-{{ student.id }}
                                </Badge>
                            </div>
                            <p class="text-xs text-slate-500 sm:text-sm dark:text-slate-400">
                                Update candidate contact information, batch assignment, and overall completion progress for
                                <span class="font-semibold text-slate-700 dark:text-slate-300">{{ student.name }}</span>.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Primary Top Action Bar -->
                <div class="flex items-center gap-3">
                    <Button
                        variant="outline"
                        type="button"
                        as-child
                        class="h-10 text-xs"
                    >
                        <Link :href="show.url(student.id)">Cancel</Link>
                    </Button>
                    <Button
                        type="submit"
                        :disabled="form.processing"
                        class="h-10 bg-indigo-600 px-6 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700"
                    >
                        <Loader2
                            v-if="form.processing"
                            class="mr-2 h-4 w-4 animate-spin"
                        />
                        <Pencil v-else class="mr-1.5 h-3.5 w-3.5" />
                        <span>{{
                            form.processing
                                ? "Updating Profile..."
                                : "Update Student Profile"
                        }}</span>
                    </Button>
                </div>
            </div>

            <!-- 2-Column Main Form Body -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
                <!-- Left Column (7 cols): Candidate Personal & Contact Information -->
                <div class="lg:col-span-7">
                    <Card class="h-full border-slate-200/80 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                        <CardHeader class="border-b border-slate-200/80 pb-4 dark:border-slate-800">
                            <div class="flex items-center gap-2 text-indigo-600 dark:text-indigo-400">
                                <UserCheck class="h-5 w-5" />
                                <CardTitle class="text-base font-bold text-slate-900 dark:text-slate-100">
                                    Candidate Identity & Contact Information
                                </CardTitle>
                            </div>
                            <CardDescription class="text-xs text-slate-500">
                                Modify name, email address, phone, and enrollment status.
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-5 p-6">
                            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                                <!-- Full Name -->
                                <div class="space-y-1.5 md:col-span-2">
                                    <Label for="student-name" class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                                        Full Name <span class="text-rose-500">*</span>
                                    </Label>
                                    <div class="relative">
                                        <User class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-slate-400" />
                                        <Input
                                            id="student-name"
                                            v-model="form.name"
                                            type="text"
                                            placeholder="e.g. Ali Raza"
                                            required
                                            class="h-10 border-slate-200 pl-9 text-xs focus:border-indigo-500 dark:border-slate-800 dark:bg-slate-950"
                                        />
                                    </div>
                                    <InputError :message="form.errors.name" />
                                </div>

                                <!-- Email Address -->
                                <div class="space-y-1.5">
                                    <Label for="student-email" class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                                        Email Address <span class="text-rose-500">*</span>
                                    </Label>
                                    <div class="relative">
                                        <Mail class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-slate-400" />
                                        <Input
                                            id="student-email"
                                            v-model="form.email"
                                            type="email"
                                            placeholder="e.g. ali@example.com"
                                            required
                                            class="h-10 border-slate-200 pl-9 text-xs focus:border-indigo-500 dark:border-slate-800 dark:bg-slate-950"
                                        />
                                    </div>
                                    <InputError :message="form.errors.email" />
                                </div>

                                <!-- Phone / WhatsApp Number -->
                                <div class="space-y-1.5">
                                    <Label for="student-phone" class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                                        Phone / WhatsApp Number
                                    </Label>
                                    <div class="relative">
                                        <Phone class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-slate-400" />
                                        <Input
                                            id="student-phone"
                                            v-model="form.phone"
                                            type="text"
                                            placeholder="e.g. +92 300 1234567"
                                            class="h-10 border-slate-200 pl-9 text-xs focus:border-indigo-500 dark:border-slate-800 dark:bg-slate-950"
                                        />
                                    </div>
                                    <InputError :message="form.errors.phone" />
                                </div>

                                <!-- Enrollment Status -->
                                <div class="space-y-1.5 md:col-span-2">
                                    <Label for="student-status" class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                                        Enrollment Lifecycle Status <span class="text-rose-500">*</span>
                                    </Label>
                                    <select
                                        id="student-status"
                                        v-model="form.status"
                                        required
                                        class="h-10 w-full rounded-md border border-slate-200 bg-white px-3 text-xs text-slate-900 shadow-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100"
                                    >
                                        <option value="enrolled">🔵 Enrolled (Registration Complete)</option>
                                        <option value="active">🟢 Active (Currently In Training)</option>
                                        <option value="completed">🟣 Completed (Graduated Candidate)</option>
                                        <option value="dropped_out">🔴 Dropped Out (Inactive / Left Batch)</option>
                                    </select>
                                    <InputError :message="form.errors.status" />
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Right Column (5 cols): Internship Batch & Overall Progress Card -->
                <div class="lg:col-span-5 space-y-6">
                    <!-- Internship Batch Card -->
                    <Card class="border-slate-200/80 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                        <CardHeader class="border-b border-slate-200/80 pb-4 dark:border-slate-800">
                            <div class="flex items-center gap-2 text-indigo-600 dark:text-indigo-400">
                                <GraduationCap class="h-5 w-5" />
                                <CardTitle class="text-base font-bold text-slate-900 dark:text-slate-100">
                                    Internship Batch Allocation
                                </CardTitle>
                            </div>
                            <CardDescription class="text-xs text-slate-500">
                                Change or re-assign student training batch.
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-5 p-6">
                            <div class="space-y-1.5">
                                <Label for="internship-id" class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                                    Primary Active Internship Batch <span class="text-rose-500">*</span>
                                </Label>
                                <select
                                    id="internship-id"
                                    v-model="form.internship_id"
                                    @change="onPrimaryBatchChange"
                                    required
                                    class="h-10 w-full rounded-md border border-slate-200 bg-white px-3 text-xs text-slate-900 shadow-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-100"
                                >
                                    <option value="" disabled>-- Select Primary Internship Batch --</option>
                                    <option
                                        v-for="batch in internships"
                                        :key="batch.id"
                                        :value="batch.id"
                                    >
                                        {{ batch.name }} (Batch {{ batch.batch_no }})
                                    </option>
                                </select>
                                <InputError :message="form.errors.internship_id" />
                            </div>

                            <!-- Selected Batch Preview Card -->
                            <div
                                v-if="selectedBatch"
                                class="rounded-xl border border-indigo-200 bg-indigo-50/40 p-4 dark:border-indigo-900/60 dark:bg-indigo-950/30 space-y-2"
                            >
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold text-indigo-900 dark:text-indigo-200">
                                        {{ selectedBatch.name }}
                                    </span>
                                    <Badge variant="default" class="bg-indigo-600 text-[10px]">
                                        Batch {{ selectedBatch.batch_no }}
                                    </Badge>
                                </div>
                                <p class="text-[11px] text-indigo-700 dark:text-indigo-300">
                                    Primary active training batch currently set for candidate.
                                </p>
                            </div>

                            <!-- Multi-Batch Enrollment Matrix -->
                            <div class="pt-2 border-t border-slate-100 dark:border-slate-800 space-y-2.5">
                                <Label class="text-xs font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                    <GraduationCap class="h-3.5 w-3.5 text-indigo-500" />
                                    Multi-Batch Enrollment Matrix
                                </Label>
                                <p class="text-[11px] text-slate-500">
                                    Check or uncheck batches to manage student enrolment history across multiple programs.
                                </p>
                                <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                                    <label
                                        v-for="b in internships"
                                        :key="'edit-multi-' + b.id"
                                        class="flex items-center justify-between rounded-lg border p-2.5 text-xs transition-colors cursor-pointer"
                                        :class="[
                                            form.selected_internships.includes(b.id)
                                                ? 'border-indigo-300 bg-indigo-50/50 dark:border-indigo-800 dark:bg-indigo-950/40'
                                                : 'border-slate-200 hover:border-slate-300 dark:border-slate-800 dark:hover:border-slate-700'
                                        ]"
                                    >
                                        <div class="flex items-center gap-2.5">
                                            <input
                                                type="checkbox"
                                                :checked="form.selected_internships.includes(b.id)"
                                                @change="toggleBatchSelection(b.id)"
                                                class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-700"
                                            />
                                            <span class="font-medium text-slate-800 dark:text-slate-200">
                                                {{ b.name }}
                                            </span>
                                        </div>
                                        <Badge variant="outline" class="text-[10px] font-mono">
                                            Batch {{ b.batch_no }}
                                        </Badge>
                                    </label>
                                </div>
                                <InputError :message="form.errors.selected_internships" />
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Progress Tracker Card -->
                    <Card class="border-slate-200/80 shadow-xs dark:border-slate-800 dark:bg-slate-900">
                        <CardHeader class="border-b border-slate-200/80 pb-4 dark:border-slate-800">
                            <div class="flex items-center gap-2 text-indigo-600 dark:text-indigo-400">
                                <Target class="h-5 w-5" />
                                <CardTitle class="text-base font-bold text-slate-900 dark:text-slate-100">
                                    Overall Progress Tracker
                                </CardTitle>
                            </div>
                            <CardDescription class="text-xs text-slate-500">
                                Adjust current overall training completion percentage.
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4 p-6">
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <Label class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                                        Completion Rating
                                    </Label>
                                    <Badge variant="secondary" class="font-bold text-indigo-600 dark:text-indigo-400">
                                        {{ form.overall_progress }}%
                                    </Badge>
                                </div>
                                <input
                                    v-model.number="form.overall_progress"
                                    type="range"
                                    min="0"
                                    max="100"
                                    class="h-2 w-full cursor-pointer rounded-lg bg-slate-200 accent-indigo-600 dark:bg-slate-700"
                                />
                                <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                    <div
                                        class="h-full rounded-full bg-indigo-600 transition-all duration-200"
                                        :style="{ width: `${form.overall_progress}%` }"
                                    ></div>
                                </div>
                            </div>
                            <InputError :message="form.errors.overall_progress" />
                        </CardContent>
                    </Card>
                </div>
            </div>
        </form>
    </div>
</template>
