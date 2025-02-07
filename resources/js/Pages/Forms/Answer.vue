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
              <div v-for="question in form.questions" :key="question.id">
                <Label :for="'question_' + question.id">
                  {{ question.label }}
                </Label>

                <div v-if="question.question_type?.type === 'multiple_choice'" class="mt-2">
                  <RadioGroup v-model="answers[question.id]">
                    <div class="space-y-2">
                      <RadioGroupItem
                        v-for="choice in question.choices"
                        :key="choice.id"
                        :value="choice.id"
                      >
                        {{ choice.text }}
                      </RadioGroupItem>
                    </div>
                  </RadioGroup>
                </div>

                <div v-else class="mt-2">
                  <Textarea
                    v-model="answers[question.id]"
                    :id="'question_' + question.id"
                    placeholder="Votre réponse..."
                  />
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

<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { Button } from '@/Components/ui/button'
import { Card, CardHeader, CardTitle, CardContent } from '@/Components/ui/card'
import { Label } from '@/Components/ui/label'
import { RadioGroup, RadioGroupItem } from '@/Components/ui/radio-group'
import { Textarea } from '@/Components/ui/textarea'
// Changer l'import de ReloadIcon
import { Loader2 } from 'lucide-vue-next'
import StudentLayout from '@/Layouts/StudentLayout.vue'

const props = defineProps({
  form: Object,
  token: String
})

const answers = ref({})
const processing = ref(false)

const submitForm = () => {
  processing.value = true
  useForm({
    answers: answers.value
  }).post(route('forms.submit-answer', props.token), {  // Utilisez props.token ici
    onSuccess: () => {
      processing.value = false
      // Rediriger vers la page de remerciement
      window.location.href = route('forms.thankyou')
    },
    onError: () => {
      processing.value = false
      alert('Une erreur est survenue lors de l\'envoi des réponses')
    }
  })
}
</script>
