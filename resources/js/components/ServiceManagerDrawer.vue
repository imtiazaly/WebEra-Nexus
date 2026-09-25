<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import {
    X,
    Plus,
    Pencil,
    Trash2,
    Briefcase,
    Search,
    ToggleLeft,
    ToggleRight,
    Users,
} from '@lucide/vue';

interface ClientService {
    id: number;
    name: string;
    slug: string;
    type: string;
    description: string | null;
    is_active: boolean;
    clients_count?: number;
}

const props = defineProps<{
    isOpen: boolean;
    services: ClientService[];
}>();

const emit = defineEmits(['close']);

const searchQuery = ref('');
const editingServiceId = ref<number | null>(null);

// New Service Form
const newServiceForm = useForm({
    name: '',
    type: 'client',
    description: '',
    is_active: true,
});

// Edit Service Form
const editServiceForm = useForm({
    name: '',
    type: 'client',
    description: '',
    is_active: true,
});

const submitNewService = () => {
    newServiceForm.post('/services', {
        preserveScroll: true,
        onSuccess: () => {
            newServiceForm.reset('name', 'description');
        },
    });
};

const startEdit = (service: ClientService) => {
    editingServiceId.value = service.id;
    editServiceForm.name = service.name;
    editServiceForm.type = service.type || 'client';
    editServiceForm.description = service.description || '';
    editServiceForm.is_active = service.is_active;
};

const cancelEdit = () => {
    editingServiceId.value = null;
};

const submitEdit = (serviceId: number) => {
    editServiceForm.put(`/services/${serviceId}`, {
        preserveScroll: true,
        onSuccess: () => {
            editingServiceId.value = null;
        },
    });
};

const toggleServiceStatus = (serviceId: number) => {
    router.post(`/services/${serviceId}/toggle`, {}, { preserveScroll: true });
};

const deleteService = (service: ClientService) => {
    if (confirm(`Are you sure you want to delete or archive '${service.name}'?`)) {
        router.delete(`/services/${service.id}`, { preserveScroll: true });
    }
};

const filteredServices = computed(() => {
    const query = searchQuery.value.toLowerCase().trim();
    if (!query) return props.services;
    return props.services.filter(
        (s) =>
            s.name.toLowerCase().includes(query) ||
            (s.description && s.description.toLowerCase().includes(query))
    );
});
</script>

