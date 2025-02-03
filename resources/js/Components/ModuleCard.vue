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
const MODULE_DIALOG_DESC = 'module-edit-description';
const NEW_PROFESSOR_DIALOG_DESC = 'module-new-professor-description';
const EDIT_PROFESSOR_DIALOG_DESC = 'module-edit-professor-description';

const props = defineProps({
    module: {
        type: Object,
        required: true
    },
    professors: Array,
    years: Array,
});

const emit = defineEmits(['showAlert']);
const isLoading = ref(false);

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
        const formDataToSend = new FormData();

        if (hasChanges.value) {
            formDataToSend.append('professor_id', formData.value.professor_id);
            formDataToSend.append('year_id', formData.value.year_id);
        }

        if (formData.value.image) {
            formDataToSend.append('image', formData.value.image);
        }

        // Utiliser put au lieu de post et passer _method pour Laravel
        if (hasChanges.value || formData.value.image) {
            formDataToSend.append('_method', 'PUT'); // Ajouter cette ligne
            await router.post(route('modules.update', props.module.id), formDataToSend);
        }

        if (hasStudentChanges.value) {
            await router.put(route('modules.updateStudents', props.module.id), {
                emails: formData.value.studentEmails
            });
        }

        emit('showAlert', 'Module mis à jour avec succès', 'success');
        showDialog.value = false;
    } catch (error) {
        emit('showAlert', error.message || 'Erreur lors de la mise à jour', 'error');
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
    'transform scale-100 shadow-lg': showDialog.value || isHovered.value,
    'transform scale-98 hover:scale-100': !showDialog.value,
    'opacity-95 hover:opacity-100': !showDialog.value,
}));

const statusIndicator = computed(() => {
    if (!props.module.professor) return { color: 'bg-yellow-400', text: 'En attente de professeur' };
    if (!props.module.students?.length) return { color: 'bg-orange-400', text: 'Sans étudiants' };
    return { color: 'bg-green-400', text: 'Actif' };
});

const showDialog = ref(false);

// Ajouter les refs pour la gestion de l'image
const imagePreview = ref(props.module.image_path || null);

