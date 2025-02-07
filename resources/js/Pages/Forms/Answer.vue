<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { Button } from '@/Components/ui/button'
import { Card, CardHeader, CardTitle, CardContent, CardDescription, CardFooter } from '@/Components/ui/card'
import { Label } from '@/Components/ui/label'
import { Input } from "@/Components/ui/input"
import { RadioGroup, RadioGroupItem } from '@/Components/ui/radio-group'
import { Textarea } from '@/Components/ui/textarea'
import { Checkbox } from "@/Components/ui/checkbox"
import { Progress } from '@/Components/ui/progress'
import StudentLayout from '@/Layouts/StudentLayout.vue'
import { Loader2 } from 'lucide-vue-next'

const props = defineProps({
  form: Object,
  token: String
})

const answers = ref({})
const processing = ref(false)
const progress = ref(0)
const currentQuestionIndex = ref(0)

const getQuestionType = (question) => {
  if (!question) return null;
  if (!question.question_type) return null;
  return question.question_type.type;
}

// Calculer la progression
const calculateProgress = () => {
  progress.value = ((currentQuestionIndex.value + 1) / props.form.questions.length) * 100
}

onMounted(() => {
  props.form.questions.forEach(question => {
    if (getQuestionType(question) === 'checkbox') {
      answers.value[question.id] = []
    } else {
      answers.value[question.id] = ''
    }
  })
  calculateProgress()
})

// Ajout d'une fonction de debug
const debugAnswers = () => {
  console.log('État actuel des réponses:', {
    answers: answers.value,
    currentQuestion: props.form.questions[currentQuestionIndex.value],
    type: getQuestionType(props.form.questions[currentQuestionIndex.value])
  });
}

const handleCheckboxChange = (value, questionId) => {
  const currentAnswers = answers.value[questionId] || [];
  const index = currentAnswers.indexOf(value);

  if (index === -1) {
    currentAnswers.push(value);
  } else {
    currentAnswers.splice(index, 1);
  }

  answers.value[questionId] = currentAnswers;
  console.log('Checkbox mise à jour:', { questionId, value, answers: answers.value[questionId] });
}

const hasAnswer = (questionId) => {
  const answer = answers.value[questionId];
  const question = props.form.questions.find(q => q.id === questionId);
  const type = getQuestionType(question);

  console.log('Vérification réponse:', {
    questionId,
    answer,
    type,
    isCheckbox: type === 'checkbox',
    answerLength: Array.isArray(answer) ? answer.length : 'not array'
  });

  if (type === 'checkbox') {
    return Array.isArray(answer) && answer.length > 0;
  }
  switch(type) {
    case 'radio':
      return typeof answer === 'string' && answer !== '';
    case 'text':
    case 'textarea':
      return typeof answer === 'string' && answer.trim() !== '';
    default:
      return false;
  }
}

// Ajout d'un watcher pour déboguer
watch(() => currentQuestionIndex.value, (newIndex) => {
  const currentQuestion = props.form.questions[newIndex];
  console.log('Question actuelle:', {
    index: newIndex,
    question: currentQuestion,
    type: getQuestionType(currentQuestion),
    answer: answers.value[currentQuestion.id],
    hasAnswer: hasAnswer(currentQuestion.id)
  });
});

// Vérifier si toutes les questions ont été répondues
const allQuestionsAnswered = computed(() => {
  return !!props.form.questions && props.form.questions.every(q => hasAnswer(q.id));
});

const nextQuestion = () => {
  if (currentQuestionIndex.value < props.form.questions.length - 1) {
    currentQuestionIndex.value++
    calculateProgress()
  }
}

const previousQuestion = () => {
  if (currentQuestionIndex.value > 0) {
    currentQuestionIndex.value--
    calculateProgress()
  }
}

// Pour la soumission, convertir les réponses en objets simples
const prepareAnswersForSubmission = () => {
  const prepared = {};
  Object.entries(answers.value).forEach(([key, value]) => {
    if (Array.isArray(value)) {
      prepared[key] = [...value]; // Pour les checkbox
    } else {
      prepared[key] = value;
    }
  });
  console.log('Réponses préparées:', prepared);
  return prepared;
}

