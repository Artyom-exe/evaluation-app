<script setup>
import { Head } from '@inertiajs/vue3'

defineProps({
  title: {
    type: String,
    required: true
  }
})
</script>

<template>
  <div>
    <Head :title="title" />

    <main class="min-h-screen bg-gray-100 py-6">
      <slot />
    </main>

    <footer class="bg-white border-t py-4">
      <div class="container mx-auto px-4 text-center text-sm text-gray-600">
        © {{ new Date().getFullYear() }} Evaluation App
      </div>
    </footer>
  </div>
</template>
