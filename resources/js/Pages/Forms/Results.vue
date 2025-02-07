<script setup>
import { ref, computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Card, CardHeader, CardTitle, CardContent } from '@/Components/ui/card';
import { Badge } from "@/Components/ui/badge";
import {
    Table,
    TableBody,
    TableCaption,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/Components/ui/table";

const props = defineProps({
    form: Object,
    responses: Array
});

// Calcul des statistiques pour chaque question
const stats = computed(() => {
    return props.responses.map(questionData => {
        const responses = questionData.responses;

        if (questionData.type === 'multiple_choice') {
            // Pour les questions à choix multiples
            const counts = {};
            responses.forEach(r => {
                const value = r.value;
                counts[value] = (counts[value || 0]) + 1; // Correction de la parenthèse
            });

            return {
                ...questionData,
                stats: {
                    counts,
                    total: responses.length
                }
            };
        }

        // Pour les questions textuelles
        return {
            ...questionData,
            responses: responses.map(r => ({
                value: r.value,
                student: r.student
            }))
        };
    });
});
</script>

<template>
    <AppLayout :title="`Résultats - ${form.title}`">
        <div class="p-6 space-y-6">
            <Card>
                <CardHeader class="flex justify-between">
                    <div>
                        <CardTitle>{{ form.title }}</CardTitle>
                        <p class="text-sm text-muted-foreground mt-2">
                            Module: {{ form.module.name }} |
                            Professeur: {{ form.module.professor.name }}
                        </p>
                    </div>
                    <Badge variant="secondary" class="h-fit">
                        {{ responses.reduce((acc, q) => acc + q.responses.length, 0) }} réponses
                    </Badge>
                </CardHeader>

                <CardContent>
                    <div class="space-y-8">
                        <Card v-for="stat in stats" :key="stat.question_id" class="mt-4">
                            <CardHeader>
                                <CardTitle class="text-lg">{{ stat.question }}</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <!-- Affichage pour les questions à choix multiples -->
                                <div v-if="stat.type === 'multiple_choice'" class="space-y-4">
                                    <div v-for="(count, answer) in stat.stats.counts"
                                         :key="answer"
                                         class="flex items-center justify-between">
                                        <span>{{ answer }}</span>
                                        <div class="flex items-center gap-4">
                                            <div class="w-40 bg-gray-200 rounded-full h-2">
                                                <div class="bg-primary h-2 rounded-full"
                                                     :style="`width: ${(count / stat.stats.total) * 100}%`">
                                                </div>
                                            </div>
                                            <Badge>
                                                {{ Math.round((count / stat.stats.total) * 100) }}%
                                            </Badge>
                                            <span class="text-sm text-gray-500">
                                                ({{ count }} réponses)
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Affichage pour les questions textuelles -->
                                <div v-else>
                                    <Table>
                                        <TableHeader>
                                            <TableRow>
                                                <TableHead>Étudiant</TableHead>
                                                <TableHead>Réponse</TableHead>
                                            </TableRow>
                                        </TableHeader>
                                        <TableBody>
                                            <TableRow v-for="(response, i) in stat.responses" :key="i">
                                                <TableCell>
                                                    {{ response.student?.name || 'Anonyme' }}
                                                </TableCell>
                                                <TableCell>{{ response.value }}</TableCell>
                                            </TableRow>
                                        </TableBody>
                                    </Table>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
