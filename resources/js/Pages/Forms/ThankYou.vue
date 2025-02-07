<template>
  <div class="container mx-auto p-4">
    <Card class="w-full max-w-lg mx-auto">
      <CardHeader>
        <CardTitle class="text-center">Merci pour vos réponses !</CardTitle>
      </CardHeader>
      <CardContent>
        <p class="text-center text-muted-foreground">
          Vos réponses ont été enregistrées avec succès.
        </p>
      </CardContent>
      <CardFooter class="flex justify-center">
        <Button variant="outline" @click="$inertia.visit(route('forms.index'))">
          Retour à l'accueil
        </Button>
      </CardFooter>
    </Card>
  </div>
</template>

<script setup>
import { Card, CardHeader, CardTitle, CardContent, CardFooter } from '@/Components/ui/card'
import { Button } from '@/Components/ui/button'
</script>