const submitForm = () => {
  const currentQuestion = props.form.questions[currentQuestionIndex.value];
  const canSubmit = hasAnswer(currentQuestion.id);
  console.log('Tentative de soumission:', {
    currentQuestion,
    answers: prepareAnswersForSubmission(),
    canSubmit
  });

  if (!canSubmit) return;

  processing.value = true;
  useForm({ answers: prepareAnswersForSubmission() })
    .post(route('forms.submit-answer', props.token), {
      onSuccess: () => window.location.href = route('forms.thankyou'),
      onError: () => {
        processing.value = false;
        alert('Une erreur est survenue');
      }
    });
}
</script>

<template>
  <StudentLayout :title="form.title">
    <div class="container mx-auto p-4 max-w-4xl">
      <Card>
        <CardHeader>
          <CardTitle class="text-2xl">{{ form.title }}</CardTitle>
          <CardDescription>
            Question {{ currentQuestionIndex + 1 }} sur {{ form.questions.length }}
          </CardDescription>
          <Progress :value="progress" class="mt-2" />
        </CardHeader>

        <CardContent>
          <form @submit.prevent="submitForm" class="space-y-6">
            <div v-if="form.questions[currentQuestionIndex]" class="transition-all">
              <h3 class="text-lg font-medium mb-4">
                {{ form.questions[currentQuestionIndex].label }}
              </h3>

              <!-- Texte -->
              <div v-if="getQuestionType(form.questions[currentQuestionIndex]) === 'text'">
                <Input
                  v-model="answers[form.questions[currentQuestionIndex].id]"
                  class="w-full"
                  placeholder="Votre réponse..."
                />
              </div>

              <!-- Texte long -->
              <div v-else-if="getQuestionType(form.questions[currentQuestionIndex]) === 'textarea'">
                <Textarea
                  v-model="answers[form.questions[currentQuestionIndex].id]"
                  :rows="5"
                  class="w-full"
                  placeholder="Votre réponse..."
                />
              </div>

              <!-- Radio -->
              <div v-else-if="getQuestionType(form.questions[currentQuestionIndex]) === 'radio'"
                   class="space-y-3">
                <RadioGroup
                  v-model="answers[form.questions[currentQuestionIndex].id]"
                  class="space-y-2"
                >
                  <div v-for="choice in form.questions[currentQuestionIndex].choices"
                       :key="choice.id"
                       class="flex items-center space-x-2">
                    <RadioGroupItem :value="choice.text" />
                    <Label>{{ choice.text }}</Label>
                  </div>
                </RadioGroup>
              </div>

              <!-- Checkbox modifié -->
              <div v-else-if="getQuestionType(form.questions[currentQuestionIndex]) === 'checkbox'"
                   class="space-y-3">
                <div v-for="choice in form.questions[currentQuestionIndex].choices"
                     :key="choice.id"
                     class="flex items-center space-x-2">
                  <input
                    type="checkbox"
                    :id="'choice_' + choice.id"
                    :value="choice.text"
                    :checked="answers[form.questions[currentQuestionIndex].id]?.includes(choice.text)"
                    @change="handleCheckboxChange(choice.text, form.questions[currentQuestionIndex].id)"
                    class="rounded border-gray-300 text-primary focus:ring-primary"
                  />
                  <Label :for="'choice_' + choice.id">{{ choice.text }}</Label>
                </div>
              </div>
            </div>
          </form>
        </CardContent>

        <CardFooter class="flex justify-between">
          <Button
            variant="outline"
            @click="previousQuestion"
            :disabled="currentQuestionIndex === 0"
          >
            Question précédente
          </Button>

          <div class="flex gap-2">
            <Button
              v-if="currentQuestionIndex < form.questions.length - 1"
              @click="nextQuestion"
              :disabled="!hasAnswer(form.questions[currentQuestionIndex].id)"
            >
              Question suivante
            </Button>
            <Button
              v-else
              @click="submitForm"
              :disabled="!hasAnswer(form.questions[currentQuestionIndex].id)"
              class="bg-green-600 hover:bg-green-700"
            >
              <Loader2 v-if="processing" class="mr-2 h-4 w-4 animate-spin" />
              {{ processing ? 'Envoi...' : 'Terminer' }}
            </Button>
          </div>
        </CardFooter>
      </Card>
    </div>
  </StudentLayout>
</template>
