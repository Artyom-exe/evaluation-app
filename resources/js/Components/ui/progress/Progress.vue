<script setup>
import { cn } from '@/lib/utils'

defineProps({
  value: {
    type: Number,
    default: 0
  }
})
</script>

<template>
  <div
    class="relative h-2 w-full overflow-hidden rounded-full bg-secondary"
  >
    <div
      class="h-full w-full flex-1 bg-primary transition-all"
      :style="{ transform: `translateX(-${100 - (value || 0)}%)` }"
    />
  </div>
</template>
