<script setup>
import { ref } from 'vue';
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
});

const isExpanded = ref(false);
const studentEmails = ref('');
// Convertir l'ID en chaîne de caractères
const selectedProfessor = ref((props.module.professor?.id || '').toString());
const isLoading = ref(false);

// Ajouter une constante pour l'image par défaut
const defaultImage = 'https://placehold.co/600x400/e2e8f0/475569?text=Module';

const toggleExpand = () => {
    isExpanded.value = !isExpanded.value;
    if (isExpanded.value) {
        studentEmails.value = props.module.students?.map(s => s.email).join(', ') || '';
    }
};

const saveStudents = async () => {
    isLoading.value = true;
    try {
        await router.put(route('modules.updateStudents', props.module.id), {
            emails: studentEmails.value
        }, {
            preserveScroll: true,
            onSuccess: () => {
                isExpanded.value = false;
            }
        });
    } finally {
        isLoading.value = false;
    }
};

const saveProfessor = async () => {
    isLoading.value = true;
    try {
        await router.put(route('modules.updateProfessor', props.module.id), {
            professor_id: selectedProfessor.value
        }, {
            preserveScroll: true,
            onSuccess: () => {
                isExpanded.value = false;
            }
        });
    } finally {
        isLoading.value = false;
    }
};

const emit = defineEmits(['showAlert']);

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
</script>

<template>
    <div class="bg-white rounded-lg shadow-lg overflow-hidden transition-all duration-300"
         :class="{ 'h-[400px]': isExpanded, 'h-[200px]': !isExpanded }">
        <div class="relative h-full">
            <!-- Card Header -->
            <div class="flex justify-between items-start p-4">
                <div class="cursor-pointer" @click="toggleExpand">
                    <img :src="module.image || defaultImage"
                         class="w-full h-32 object-cover"
                         alt="Module image">
                    <h3 class="text-lg font-semibold mt-2">{{ module.name }}</h3>
                </div>
                <!-- Ajout du bouton de suppression -->
                <Button
                    variant="ghost"
                    size="sm"
                    @click.stop="deleteModule"
                    class="text-red-500 hover:text-red-700"
                >
                    <i class="ri-delete-bin-line"></i>
                </Button>
            </div>

            <!-- Expanded Content -->
            <div v-show="isExpanded"
                 class="absolute inset-0 bg-white transform transition-transform duration-300"
                 :class="{ 'translate-y-0': isExpanded, 'translate-y-full': !isExpanded }">
                <div class="p-4 space-y-4">
                    <h3 class="text-lg font-semibold">{{ module.name }}</h3>

                    <!-- Professor Selection -->
                    <div class="space-y-2">
                        <label class="text-sm font-medium">Professeur</label>
                        <Select v-model="selectedProfessor">
                            <SelectTrigger>
                                <SelectValue placeholder="Sélectionner un professeur" />
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

                    <!-- Student Emails -->
                    <div class="space-y-2">
                        <label class="text-sm font-medium">Emails des étudiants</label>
                        <Textarea
                            v-model="studentEmails"
                            placeholder="Entrez les emails séparés par des virgules"
                            rows="4"
                        />
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end gap-2">
                        <Button variant="outline" @click="toggleExpand">
                            Annuler
                        </Button>
                        <Button @click="saveStudents" :disabled="isLoading">
                            Enregistrer les étudiants
                        </Button>
                        <Button @click="saveProfessor" :disabled="isLoading">
                            Enregistrer le professeur
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
