<script setup>
import { useId } from 'vue'

defineProps({
  label: { type: String, default: '' },
  type: { type: String, default: 'text' },
  placeholder: { type: String, default: '' },
  error: { type: String, default: '' },
  disabled: { type: Boolean, default: false },
  clearable: { type: Boolean, default: false }, // 👈 Nueva prop para activar la "X"
})

const model = defineModel()
const id = useId()

const limpiarInput = () => {
  model.value = ''
}

defineOptions({ inheritAttrs: false })
</script>

<template>
  <div class="flex flex-col gap-1">
    <label v-if="label" :for="id" class="text-sm font-medium text-gray-700">
      {{ label }}
    </label>

    <!-- Contenedor relativo para posicionar la "X" dentro del input -->
    <div class="relative flex items-center">
      <input
        :id="id"
        v-model="model"
        v-bind="$attrs"
        :type="type"
        :placeholder="placeholder"
        :disabled="disabled"
        :aria-invalid="!!error"
        class="w-full px-3 py-2 rounded-lg border bg-white text-gray-900
               transition duration-150
               focus:outline-none focus:ring-2
               disabled:bg-gray-100 disabled:cursor-not-allowed"
        :class="[
          error
            ? 'border-red-500 focus:ring-red-300'
            : 'border-gray-300 focus:ring-green-400 focus:border-green-500',
          // Agregamos un padding a la derecha si es clearable para que el texto no choque con la X
          clearable && model ? 'pr-10' : 'pr-3'
        ]"
      />

      <!-- Botón de la X (Solo aparece si clearable es true y hay texto escrito) -->
      <button
        v-if="clearable && model"
        type="button"
        @click="limpiarInput"
        class="absolute right-3 text-gray-400 hover:text-gray-600 focus:outline-none p-1"
        title="Limpiar búsqueda"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>

    <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
  </div>
</template>