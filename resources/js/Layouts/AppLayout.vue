<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetTitle,
    SheetClose,
    SheetOverlay,
    SheetPortal
} from "@/Components/ui/sheet";
import ModuleCard from '@/Components/ModuleCard.vue';
import { Button } from '@/Components/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter, DialogDescription } from '@/Components/ui/dialog';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/Components/ui/select";
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';

// Consolidation des IDs de description dans un objet
const DIALOG_DESC = {
    MODULE_EDIT: 'module-edit-description',
    NEW_MODULE: 'new-module-description',
    NEW_PROFESSOR: 'new-professor-description',
    EDIT_PROFESSOR: 'edit-professor-description',
};

defineProps({
    title: String,
    modules: Array, // Ajout de la prop modules
});

const showingNavigationDropdown = ref(false);
const showModulesSheet = ref(false);
const alertMessage = ref(null);
const isCreatingModule = ref(false);

// État pour les dialogues et formulaires
const newModule = ref({
    name: '',
    year_id: '',
    professor_id: '',
    students: [],  // Ajout des étudiants
    image: null
});

const handleImageUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        newModule.value.image = file;
        // Prévisualisation de l'image
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const imagePreview = ref(null);

const showNewProfessorDialog = ref(false);
const newProfessor = ref({ name: '', email: '' });
const studentsEmails = ref(''); // Pour le textarea des emails

// Fonction pour parser et ajouter les étudiants depuis le textarea
const addStudentsFromEmails = () => {
    const emails = studentsEmails.value
        .split(/[\n,]/) // Sépare par ligne ou virgule
        .map(email => email.trim())
        .filter(email => email && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)); // Validation basique email

    if (emails.length) {
        newModule.value.students = [
            ...newModule.value.students,
            ...emails.map(email => ({ email }))
        ];
        studentsEmails.value = ''; // Clear textarea
    }
};

// Fonction pour créer un nouveau professeur
const createProfessor = async () => {
    try {
        await router.post(route('professors.store'), newProfessor.value, {
            preserveScroll: true,
            onSuccess: () => {
                showNewProfessorDialog.value = false;
                showAlert('Professeur ajouté avec succès');
                newProfessor.value = { name: '', email: '' };
            },
            onError: (errors) => {
                showAlert(Object.values(errors)[0], 'error');
            }
        });
    } catch (error) {
        showAlert('Une erreur est survenue', 'error');
    }
};

// Ajouter la fonction de suppression de professeur
const deleteProfessor = async (professor, event) => {
    event.preventDefault();
    event.stopPropagation();

    const confirmMessage = `Êtes-vous sûr de vouloir supprimer ce professeur : ${professor.name} (${professor.email}) ?`;
    if (!confirm(confirmMessage)) {
        return;
    }

    try {
        await router.delete(route('professors.destroy', professor.id), {
            preserveScroll: true,
            onSuccess: (page) => {
                showAlert(page.props?.flash?.success || 'Professeur supprimé avec succès');
            },
            onError: (errors) => {
                showAlert(errors.error, 'error');
            }
        });
    } catch (error) {
        showAlert('Une erreur est survenue', 'error');
    }
};

// Gestionnaire d'alerte
const showAlert = (message, type = 'success') => {
    alertMessage.value = { message, type };
    setTimeout(() => alertMessage.value = null, 3000);
};

// Gestion des modules
const handleNewModule = () => {
    showNewModuleDialog.value = true;  // Ne plus fermer le Sheet
};

// Fonction pour ajouter un étudiant à la liste
const addStudent = () => {
    if (newStudent.value.name && newStudent.value.email) {
        newModule.value.students.push({ ...newStudent.value });
        newStudent.value = { name: '', email: '' };
    }
};

// Fonction pour supprimer un étudiant
const removeStudent = (index) => {
    newModule.value.students.splice(index, 1);
};

// Modifier createModule pour inclure les étudiants
const createModule = async () => {
    isCreatingModule.value = true;

    const formData = new FormData();
    formData.append('name', newModule.value.name);
    formData.append('year_id', newModule.value.year_id);
    formData.append('professor_id', newModule.value.professor_id);
    formData.append('students', JSON.stringify(newModule.value.students));

    if (newModule.value.image) {
        formData.append('image', newModule.value.image);
    }

    try {
        await router.post(route('modules.store'), formData, {
            preserveScroll: true,
            onSuccess: () => {
                showNewModuleDialog.value = false;
                showAlert('Module créé avec succès');
                // Reset du formulaire
                newModule.value = {
                    name: '',
                    year_id: '',
                    professor_id: '',
                    students: [],
                    image: null
                };
                imagePreview.value = null;
            },
            onError: (errors) => {
                showAlert(errors.error || 'Erreur lors de la création du module', 'error');
            },
        });
    } finally {
        isCreatingModule.value = false;
    }
};

