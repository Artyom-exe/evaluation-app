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

const props = defineProps({
    module: Object,
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
</script>

<template>
    <div class="bg-white rounded-lg shadow-lg overflow-hidden transition-all duration-300 ease-out"
         :class="{ 'h-auto scale-100 opacity-100': isExpanded, 'h-[260px] scale-98 opacity-95': !isExpanded }">
        <div class="relative" :class="{ 'h-full': !isExpanded }">
            <!-- Header non-expansé -->
            <div v-if="!isExpanded" class="h-full flex flex-col">
                <div class="cursor-pointer group flex-1" @click="isExpanded = true">
                    <img :src="module.image || defaultImage"
                         class="w-full h-32 object-cover transition-opacity group-hover:opacity-90"
                         alt="Module image">
                    <div class="p-4 space-y-3">
                        <h3 class="text-base font-semibold group-hover:text-blue-600 line-clamp-1">{{ module.name }}</h3>
                        <div class="space-y-1">
                            <div class="flex items-center gap-2 text-sm text-gray-600">
                                <i class="ri-user-line w-4 flex-shrink-0"></i>
                                <span class="truncate">{{ module.professor?.name || 'Aucun professeur' }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm text-gray-600">
                                <i class="ri-calendar-line w-4 flex-shrink-0"></i>
                                <span class="truncate">{{ module.year?.name || 'Année non définie' }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm text-gray-500">
                                <i class="ri-team-line w-4 flex-shrink-0"></i>
                                <span class="truncate">{{ module.students?.length || 0 }} étudiants</span>
                            </div>
                        </div>
                    </div>
                </div>
                <Button
                    variant="ghost"
                    size="sm"
                    @click.stop="deleteModule"
                    class="absolute top-2 right-2 text-red-500 hover:text-red-700 bg-white/80 hover:bg-white"
                >
                    <i class="ri-delete-bin-line"></i>
                </Button>
            </div>

            <!-- Contenu expansé -->
            <div v-else class="bg-white w-full transition-all duration-300 ease-out"
                 :class="{ 'opacity-100 translate-y-0': isExpanded, 'opacity-0 -translate-y-4': !isExpanded }">
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
                        <button
                            @click="activeTab = 'details'"
                            :class="[
                                'px-4 py-2 border-b-2 transition-colors',
                                activeTab === 'details'
                                    ? 'border-blue-500 text-blue-600'
                                    : 'border-transparent hover:border-gray-300'
                            ]"
                        >
                            Détails
                        </button>
                        <button
                            @click="activeTab = 'students'"
                            :class="[
                                'px-4 py-2 border-b-2 transition-colors',
                                activeTab === 'students'
                                    ? 'border-blue-500 text-blue-600'
                                    : 'border-transparent hover:border-gray-300'
                            ]"
                        >
                            Étudiants
                        </button>
                    </div>

                    <!-- Contenu des onglets avec scroll contrôlé -->
                    <div class="flex-1">
                        <div class="p-4">
                            <div v-if="activeTab === 'details'" class="space-y-4">
                                <div class="space-y-2">
                                    <label class="text-sm font-medium">Professeur</label>
                                    <Select v-model="formData.professor_id">
                                        <SelectTrigger>
                                            <SelectValue :placeholder="selectedProf.label" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem
                                                v-for="professor in professors"
                                                :key="professor.id"
                                                :value="professor.id.toString()"
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
                                                :value="year.id.toString()"
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
                    <div class="border-t p-4 flex justify-end gap-2 bg-gray-50">
                        <Button variant="outline" @click="isExpanded = false">
                            Annuler
                        </Button>
                        <Button
                            @click="saveChanges"
                            :disabled="isLoading || (!hasChanges && !hasStudentChanges)"
                        >
                            {{ isLoading ? 'Enregistrement...' : 'Enregistrer' }}
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
