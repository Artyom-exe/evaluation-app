<script setup>
import { TabsList } from 'radix-vue'
import { cn } from '@/lib/utils'
</script>

<template>
  <TabsList
    :class="cn(
      'inline-flex h-10 items-center justify-center rounded-lg bg-muted p-1 text-muted-foreground',
      $attrs.class ?? ''
    )"
    v-bind="$attrs"
  >
    <slot />
  </TabsList>
</template>
