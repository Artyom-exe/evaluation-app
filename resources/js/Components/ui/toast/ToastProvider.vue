<script setup>
import { useToast } from './use-toast'
import { ToastProvider, ToastViewport } from 'radix-vue'
import Toast from './Toast.vue'

const { toasts } = useToast()
</script>

<template>
  <ToastProvider>
    <ToastViewport class="fixed top-0 right-0 z-[100] flex max-h-screen w-full flex-col-reverse p-4 sm:bottom-0 sm:right-0 sm:top-auto sm:flex-col md:max-w-[420px]">
      <Toast v-for="toast in toasts" :key="toast.id" :toast="toast" />
    </ToastViewport>
  </ToastProvider>
</template>
