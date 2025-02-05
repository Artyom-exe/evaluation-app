<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head } from "@inertiajs/vue3";

defineProps({
    form: Object,
    responses: Object,
});
</script>

<template>
    <AppLayout>
        <Head :title="`Résultats - ${form.title}`" />

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6"
                >
                    <h1 class="text-2xl font-bold mb-6">
                        {{ form.title }} - Résultats
                    </h1>

                    <div
                        v-for="(responses, student_id) in responses"
                        :key="student_id"
                        class="mb-8 p-6 bg-gray-50 rounded-lg"
                    >
                        <h2 class="text-lg font-semibold mb-4">
                            Réponses de l'étudiant #{{ student_id }}
                        </h2>

                        <div
                            v-for="question in form.questions"
                            :key="question.id"
                            class="mb-4"
                        >
                            <p class="font-medium text-gray-700">
                                {{ question.label }}
                            </p>
                            <p class="mt-1 text-gray-600">
                                {{
                                    responses.find(
                                        (r) =>
                                            r.form_question_id === question.id
                                    )?.answers || "Non répondu"
                                }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 text-right">
                        <button
                            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
                            disabled
                        >
                            Export Excel (Bientôt disponible)
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
