<script setup>
import { ref, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label/index.js';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue
} from "@/Components/ui/select/index.js";
import Draggable from 'vuedraggable/src/vuedraggable';
import { Alert, AlertTitle, AlertDescription } from "@/Components/ui/alert"
import { ExclamationTriangleIcon, CheckCircledIcon } from "@radix-icons/vue"

const props = defineProps({
    modules: Array,
    questionTypes: Array
});

// Fonction pour charger les données du localStorage
const loadFromLocalStorage = () => {
    const savedForm = localStorage.getItem('evaluation_form_draft');
    if (savedForm) {
        try {
            return JSON.parse(savedForm);
        } catch (e) {
            localStorage.removeItem('evaluation_form_draft');
        }
    }
    return {
        title: '',
        module_id: '',
        statut: 'open',
        questions: []
    };
};

// Initialisation du formulaire avec les données du localStorage
const form = ref(loadFromLocalStorage());

// Watch pour sauvegarder automatiquement dans le localStorage
watch(form, (newValue) => {
    localStorage.setItem('evaluation_form_draft', JSON.stringify(newValue));
}, { deep: true });

const errors = ref({});
const drag = ref(false); // Ajout de la variable drag
const isSubmitting = ref(false)

const alertMessage = ref(null);
const showAlert = (message, type = 'success') => {
    alertMessage.value = { message, type };
    setTimeout(() => {
        alertMessage.value = null;
    }, 3000);
};

const hasAttemptedSubmit = ref(false);

// Validation computée
const formValidation = computed(() => {
    const validations = {
        title: !!form.value.title,
        module: !!form.value.module_id,
        questions: form.value.questions.length > 0,
        questionsValidation: form.value.questions.map(q => ({
            label: !!q.label,
            options: !['radio', 'checkbox'].includes(q.type) || (q.options && q.options.length >= 2)
        }))
    }

    return {
        ...validations,
        isValid: validations.title &&
                 validations.module &&
                 validations.questions &&
                 validations.questionsValidation.every(v => v.label && v.options)
    }
})

// Simplification des fonctions
const addQuestion = (type) => {
    form.value.questions.push({
        id: Date.now(),
        type: type.type,
        label: '',
        options: type.type === 'radio' || type.type === 'checkbox' ? [] : null,
    });
};

const removeQuestion = (index) => {
    form.value.questions.splice(index, 1);
};

// Modifier la fonction submitForm pour nettoyer le localStorage après succès
const submitForm = () => {
    hasAttemptedSubmit.value = true;

    if (!formValidation.value.isValid) {
        showAlert('Veuillez remplir tous les champs obligatoires', 'error');
        return;
    }

    isSubmitting.value = true;
    const formData = {
        title: form.value.title,
        module_id: form.value.module_id,
        questions: form.value.questions.map(q => ({
            type: q.type,
            label: q.label,
            options: q.options || []
        }))
    }

    router.post('/forms', formData, {
        onSuccess: () => {
            localStorage.removeItem('evaluation_form_draft'); // Nettoyage du localStorage
            showAlert('Le formulaire a été créé avec succès');
            router.visit('/forms');
        },
        onError: (errors) => {
            showAlert(errors.error || "Une erreur est survenue lors de la création du formulaire", 'error');
        },
        onFinish: () => {
            isSubmitting.value = false;
        }
    });
};

// Ajouter un cleanup lors de l'annulation
const handleCancel = () => {
    if (confirm('Voulez-vous vraiment annuler ? Toutes les modifications seront perdues.')) {
        localStorage.removeItem('evaluation_form_draft');
        router.get('/forms');
    }
};

// Configuration du draggable
const dragOptions = {
    animation: 150,
    handle: '.drag-handle',
    ghostClass: 'bg-gray-100',
    onStart: () => drag.value = true,
    onEnd: () => drag.value = false
};

</script>

