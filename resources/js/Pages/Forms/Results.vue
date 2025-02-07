<script setup>
import { ref, computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { Card, CardHeader, CardTitle, CardContent, CardDescription } from '@/Components/ui/card';
import { Badge } from "@/Components/ui/badge";
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/Components/ui/table";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/Components/ui/tabs"
import { ScrollArea } from "@/Components/ui/scroll-area"
import {
    PieChart,
    BarChart,
    CheckCircle,
    Users,
    ListChecks
} from 'lucide-vue-next'

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

const getParticipationStatus = computed(() => {
    const rate = participationRate.value
    if (rate >= 75) return { color: 'text-green-500', text: 'Excellente participation' }
    if (rate >= 50) return { color: 'text-yellow-500', text: 'Bonne participation' }
    return { color: 'text-red-500', text: 'Participation faible' }
})
</script>

<template>
  <AppLayout :title="`Résultats - ${form.title}`">
    <div class="container mx-auto p-6">
      <!-- En-tête avec statistiques -->
      <div class="grid gap-4 mb-8 md:grid-cols-4">
        <Card>
          <CardHeader class="flex flex-row items-center justify-between pb-2 space-y-0">
            <CardTitle class="text-sm font-medium">Taux de participation</CardTitle>
            <PieChart class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold" :class="getParticipationStatus.color">
              {{ participationRate }}%
            </div>
            <p class="text-xs text-muted-foreground">
              {{ getParticipationStatus.text }}
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between pb-2 space-y-0">
            <CardTitle class="text-sm font-medium">Réponses reçues</CardTitle>
            <CheckCircle class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ studentsCount }}</div>
            <p class="text-xs text-muted-foreground">
              sur {{ totalStudents }} étudiants
            </p>
          </CardContent>
        </Card>
      </div>

      <!-- Contenu principal -->
      <Tabs defaultValue="summary" class="space-y-4">
        <TabsList>
          <TabsTrigger value="summary">Vue d'ensemble</TabsTrigger>
          <TabsTrigger value="details">Détails par question</TabsTrigger>
        </TabsList>

        <TabsContent value="summary">
          <Card>
            <CardHeader>
              <CardTitle>Résumé des réponses</CardTitle>
              <CardDescription>Vue globale des réponses par type de question</CardDescription>
            </CardHeader>
            <CardContent>
              <ScrollArea class="h-[60vh] px-1">
                <!-- Contenu existant optimisé -->
              </ScrollArea>
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="details">
          <div class="grid gap-4">
            <Card v-for="stat in stats" :key="stat.question_id">
              <CardHeader>
                <CardTitle class="text-lg">{{ stat.question }}</CardTitle>
                <CardDescription>{{ stat.type }}</CardDescription>
              </CardHeader>

              <CardContent>
                <!-- Questions à choix -->
                <div v-if="['checkbox', 'radio'].includes(stat.type) && stat.stats?.counts">
                  <div class="space-y-4">
                    <div v-for="(count, answer) in stat.stats.counts"
                         :key="answer"
                         class="flex items-center gap-4">
                      <div class="w-48 truncate">{{ answer }}</div>
                      <div class="flex-1">
                        <div class="w-full bg-secondary rounded-full h-2">
                          <div class="bg-primary h-2 rounded-full"
                               :style="`width: ${(count / stat.stats.total) * 100}%`">
                          </div>
                        </div>
                      </div>
                      <div class="w-20 text-right">
                        {{ Math.round((count / stat.stats.total) * 100) }}%
                      </div>
                      <Badge variant="secondary">
                        {{ count }}
                      </Badge>
                    </div>
                  </div>
                </div>

                <!-- Réponses textuelles -->
                <div v-else>
                  <ScrollArea class="h-[300px] w-full rounded-md border p-4">
                    <div v-for="(response, i) in stat.responses" :key="i" class="mb-4 last:mb-0">
                      <div class="font-medium text-sm text-muted-foreground mb-1">
                        {{ response.student?.name || 'Anonyme' }}
                      </div>
                      <div class="bg-secondary p-3 rounded-lg">
                        {{ response.value }}
                      </div>
                    </div>
                  </ScrollArea>
                </div>
              </CardContent>
            </Card>
          </div>
        </TabsContent>
      </Tabs>
    </div>
  </AppLayout>
</template>
