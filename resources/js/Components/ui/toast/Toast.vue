<script setup>
import { computed } from 'vue'
import {
  ToastRoot,
  ToastTitle,
  ToastDescription,
  ToastClose,
  ToastProvider,
  ToastViewport,
} from 'radix-vue'
import { XIcon } from 'lucide-vue-next'

const props = defineProps({
  toast: {
    type: Object,
    required: true,
  },
})

const styles = computed(() => {
  return {
    base: 'group pointer-events-auto relative flex w-full items-center justify-between space-x-4 overflow-hidden rounded-md border p-6 pr-8 shadow-lg transition-all',
    standard: 'bg-background',
    destructive: 'destructive border-destructive bg-destructive text-destructive-foreground'
  }
})
</script>

<template>
  <ToastRoot
    :class="[
      styles.base,
      props.toast.variant === 'destructive' ? styles.destructive : styles.standard
    ]"
  >
    <div class="grid gap-1">
      <ToastTitle class="text-sm font-semibold">
        {{ props.toast.title }}
      </ToastTitle>
      <ToastDescription v-if="props.toast.description" class="text-sm opacity-90">
        {{ props.toast.description }}
      </ToastDescription>
    </div>
    <ToastClose
      class="absolute right-2 top-2 rounded-md p-1 text-foreground/50 opacity-0 transition-opacity hover:text-foreground focus:opacity-100 focus:outline-none focus:ring-2 group-hover:opacity-100"
    >
      <XIcon class="h-4 w-4" />
    </ToastClose>
  </ToastRoot>
</template>
