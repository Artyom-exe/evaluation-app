<script setup>
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { Button } from '@/Components/ui/button';
import { Textarea } from "@/Components/ui/textarea";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter, DialogDescription } from "@/Components/ui/dialog";
import { Input } from "@/Components/ui/input";
import { Label } from "@/Components/ui/label";

// Ajouter les constantes pour les IDs des descriptions
const NEW_PROFESSOR_DIALOG_DESC = 'module-new-professor-description';

const props = defineProps({
    module: {
        type: Object,
        required: true
    },
    professors: Array,
    years: Array,
});

const emit = defineEmits(['showAlert']);
const isExpanded = ref(false);
const isLoading = ref(false);
const activeTab = ref('details'); // Nouvelle ref pour la gestion des onglets

// État initial des données
const formData = ref({
    studentEmails: props.module.students?.map(s => s.email).join(', ') || '',
    professor_id: props.module.professor?.id ? String(props.module.professor.id) : '',
    year_id: props.module.year?.id ? String(props.module.year.id) : '' // Correction de l'initialisation de year_id
});

const selectedYear = ref({
    value: props.module.year?.id ? String(props.module.year.id) : '',
    label: props.module.year?.name || 'Sélectionner une année'
});

// Ajouter un watch pour mettre à jour le label quand le module change
watch(() => props.module.year, (newYear) => {
    selectedYear.value = {
        value: newYear?.id ? String(newYear.id) : '',
        label: newYear?.name || 'Sélectionner une année'
    };
}, { immediate: true });

const selectedProf = ref({
    value: props.module.professor?.id ? String(props.module.professor.id) : '',
    label: props.module.professor?.name || 'Sélectionner un professeur'
});

// Gestion des modifications
const hasChanges = computed(() => {
    const currentProfessorId = String(props.module.professor?.id || '');
    const currentYearId = String(props.module.year?.id || '');

    return formData.value.professor_id !== currentProfessorId ||
           formData.value.year_id !== currentYearId;
});

const hasStudentChanges = computed(() => {
    const originalEmails = props.module.students?.map(s => s.email).join(', ') || '';
    return formData.value.studentEmails !== originalEmails;
});

// Fonction de sauvegarde unifiée
const saveChanges = async () => {
    isLoading.value = true;
    try {
        if (hasChanges.value) {
            await router.put(route('modules.updateProfessorAndYear', props.module.id), {
                professor_id: formData.value.professor_id,
                year_id: formData.value.year_id
            });
        }

        if (hasStudentChanges.value) {
            await router.put(route('modules.updateStudents', props.module.id), {
                emails: formData.value.studentEmails
            });
        }

        emit('showAlert', 'Module mis à jour avec succès', 'success');
        isExpanded.value = false;
    } catch (error) {
        emit('showAlert', 'Erreur lors de la mise à jour', 'error');
    } finally {
        isLoading.value = false;
    }
};

// Ajouter une constante pour l'image par défaut
const defaultImage = 'https://placehold.co/600x400/e2e8f0/475569?text=Module';

const deleteModule = async () => {
    if (!confirm('Voulez-vous vraiment supprimer ce module ?')) {
        return;
    }

    await router.delete(route('modules.destroy', props.module.id), {
        preserveScroll: true,
        onSuccess: () => {
            emit('showAlert', 'Module supprimé avec succès', 'success');
        },
        onError: (error) => {
            // Accès correct à l'erreur
            const errorMessage = error.error || "Impossible de supprimer ce module";
            emit('showAlert', errorMessage, 'error');
        }
    });
};

const saveStudents = async () => {
    isLoading.value = true;
    try {
        await router.put(route('modules.updateStudents', props.module.id), {
            emails: formData.value.studentEmails
        }, {
            preserveScroll: true,
            onSuccess: () => {
                emit('showAlert', 'Étudiants mis à jour avec succès', 'success');
                isExpanded.value = false;
            },
            onError: (errors) => {
                emit('showAlert', 'Erreur lors de la mise à jour des étudiants', 'error');
            }
        });
    } finally {
        isLoading.value = false;
    }
};

const showNewProfessorDialog = ref(false);
const newProfessor = ref({ name: '', email: '' });
const isCreatingProfessor = ref(false);

