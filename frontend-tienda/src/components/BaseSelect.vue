<script setup>
import { useId } from 'vue'

defineOptions({ inheritAttrs: false })

defineProps({
  label: { type: String, default: '' },
  options: { type: Array, required: true },
  placeholder: { type: String, default: 'Selecciona una opción' },
  error: { type: String, default: '' },
  disabled: { type: Boolean, default: false },
})

const model = defineModel()
const id = useId()
</script>

<template>
  <div class="flex flex-col gap-1">
    <label v-if="label" :for="id" class="text-sm font-medium text-gray-700">
      {{ label }}
    </label>

    <select
      :id="id"
      v-model="model"
      v-bind="$attrs"
      :disabled="disabled"
      :aria-invalid="!!error"
      class="px-3 py-2 rounded-lg border bg-white text-gray-900
             transition duration-150
             focus:outline-none focus:ring-2
             disabled:bg-gray-100 disabled:cursor-not-allowed"
      :class="error
        ? 'border-red-500 focus:ring-red-300'
        : 'border-gray-300 focus:ring-green-400 focus:border-green-500'"
    >
      <option disabled value="">{{ placeholder }}</option>
      <option
        v-for="option in options"
        :key="option.value"
        :value="option.value"
      >
        {{ option.label }}
      </option>
    </select>

    <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
  </div>
</template>
