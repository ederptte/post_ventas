<script setup>
import { computed } from 'vue'

const props = defineProps({
  type: { type: String, default: 'button' },
  variante: { type: String, default: 'primario' },
  size: { type: String, default: 'md' },
  loading: { type: Boolean, default: false },
  disabled: { type: Boolean, default: false },
})

const variantes = {
  primario: 'bg-green-600 text-white hover:bg-green-500 active:bg-green-700 focus-visible:ring-green-400 shadow-sm',
  danger: 'bg-red-600 text-white hover:bg-red-500 active:bg-red-700 focus-visible:ring-red-400 shadow-sm',
  secundario: 'bg-gray-200 text-gray-800 hover:bg-gray-300 active:bg-gray-400 focus-visible:ring-gray-400',
  terciario: 'bg-blue-600 text-white hover:bg-blue-500 active:bg-blue-700 focus-visible:ring-blue-400 shadow-sm',
  outline: 'border border-gray-300 text-gray-700 hover:bg-gray-50 active:bg-gray-100 focus-visible:ring-green-400 bg-white',
  ghost: 'text-gray-600 hover:bg-gray-100 active:bg-gray-200 focus-visible:ring-gray-400',
  ghost_danger: 'text-red-600 hover:bg-red-50 active:bg-red-100 focus-visible:ring-red-400',
}

const sizes = {
  sm: 'px-3 py-1.5 text-xs font-medium',
  md: 'px-4 py-2 text-sm font-medium',
  lg: 'px-6 py-3 text-base font-semibold',
  icon: 'p-2', // Ideal para botones que solo llevan un icono
}

const classes = computed(() => [variantes[props.variante] || variantes.primario, sizes[props.size] || sizes.md])
const isDisabled = computed(() => props.disabled || props.loading)
</script>

<template>
  <button
    :type="type"
    :disabled="isDisabled"
    :aria-busy="loading"
    class="inline-flex items-center justify-center gap-2 rounded-lg cursor-pointer select-none
           transition-all duration-150 ease-in-out
           active:scale-[0.98]
           focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2
           disabled:opacity-60 disabled:cursor-not-allowed disabled:active:scale-100"
    :class="classes"
  >
    <!-- Spinner de carga -->
    <svg
      v-if="loading"
      class="w-4 h-4 animate-spin shrink-0"
      xmlns="http://www.w3.org/2000/svg"
      fill="none"
      viewBox="0 0 24 24"
      aria-hidden="true"
    >
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
    </svg>

    <!-- Slot para icono opcional (se oculta si está cargando para dar paso al spinner) -->
    <slot v-if="!loading" name="icon" />

    <!-- Texto o contenido del botón -->
    <slot />
  </button>
</template>