const handleImageUpload = (event) => {
    event.stopPropagation(); // Arrêter la propagation de l'événement
    const file = event.target.files[0];
    if (file) {
        formData.value.image = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

// Ajouter une ref pour la gestion des emails en cours d'ajout
const studentsEmails = ref('');

// Fonction pour ajouter des étudiants depuis le textarea
const addStudentsFromEmails = () => {
    const emails = studentsEmails.value
        .split(/[\n,]/)
        .map(email => email.trim())
        .filter(email => email && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email));

    if (emails.length) {
        const currentEmails = formData.value.studentEmails.split(/[,\s]+/).filter(e => e);
        const newEmails = [...new Set([...currentEmails, ...emails])];
        formData.value.studentEmails = newEmails.join(', ');
        studentsEmails.value = '';
    }
};

// Corriger la fonction removeStudent
const removeStudent = async (emailToRemove) => {
    if (!confirm('Voulez-vous vraiment retirer cet étudiant du module ?')) {
        return;
    }

    try {
        await router.delete(route('modules.removeStudent', props.module.id), {
            data: { email: emailToRemove },
            preserveScroll: true,
            onSuccess: () => {
                // Mettre à jour l'interface immédiatement après succès
                const emails = formData.value.studentEmails.split(/[,\s]+/).filter(e => e);
                formData.value.studentEmails = emails.filter(email => email !== emailToRemove).join(', ');
                emit('showAlert', 'Étudiant retiré avec succès', 'success');
            },
            onError: (error) => {
                emit('showAlert', error?.error || 'Erreur lors de la suppression de l\'étudiant', 'error');
            }
        });
    } catch (error) {
        emit('showAlert', 'Une erreur est survenue', 'error');
    }
};

// Ajouter les états pour l'édition des professeurs
const showEditProfessorDialog = ref(false);
const editingProfessor = ref({ id: null, name: '', email: '' });
const isEditingProfessor = ref(false);

// Fonction pour éditer un professeur
const editProfessor = (professor) => {
    editingProfessor.value = { ...professor };
    showEditProfessorDialog.value = true;
};

// Fonction pour mettre à jour un professeur
const updateProfessor = async () => {
    isEditingProfessor.value = true;
    try {
        await router.put(route('professors.update', editingProfessor.value.id), editingProfessor.value, {
            preserveScroll: true,
            onSuccess: () => {
                showEditProfessorDialog.value = false;
                emit('showAlert', 'Professeur modifié avec succès', 'success');
            },
            onError: () => {
                emit('showAlert', 'Erreur lors de la modification du professeur', 'error');
            }
        });
    } finally {
        isEditingProfessor.value = false;
    }
};

// Fonction pour supprimer un professeur
const deleteProfessor = async (professor, event) => {
    event.preventDefault();
    event.stopPropagation();

    if (!confirm(`Voulez-vous vraiment supprimer le professeur ${professor.name} ?`)) {
        return;
    }

    try {
        await router.delete(route('professors.destroy', professor.id), {
            preserveScroll: true,
            onSuccess: () => {
                emit('showAlert', 'Professeur supprimé avec succès', 'success');
            },
            onError: () => {
                emit('showAlert', 'Erreur lors de la suppression du professeur', 'error');
            }
        });
    } catch (error) {
        emit('showAlert', 'Une erreur est survenue', 'error');
    }
};
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

        <!-- Card view -->
        <div class="h-full">
            <div class="cursor-pointer group" @click="showDialog = true">
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

        <!-- Quick actions -->
        <div class="absolute top-2 right-2 flex items-center gap-1 opacity-0 transition-opacity"
             :class="{'opacity-100': isHovered}">
            <Button
                variant="ghost"
                size="sm"
                class="bg-white/90 hover:bg-white shadow-sm"
                @click.stop="showDialog = true"
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

    <!-- Dialog pour l'édition du module -->
    <Dialog :open="showDialog" @update:open="showDialog = $event">
        <DialogContent
            class="fixed top-[50%] left-[50%] translate-x-[-50%] translate-y-[-50%] w-[90vw] sm:max-w-[600px] max-h-[85vh] !p-0 flex flex-col bg-white rounded-lg overflow-hidden"
            :aria-describedby="MODULE_DIALOG_DESC"
        >
            <!-- En-tête fixe -->
            <div class="flex-none bg-white border-b">
                <div class="p-6">
                    <DialogHeader>
                        <DialogTitle class="text-xl font-semibold">Modifier le module</DialogTitle>
                        <DialogDescription :id="MODULE_DIALOG_DESC" class="text-sm text-gray-500">
                            Modifier les informations du module {{ module.name }}
                        </DialogDescription>
                    </DialogHeader>
                </div>
            </div>

            <!-- Corps scrollable avec padding fixe -->
            <div class="flex-1 overflow-y-auto">
                <div class="p-6 space-y-6">
                    <!-- Informations de base du module -->
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <Label>Année</Label>
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
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <Label>Professeur</Label>
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    @click="showNewProfessorDialog = true"
                                    class="hover:text-black"
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
                                    <div class="max-h-[200px] overflow-y-auto">
                                        <div v-for="professor in professors"
                                             :key="professor.id"
                                             class="flex items-center justify-between p-2 hover:bg-gray-100"
                                        >
                                            <SelectItem :value="String(professor.id)">
                                                {{ professor.name }}
                                                <span v-if="professors.filter(p => p.name === professor.name).length > 1"
                                                      class="text-sm text-gray-500 ml-1">
                                                    ({{ professor.email }})
                                                </span>
                                            </SelectItem>
                                            <div class="flex gap-2">
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    @click.stop="editProfessor(professor)"
                                                    class="text-blue-500 hover:text-blue-700"
                                                >
                                                    <i class="ri-edit-line"></i>
                                                </Button>
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    @click.stop="(e) => deleteProfessor(professor, e)"
                                                    class="text-red-500 hover:text-red-700"
                                                >
                                                    <i class="ri-delete-bin-line"></i>
                                                </Button>
                                            </div>
                                        </div>
                                    </div>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <!-- Section des étudiants -->
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <Label>Étudiants</Label>
                            <span class="text-sm text-gray-500">
                                {{ formData.studentEmails.split(/[,\s]+/).filter(e => e).length }} étudiant(s)
                            </span>
                        </div>

                        <!-- Textarea pour les emails -->
                        <div class="space-y-2">
                            <Label>Ajouter des emails (un par ligne ou séparés par des virgules)</Label>
                            <textarea
                                v-model="studentsEmails"
                                rows="4"
                                class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background"
                                placeholder="email1@example.com&#10;email2@example.com"
                            ></textarea>
                            <Button @click="addStudentsFromEmails" variant="outline" class="w-full">
                                <i class="ri-user-add-line mr-2"></i>
                                Ajouter les étudiants
                            </Button>
                        </div>

                        <!-- Liste des étudiants -->
                        <div class="max-h-[200px] overflow-y-auto border rounded-lg divide-y">
                            <div v-for="email in formData.studentEmails.split(/[,\s]+/).filter(e => e)"
                                 :key="email"
                                 class="flex justify-between items-center p-2 hover:bg-gray-50"
                            >
                                <div class="text-sm">{{ email }}</div>
                                <Button variant="ghost" size="sm" @click="removeStudent(email)">
                                    <i class="ri-delete-bin-line text-red-500" />
                                </Button>
                            </div>
                            <div v-if="!formData.studentEmails"
                                 class="p-4 text-center text-gray-500"
                            >
                                Aucun étudiant ajouté
                            </div>
                        </div>
                    </div>

                    <!-- Section upload image -->
                    <div class="space-y-4">
                        <Label>Image du module</Label>
                        <div class="flex flex-col gap-4">
                            <div class="flex items-center gap-4">
                                <div class="relative aspect-video w-full max-w-[300px] rounded-lg overflow-hidden">
                                    <img
                                        :src="imagePreview || module.image_path || defaultImage"
                                        class="w-full h-full object-cover"
                                        alt="Preview"
                                    />
                                    <Button
                                        variant="secondary"
                                        class="absolute inset-0 w-full h-full bg-black/30 opacity-0 hover:opacity-100 transition-opacity"
                                        @click.stop="$refs.imageInput.click()"
                                    >
                                        <i class="ri-image-edit-line mr-2"></i>
                                        Changer l'image
                                    </Button>
                                    <input
                                        ref="imageInput"
                                        type="file"
                                        accept="image/*"
                                        class="hidden"
                                        @change="handleImageUpload"
                                        @click.stop
                                    />
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm text-gray-500">
                                        Cliquez sur l'image pour la modifier.
                                        <br>
                                        Formats acceptés : JPG, PNG. Max 2MB.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer fixe -->
            <div class="flex-none bg-white border-t">
                <div class="p-6 flex justify-end gap-3">
                    <Button variant="outline" @click="showDialog = false">
                        Annuler
                    </Button>
                    <Button @click="saveChanges" :disabled="isLoading"
                        class="bg-black text-white hover:bg-gray-800 disabled:opacity-50">
                        {{ isLoading ? 'Enregistrement...' : 'Enregistrer' }}
                    </Button>
                </div>
            </div>
        </DialogContent>
    </Dialog>

    <!-- Dialog pour nouveau professeur -->
    <Dialog :open="showNewProfessorDialog" @update:open="showNewProfessorDialog = $event">
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

    <!-- Dialog pour modifier professeur -->
    <Dialog :open="showEditProfessorDialog" @update:open="showEditProfessorDialog = $event">
        <DialogContent
            class="sm:max-w-[425px] !z-[100]"
            :aria-describedby="EDIT_PROFESSOR_DIALOG_DESC"
        >
            <DialogHeader>
                <DialogTitle>Modifier le professeur</DialogTitle>
                <DialogDescription :id="EDIT_PROFESSOR_DIALOG_DESC">
                    Modifiez les informations du professeur.
                </DialogDescription>
            </DialogHeader>
            <div class="space-y-4">
                <div class="space-y-2">
                    <Label for="editProfName">Nom du professeur</Label>
                    <Input id="editProfName" v-model="editingProfessor.name" placeholder="Nom complet" />
                </div>
                <div class="space-y-2">
                    <Label for="editProfEmail">Email</Label>
                    <Input id="editProfEmail" v-model="editingProfessor.email" type="email" placeholder="Email" />
                </div>
            </div>
            <DialogFooter>
                <Button variant="outline" @click="showEditProfessorDialog = false">
                    Annuler
                </Button>
                <Button @click="updateProfessor" :disabled="isEditingProfessor">
                    {{ isEditingProfessor ? 'Modification...' : 'Modifier' }}
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

/* Ajouter ces nouvelles règles */
.DialogOverlay {
    z-index: 99 !important;
}

.DialogContent {
    z-index: 100 !important;
}

/* Assurer que le select reste en dessous */
.SelectContent {
    z-index: 50 !important;
}
</style>
