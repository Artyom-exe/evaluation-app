<script setup>
import { TabsRoot } from 'radix-vue'

const props = defineProps({
  defaultValue: {
    type: String,
    required: false
  }
})
</script>

<template>
  <TabsRoot v-bind="{ ...props, ...$attrs }">
    <slot />
  </TabsRoot>
</template>
