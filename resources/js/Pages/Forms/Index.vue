<script setup>
import { ref, computed, watch, onUnmounted, onMounted } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';  // Ajout de router et useForm ici
import { Button } from '@/Components/ui/button';
import { Input } from "@/Components/ui/input";
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";
import { Badge } from "@/Components/ui/badge";
import { Search } from 'lucide-vue-next';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter, DialogDescription } from "@/Components/ui/dialog";
import { Label } from "@/Components/ui/label";
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    forms: Array,
    modules: Array,
    years: Array,
    professors: Array
});

// Fonction pour obtenir les valeurs du localStorage avec valeur par défaut
const getStoredValue = (key, defaultValue) => {
    const stored = localStorage.getItem(`forms_${key}`);
    return stored !== null ? stored : defaultValue;
};

const searchQuery = ref(getStoredValue('search', ''));
const selectedModule = ref(getStoredValue('module', 'all'));
const selectedYear = ref(getStoredValue('year', 'all'));

// Surveiller les changements et mettre à jour le localStorage
watch(searchQuery, (newValue) => {
    localStorage.setItem('forms_search', newValue);
});

watch(selectedModule, (newValue) => {
    localStorage.setItem('forms_module', newValue);
});

watch(selectedYear, (newValue) => {
    localStorage.setItem('forms_year', newValue);
});

// Nettoyage du localStorage quand le composant est démonté
onUnmounted(() => {
    localStorage.removeItem('forms_search');
    localStorage.removeItem('forms_module');
    localStorage.removeItem('forms_year');
});

const filteredForms = computed(() => {
    const search = searchQuery.value.toLowerCase().trim();

    return props.forms.filter(form => {
        // Vérification de plusieurs champs pour la recherche
        const searchInFields = [
            form.title,
            form.module.name,
            form.module.professor.name,
            form.module.year.name
        ].map(field => (field || '').toLowerCase());

        const matchesSearch = search === '' || searchInFields.some(field => field.includes(search));
        const matchesModule = selectedModule.value === 'all' || Number(selectedModule.value) === form.module.id;
        const matchesYear = selectedYear.value === 'all' || Number(selectedYear.value) === form.module.year.id;

        return matchesSearch && matchesModule && matchesYear;
    });
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('fr-FR');
};

const deleteForm = async (id) => {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce formulaire ?')) {
        router.delete(`/forms/${id}`);  // Utilisation de router.delete au lieu de $inertia
    }
};

// Ajout des classes de couleur pour les statuts
const getStatusClasses = (status) => ({
    'success': status === 'open',
    'destructive': status === 'closed'
});

// Ajout du texte pour les statuts
const getStatusText = (status) => ({
    'open': 'Ouvert',
    'closed': 'Fermé'
}[status]);

// Modifier la fonction showAlert pour augmenter la durée d'affichage
const showAlert = (message, type = 'success') => {
    alertMessage.value = { message, type };
    setTimeout(() => {
        alertMessage.value = null;
    }, 5000); // Augmenté à 5 secondes pour une meilleure visibilité
};

const alertMessage = ref(null);

const duplicate = (form) => {
    if (confirm('Voulez-vous dupliquer ce formulaire ?')) {
        router.post(route('forms.duplicate', form.id), {}, {
            onSuccess: () => {
                showAlert('Formulaire dupliqué avec succès');
            },
            onError: () => {
                showAlert('Erreur lors de la duplication', 'error');
            }
        });
    }
};

const showNewModuleDialog = ref(false);
const isCreatingModule = ref(false);
const newModule = ref({
    name: '',
    year_id: '',
    professor_id: ''
});

const createModule = async () => {
    isCreatingModule.value = true;
    try {
        await router.post(route('modules.store'), newModule.value, {
            preserveScroll: true,
            onSuccess: () => {
                showNewModuleDialog.value = false;
                showAlert('Module créé avec succès');
                newModule.value = { name: '', year_id: '', professor_id: '' };
            },
            onError: () => {
                showAlert('Erreur lors de la création du module', 'error');
            }
        });
    } finally {
        isCreatingModule.value = false;
    }
};

const handleAlert = (message, type = 'success') => {
    console.log('Alert received:', message, type); // Pour le débogage
    if (message) {
        showAlert(message, type);
    }
};

// Ajout de la vérification des messages flash au chargement
onMounted(() => {
    const page = router.page.props;

    if (page.flash?.success) {
        showAlert(page.flash.success, 'success');
    }
    if (page.errors?.error) {
        showAlert(page.errors.error, 'error');
    }
});

const closeDialog = () => {
    showNewModuleDialog.value = false;
    newModule.value = { name: '', year_id: '', professor_id: '' };
};

const showNewProfessorDialog = ref(false);
const newProfessor = ref({ name: '', email: '' });
const isCreatingProfessor = ref(false);

const createProfessor = async () => {
    isCreatingProfessor.value = true;
    try {
        const response = await router.post(route('professors.store'), newProfessor.value, {
            preserveScroll: true,
            onSuccess: () => {
                showNewProfessorDialog.value = false;
                showAlert('Professeur ajouté avec succès', 'success');
                newProfessor.value = { name: '', email: '' };
            },
            onError: () => {
                showAlert('Erreur lors de la création du professeur', 'error');
            }
        });
    } finally {
        isCreatingProfessor.value = false;
    }
};

