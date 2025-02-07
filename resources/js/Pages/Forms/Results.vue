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
    responses: Array,
    studentsCount: Number,
    totalStudents: Number
});

const parseValue = (value) => {
    if (!value) return [];
    if (Array.isArray(value)) return value;

    try {
        const parsed = JSON.parse(value);
        return Array.isArray(parsed) ? parsed : [parsed];
    } catch (e) {
        return [value];
    }
};

const formatCheckboxValue = (value) => {
    if (!value) return [];
    if (Array.isArray(value)) return value;
    if (typeof value === 'string') {
        try {
            const parsed = JSON.parse(value);
            return Array.isArray(parsed) ? parsed : [parsed];
        } catch (e) {
            return [value];
        }
    }
    return [value];
};

const stats = computed(() => {
    return props.responses.map(questionData => {
        const responses = questionData.responses;
        const total = responses.length;

        // Pour les questions à choix (checkbox ou radio)
        if (['checkbox', 'radio'].includes(questionData.type)) {
            const counts = {};
            responses.forEach(r => {
                // Pour checkbox, r.value est déjà un tableau
                if (Array.isArray(r.value)) {
                    r.value.forEach(val => {
                        counts[val] = (counts[val] || 0) + 1;
                    });
                } else {
                    counts[r.value] = (counts[r.value] || 0) + 1;
                }
            });

            return {
                ...questionData,
                stats: { counts, total },
                responses
            };
        }

        // Pour les questions textuelles
        return questionData;
    });
});

// Calcul de la classe CSS pour le taux de participation
const getParticipationClass = computed(() => {
    if (!props.totalStudents) return 'text-gray-600';
    const rate = (props.studentsCount / props.totalStudents) * 100;
    if (rate >= 75) return 'text-green-600';
    if (rate >= 50) return 'text-yellow-600';
    return 'text-red-600';
});

// Calcul du taux de participation
const participationRate = computed(() => {
    if (!props.totalStudents) return 0;
    return Math.round((props.studentsCount / props.totalStudents) * 100);
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
                    <div class="flex flex-col items-end gap-2">
                        <Badge variant="secondary" class="h-fit">
                            {{ studentsCount }} étudiant{{ studentsCount > 1 ? 's' : '' }} ont répondu
                        </Badge>
                        <p class="text-sm text-muted-foreground">
                            sur {{ totalStudents }} étudiant{{ totalStudents > 1 ? 's' : '' }} dans le module
                            ({{ form.module.students?.length || 0 }} inscrits)
                        </p>
                        <p v-if="totalStudents > 0" class="text-sm" :class="getParticipationClass">
                            Taux de participation : {{ participationRate }}%
                        </p>
                        <p v-else class="text-sm text-gray-500">
                            Aucun étudiant inscrit dans le module
                        </p>
                    </div>
                </CardHeader>

                <CardContent>
                    <div v-for="stat in stats" :key="stat.question_id" class="mb-8">
                        <h3 class="text-lg font-semibold mb-4">{{ stat.question }}</h3>

                        <!-- Affichage des réponses -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Étudiant</TableHead>
                                        <TableHead>Réponse(s)</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="(response, i) in stat.responses" :key="i">
                                        <TableCell>{{ response.student?.name || 'Anonyme' }}</TableCell>
                                        <TableCell>
                                            <div class="flex flex-wrap gap-2">
                                                <template v-if="stat.type === 'checkbox'">
                                                    <Badge
                                                        v-for="(value, index) in (Array.isArray(response.value) ? response.value : [])"
                                                        :key="index"
                                                        variant="secondary"
                                                    >
                                                        {{ value }}
                                                    </Badge>
                                                </template>
                                                <template v-else>
                                                    {{ response.value }}
                                                </template>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>

                            <!-- Statistiques pour les questions à choix -->
                            <div v-if="['checkbox', 'radio'].includes(stat.type) &&
                                       stat.stats?.counts &&
                                       Object.keys(stat.stats.counts).length > 0"
                                 class="mt-6">
                                <h4 class="font-medium mb-4">Statistiques des réponses</h4>
                                <div class="space-y-3">
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
                                                ({{ count }} sélection{{ count > 1 ? 's' : '' }})
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
