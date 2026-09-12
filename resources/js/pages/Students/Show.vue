<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { index, show, edit } from '@/routes/students';
import { update as updateWeeklyReport } from '@/routes/weekly-reports';
import { ref } from 'vue';

interface Project {
    id: number;
    title: string;
    pivot: {
        id: number;
        role: string;
        progress: number;
        submission_status: string;
    };
}

interface WeeklyReport {
    id: number;
    week_number: number;
    tasks_completed: string;
    learnings: string | null;
    blockers: string | null;
    status: string;
    feedback: string | null;
    ai_summary: string | null;
    submitted_at: string;
}

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
    internship: Internship | null;
    weekly_reports?: WeeklyReport[];
    projects?: Project[];
}

interface ProjectOption {
    id: number;
    title: string;
}

const props = defineProps<{
    student: Student;
    availableProjects: ProjectOption[];
}>();

// Assign Project Form
const assignForm = useForm({
    project_id: '' as string | number,
    role: 'Frontend Developer',
    progress_val: 0,
    submission_status: 'assigned',
});

const submitAssignProject = () => {
    assignForm
        .transform((data) => ({
            project_id: data.project_id,
            role: data.role,
            progress: data.progress_val,
            submission_status: data.submission_status,
        }))
        .post(route('students.assign-project', props.student.id), {
            onSuccess: () => assignForm.reset(),
        });
};

// Submit Weekly Report Form
const reportForm = useForm({
    week_number: (props.student.weekly_reports?.length || 0) + 1,
    tasks_completed: '',
    learnings: '',
    blockers: '',
    status: 'submitted',
});

const submitWeeklyReport = () => {
    reportForm.post(route('weekly-reports.store', props.student.id), {
        onSuccess: () => reportForm.reset(),
    });
};

// Review Weekly Report (Admin Feedback)
const editingReportId = ref<number | null>(null);
const reviewForm = useForm({
    status: 'approved',
    feedback: '',
});

const openReview = (report: WeeklyReport) => {
    editingReportId.value = report.id;
    reviewForm.status = report.status;
    reviewForm.feedback = report.feedback || '';
};

const submitReportReview = (reportId: number) => {
    reviewForm.put(updateWeeklyReport.url(reportId), {
        onSuccess: () => {
            editingReportId.value = null;
        },
    });
};

const formatStatus = (status: string) => {
    return status.replace('_', ' ').replace(/\b\w/g, (l) => l.toUpperCase());
};
</script>

