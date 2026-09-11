<script setup lang="ts">
import { Head, Link, useForm } from "@inertiajs/vue3";
import AppLayout from "@/layouts/AppLayout.vue";
import { index, create, store } from "@/routes/clients";
import { ref } from "vue";

interface Service {
    id: number;
    name: string;
}

const props = defineProps<{
    services: Service[];
}>();

const selectedServices = ref<
    { id: number; requirements: string; estimated_budget: string }[]
>([]);

const form = useForm({
    name: "",
    email: "",
    phone: "",
    company_name: "",
    status: "new_lead",
    notes: "",
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
            requirements: "",
            estimated_budget: "",
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
    form.post(store.url());
};
</script>

<template>
    <Head title="Create Client / Lead" />

    <AppLayout
        :breadcrumbs="[
            { title: 'Clients & Leads', href: index.url() },
            { title: 'Create Client', href: create.url() },
        ]"
    >
        <div class="max-w-4xl mx-auto p-6 space-y-6">
            <div class="flex items-center justify-between">
                <h1
                    class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white"
                >
                    Add New Client / Lead
                </h1>
                <Link
                    :href="index.url()"
                    class="text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
                    >← Back to List</Link
                >
            </div>

            <form
                @submit.prevent="submit"
                class="bg-white dark:bg-gray-900 shadow-sm rounded-xl border border-gray-200 dark:border-gray-800 p-6 space-y-6"
            >
                <!-- Basic Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >Name *</label
                        >
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        />
                        <span
                            v-if="form.errors.name"
                            class="text-xs text-rose-500 mt-1 block"
                            >{{ form.errors.name }}</span
                        >
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >Email Address *</label
                        >
                        <input
                            v-model="form.email"
                            type="email"
                            required
                            class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        />
                        <span
                            v-if="form.errors.email"
                            class="text-xs text-rose-500 mt-1 block"
                            >{{ form.errors.email }}</span
                        >
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >Phone / WhatsApp</label
                        >
                        <input
                            v-model="form.phone"
                            type="text"
                            class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        />
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >Company Name</label
                        >
                        <input
                            v-model="form.company_name"
                            type="text"
                            class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        />
                    </div>

                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >Initial Status *</label
                        >
                        <select
                            v-model="form.status"
                            class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        >
                            <option value="new_lead">New Lead</option>
                            <option value="contacted">Contacted</option>
                            <option value="converted">Converted Client</option>
                            <option value="lost">Lost</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300"
                            >Notes / Communication History</label
                        >
                        <textarea
                            v-model="form.notes"
                            rows="3"
                            class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        ></textarea>
                    </div>
                </div>

                <!-- Services Requested -->
                <div
                    class="border-t border-gray-200 dark:border-gray-800 pt-6 space-y-4"
                >
                    <h2
                        class="text-lg font-semibold text-gray-900 dark:text-white"
                    >
                        Services Requested
                    </h2>
                    <div class="grid grid-cols-1 gap-4">
                        <div
                            v-for="service in services"
                            :key="service.id"
                            class="border border-gray-200 dark:border-gray-800 rounded-lg p-4 bg-gray-50/50 dark:bg-gray-800/30 space-y-3"
                        >
                            <label
                                class="flex items-center space-x-3 cursor-pointer"
                            >
                                <input
                                    type="checkbox"
                                    :checked="isServiceSelected(service.id)"
                                    @change="toggleService(service.id)"
                                    class="rounded text-indigo-600 focus:ring-indigo-500 dark:bg-gray-800 border-gray-300"
                                />
                                <span
                                    class="font-medium text-gray-900 dark:text-white"
                                    >{{ service.name }}</span
                                >
                            </label>

                            <div
                                v-if="isServiceSelected(service.id)"
                                class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2 pl-7"
                            >
                                <div>
                                    <label
                                        class="block text-xs font-medium text-gray-600 dark:text-gray-400"
                                        >Specific Requirements</label
                                    >
                                    <textarea
                                        v-model="
                                            getServiceSelection(service.id)!
                                                .requirements
                                        "
                                        rows="2"
                                        placeholder="Describe client requirements..."
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-xs text-white"
                                    ></textarea>
                                </div>
                                <div>
                                    <label
                                        class="block text-xs font-medium text-gray-600 dark:text-gray-400"
                                        >Estimated Budget ($)</label
                                    >
                                    <input
                                        v-model="
                                            getServiceSelection(service.id)!
                                                .estimated_budget
                                        "
                                        type="number"
                                        step="0.01"
                                        placeholder="e.g. 500"
                                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-xs text-white"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="flex justify-end space-x-3 border-t border-gray-200 dark:border-gray-800 pt-6"
                >
                    <Link
                        :href="index.url()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 rounded-lg hover:bg-gray-200"
                        >Cancel</Link
                    >
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        Save Client
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
