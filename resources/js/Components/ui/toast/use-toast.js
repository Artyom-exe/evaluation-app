import { ref } from 'vue'

const toasts = ref([])

export function useToast() {
  function toast({ title, description, variant = '' }) {
    const id = Math.random().toString(36).slice(2, 9)
    const newToast = {
      id,
      title,
      description,
      variant,
    }

    toasts.value.push(newToast)

    setTimeout(() => {
      toasts.value = toasts.value.filter((toast) => toast.id !== id)
    }, 5000)
  }

  return {
    toasts,
    toast,
  }
}
