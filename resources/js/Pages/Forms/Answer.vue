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

// Ajouter la fonction debounce au début du fichier
function debounce(fn, delay) {
  let timeoutId;
  return function (...args) {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => fn.apply(this, args), delay);
  };
}

const props = defineProps({
  form: Object,
  token: String,
  savedAnswers: Object
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

// Fonction pour sauvegarder dans le localStorage
const saveToLocalStorage = () => {
  const storageKey = `form_answers_${props.token}`
  localStorage.setItem(storageKey, JSON.stringify({
    answers: answers.value,
    lastQuestionIndex: currentQuestionIndex.value,
    timestamp: new Date().getTime()
  }))
}

// Fonction pour charger depuis le localStorage
const loadFromLocalStorage = () => {
  const storageKey = `form_answers_${props.token}`
  const saved = localStorage.getItem(storageKey)
  if (saved) {
    try {
      const { answers: savedAnswers, lastQuestionIndex, timestamp } = JSON.parse(saved)
      // Vérifier si les données ont moins de 24h
      const now = new Date().getTime()
      const isValid = (now - timestamp) < 24 * 60 * 60 * 1000

      if (isValid) {
        answers.value = savedAnswers
        currentQuestionIndex.value = lastQuestionIndex
        return true
      }
    } catch (e) {
      console.error('Erreur lors du chargement des réponses:', e)
    }
  }
  return false
}

// Nettoyer le localStorage une fois le formulaire soumis
const clearLocalStorage = () => {
  const storageKey = `form_answers_${props.token}`
  localStorage.removeItem(storageKey)
}

onMounted(() => {
  // D'abord essayer de charger depuis le localStorage
  const loadedFromStorage = loadFromLocalStorage()

  if (!loadedFromStorage) {
    // Si rien dans le localStorage, initialiser avec les réponses sauvegardées du serveur ou valeurs par défaut
    props.form.questions.forEach(question => {
      if (props.savedAnswers && props.savedAnswers[question.id] !== undefined) {
        answers.value[question.id] = props.savedAnswers[question.id]
      } else {
        answers.value[question.id] = getQuestionType(question) === 'checkbox' ? [] : ''
      }
    })
  }

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

// Modifier la fonction submitForm pour nettoyer le localStorage
const submitForm = () => {
  const currentQuestion = props.form.questions[currentQuestionIndex.value];
  const canSubmit = hasAnswer(currentQuestion.id);

  if (!canSubmit) return;

  processing.value = true;
  useForm({ answers: prepareAnswersForSubmission() })
    .post(route('forms.submit-answer', props.token), {
      onSuccess: () => {
        clearLocalStorage();
        window.location.href = route('forms.thankyou');
      },
      onError: (errors) => {
        processing.value = false;
        if (errors.error) {
          alert(errors.error);
        } else {
          alert('Une erreur est survenue lors de la soumission');
        }
      }
    });
}

const handleKeyDown = (event) => {
  if (event.key === 'Enter') {
    event.preventDefault(); // Empêche la soumission du formulaire
    const currentQuestion = props.form.questions[currentQuestionIndex.value];
    if (hasAnswer(currentQuestion.id)) {
      if (currentQuestionIndex.value < props.form.questions.length - 1) {
        nextQuestion();
      } else {
        submitForm();
      }
    }
  }
}

// Mettre cette fonction avant qu'elle ne soit utilisée
const autoSave = debounce(() => {
  if (!props.token) return;

  useForm({ answers: prepareAnswersForSubmission() })
    .post(route('forms.save-progress', props.token), {
      preserveScroll: true,
      preserveState: true,
      onError: () => {
        console.error('Erreur lors de la sauvegarde automatique');
      }
    });
}, 1000);

// Modifier le watcher pour qu'il ne s'exécute que si la question existe
watch([answers, currentQuestionIndex], () => {
  if (props.form.questions[currentQuestionIndex.value]) {
    saveToLocalStorage();
    autoSave();
  }
}, { deep: true })
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
                  @keydown="handleKeyDown"
                />
              </div>

              <!-- Texte long -->
              <div v-else-if="getQuestionType(form.questions[currentQuestionIndex]) === 'textarea'">
                <Textarea
                  v-model="answers[form.questions[currentQuestionIndex].id]"
                  :rows="5"
                  class="w-full"
                  placeholder="Votre réponse..."
                  @keydown="handleKeyDown"
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
                  <Checkbox
                    :id="'choice_' + choice.id"
                    :checked="answers[form.questions[currentQuestionIndex].id]?.includes(choice.text)"
                    @update:checked="(checked) => {
                      handleCheckboxChange(choice.text, form.questions[currentQuestionIndex].id)
                    }"
                  />
                  <Label :for="'choice_' + choice.id" class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70">
                    {{ choice.text }}
                  </Label>
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