const createProfessor = async () => {
    isCreatingProfessor.value = true;
    try {
        await router.post(route('professors.store'), newProfessor.value, {
            preserveScroll: true,
            onSuccess: () => {
                showNewProfessorDialog.value = false;
                emit('showAlert', 'Professeur ajouté avec succès', 'success');
                newProfessor.value = { name: '', email: '' };
            },
            onError: () => {
                emit('showAlert', 'Erreur lors de la création du professeur', 'error');
            }
        });
    } finally {
        isCreatingProfessor.value = false;
    }
};

const showImagePreview = ref(false);
const isHovered = ref(false);

// Ajout de computed properties pour une meilleure gestion des états
const cardStateClass = computed(() => ({
    'transform scale-100 shadow-lg': isExpanded.value || isHovered.value,
    'transform scale-98 hover:scale-100': !isExpanded.value,
    'opacity-95 hover:opacity-100': !isExpanded.value,
}));

const statusIndicator = computed(() => {
    if (!props.module.professor) return { color: 'bg-yellow-400', text: 'En attente de professeur' };
    if (!props.module.students?.length) return { color: 'bg-orange-400', text: 'Sans étudiants' };
    return { color: 'bg-green-400', text: 'Actif' };
});
</script>

<template>
    <div class="relative bg-white rounded-lg overflow-hidden transition-all duration-300 ease-out"
         :class="cardStateClass"
         @mouseenter="isHovered = true"
         @mouseleave="isHovered = false">

        <!-- Status indicator -->
        <div class="absolute top-2 left-2 z-10 flex items-center gap-2">
            <span class="flex items-center gap-1.5">
                <span :class="[statusIndicator.color, 'w-2 h-2 rounded-full']"></span>
                <span class="text-xs font-medium text-gray-600 bg-white/90 px-2 py-0.5 rounded-full">
                    {{ statusIndicator.text }}
                </span>
            </span>
        </div>

        <!-- Non-expanded view -->
        <div v-if="!isExpanded" class="h-full">
            <div class="cursor-pointer group" @click="isExpanded = true">
                <!-- Image container avec preview -->
                <div class="relative aspect-w-16 aspect-h-9 group">
                    <img :src="module.image_path || defaultImage"
                         :alt="module.name"
                         class="object-cover w-full h-full transition-transform duration-300"
                         :class="{'scale-105': isHovered}"
                    />
                    <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                        <Button variant="secondary" class="bg-white/90 hover:bg-white">
                            <i class="ri-edit-line mr-1"></i>
                            Modifier
                        </Button>
                    </div>
                </div>

                <!-- Content -->
                <div class="p-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-base font-semibold group-hover:text-blue-600 line-clamp-1">
                            {{ module.name }}
                        </h3>
                    </div>

                    <!-- Info grid -->
                    <div class="grid grid-cols-2 gap-3">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2 text-sm text-gray-600">
                                <i class="ri-user-line w-4"></i>
                                <span class="truncate">{{ module.professor?.name || 'Non assigné' }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm text-gray-600">
                                <i class="ri-calendar-line w-4"></i>
                                <span class="truncate">{{ module.year?.name || 'Non défini' }}</span>
                            </div>
                        </div>
                        <div class="flex flex-col items-end justify-center">
                            <div class="text-2xl font-semibold text-gray-900">
                                {{ module.students?.length || 0 }}
                            </div>
                            <div class="text-sm text-gray-500">Étudiants</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Expanded view -->
        <div v-else>
            <div class="flex flex-col">
                <!-- En-tête -->
                <div class="flex items-center justify-between p-4 border-b">
                    <h3 class="text-lg font-semibold">{{ module.name }}</h3>
                    <Button variant="ghost" size="sm" @click="isExpanded = false">
                        <i class="ri-close-line"></i>
                    </Button>
                </div>

                <!-- Onglets -->
                <div class="flex border-b">
                    <button v-for="tab in ['details', 'students']"
                            :key="tab"
                            @click="activeTab = tab"
                            class="flex-1 px-4 py-2 border-b-2 transition-colors text-sm font-medium"
                            :class="[
                                activeTab === tab
                                    ? 'border-blue-500 text-blue-600 bg-blue-50/50'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                            ]"
                    >
                        <i :class="tab === 'details' ? 'ri-settings-4-line mr-1' : 'ri-team-line mr-1'"></i>
                        {{ tab === 'details' ? 'Détails' : 'Étudiants' }}
                    </button>
                </div>

                <!-- Contenu des onglets avec scroll contrôlé -->
                <div class="flex-1">
                    <div class="p-4">
                        <div v-if="activeTab === 'details'" class="space-y-4">
                            <div class="space-y-2">
                                <div class="flex items-center justify-between">
                                    <label class="text-sm font-medium">Professeur</label>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        @click="showNewProfessorDialog = true"
                                        class="text-blue-600 hover:text-blue-700"
                                    >
                                        <i class="ri-add-line mr-1"></i>
                                        Nouveau
                                    </Button>
                                </div>
                                <Select v-model="formData.professor_id">
                                    <SelectTrigger>
                                        <SelectValue :placeholder="selectedProf.label" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="professor in professors"
                                            :key="professor.id"
                                            :value="String(professor.id)"
                                        >
                                            {{ professor.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium">Année</label>
                                <Select v-model="formData.year_id">
                                    <SelectTrigger>
                                        <SelectValue :placeholder="selectedYear.label" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="year in years"
                                            :key="year.id"
                                            :value="String(year.id)"
                                        >
                                            {{ year.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div v-else-if="activeTab === 'students'" class="space-y-4">
                            <div class="space-y-2">
                                <label class="text-sm font-medium">Emails des étudiants</label>
                                <Textarea
                                    v-model="formData.studentEmails"
                                    placeholder="Entrez les emails séparés par des virgules"
                                    rows="8"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions avec position fixe -->
                <div class="border-t p-4 bg-gray-50 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-500">
                            Dernière modification: {{ new Date(module.updated_at).toLocaleDateString() }}
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <Button variant="outline" @click="isExpanded = false">
                            Annuler
                        </Button>
                        <Button
                            @click="saveChanges"
                            :disabled="isLoading || (!hasChanges && !hasStudentChanges)"
                            :class="{'opacity-50': !hasChanges && !hasStudentChanges}"
                        >
                            <i class="ri-save-line mr-1"></i>
                            {{ isLoading ? 'Enregistrement...' : 'Enregistrer' }}
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick actions -->
        <div v-if="!isExpanded"
             class="absolute top-2 right-2 flex items-center gap-1 opacity-0 transition-opacity"
             :class="{'opacity-100': isHovered}">
            <Button
                variant="ghost"
                size="sm"
                class="bg-white/90 hover:bg-white shadow-sm"
                @click.stop="isExpanded = true"
            >
                <i class="ri-edit-line"></i>
            </Button>
            <Button
                variant="ghost"
                size="sm"
                class="bg-white/90 hover:bg-red-50 text-red-500 hover:text-red-600 shadow-sm"
                @click.stop="deleteModule"
            >
                <i class="ri-delete-bin-line"></i>
            </Button>
        </div>
    </div>

    <!-- Dialog pour nouveau professeur -->
    <Dialog :open="showNewProfessorDialog" @close="showNewProfessorDialog = false">
        <DialogContent :aria-describedby="NEW_PROFESSOR_DIALOG_DESC">
            <DialogHeader>
                <DialogTitle>Nouveau professeur</DialogTitle>
                <DialogDescription :id="NEW_PROFESSOR_DIALOG_DESC">
                    Ajouter un nouveau professeur à la liste
                </DialogDescription>
            </DialogHeader>
            <div class="space-y-4">
                <div class="space-y-2">
                    <Label for="profName">Nom du professeur</Label>
                    <Input id="profName" v-model="newProfessor.name" placeholder="Nom complet" />
                </div>
                <div class="space-y-2">
                    <Label for="profEmail">Email</Label>
                    <Input id="profEmail" v-model="newProfessor.email" type="email" placeholder="Email" />
                </div>
            </div>
            <DialogFooter>
                <Button variant="outline" @click="showNewProfessorDialog = false">Annuler</Button>
                <Button @click="createProfessor" :disabled="isCreatingProfessor">
                    {{ isCreatingProfessor ? 'Création...' : 'Créer' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>

<style scoped>
.scale-98 {
    transform: scale(0.98);
}

.card-transition-enter-active,
.card-transition-leave-active {
    transition: all 0.3s ease;
}

.card-transition-enter-from,
.card-transition-leave-to {
    opacity: 0;
    transform: scale(0.95);
}
</style>
