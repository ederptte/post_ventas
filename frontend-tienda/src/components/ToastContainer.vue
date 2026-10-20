<script setup>
import { Icon } from '@iconify/vue'
import { useToast } from '../stores/useToast.js'

const { toasts, quitar } = useToast()

const estilos = {
  success: { clase: 'border-green-500 bg-green-50 text-green-800', icono: 'lucide:check-circle' },
  error: { clase: 'border-red-500 bg-red-50 text-red-800', icono: 'lucide:x-circle' },
  warning: { clase: 'border-yellow-500 bg-yellow-50 text-yellow-800', icono: 'lucide:alert-triangle' },
  info: { clase: 'border-blue-500 bg-blue-50 text-blue-800', icono: 'lucide:info' },
}
</script>

<template>
  <Teleport to="body">
    <div class="fixed right-4 top-4 z-50 flex w-full max-w-sm flex-col gap-2">
      <TransitionGroup
        enter-active-class="transition duration-200"
        enter-from-class="translate-x-4 opacity-0"
        leave-active-class="transition duration-150"
        leave-to-class="opacity-0"
      >
        <div
          v-for="toast in toasts"
          :key="toast.id"
          :role="toast.tipo === 'error' ? 'alert' : 'status'"
          class="flex items-start gap-3 rounded-lg border-l-4 p-4 shadow-lg"
          :class="estilos[toast.tipo].clase"
        >
          <Icon :icon="estilos[toast.tipo].icono" class="mt-0.5 h-5 w-5 shrink-0" />
          <p class="flex-1 text-sm font-medium">{{ toast.mensaje }}</p>
          <button
            type="button"
            aria-label="Cerrar notificación"
            class="shrink-0 cursor-pointer rounded p-0.5 opacity-60 transition hover:opacity-100
                   focus:outline-none focus-visible:ring-2 focus-visible:ring-current"
            @click="quitar(toast.id)"
          >
            <Icon icon="lucide:x" class="h-4 w-4" />
          </button>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>