<script setup>
import { ref, computed, watch, onUnmounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';  // Ajout de router ici
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

const props = defineProps({
    forms: Array,
    modules: Array,
    years: Array
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
</script>

<template>
    <div class="p-6 space-y-6">
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
                                    @click="deleteForm(form.id)"
                                >
                                    <i class="ri-delete-bin-line text-base text-red-500 hover:text-red-600"></i>
                                </Button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