const sendFormToStudents = (formId) => {
    // Correction : créer une instance form avec useForm avant de l'utiliser
    const form = useForm({});

    form.post(route('forms.send-access', formId), {
        preserveScroll: true,
        onSuccess: () => {
            showAlert('Emails envoyés avec succès', 'success');
        },
        onError: () => {
            showAlert('Erreur lors de l\'envoi des emails', 'error');
        }
    });
};
</script>

<template>
    <AppLayout title="Formulaires">
        <div class="p-6 space-y-6">
            <!-- Modifier l'alerte pour s'assurer qu'elle est bien visible -->
            <div v-if="alertMessage"
                :class="[
                    'fixed top-4 right-4 p-4 rounded-lg shadow-lg z-[9999] transition-all duration-500',
                    alertMessage.type === 'error' ? 'bg-red-500 text-white' : 'bg-green-500 text-white'
                ]"
            >
                {{ alertMessage.message }}
            </div>

            <div class="flex justify-between items-center bg-gray-50 p-4 rounded-lg">
                <h1 class="text-2xl font-semibold">Liste des formulaires d'évaluation</h1>
            </div>

            <div class="flex gap-4 items-center bg-white p-4 rounded-lg border">
                <div class="relative flex-1 max-w-sm">
                    <Search class="absolute left-2 top-2.5 h-4 w-4 text-gray-500" />
                    <Input
                        v-model.trim="searchQuery"
                        type="search"
                        placeholder="Rechercher un formulaire..."
                        class="pl-8"
                    />
                </div>

                <Select v-model="selectedModule">
                    <SelectTrigger class="w-[200px]">
                        <SelectValue placeholder="Filtrer par module" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">Tous les modules</SelectItem>
                        <SelectItem
                            v-for="module in modules"
                            :key="module.id"
                            :value="module.id.toString()"
                        >
                            {{ module.name }}
                        </SelectItem>
                    </SelectContent>
                </Select>

                <Select v-model="selectedYear">
                    <SelectTrigger class="w-[200px]">
                        <SelectValue placeholder="Filtrer par année" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">Toutes les années</SelectItem>
                        <SelectItem
                            v-for="year in years"
                            :key="year.id"
                            :value="year.id.toString()"
                        >
                            {{ year.name }}
                        </SelectItem>
                    </SelectContent>
                </Select>

                <Link href="/forms/create" class="ml-auto">
                    <Button>Nouveau formulaire</Button>
                </Link>
            </div>

            <div class="bg-white rounded-lg border">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 border-b">
                            <th class="text-left p-4 font-medium">Titre</th>
                            <th class="text-left p-4 font-medium">Module</th>
                            <th class="text-left p-4 font-medium">Professeur</th>
                            <th class="text-left p-4 font-medium">Année</th>
                            <th class="text-left p-4 font-medium">Statut</th>
                            <th class="text-right p-4 font-medium">Actions</th>
                        </tr>
                        </thead>
                    <tbody>
                        <tr v-for="form in filteredForms" :key="form.id" class="border-b last:border-b-0">
                            <td class="p-4">{{ form.title }}</td>
                            <td class="p-4">{{ form.module.name }}</td>
                            <td class="p-4">{{ form.module.professor.name }}</td>
                            <td class="p-4">{{ form.module.year.name }}</td>
                            <td class="p-4">
                                <Badge :variant="form.statut === 'open' ? 'success' : 'destructive'" class="gap-2 px-3 py-1">
                                    <i :class="[
                                        form.statut === 'open' ? 'ri-checkbox-circle-line' : 'ri-close-circle-line',
                                        form.statut === 'open' ? 'text-green-500' : 'text-red-500'
                                    ]"></i>
                                    {{ getStatusText(form.statut) }}
                                </Badge>
                            </td>
                            <td class="p-4">
                                <div class="flex gap-2 justify-end">
                                    <Button variant="outline" size="sm" asChild>
                                        <Link :href="`/forms/${form.id}/edit`">
                                            <i class="ri-edit-line text-base text-blue-500 hover:text-blue-600"></i>
                                        </Link>
                                    </Button>
                                    <Button variant="outline" size="sm" asChild>
                                        <Link :href="`/forms/${form.id}/results`">
                                            <i class="ri-bar-chart-line text-base text-purple-500 hover:text-purple-600"></i>
                                        </Link>
                                    </Button>
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        @click="duplicate(form)"
                                        title="Dupliquer"
                                    >
                                        <i class="ri-file-copy-line"></i>
                                    </Button>
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        @click="deleteForm(form.id)"
                                    >
                                        <i class="ri-delete-bin-line text-base text-red-500 hover:text-red-600"></i>
                                    </Button>
                                    <Button
                                        @click="() => sendFormToStudents(form.id)"
                                        variant="secondary"
                                        size="sm"
                                    >
                                        Envoyer aux étudiants
                                    </Button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
