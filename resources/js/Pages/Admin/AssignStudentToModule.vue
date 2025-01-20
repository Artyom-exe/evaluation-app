<script setup>
import { ref } from "vue";

const selectedStudent = ref(null);
const selectedModule = ref(null);
</script>

<template>
    <AppLayout>
        <div class="p-6 max-w-2xl mx-auto">
            <h1 class="text-2xl font-bold mb-6 text-center">
                Assigner un étudiant à un module
            </h1>

            <!-- Conteneur pour le tableau -->
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <div class="p-4 border-b">
                    <h2 class="text-lg font-semibold mb-4">
                        Sélectionnez un étudiant et un module
                    </h2>

                    <!-- Tableau avec étudiants -->
                    <div class="mb-6">
                        <label
                            for="student"
                            class="block text-sm font-medium mb-2"
                            >Étudiant</label
                        >
                        <select
                            id="student"
                            v-model="selectedStudent"
                            class="w-full p-2 border rounded-md shadow-sm focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="" disabled selected>
                                Choisissez un étudiant
                            </option>
                            <option value="student1@example.com">
                                student1@example.com
                            </option>
                            <option value="student2@example.com">
                                student2@example.com
                            </option>
                        </select>
                    </div>

                    <!-- Tableau avec modules -->
                    <div class="mb-6">
                        <label
                            for="module"
                            class="block text-sm font-medium mb-2"
                            >Module</label
                        >
                        <select
                            id="module"
                            v-model="selectedModule"
                            class="w-full p-2 border rounded-md shadow-sm focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="" disabled selected>
                                Choisissez un module
                            </option>
                            <option value="module1">Module 1</option>
                            <option value="module2">Module 2</option>
                        </select>
                    </div>
                </div>

                <!-- Bouton pour soumettre -->
                <div class="p-4 text-center border-t">
                    <button
                        class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-all"
                        :disabled="!selectedStudent || !selectedModule"
                    >
                        Assigner
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Personnalisation du design pour une meilleure structure */
</style>