// Gestion des professeurs
const switchToTeam = (team) => {
    router.put(route('current-team.update'), {
        team_id: team.id,
    }, {
        preserveState: false,
    });
};

const logout = () => {
    router.post(route('logout'));
};

const handleSheetClose = (val) => {
    showModulesSheet.value = val === undefined ? false : val;
};

const showNewModuleDialog = ref(false);

// Ajouter les états pour l'édition
const showEditProfessorDialog = ref(false);
const editingProfessor = ref({ id: null, name: '', email: '' });

// Ajouter la fonction d'édition
const editProfessor = (professor) => {
    editingProfessor.value = { ...professor };
    showEditProfessorDialog.value = true;
};

// Ajouter la fonction de mise à jour
const updateProfessor = async () => {
    try {
        await router.put(route('professors.update', editingProfessor.value.id), editingProfessor.value, {
            preserveScroll: true,
            onSuccess: () => {
                showEditProfessorDialog.value = false;
                showAlert('Professeur modifié avec succès');
            },
            onError: (errors) => {
                showAlert(Object.values(errors)[0], 'error');
            }
        });
    } catch (error) {
        showAlert('Une erreur est survenue', 'error');
    }
};
</script>

<template>
    <div>
        <Head :title="title" />

        <Banner />

        <div class="min-h-screen bg-gray-100">
            <!-- Ajout de sticky, top-0 et z-50 à la nav -->
            <nav class="sticky top-0 z-50 bg-white/95 backdrop-blur-sm border-b border-gray-100 shadow-sm">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="shrink-0 flex items-center">
                                <Link :href="route('dashboard')">
                                    <ApplicationMark class="block h-9 w-auto" />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                                <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                                    Dashboard
                                </NavLink>
                                <button
                                    @click="showModulesSheet = true"
                                    class="inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out"
                                >
                                    Modules
                                </button>
                            </div>
                        </div>

                        <div class="hidden sm:flex sm:items-center sm:ms-6">
                            <div class="ms-3 relative">
                                <!-- Teams Dropdown -->
                                <Dropdown v-if="$page.props.jetstream.hasTeamFeatures" align="right" width="60">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition ease-in-out duration-150">
                                                {{ $page.props.auth.user.current_team.name }}

                                                <svg class="ms-2 -me-0.5 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <div class="w-60">
                                            <!-- Team Management -->
                                            <div class="block px-4 py-2 text-xs text-gray-400">
                                                Manage Team
                                            </div>

                                            <!-- Team Settings -->
                                            <DropdownLink :href="route('teams.show', $page.props.auth.user.current_team)">
                                                Team Settings
                                            </DropdownLink>

                                            <DropdownLink v-if="$page.props.jetstream.canCreateTeams" :href="route('teams.create')">
                                                Create New Team
                                            </DropdownLink>

                                            <!-- Team Switcher -->
                                            <template v-if="$page.props.auth.user.all_teams.length > 1">
                                                <div class="border-t border-gray-200" />

                                                <div class="block px-4 py-2 text-xs text-gray-400">
                                                    Switch Teams
                                                </div>

                                                <template v-for="team in $page.props.auth.user.all_teams" :key="team.id">
                                                    <form @submit.prevent="switchToTeam(team)">
                                                        <DropdownLink as="button">
                                                            <div class="flex items-center">
                                                                <svg v-if="team.id == $page.props.auth.user.current_team_id" class="me-2 size-5 text-green-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                </svg>

                                                                <div>{{ team.name }}</div>
                                                            </div>
                                                        </DropdownLink>
                                                    </form>
                                                </template>
                                            </template>
                                        </div>
                                    </template>
                                </Dropdown>
                            </div>

                            <!-- Settings Dropdown -->
                            <div class="ms-3 relative">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <button v-if="$page.props.jetstream.managesProfilePhotos" class="flex text-sm border-2 border-transparent rounded-full focus:outline-none focus:border-gray-300 transition">
                                            <img class="size-8 rounded-full object-cover" :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name">
                                        </button>

                                        <span v-else class="inline-flex rounded-md">
                                            <button type="button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none focus:bg-gray-50 active:bg-gray-50 transition ease-in-out duration-150">
                                                {{ $page.props.auth.user.name }}

                                                <svg class="ms-2 -me-0.5 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <!-- Account Management -->
                                        <div class="block px-4 py-2 text-xs text-gray-400">
                                            Manage Account
                                        </div>

                                        <DropdownLink :href="route('profile.show')">
                                            Profile
                                        </DropdownLink>

                                        <DropdownLink v-if="$page.props.jetstream.hasApiFeatures" :href="route('api-tokens.index')">
                                            API Tokens
                                        </DropdownLink>

                                        <div class="border-t border-gray-200" />

                                        <!-- Authentication -->
                                        <form @submit.prevent="logout">
                                            <DropdownLink as="button">
                                                Log Out
                                            </DropdownLink>
                                        </form>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out" @click="showingNavigationDropdown = ! showingNavigationDropdown">
                                <svg
                                    class="size-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        :class="{'hidden': showingNavigationDropdown, 'inline-flex': ! showingNavigationDropdown }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{'hidden': ! showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div :class="{'block': showingNavigationDropdown, 'hidden': ! showingNavigationDropdown}" class="sm:hidden">
                    <div class="pt-2 pb-3 space-y-1">
                        <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
                            Dashboard
                        </ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="pt-4 pb-1 border-t border-gray-200">
                        <div class="flex items-center px-4">
                            <div v-if="$page.props.jetstream.managesProfilePhotos" class="shrink-0 me-3">
                                <img class="size-10 rounded-full object-cover" :src="$page.props.auth.user.profile_photo_url" :alt="$page.props.auth.user.name">
                            </div>

                            <div>
                                <div class="font-medium text-base text-gray-800">
                                    {{ $page.props.auth.user.name }}
                                </div>
                                <div class="font-medium text-sm text-gray-500">
                                    {{ $page.props.auth.user.email }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.show')" :active="route().current('profile.show')">
                                Profile
                            </ResponsiveNavLink>

                            <ResponsiveNavLink v-if="$page.props.jetstream.hasApiFeatures" :href="route('api-tokens.index')" :active="route().current('api-tokens.index')">
                                API Tokens
                            </ResponsiveNavLink>

                            <!-- Authentication -->
                            <form method="POST" @submit.prevent="logout">
                                <ResponsiveNavLink as="button">
                                    Log Out
                                </ResponsiveNavLink>
                            </form>

                            <!-- Team Management -->
                            <template v-if="$page.props.jetstream.hasTeamFeatures">
                                <div class="border-t border-gray-200" />

                                <div class="block px-4 py-2 text-xs text-gray-400">
                                    Manage Team
                                </div>

                                <!-- Team Settings -->
                                <ResponsiveNavLink :href="route('teams.show', $page.props.auth.user.current_team)" :active="route().current('teams.show')">
                                    Team Settings
                                </ResponsiveNavLink>

                                <ResponsiveNavLink v-if="$page.props.jetstream.canCreateTeams" :href="route('teams.create')" :active="route().current('teams.create')">
                                    Create New Team
                                </ResponsiveNavLink>

                                <!-- Team Switcher -->
                                <template v-if="$page.props.auth.user.all_teams.length > 1">
                                    <div class="border-t border-gray-200" />

                                    <div class="block px-4 py-2 text-xs text-gray-400">
                                        Switch Teams
                                    </div>

                                    <template v-for="team in $page.props.auth.user.all_teams" :key="team.id">
                                        <form @submit.prevent="switchToTeam(team)">
                                            <ResponsiveNavLink as="button">
                                                <div class="flex items-center">
                                                    <svg v-if="team.id == $page.props.auth.user.current_team_id" class="me-2 size-5 text-green-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    <div>{{ team.name }}</div>
                                                </div>
                                            </ResponsiveNavLink>
                                        </form>
                                    </template>
                                </template>
                            </template>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header v-if="$slots.header" class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>

        <!-- Modifier le Sheet -->
        <Sheet v-model:open="showModulesSheet">
            <SheetPortal>
                <Transition
                    enter-active-class="transition duration-300 ease-out"
                    enter-from-class="opacity-0"
                    enter-to-class="opacity-100"
                    leave-active-class="transition duration-200 ease-in"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <SheetOverlay
                        class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[60]"
                    />
                </Transition>

                <Transition
                    enter-active-class="transform transition duration-300 ease-out"
                    enter-from-class="translate-x-full"
                    enter-to-class="translate-x-0"
                    leave-active-class="transform transition duration-200 ease-in"
                    leave-from-class="translate-x-0"
                    leave-to-class="translate-x-full"
                >
                    <SheetContent
                        class="fixed right-0 top-0 w-[400px] sm:w-[540px] h-full bg-white p-4 shadow-lg z-[61]"
                    >
                        <div class="flex flex-col h-full">
                            <div class="flex justify-between items-center mb-3">
                                <SheetTitle class="text-lg font-semibold">Modules</SheetTitle>
                                <div class="flex items-center gap-2">
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        class="rounded-full hover:bg-gray-100"
                                        @click="handleNewModule"
                                    >
                                        <i class="ri-add-line text-xl" />
                                    </Button>
                                    <SheetClose @click="showModulesSheet = false">
                                        <i class="ri-close-line h-4 w-4" />
                                    </SheetClose>
                                </div>
                            </div>

                            <div class="flex-1 overflow-y-auto">
                                <div class="grid grid-cols-2 gap-3">
                                    <ModuleCard
                                        v-for="module in $page.props.modules"
                                        :key="module.id"
                                        :module="module"
                                        :professors="$page.props.professors"
                                        :years="$page.props.years"
                                        @show-alert="showAlert"
                                    />
                                </div>
                            </div>
                        </div>
                    </SheetContent>
                </Transition>
            </SheetPortal>
        </Sheet>

        <!-- Dialog pour nouveau module -->
        <Dialog :open="showNewModuleDialog" @update:open="showNewModuleDialog = false">
            <DialogContent
                class="!fixed !left-[calc((100vw-540px)/4)] !translate-x-0 top-[50%] translate-y-[-50%] w-[90vw] sm:max-w-[600px] max-h-[85vh] !p-0 flex flex-col bg-white rounded-lg overflow-hidden z-[70]"
                :aria-describedby="DIALOG_DESC.NEW_MODULE"
            >
                <!-- En-tête fixe -->
                <div class="flex-none bg-white border-b">
                    <div class="p-6">
                        <DialogHeader>
                            <DialogTitle class="text-xl font-semibold">Nouveau module</DialogTitle>
                            <DialogDescription :id="DIALOG_DESC.NEW_MODULE" class="text-sm text-gray-500">
                                Remplissez les informations pour créer un nouveau module.
                            </DialogDescription>
                        </DialogHeader>
                    </div>
                </div>

                <!-- Corps scrollable avec padding fixe -->
                <div class="flex-1 overflow-y-auto">
                    <div class="p-6 space-y-6">
                        <!-- Informations de base du module -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label>Nom du module</Label>
                                <Input v-model="newModule.name" placeholder="Nom du module" />
                            </div>
                            <div class="space-y-2">
                                <Label>Année</Label>
                                <Select v-model="newModule.year_id">
                                    <SelectTrigger class="w-full">
                                        <SelectValue placeholder="Sélectionner une année" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="year in $page.props.years"
                                            :key="year.id"
                                            :value="String(year.id)"
                                        >
                                            {{ year.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <!-- Section Professeur -->
                        <div class="space-y-2">
                            <div class="flex justify-between items-center">
                                <Label>Professeur</Label>
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
                            <Select v-model="newModule.professor_id">
                                <SelectTrigger>
                                    <SelectValue placeholder="Sélectionner un professeur" />
                                </SelectTrigger>
                                <SelectContent>
                                    <div class="max-h-[200px] overflow-y-auto">
                                        <div v-for="professor in $page.props.professors"
                                             :key="professor.id"
                                             class="flex items-center justify-between p-2 hover:bg-gray-100"
                                        >
                                            <SelectItem :value="String(professor.id)">
                                                {{ professor.name }}
                                                <span v-if="$page.props.professors.filter(p => p.name === professor.name).length > 1"
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

                        <!-- Section des étudiants -->
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <Label>Étudiants</Label>
                                <span class="text-sm text-gray-500">{{ newModule.students.length }} étudiant(s)</span>
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
                                <div v-for="(student, index) in newModule.students"
                                     :key="index"
                                     class="flex justify-between items-center p-2 hover:bg-gray-50"
                                >
                                    <div class="text-sm">{{ student.email }}</div>
                                    <Button variant="ghost" size="sm" @click="removeStudent(index)">
                                        <i class="ri-delete-bin-line text-red-500" />
                                    </Button>
                                </div>
                                <div v-if="newModule.students.length === 0"
                                     class="p-4 text-center text-gray-500"
                                >
                                    Aucun étudiant ajouté
                                </div>
                            </div>
                        </div>

                        <!-- Section upload image -->
                        <div class="space-y-4">
                            <Label>Image du module</Label>
                            <div class="flex items-center gap-4">
                                <div
                                    class="relative w-32 h-32 border-2 border-dashed rounded-lg overflow-hidden hover:bg-gray-50 transition-colors"
                                    :class="{'border-primary': imagePreview}"
                                >
                                    <input
                                        type="file"
                                        accept="image/*"
                                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                        @change="handleImageUpload"
                                    />
                                    <div v-if="!imagePreview" class="absolute inset-0 flex items-center justify-center">
                                        <i class="ri-image-add-line text-2xl text-gray-400"></i>
                                    </div>
                                    <img
                                        v-if="imagePreview"
                                        :src="imagePreview"
                                        class="absolute inset-0 w-full h-full object-cover"
                                    />
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm text-gray-500">
                                        Cliquez ou glissez une image ici pour l'ajouter au module.
                                        <br>
                                        Formats acceptés : JPG, PNG. Max 2MB.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer fixe -->
                <div class="flex-none bg-white border-t">
                    <div class="p-6 flex justify-end gap-3">
                        <Button variant="outline" @click="showNewModuleDialog = false">
                            Annuler
                        </Button>
                        <Button @click="createModule" :disabled="isCreatingModule"
                            class="bg-black text-white hover:bg-gray-800 disabled:opacity-50">
                            {{ isCreatingModule ? 'Création...' : 'Créer' }}
                        </Button>
                    </div>
                </div>
            </DialogContent>
        </Dialog>

        <!-- Dialog pour nouveau professeur -->
        <Dialog :open="showNewProfessorDialog" @update:open="showNewProfessorDialog = false">
            <DialogContent
                class="!fixed !left-8 !translate-x-0 top-[50%] translate-y-[-50%] sm:max-w-[425px] z-[70]"
                :aria-describedby="DIALOG_DESC.NEW_PROFESSOR"
            >
                <DialogHeader>
                    <DialogTitle>Nouveau professeur</DialogTitle>
                    <DialogDescription :id="DIALOG_DESC.NEW_PROFESSOR">
                        Remplissez les informations pour ajouter un nouveau professeur.
                    </DialogDescription>
                </DialogHeader>
                <div class="space-y-4 py-4">
                    <div class="space-y-2">
                        <Label>Nom complet</Label>
                        <Input v-model="newProfessor.name" placeholder="Nom du professeur" />
                    </div>
                    <div class="space-y-2">
                        <Label>Email</Label>
                        <Input v-model="newProfessor.email" type="email" placeholder="Email professionnel" />
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="showNewProfessorDialog = false">
                        Annuler
                    </Button>
                    <Button @click="createProfessor">
                        Créer
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- Dialog pour modifier professeur -->
        <Dialog :open="showEditProfessorDialog" @update:open="showEditProfessorDialog = false">
            <DialogContent
                class="!fixed !left-8 !translate-x-0 top-[50%] translate-y-[-50%] sm:max-w-[425px] z-[70]"
                :aria-describedby="DIALOG_DESC.EDIT_PROFESSOR"
            >
                <DialogHeader>
                    <DialogTitle>Modifier le professeur</DialogTitle>
                    <DialogDescription :id="DIALOG_DESC.EDIT_PROFESSOR">
                        Modifiez les informations du professeur.
                    </DialogDescription>
                </DialogHeader>
                <div class="space-y-4 py-4">
                    <div class="space-y-2">
                        <Label>Nom complet</Label>
                        <Input v-model="editingProfessor.name" placeholder="Nom du professeur" />
                    </div>
                    <div class="space-y-2">
                        <Label>Email</Label>
                        <Input v-model="editingProfessor.email" type="email" placeholder="Email professionnel" />
                    </div>
                </div>
                <DialogFooter>
                    <Button variant="outline" @click="showEditProfessorDialog = false">
                        Annuler
                    </Button>
                    <Button @click="updateProfessor">
                        Modifier
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>

<style>
.transform {
    transition-property: transform;
}

.grid-cols-2 > * {
    min-width: 0;
}

/* Ajout des styles pour le dialog */
:root {
    --dialog-padding: 1.5rem;
}

/* Assurer que le dialog est toujours au-dessus des autres éléments */
.DialogOverlay {
    z-index: 65 !important;
}

.DialogContent {
    z-index: 70 !important;
}

/* Ajout des styles pour la nav fixe */
nav.sticky {
    transition: background-color 0.2s ease;
    z-index: 50;
}

.SheetOverlay {
    z-index: 60;
}

.SheetContent {
    z-index: 61;
}

nav.sticky:hover {
    background-color: rgba(255, 255, 255, 1);
}

/* Ajout des styles pour les transitions */
.transform {
  transform: translateX(0);
  transition-property: transform, opacity;
}
</style>
