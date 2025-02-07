<script setup>
import StudentLayout from '@/Layouts/StudentLayout.vue'
import { Card, CardHeader, CardTitle, CardContent, CardFooter } from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'
import { useForm } from '@inertiajs/vue3'

const form = useForm({})
</script>

<template>
  <StudentLayout title="Erreur">
    <div class="container mx-auto p-4">
      <Card class="max-w-lg mx-auto">
        <CardHeader>
          <CardTitle class="text-red-600">Erreur d'accès</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="text-center space-y-4">
            <div class="text-4xl text-red-500 mb-4">
              <i class="ri-error-warning-line"></i>
            </div>
            <p class="text-gray-600">
              Ce lien n'est plus valide ou a déjà été utilisé.
            </p>
            <p class="text-sm text-gray-500">
              Si vous pensez qu'il s'agit d'une erreur, veuillez contacter votre responsable pédagogique.
            </p>
          </div>
        </CardContent>
        <CardFooter class="flex justify-center">
          <Button
            variant="outline"
            @click="() => window.close()"
            class="mt-4"
          >
            Fermer cette page
          </Button>
        </CardFooter>
      </Card>
    </div>
  </StudentLayout>
</template>