<template>
    <div class="min-h-screen bg-gray-100 p-6">
        <div v-if="alertMessage"
             :class="[
                'fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 transition-all duration-500',
                alertMessage.type === 'error' ? 'bg-red-500 text-white' : 'bg-green-500 text-white'
             ]"
        >
            {{ alertMessage.message }}
        </div>
        <div class="max-w-7xl mx-auto space-y-6">
            <!-- En-tête -->
            <div class="bg-white shadow rounded-lg p-4">
                <h1 class="text-2xl font-semibold">Créer un formulaire d'évaluation</h1>
            </div>

            <!-- Formulaire principal -->
            <div class="grid grid-cols-4 gap-6">
                <!-- Colonne de gauche -->
                <div class="col-span-1 space-y-4">
                    <div class="bg-white p-4 rounded-lg shadow space-y-4">
                        <div class="space-y-2">
                            <Label for="title">Titre du formulaire</Label>
                            <Input id="title" v-model="form.title" placeholder="Entrez un titre..." />
                        </div>

                        <div class="space-y-2">
                            <Label for="module">Module</Label>
                            <Select v-model="form.module_id">
                                <SelectTrigger>
                                    <SelectValue placeholder="Sélectionnez un module" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="module in modules" :key="module.id" :value="module.id.toString()">
                                        {{ module.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="pt-4 border-t">
                            <h3 class="text-sm font-medium mb-3">Types de questions</h3>
                            <div class="space-y-2">
                                <Button
                                    v-for="type in questionTypes"
                                    :key="type.id"
                                    variant="outline"
                                    class="w-full justify-start"
                                    @click="addQuestion(type)"
                                >
                                    <i :class="type.icon" class="mr-2"></i>
                                    {{ type.label }}
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Colonne de droite -->
                <div class="col-span-3">
                    <div class="bg-white rounded-lg shadow p-6 mb-8">
                        <!-- Message si pas de questions -->
                        <div v-if="!form.questions.length" class="text-center py-12 text-gray-500">
                            Ajoutez des questions depuis le menu de gauche
                        </div>

                        <!-- Liste des questions -->
                        <Draggable
                            v-model="form.questions"
                            :componentData="{
                                tag: 'div',
                                type: 'transition-group',
                                name: !drag ? 'flip-list' : null
                            }"
                            v-bind="dragOptions"
                            class="space-y-4"
                            item-key="id"
                        >
                            <template #item="{element: question, index}">
                                <div class="border rounded-lg p-4 bg-white">
                                    <div class="flex items-start gap-4">
                                        <i class="ri-drag-move-line drag-handle cursor-move text-gray-400"></i>
                                        <div class="flex-1 space-y-4">
                                            <Input
                                                v-model="question.label"
                                                placeholder="Votre question..."
                                                class="text-lg"
                                            />
                                            <!-- Options pour radio/checkbox -->
                                            <div v-if="['radio', 'checkbox'].includes(question.type)" class="pl-6 space-y-2">
                                                <div v-for="(option, optIndex) in question.options" :key="optIndex" class="flex items-center gap-2">
                                                    <Input v-model="question.options[optIndex]" placeholder="Option..." />
                                                    <Button size="sm" variant="ghost" @click="question.options.splice(optIndex, 1)">
                                                        <i class="ri-delete-bin-line"></i>
                                                    </Button>
                                                </div>
                                                <Button size="sm" variant="ghost" class="mt-2" @click="question.options.push('')">
                                                    <i class="ri-add-line mr-2"></i> Ajouter une option
                                                </Button>
                                            </div>
                                        </div>
                                        <Button variant="ghost" size="sm" @click="removeQuestion(index)">
                                            <i class="ri-delete-bin-line text-red-500"></i>
                                        </Button>
                                    </div>
                                </div>
                            </template>
                        </Draggable>
                    </div>

                    <!-- Section des alertes avec plus d'espace -->
                    <div class="space-y-4 mb-8">
                        <!-- Affichage des erreurs -->
                        <div v-if="errors.error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                            {{ errors.error }}
                        </div>

                        <!-- Validation Alerts - Modifié pour n'apparaître qu'après tentative de soumission -->
                        <Alert
                            v-if="hasAttemptedSubmit && (!formValidation.title || !formValidation.module)"
                            variant="destructive"
                            class="mb-4"
                        >
                            <ExclamationTriangleIcon class="h-4 w-4" />
                            <AlertTitle>Informations manquantes</AlertTitle>
                            <AlertDescription>
                                <ul class="list-disc pl-4">
                                    <li v-if="!formValidation.title">Le titre est requis</li>
                                    <li v-if="!formValidation.module">Sélectionnez un module</li>
                                </ul>
                            </AlertDescription>
                        </Alert>

                        <!-- Questions Validation - Modifié pour n'apparaître qu'après tentative de soumission -->
                        <Alert
                            v-if="hasAttemptedSubmit && !formValidation.questions"
                            variant="warning"
                            class="mb-4"
                        >
                            <ExclamationTriangleIcon class="h-4 w-4" />
                            <AlertTitle>Questions requises</AlertTitle>
                            <AlertDescription>
                                Ajoutez au moins une question à votre formulaire
                            </AlertDescription>
                        </Alert>
                    </div>

                    <!-- Actions -->
                    <div class="mt-6 flex justify-end gap-4">
                        <Button variant="outline" @click="handleCancel" :disabled="isSubmitting">
                            Annuler
                        </Button>
                        <Button
                            @click="submitForm"
                            :disabled="isSubmitting"
                            :class="{ 'opacity-75': !formValidation.isValid }"
                        >
                            {{ isSubmitting ? 'Enregistrement...' : 'Enregistrer le formulaire' }}
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

