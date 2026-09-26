<script setup lang="ts">
import { ref, computed } from "vue";
import { useForm, router } from "@inertiajs/vue3";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Badge } from "@/components/ui/badge";
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
} from "@/components/ui/dialog";
import {
    Plus,
    Pencil,
    Trash2,
    Layers,
    Search,
    ToggleLeft,
    ToggleRight,
    GraduationCap,
} from "@lucide/vue";

export interface ServiceTrack {
    id: number;
    name: string;
    slug?: string;
    type?: string;
    description?: string | null;
    is_active?: boolean;
    internships_count?: number;
}

const props = defineProps<{
    isOpen: boolean;
    tracks: ServiceTrack[];
}>();

const emit = defineEmits<{
    (e: "close"): void;
}>();

const handleOpenChange = (open: boolean) => {
    if (!open) {
        emit("close");
    }
};

const searchQuery = ref("");
const editingTrackId = ref<number | null>(null);

// New Track Form
const newTrackForm = useForm({
    name: "",
    type: "internship",
    description: "",
    is_active: true,
});

// Edit Track Form
const editTrackForm = useForm({
    name: "",
    type: "internship",
    description: "",
    is_active: true,
});

const submitNewTrack = () => {
    newTrackForm.post("/services", {
        preserveScroll: true,
        onSuccess: () => {
            newTrackForm.reset("name", "description");
        },
    });
};

const startEdit = (track: ServiceTrack) => {
    editingTrackId.value = track.id;
    editTrackForm.name = track.name;
    editTrackForm.type = track.type || "internship";
    editTrackForm.description = track.description || "";
    editTrackForm.is_active = track.is_active ?? true;
};

const cancelEdit = () => {
    editingTrackId.value = null;
};

const submitEdit = (trackId: number) => {
    editTrackForm.put(`/services/${trackId}`, {
        preserveScroll: true,
        onSuccess: () => {
            editingTrackId.value = null;
        },
    });
};

const toggleTrackStatus = (trackId: number) => {
    router.post(`/services/${trackId}/toggle`, {}, { preserveScroll: true });
};

const deleteTrack = (track: ServiceTrack) => {
    if (
        confirm(`Are you sure you want to delete or archive '${track.name}'?`)
    ) {
        router.delete(`/services/${track.id}`, { preserveScroll: true });
    }
};

const filteredTracks = computed(() => {
    const query = searchQuery.value.toLowerCase().trim();
    if (!query) return props.tracks;
    return props.tracks.filter(
        (t) =>
            t.name.toLowerCase().includes(query) ||
            (t.description && t.description.toLowerCase().includes(query)),
    );
});
</script>

