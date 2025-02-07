<script setup>
import { ref, onMounted } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { Button } from '@/Components/ui/button'
import { Card, CardHeader, CardTitle, CardContent } from '@/Components/ui/card'
import { Label } from '@/Components/ui/label'
import { Input } from "@/Components/ui/input"
import { RadioGroup, RadioGroupItem } from '@/Components/ui/radio-group'
import { Textarea } from '@/Components/ui/textarea'
import { Checkbox } from "@/Components/ui/checkbox"
import { Loader2 } from 'lucide-vue-next'
import StudentLayout from '@/Layouts/StudentLayout.vue'

const props = defineProps({
  form: Object,
  token: String
})

const answers = ref({})
const processing = ref(false)

const getQuestionType = (question) => {
  const type = question.question_type?.type;
  console.log('Question type:', type, 'Question:', question);
  return type;
}

// Initialiser les réponses selon le type de question
onMounted(() => {
  props.form.questions.forEach(question => {
    if (question.question_type?.type === 'checkbox') {
      answers.value[question.id] = []; // Initialiser avec un tableau vide pour checkbox
    } else {
      answers.value[question.id] = ''; // String pour les autres types
    }
  });
})

const submitForm = () => {
  processing.value = true;

  // Log pour déboguer
  console.log('Réponses avant envoi:', answers.value);

  useForm({
    answers: answers.value
  }).post(route('forms.submit-answer', props.token), {
    onSuccess: () => {
      processing.value = false;
      window.location.href = route('forms.thankyou');
    },
    onError: () => {
      processing.value = false;
      alert('Une erreur est survenue lors de l\'envoi des réponses');
    }
  });
}
</script>

<template>
  <StudentLayout :title="form.title">
    <div class="container mx-auto p-4">
      <Card class="max-w-4xl mx-auto">
        <CardHeader>
          <CardTitle>{{ form.title }}</CardTitle>
        </CardHeader>
        <CardContent>
          <form @submit.prevent="submitForm">
            <div class="space-y-6">
              <div v-for="question in form.questions" :key="question.id" class="border-b pb-4">
                <Label :for="'question_' + question.id" class="text-lg font-medium">
                  {{ question.label }}
                </Label>

                <!-- Champ texte court -->
                <div v-if="getQuestionType(question) === 'text'" class="mt-4">
                  <Input
                    v-model="answers[question.id]"
                    :id="'question_' + question.id"
                    type="text"
                    placeholder="Votre réponse..."
                    class="w-full"
                  />
                </div>

                <!-- Champ texte long -->
                <div v-else-if="getQuestionType(question) === 'textarea'" class="mt-4">
                  <Textarea
                    v-model="answers[question.id]"
                    :id="'question_' + question.id"
                    placeholder="Votre réponse..."
                    rows="5"
                    class="w-full"
                  />
                </div>

                <!-- Choix unique (radio) -->
                <div v-else-if="getQuestionType(question) === 'radio' && question.choices" class="mt-4">
                  <RadioGroup v-model="answers[question.id]" class="space-y-2">
                    <div v-for="choice in question.choices" :key="choice.id"
                         class="flex items-center space-x-2">
                      <RadioGroupItem :value="choice.text" :id="'choice_' + choice.id" />
                      <Label :for="'choice_' + choice.id">{{ choice.text }}</Label>
                    </div>
                  </RadioGroup>
                </div>

                <!-- Choix multiples (checkbox) -->
                <div v-else-if="getQuestionType(question) === 'checkbox' && question.choices" class="mt-4">
                  <div v-for="choice in question.choices" :key="choice.id"
                       class="flex items-center space-x-2 mb-2">
                    <input
                      type="checkbox"
                      :id="'choice_' + choice.id"
                      :value="choice.text"
                      v-model="answers[question.id]"
                      class="rounded border-gray-300 text-primary focus:ring-primary"
                    />
                    <Label :for="'choice_' + choice.id">{{ choice.text }}</Label>
                  </div>
                </div>
              </div>
            </div>

            <Button
              type="submit"
              class="w-full mt-6"
              :disabled="processing"
            >
              <Loader2 v-if="processing" class="mr-2 h-4 w-4 animate-spin" />
              {{ processing ? 'Envoi en cours...' : 'Envoyer mes réponses' }}
            </Button>
          </form>
        </CardContent>
      </Card>
    </div>
  </StudentLayout>
</template>
