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
const selectedStatus = ref(getStoredValue('status', 'all'));
const selectedProfessor = ref(getStoredValue('professor', 'all'));

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

watch(selectedStatus, (newValue) => {
    localStorage.setItem('forms_status', newValue);
});

watch(selectedProfessor, (newValue) => {
    localStorage.setItem('forms_professor', newValue);
});

// Nettoyage du localStorage quand le composant est démonté
onUnmounted(() => {
    localStorage.removeItem('forms_search');
    localStorage.removeItem('forms_module');
    localStorage.removeItem('forms_year');
    localStorage.removeItem('forms_status');
    localStorage.removeItem('forms_professor');
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
        const matchesStatus = selectedStatus.value === 'all' || form.statut === selectedStatus.value;
        const matchesProfessor = selectedProfessor.value === 'all' || Number(selectedProfessor.value) === form.module.professor.id;

        return matchesSearch && matchesModule && matchesYear && matchesStatus && matchesProfessor;
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

// Mise à jour des fonctions pour les statuts et badges
const getStatusBadgeProps = (status) => {
  switch (status) {
    case 'draft':
      return { variant: 'secondary', class: 'bg-slate-100 text-slate-800 border border-slate-200' };
    case 'pending':
      return { variant: 'warning', class: 'bg-amber-100 text-amber-800 border border-amber-200' };
    case 'completed':
      return { variant: 'success', class: 'bg-emerald-100 text-emerald-800 border border-emerald-200' };
    default:
      return { variant: 'secondary', class: 'bg-gray-100 text-gray-800 border border-gray-200' };
  }
};

const getStatusText = (status) => {
  const texts = {
    'draft': 'Brouillon',
    'pending': 'En cours',
    'completed': 'Terminé'
  };
  return texts[status] || status;
};

// Fonction pour obtenir les classes des boutons
const getButtonClass = (type) => {
  const classes = {
    'edit': 'text-blue-600 hover:text-blue-800 hover:bg-blue-50',
    'delete': 'text-red-600 hover:text-red-800 hover:bg-red-50',
    'results': 'text-violet-600 hover:text-violet-800 hover:bg-violet-50',
    'send': 'bg-emerald-500 hover:bg-emerald-600 text-white shadow-sm hover:shadow transition-all duration-200'
  };
  return classes[type] || '';
};

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

const canDuplicate = (form) => {
  return form.statut === 'draft' || form.statut === 'completed';
};

const canDelete = (form) => {
    return form.statut === 'draft' || form.statut === 'completed';
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

const canModifyForm = (form) => form.statut === 'draft';

const statuses = [
    { value: 'all', label: 'Tous les statuts' },
    { value: 'draft', label: 'Brouillon' },
    { value: 'pending', label: 'En cours' },
    { value: 'completed', label: 'Terminé' }
];

// Ajout des couleurs pour les statuts
const getStatusColor = (status) => {
  switch (status) {
    case 'draft':
      return { background: 'bg-blue-50', text: 'text-blue-700', icon: 'ri-draft-line' };
    case 'pending':
      return { background: 'bg-yellow-50', text: 'text-yellow-700', icon: 'ri-time-line' };
    case 'completed':
      return { background: 'bg-green-50', text: 'text-green-700', icon: 'ri-check-double-line' };
    default:
      return { background: 'bg-gray-50', text: 'text-gray-700', icon: 'ri-question-line' };
  }
};
</script>

<template>
    <AppLayout title="Formulaires">
        <div class="p-6 space-y-6 bg-gray-50 min-h-screen">
            <!-- Modifier l'alerte pour s'assurer qu'elle est bien visible -->
            <div v-if="alertMessage" :class="[
                    'fixed top-4 right-4 p-4 rounded-lg shadow-lg z-[9999] transition-all duration-500',
                    alertMessage.type === 'error' ? 'bg-red-500 text-white' : 'bg-green-500 text-white'
                ]">
                {{ alertMessage.message }}
            </div>

            <div class="flex justify-between items-center bg-gray-50 p-4 rounded-lg">
                <h1 class="text-2xl font-semibold">Liste des formulaires d'évaluation</h1>
            </div>

            <div class="flex gap-4 items-center bg-white p-4 rounded-lg border">
                <div class="relative flex-1 max-w-sm">
                    <Search class="absolute left-2 top-2.5 h-4 w-4 text-gray-500" />
                    <Input v-model.trim="searchQuery" type="search" placeholder="Rechercher un formulaire..."
                        class="pl-8" />
                </div>

                <Select v-model="selectedModule">
                    <SelectTrigger class="w-[200px]">
                        <SelectValue placeholder="Filtrer par module" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">Tous les modules</SelectItem>
                        <SelectItem v-for="module in modules" :key="module.id" :value="module.id.toString()">
                            {{ module.name }}
                        </SelectItem>
                    </SelectContent>
                </Select>

                <Select v-model="selectedProfessor">
                    <SelectTrigger class="w-[200px]">
                        <SelectValue placeholder="Filtrer par professeur" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">Tous les professeurs</SelectItem>
                        <SelectItem v-for="professor in professors" :key="professor.id"
                            :value="professor.id.toString()">
                            {{ professor.name }}
                        </SelectItem>
                    </SelectContent>
                </Select>

                <Select v-model="selectedYear">
                    <SelectTrigger class="w-[200px]">
                        <SelectValue placeholder="Filtrer par année" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">Toutes les années</SelectItem>
                        <SelectItem v-for="year in years" :key="year.id" :value="year.id.toString()">
                            {{ year.name }}
                        </SelectItem>
                    </SelectContent>
                </Select>

                <Select v-model="selectedStatus">
                    <SelectTrigger class="w-[200px]">
                        <SelectValue placeholder="Filtrer par statut" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem v-for="status in statuses" :key="status.value" :value="status.value">
                            {{ status.label }}
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
                                <Badge v-bind="getStatusBadgeProps(form.statut)" class="px-2 py-1">
                                    {{ getStatusText(form.statut) }}
                                </Badge>
                            </td>
                            <td class="p-4">
                                <div class="flex gap-2 justify-end">
                                    <Button v-if="form.statut === 'draft'" variant="outline" size="sm"
                                        :class="getButtonClass('edit')" asChild>
                                        <Link :href="`/forms/${form.id}/edit`">
                                        <i class="ri-edit-line"></i>
                                        </Link>
                                    </Button>

                                    <Button v-if="canDuplicate(form)" variant="outline" size="sm"
                                        :class="getButtonClass('edit')" @click="duplicate(form)" title="Dupliquer">
                                        <i class="ri-file-copy-line"></i>
                                    </Button>

                                    <Button v-if="canDelete(form)" variant="outline" size="sm"
                                        :class="getButtonClass('delete')" @click="deleteForm(form.id)">
                                        <i class="ri-delete-bin-line"></i>
                                    </Button>

                                    <Button variant="outline" size="sm" :class="getButtonClass('results')" asChild
                                        v-if="form.statut === 'pending' || form.statut === 'completed'">
                                        <Link :href="`/forms/${form.id}/results`">
                                        <i class="ri-bar-chart-line"></i>
                                        </Link>
                                    </Button>

                                    <Button v-if="form.statut === 'draft'" @click="() => sendFormToStudents(form.id)"
                                        :class="getButtonClass('send')" size="sm" class="gap-2">
                                        <i class="ri-send-plane-line"></i>
                                        <span>Envoyer</span>
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
