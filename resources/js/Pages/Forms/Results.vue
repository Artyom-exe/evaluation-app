<script setup>
import { computed, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';  // Ajout de l'import du router
import AppLayout from '@/Layouts/AppLayout.vue';
import { Card, CardHeader, CardTitle, CardContent } from '@/Components/ui/card';
import { Badge } from "@/Components/ui/badge";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/Components/ui/tabs";
import { PieChart, CheckCircle, ListChecks } from 'lucide-vue-next';

const props = defineProps({
    form: Object,
    responses: Array,
    studentsCount: Number,
    totalStudents: Number,
    modules: Array
});

const stats = computed(() => {
    return props.responses.map(questionData => {
        const responses = questionData.responses || [];
        const total = responses.length;

        if (['checkbox', 'radio'].includes(questionData.type)) {
            const counts = {};
            responses.forEach(r => {
                const value = r.value;
                if (Array.isArray(value)) {
                    value.forEach(val => {
                        counts[val] = (counts[val] || 0) + 1;
                    });
                } else if (value) {
                    counts[value] = (counts[value] || 0) + 1;
                }
            });

            // Trier les réponses par nombre décroissant
            const sortedCounts = Object.entries(counts)
                .sort(([,a], [,b]) => b - a)
                .reduce((acc, [key, value]) => ({
                    ...acc,
                    [key]: value
                }), {});

            return {
                ...questionData,
                stats: { counts: sortedCounts, total },
                responses
            };
        }

        return {
            ...questionData,
            responses
        };
    });
});

// Statut de participation
const participationRate = computed(() => {
    if (!props.totalStudents) return 0;
    return Math.round((props.studentsCount / props.totalStudents) * 100);
});

const getParticipationStatus = computed(() => {
    const rate = participationRate.value;
    if (rate >= 75) return { color: 'text-green-500', text: 'Excellente participation' };
    if (rate >= 50) return { color: 'text-yellow-500', text: 'Bonne participation' };
    return { color: 'text-red-500', text: 'Participation faible' };
});

// Supprimons l'intervalle de vérification du statut
onMounted(() => {
    // Ne rien faire au montage
});

onUnmounted(() => {
    // Ne rien faire au démontage
});
</script>

<template>
  <AppLayout :title="`Résultats - ${form.title}`">
    <div class="container mx-auto p-6">
      <!-- En-tête avec stats principales -->
      <div class="grid gap-4 mb-8 md:grid-cols-3">
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
              {{ studentsCount }} sur {{ totalStudents }} étudiants
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between pb-2 space-y-0">
            <CardTitle class="text-sm font-medium">Module</CardTitle>
            <CheckCircle class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-lg font-medium">{{ form.module.name }}</div>
            <p class="text-xs text-muted-foreground">
              {{ form.module.year.name }}
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between pb-2 space-y-0">
            <CardTitle class="text-sm font-medium">Questions</CardTitle>
            <ListChecks class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ stats.length }}</div>
          </CardContent>
        </Card>
      </div>

      <!-- Liste des questions et réponses -->
      <div class="space-y-6">
        <Card v-for="stat in stats" :key="stat.question_id">
          <CardHeader class="border-b">
            <CardTitle class="text-lg">{{ stat.question }}</CardTitle>
          </CardHeader>

          <CardContent class="pt-6">
            <!-- Questions à choix -->
            <div v-if="['checkbox', 'radio'].includes(stat.type) && stat.stats?.counts"
                 class="space-y-4">
              <div v-for="(count, answer) in stat.stats.counts"
                   :key="answer"
                   class="relative">
                <div class="flex items-center justify-between mb-2">
                  <span class="font-medium">{{ answer }}</span>
                  <span class="text-muted-foreground">
                    {{ count }}/{{ stat.stats.total }}
                  </span>
                </div>
                <div class="w-full bg-secondary/30 rounded-full h-2.5">
                  <div class="bg-primary h-2.5 rounded-full transition-all"
                       :style="`width: ${(count / stat.stats.total) * 100}%`">
                  </div>
                </div>
              </div>
            </div>

            <!-- Réponses textuelles -->
            <div v-else class="space-y-3">
              <div v-for="response in stat.responses"
                   :key="response.student?.email"
                   class="bg-muted rounded-lg p-3">
                <div class="font-medium text-sm mb-2 text-muted-foreground">
                  {{ response.student?.name || 'Anonyme' }}
                </div>
                <p class="whitespace-pre-wrap">{{ response.value }}</p>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>