<template>
    <Dialog :open="isOpen" @update:open="handleOpenChange">
        <DialogContent
            class="max-w-2xl sm:max-w-2xl max-h-[85vh] flex flex-col p-6 gap-0"
        >
            <!-- Header -->
            <DialogHeader
                class="pb-4 border-b border-slate-200/80 dark:border-slate-800"
            >
                <div class="space-y-1 text-left">
                    <div
                        class="inline-flex items-center gap-2 rounded-full border border-indigo-200 bg-indigo-50 px-3 py-0.5 text-xs font-semibold text-indigo-700 dark:border-indigo-400/30 dark:bg-indigo-500/10 dark:text-indigo-300"
                    >
                        <Layers class="h-3.5 w-3.5 text-indigo-500" />
                        <span>Academy Track Manager</span>
                    </div>
                    <DialogTitle
                        class="text-xl font-black tracking-tight text-slate-900 dark:text-white"
                    >
                        Internship Learning Tracks
                    </DialogTitle>
                    <DialogDescription
                        class="text-xs text-slate-500 dark:text-slate-400"
                    >
                        Manage learning tracks and syllabi for internships.
                    </DialogDescription>
                </div>
            </DialogHeader>

            <!-- Scrollable Body -->
            <div
                class="flex-1 overflow-y-auto scrollbar-hide space-y-6 pt-4 pr-1"
            >
                <!-- 1. ADD NEW TRACK INLINE FORM -->
                <div
                    class="rounded-2xl border border-indigo-500/30 bg-indigo-50/50 p-4 space-y-4 dark:border-indigo-500/20 dark:bg-indigo-950/30"
                >
                    <div
                        class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-indigo-700 dark:text-indigo-300"
                    >
                        <Plus class="h-4 w-4" />
                        <span>Add New Learning Track</span>
                    </div>

                    <form @submit.prevent="submitNewTrack" class="space-y-3">
                        <Input
                            v-model="newTrackForm.name"
                            type="text"
                            required
                            placeholder="e.g. Cybersecurity & Network Track"
                            class="h-10 rounded-xl border-slate-200 text-xs bg-white focus:border-indigo-500 dark:border-slate-800 dark:bg-slate-900 dark:text-white"
                        />

                        <Input
                            v-model="newTrackForm.description"
                            type="text"
                            placeholder="Brief track description or syllabus overview..."
                            class="h-10 rounded-xl border-slate-200 text-xs bg-white focus:border-indigo-500 dark:border-slate-800 dark:bg-slate-900 dark:text-white"
                        />

                        <div class="flex justify-end">
                            <Button
                                type="submit"
                                size="sm"
                                :disabled="newTrackForm.processing"
                                class="rounded-xl bg-indigo-600 font-bold text-white shadow-sm hover:bg-indigo-500"
                            >
                                <Plus class="mr-1.5 h-3.5 w-3.5" /> Create Track
                            </Button>
                        </div>
                    </form>
                </div>

                <!-- 2. EXISTING TRACKS LIST & MANAGEMENT -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between gap-3">
                        <div class="relative w-full">
                            <Search
                                class="absolute top-1/2 left-3 h-3.5 w-3.5 -translate-y-1/2 text-slate-400"
                            />
                            <Input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search active tracks..."
                                class="h-9 rounded-xl border-slate-200 pl-9 text-xs dark:border-slate-800 dark:bg-slate-800/50"
                            />
                        </div>
                    </div>

                    <div
                        v-if="filteredTracks.length === 0"
                        class="py-8 text-center text-xs text-slate-400"
                    >
                        No learning tracks found.
                    </div>

                    <div class="space-y-3">
                        <div
                            v-for="track in filteredTracks"
                            :key="track.id"
                            class="rounded-2xl border border-slate-200/80 bg-slate-50/50 p-4 transition-all hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-800/40 dark:hover:bg-slate-800/70"
                        >
                            <!-- EDIT MODE -->
                            <form
                                v-if="editingTrackId === track.id"
                                @submit.prevent="submitEdit(track.id)"
                                class="space-y-3"
                            >
                                <Input
                                    v-model="editTrackForm.name"
                                    type="text"
                                    required
                                    class="h-9 rounded-xl border-slate-200 text-xs bg-white dark:border-slate-700 dark:bg-slate-900"
                                />
                                <Input
                                    v-model="editTrackForm.description"
                                    type="text"
                                    class="h-9 rounded-xl border-slate-200 text-xs bg-white dark:border-slate-700 dark:bg-slate-900"
                                />
                                <div class="flex justify-end gap-2">
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        @click="cancelEdit"
                                        >Cancel</Button
                                    >
                                    <Button
                                        type="submit"
                                        size="sm"
                                        class="bg-indigo-600 text-white hover:bg-indigo-700"
                                    >
                                        Save Changes
                                    </Button>
                                </div>
                            </form>

                            <!-- VIEW MODE -->
                            <div
                                v-else
                                class="flex items-start justify-between gap-3"
                            >
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <h4
                                            class="text-sm font-bold text-slate-900 dark:text-slate-100"
                                        >
                                            {{ track.name }}
                                        </h4>
                                        <Badge
                                            :variant="
                                                track.is_active !== false
                                                    ? 'default'
                                                    : 'secondary'
                                            "
                                            class="rounded-full px-2 py-0.2 text-[10px] font-bold"
                                        >
                                            {{
                                                track.is_active !== false
                                                    ? "Active"
                                                    : "Inactive"
                                            }}
                                        </Badge>
                                    </div>
                                    <p
                                        v-if="track.description"
                                        class="text-xs text-slate-500 dark:text-slate-400"
                                    >
                                        {{ track.description }}
                                    </p>
                                    <div
                                        class="flex items-center gap-3 pt-1 text-[11px] text-slate-400"
                                    >
                                        <span
                                            class="flex items-center gap-1 font-semibold text-indigo-500"
                                        >
                                            <GraduationCap
                                                class="h-3.5 w-3.5"
                                            />
                                            {{ track.internships_count || 0 }}
                                            Batches Linked
                                        </span>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center gap-1">
                                    <Button
                                        variant="ghost"
                                        size="icon-lg"
                                        @click="toggleTrackStatus(track.id)"
                                        :title="
                                            track.is_active !== false
                                                ? 'Deactivate Track'
                                                : 'Activate Track'
                                        "
                                        class="text-slate-400 hover:text-indigo-600"
                                    >
                                        <ToggleRight
                                            v-if="track.is_active !== false"
                                            class="h-4 w-4 text-emerald-500"
                                        />
                                        <ToggleLeft
                                            v-else
                                            class="h-4 w-4 text-slate-400"
                                        />
                                    </Button>

                                    <Button
                                        variant="ghost"
                                        size="icon-lg"
                                        @click="startEdit(track)"
                                        title="Edit Track"
                                        class="text-slate-400 hover:text-slate-900 dark:hover:text-white"
                                    >
                                        <Pencil class="h-3.5 w-3.5" />
                                    </Button>

                                    <Button
                                        variant="ghost"
                                        size="icon-lg"
                                        @click="deleteTrack(track)"
                                        title="Delete or Archive Track"
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
        </DialogContent>
    </Dialog>
</template>

<style scoped>
.scrollbar-hide {
    scrollbar-width: none;
    -ms-overflow-style: none;
}

.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
</style>