<template>
    <!-- Drawer Overlay Backdrop -->
    <div
        v-if="isOpen"
        class="fixed inset-0 z-50 flex justify-end bg-slate-950/60 backdrop-blur-xs transition-all duration-300"
        @click.self="emit('close')"
    >
        <!-- Drawer Panel -->
        <div
            class="relative flex h-full w-full max-w-xl flex-col border-l border-slate-200 bg-white p-6 shadow-2xl transition-all dark:border-slate-800 dark:bg-slate-900"
        >
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-slate-200/80 pb-4 dark:border-slate-800">
                <div class="space-y-1">
                    <div class="inline-flex items-center gap-2 rounded-full border border-indigo-200 bg-indigo-50 px-3 py-0.5 text-xs font-semibold text-indigo-700 dark:border-indigo-400/30 dark:bg-indigo-500/10 dark:text-indigo-300">
                        <Briefcase class="h-3.5 w-3.5 text-indigo-500" />
                        <span>Services Management Studio</span>
                    </div>
                    <h2 class="text-xl font-black tracking-tight text-slate-900 dark:text-white">
                        Client Offerings & Services
                    </h2>
                </div>

                <Button variant="ghost" size="icon-sm" @click="emit('close')" class="rounded-xl text-slate-400 hover:text-slate-900 dark:hover:text-white">
                    <X class="h-5 w-5" />
                </Button>
            </div>

            <!-- Scrollable Body -->
            <div class="flex-1 overflow-y-auto space-y-6 py-6 pr-1">
                <!-- 1. ADD NEW SERVICE INLINE FORM -->
                <div class="rounded-2xl border border-indigo-500/30 bg-indigo-50/50 p-4 space-y-4 dark:border-indigo-500/20 dark:bg-indigo-950/30">
                    <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-indigo-700 dark:text-indigo-300">
                        <Plus class="h-4 w-4" />
                        <span>Add New Client Service</span>
                    </div>

                    <form @submit.prevent="submitNewService" class="space-y-3">
                        <Input
                            v-model="newServiceForm.name"
                            type="text"
                            required
                            placeholder="e.g. Mobile App Development Service"
                            class="h-10 rounded-xl border-slate-200 text-xs bg-white focus:border-indigo-500 dark:border-slate-800 dark:bg-slate-900 dark:text-white"
                        />

                        <Input
                            v-model="newServiceForm.description"
                            type="text"
                            placeholder="Service offering details or package scope..."
                            class="h-10 rounded-xl border-slate-200 text-xs bg-white focus:border-indigo-500 dark:border-slate-800 dark:bg-slate-900 dark:text-white"
                        />

                        <div class="flex justify-end">
                            <Button
                                type="submit"
                                size="sm"
                                :disabled="newServiceForm.processing"
                                class="rounded-xl bg-indigo-600 font-bold text-white shadow-sm hover:bg-indigo-500"
                            >
                                <Plus class="mr-1.5 h-3.5 w-3.5" /> Create Client Service
                            </Button>
                        </div>
                    </form>
                </div>

                <!-- 2. EXISTING SERVICES LIST -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between gap-3">
                        <div class="relative w-full">
                            <Search class="absolute top-1/2 left-3 h-3.5 w-3.5 -translate-y-1/2 text-slate-400" />
                            <Input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search client services..."
                                class="h-9 rounded-xl border-slate-200 pl-9 text-xs dark:border-slate-800 dark:bg-slate-800/50"
                            />
                        </div>
                    </div>

                    <div v-if="filteredServices.length === 0" class="py-8 text-center text-xs text-slate-400">
                        No client services found.
                    </div>

                    <div class="space-y-3">
                        <div
                            v-for="service in filteredServices"
                            :key="service.id"
                            class="rounded-2xl border border-slate-200/80 bg-slate-50/50 p-4 transition-all hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-800/40 dark:hover:bg-slate-800/70"
                        >
                            <!-- EDIT MODE -->
                            <form v-if="editingServiceId === service.id" @submit.prevent="submitEdit(service.id)" class="space-y-3">
                                <Input
                                    v-model="editServiceForm.name"
                                    type="text"
                                    required
                                    class="h-9 rounded-xl border-slate-200 text-xs bg-white dark:border-slate-700 dark:bg-slate-900"
                                />
                                <Input
                                    v-model="editServiceForm.description"
                                    type="text"
                                    class="h-9 rounded-xl border-slate-200 text-xs bg-white dark:border-slate-700 dark:bg-slate-900"
                                />
                                <div class="flex justify-end gap-2">
                                    <Button type="button" variant="ghost" size="xs" @click="cancelEdit">Cancel</Button>
                                    <Button type="submit" size="xs" class="bg-indigo-600 text-white">Save Changes</Button>
                                </div>
                            </form>

                            <!-- VIEW MODE -->
                            <div v-else class="flex items-start justify-between gap-3">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <h4 class="text-sm font-bold text-slate-900 dark:text-slate-100">
                                            {{ service.name }}
                                        </h4>
                                        <Badge
                                            :variant="service.is_active ? 'default' : 'secondary'"
                                            class="rounded-full px-2 py-0.2 text-[10px] font-bold"
                                        >
                                            {{ service.is_active ? 'Active' : 'Inactive' }}
                                        </Badge>
                                    </div>
                                    <p v-if="service.description" class="text-xs text-slate-500 dark:text-slate-400">
                                        {{ service.description }}
                                    </p>
                                    <div class="flex items-center gap-3 pt-1 text-[11px] text-slate-400">
                                        <span class="flex items-center gap-1 font-semibold text-indigo-500">
                                            <Users class="h-3.5 w-3.5" /> {{ service.clients_count || 0 }} Clients Attached
                                        </span>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center gap-1">
                                    <Button
                                        variant="ghost"
                                        size="icon-xs"
                                        @click="toggleServiceStatus(service.id)"
                                        :title="service.is_active ? 'Deactivate Service' : 'Activate Service'"
                                        class="text-slate-400 hover:text-indigo-600"
                                    >
                                        <ToggleRight v-if="service.is_active" class="h-4 w-4 text-emerald-500" />
                                        <ToggleLeft v-else class="h-4 w-4 text-slate-400" />
                                    </Button>

                                    <Button
                                        variant="ghost"
                                        size="icon-xs"
                                        @click="startEdit(service)"
                                        title="Edit Service"
                                        class="text-slate-400 hover:text-slate-900 dark:hover:text-white"
                                    >
                                        <Pencil class="h-3.5 w-3.5" />
                                    </Button>

                                    <Button
                                        variant="ghost"
                                        size="icon-xs"
                                        @click="deleteService(service)"
                                        title="Delete or Archive Service"
                                        class="text-slate-400 hover:text-rose-600"
                                    >
                                        <Trash2 class="h-3.5 w-3.5" />
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
