<script setup>
import { useScroll } from '@vueuse/core'
import { ref } from 'vue'
import { cn } from '@/lib/utils'

const container = ref(null)
const { x, y } = useScroll(container)

defineProps({
  className: {
    type: String,
    default: ''
  }
})
</script>

<template>
  <div
    ref="container"
    :class="cn('relative overflow-auto', className)"
  >
    <slot />
    <div
      v-if="y.value > 0"
      class="absolute right-1 top-1 bottom-1 w-2 transition-all"
    >
      <div
        class="absolute rounded-full bg-border w-full opacity-50"
        :style="{
          height: `${(container?.clientHeight / container?.scrollHeight) * 100}%`,
          transform: `translateY(${(y.value / container?.scrollHeight) * container?.clientHeight}px)`
        }"
      />
    </div>
  </div>
</template>
