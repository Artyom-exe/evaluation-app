<script setup>
import { ref } from "vue";

// Modèle pour les réponses des étudiants
const responses = ref({
    question1: "",
    question2: [],
    question3: "",
});
</script>

<template>
    <AppLayout>
        <div class="p-6 max-w-3xl mx-auto">
            <h1 class="text-2xl font-bold mb-6">Formulaire d'évaluation</h1>

            <form>
                <!-- Question 1 : Champ texte -->
                <div class="mb-6">
                    <label
                        for="question1"
                        class="block text-sm font-medium mb-2"
                        >Quel est votre nom ?</label
                    >
                    <input
                        id="question1"
                        type="text"
                        v-model="responses.question1"
                        class="block w-full p-2 border rounded"
                        placeholder="Entrez votre nom"
                    />
                </div>

                <!-- Question 2 : Case à cocher -->
                <div class="mb-6">
                    <label
                        for="question2"
                        class="block text-sm font-medium mb-2"
                        >Choisissez vos modules :</label
                    >
                    <div>
                        <input
                            type="checkbox"
                            id="module1"
                            v-model="responses.question2"
                            value="Module 1"
                            class="mr-2"
                        />
                        <label for="module1">Module 1</label>
                    </div>
                    <div>
                        <input
                            type="checkbox"
                            id="module2"
                            v-model="responses.question2"
                            value="Module 2"
                            class="mr-2"
                        />
                        <label for="module2">Module 2</label>
                    </div>
                </div>

                <!-- Question 3 : Bouton radio -->
                <div class="mb-6">
                    <label
                        for="question3"
                        class="block text-sm font-medium mb-2"
                        >Quel est votre niveau ?</label
                    >
                    <div>
                        <input
                            type="radio"
                            id="beginner"
                            value="Débutant"
                            v-model="responses.question3"
                            class="mr-2"
                        />
                        <label for="beginner">Débutant</label>
                    </div>
                    <div>
                        <input
                            type="radio"
                            id="intermediate"
                            value="Intermédiaire"
                            v-model="responses.question3"
                            class="mr-2"
                        />
                        <label for="intermediate">Intermédiaire</label>
                    </div>
                    <div>
                        <input
                            type="radio"
                            id="advanced"
                            value="Avancé"
                            v-model="responses.question3"
                            class="mr-2"
                        />
                        <label for="advanced">Avancé</label>
                    </div>
                </div>

                <!-- Bouton Soumettre -->
                <div class="text-center">
                    <button
                        type="submit"
                        class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700"
                    >
                        Soumettre
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Personnalisation du design pour une meilleure structure */
</style>