<template>
    <Head :title="student.name" />

    <AppLayout
        :breadcrumbs="[
            { title: 'Students Portal', href: index.url() },
            { title: student.name, href: show.url(student.id) },
        ]"
    >
        <div class="mx-auto max-w-6xl space-y-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1
                        class="text-2xl font-bold text-gray-900 dark:text-white"
                    >
                        {{ student.name }}
                    </h1>
                    <div
                        class="mt-1 flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400"
                    >
                        <span>{{ student.email }}</span>
                        <span>•</span>
                        <span>{{ student.phone || 'No phone' }}</span>
                        <span>•</span>
                        <span
                            class="font-medium text-gray-800 dark:text-gray-200"
                            >{{ student.internship?.name }} ({{
                                student.internship?.batch_no
                            }})</span
                        >
                    </div>
                </div>
                <div class="space-x-3">
                    <Link
                        :href="edit.url(student.id)"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                        >Edit Profile</Link
                    >
                    <Link
                        :href="index.url()"
                        class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300"
                        >Back</Link
                    >
                </div>
            </div>

            <!-- Overall Progress Card -->
            <div
                class="space-y-3 rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900"
            >
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                        Overall Internship Performance Progress
                    </h2>
                    <span
                        class="text-2xl font-extrabold text-indigo-600 dark:text-indigo-400"
                        >{{ student.overall_progress }}%</span
                    >
                </div>
                <div
                    class="h-3 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700"
                >
                    <div
                        class="h-3 rounded-full bg-indigo-600 transition-all duration-500"
                        :style="{ width: `${student.overall_progress}%` }"
                    ></div>
                </div>
            </div>

            <!-- Main Content Grid (Left: Projects & Reports, Right: Forms) -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Left 2 Cols: Assigned Projects & Weekly Reports List -->
                <div class="space-y-6 lg:col-span-2">
                    <!-- Assigned Projects Card -->
                    <div
                        class="space-y-4 rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900"
                    >
                        <h2
                            class="text-lg font-bold text-gray-900 dark:text-white"
                        >
                            Assigned Projects & Practice Tasks
                        </h2>
                        <div
                            v-if="
                                student.projects && student.projects.length > 0
                            "
                            class="divide-y divide-gray-200 dark:divide-gray-800"
                        >
                            <div
                                v-for="proj in student.projects"
                                :key="proj.id"
                                class="space-y-2 py-3"
                            >
                                <div class="flex items-center justify-between">
                                    <span
                                        class="font-semibold text-gray-900 dark:text-white"
                                        >{{ proj.title }}</span
                                    >
                                    <span
                                        class="rounded bg-indigo-50 px-2 py-0.5 text-xs font-bold text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300"
                                    >
                                        {{
                                            formatStatus(
                                                proj.pivot.submission_status,
                                            )
                                        }}
                                    </span>
                                </div>
                                <div
                                    class="flex justify-between text-xs text-gray-500"
                                >
                                    <span>Role: {{ proj.pivot.role }}</span>
                                    <span
                                        >Progress:
                                        {{ proj.pivot.progress }}%</span
                                    >
                                </div>
                            </div>
                        </div>
                        <p
                            v-else
                            class="text-sm text-gray-500 dark:text-gray-400"
                        >
                            No projects assigned to this student yet.
                        </p>
                    </div>

                    <!-- Weekly Reports History Card -->
                    <div
                        class="space-y-4 rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900"
                    >
                        <h2
                            class="text-lg font-bold text-gray-900 dark:text-white"
                        >
                            Weekly Reports History
                        </h2>
                        <div
                            v-if="
                                student.weekly_reports &&
                                student.weekly_reports.length > 0
                            "
                            class="space-y-4"
                        >
                            <div
                                v-for="report in student.weekly_reports"
                                :key="report.id"
                                class="space-y-3 rounded-lg border border-gray-200 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-gray-800/30"
                            >
                                <div class="flex items-center justify-between">
                                    <span
                                        class="font-bold text-indigo-600 dark:text-indigo-400"
                                        >Week
                                        {{ report.week_number }} Report</span
                                    >
                                    <span
                                        class="rounded-full bg-gray-200 px-2 py-0.5 text-xs font-semibold text-gray-800 dark:bg-gray-700 dark:text-gray-200"
                                    >
                                        {{ formatStatus(report.status) }}
                                    </span>
                                </div>

                                <div
                                    class="space-y-1 text-xs text-gray-700 dark:text-gray-300"
                                >
                                    <div>
                                        <strong>Tasks:</strong>
                                        {{ report.tasks_completed }}
                                    </div>
                                    <div v-if="report.learnings">
                                        <strong>Learnings:</strong>
                                        {{ report.learnings }}
                                    </div>
                                    <div
                                        v-if="report.blockers"
                                        class="text-rose-600 dark:text-rose-400"
                                    >
                                        <strong>Blockers:</strong>
                                        {{ report.blockers }}
                                    </div>
                                </div>

                                <div
                                    v-if="report.feedback"
                                    class="rounded border border-indigo-100 bg-indigo-50 p-2.5 text-xs text-indigo-900 dark:border-indigo-900 dark:bg-indigo-950/40 dark:text-indigo-200"
                                >
                                    <strong>Admin Feedback:</strong>
                                    {{ report.feedback }}
                                </div>

                                <!-- Review Button / Inline Form -->
                                <div
                                    class="flex justify-end border-t border-gray-200 pt-2 dark:border-gray-800"
                                >
                                    <button
                                        v-if="editingReportId !== report.id"
                                        @click="openReview(report)"
                                        class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 dark:text-indigo-400"
                                    >
                                        ✏️ Review / Add Feedback
                                    </button>
                                    <form
                                        v-else
                                        @submit.prevent="
                                            submitReportReview(report.id)
                                        "
                                        class="w-full space-y-2 pt-2"
                                    >
                                        <div class="grid grid-cols-2 gap-2">
                                            <div>
                                                <label
                                                    class="block text-[10px] font-bold text-gray-500 uppercase"
                                                    >Status</label
                                                >
                                                <select
                                                    v-model="reviewForm.status"
                                                    class="w-full rounded border-gray-300 text-xs text-white dark:border-gray-700 dark:bg-gray-800"
                                                >
                                                    <option value="submitted">
                                                        Submitted
                                                    </option>
                                                    <option
                                                        value="under_review"
                                                    >
                                                        Under Review
                                                    </option>
                                                    <option value="approved">
                                                        Approved
                                                    </option>
                                                    <option
                                                        value="revision_requested"
                                                    >
                                                        Revision Requested
                                                    </option>
                                                </select>
                                            </div>
                                            <div>
                                                <label
                                                    class="block text-[10px] font-bold text-gray-500 uppercase"
                                                    >Admin Feedback</label
                                                >
                                                <input
                                                    v-model="
                                                        reviewForm.feedback
                                                    "
                                                    type="text"
                                                    placeholder="Comments..."
                                                    class="w-full rounded border-gray-300 text-xs text-white dark:border-gray-700 dark:bg-gray-800"
                                                />
                                            </div>
                                        </div>
                                        <div class="flex justify-end space-x-2">
                                            <button
                                                type="button"
                                                @click="editingReportId = null"
                                                class="text-xs text-gray-500"
                                            >
                                                Cancel
                                            </button>
                                            <button
                                                type="submit"
                                                class="rounded bg-indigo-600 px-2 py-1 text-xs font-semibold text-white"
                                            >
                                                Save Review
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <p
                            v-else
                            class="text-sm text-gray-500 dark:text-gray-400"
                        >
                            No weekly reports submitted yet.
                        </p>
                    </div>
                </div>

                <!-- Right 1 Col: Assign Project & Submit Report Forms -->
                <div class="space-y-6">
                    <!-- Assign Project Form Card -->
                    <div
                        class="space-y-4 rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900"
                    >
                        <h3
                            class="text-md font-bold text-gray-900 dark:text-white"
                        >
                            Assign Project / Task
                        </h3>
                        <form
                            @submit.prevent="submitAssignProject"
                            class="space-y-3"
                        >
                            <div>
                                <label
                                    class="block text-xs font-medium text-gray-700 dark:text-gray-300"
                                    >Select Project *</label
                                >
                                <select
                                    v-model="assignForm.project_id"
                                    required
                                    class="mt-1 block w-full rounded-lg border-gray-300 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                >
                                    <option value="" disabled>
                                        -- Select Project --
                                    </option>
                                    <option
                                        v-for="proj in availableProjects"
                                        :key="proj.id"
                                        :value="proj.id"
                                    >
                                        {{ proj.title }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label
                                    class="block text-xs font-medium text-gray-700 dark:text-gray-300"
                                    >Student Role *</label
                                >
                                <input
                                    v-model="assignForm.role"
                                    type="text"
                                    required
                                    placeholder="e.g. Frontend Developer"
                                    class="mt-1 block w-full rounded-lg border-gray-300 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                />
                            </div>

                            <div>
                                <label
                                    class="block text-xs font-medium text-gray-700 dark:text-gray-300"
                                    >Student Progress (%)</label
                                >
                                <input
                                    v-model="assignForm.progress_val"
                                    type="number"
                                    min="0"
                                    max="100"
                                    class="mt-1 block w-full rounded-lg border-gray-300 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                />
                            </div>

                            <div>
                                <label
                                    class="block text-xs font-medium text-gray-700 dark:text-gray-300"
                                    >Status</label
                                >
                                <select
                                    v-model="assignForm.submission_status"
                                    class="mt-1 block w-full rounded-lg border-gray-300 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                >
                                    <option value="assigned">Assigned</option>
                                    <option value="in_progress">
                                        In Progress
                                    </option>
                                    <option value="submitted">Submitted</option>
                                    <option value="approved">Approved</option>
                                    <option value="revision_needed">
                                        Revision Needed
                                    </option>
                                </select>
                            </div>

                            <button
                                type="submit"
                                :disabled="assignForm.processing"
                                class="w-full rounded-lg bg-indigo-600 py-2 text-xs font-semibold text-white hover:bg-indigo-700"
                            >
                                Assign Project
                            </button>
                        </form>
                    </div>

                    <!-- Submit Weekly Report Form Card -->
                    <div
                        class="space-y-4 rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900"
                    >
                        <h3
                            class="text-md font-bold text-gray-900 dark:text-white"
                        >
                            Submit Weekly Report
                        </h3>
                        <form
                            @submit.prevent="submitWeeklyReport"
                            class="space-y-3"
                        >
                            <div>
                                <label
                                    class="block text-xs font-medium text-gray-700 dark:text-gray-300"
                                    >Week Number *</label
                                >
                                <input
                                    v-model="reportForm.week_number"
                                    type="number"
                                    min="1"
                                    required
                                    class="mt-1 block w-full rounded-lg border-gray-300 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                />
                            </div>

                            <div>
                                <label
                                    class="block text-xs font-medium text-gray-700 dark:text-gray-300"
                                    >Tasks Completed *</label
                                >
                                <textarea
                                    v-model="reportForm.tasks_completed"
                                    rows="2"
                                    required
                                    placeholder="What was completed this week..."
                                    class="mt-1 block w-full rounded-lg border-gray-300 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                ></textarea>
                            </div>

                            <div>
                                <label
                                    class="block text-xs font-medium text-gray-700 dark:text-gray-300"
                                    >Learnings</label
                                >
                                <textarea
                                    v-model="reportForm.learnings"
                                    rows="2"
                                    placeholder="Key concepts learned..."
                                    class="mt-1 block w-full rounded-lg border-gray-300 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                ></textarea>
                            </div>

                            <div>
                                <label
                                    class="block text-xs font-medium text-gray-700 dark:text-gray-300"
                                    >Blockers / Challenges</label
                                >
                                <textarea
                                    v-model="reportForm.blockers"
                                    rows="2"
                                    placeholder="Any issues faced..."
                                    class="mt-1 block w-full rounded-lg border-gray-300 text-xs dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                ></textarea>
                            </div>

                            <button
                                type="submit"
                                :disabled="reportForm.processing"
                                class="w-full rounded-lg bg-emerald-600 py-2 text-xs font-semibold text-white hover:bg-emerald-700"
                            >
                                Submit Report
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
