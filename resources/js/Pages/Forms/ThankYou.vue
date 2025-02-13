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
    </Card>
  </div>
</template>

<script setup>
import { Card, CardHeader, CardTitle, CardContent } from '@/Components/ui/card'
</script>
