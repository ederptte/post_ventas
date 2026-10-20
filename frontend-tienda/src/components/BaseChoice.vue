<script setup>
import { ref, useId } from 'vue'

const props = defineProps({
  label: { type: String, default: '' },
  options: { type: Array, required: true }, // { value, label, short? }
  error: { type: String, default: '' },
  disabled: { type: Boolean, default: false },
  clearable: { type: Boolean, default: false },
})

const model = defineModel({ default: '' })
const id = useId()
const buttons = ref([])

const seleccionar = (value) => {
  // Con clearable, volver a pulsar la opción elegida la desmarca
  model.value = props.clearable && model.value === value ? '' : value
}

// Solo un botón entra con Tab: el elegido, o el primero si no hay ninguno
const tabIndex = (i) => {
  const elegido = props.options.findIndex((o) => o.value === model.value)
  return (elegido === -1 ? i === 0 : i === elegido) ? 0 : -1
}

// Flechas para moverse entre opciones
const onKey = (e, i) => {
  const n = props.options.length
  let siguiente

  if (e.key === 'ArrowRight' || e.key === 'ArrowDown') siguiente = (i + 1) % n
  else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') siguiente = (i - 1 + n) % n
  else return

  e.preventDefault()
  model.value = props.options[siguiente].value
  buttons.value[siguiente]?.focus()
}
</script>

<template>
  <div class="flex flex-col gap-1">
    <span v-if="label" :id="`${id}-label`" class="mb-1 text-sm font-medium text-gray-700">
      {{ label }}
    </span>

    <div
      role="radiogroup"
      :aria-labelledby="label ? `${id}-label` : undefined"
      class="flex flex-wrap gap-2"
    >
      <button
        v-for="(option, i) in options"
        :key="option.value"
        :ref="(el) => (buttons[i] = el)"
        type="button"
        role="radio"
        :aria-checked="model === option.value"
        :aria-label="option.label"
        :tabindex="tabIndex(i)"
        :disabled="disabled"
        class="cursor-pointer rounded-lg border px-3 py-1.5 text-sm font-medium transition duration-150
               active:scale-95
               focus:outline-none focus-visible:ring-2 focus-visible:ring-green-400 focus-visible:ring-offset-1
               disabled:cursor-not-allowed disabled:opacity-50"
        :class="model === option.value
          ? 'border-green-600 bg-green-600 text-white'
          : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50'"
        @click="seleccionar(option.value)"
        @keydown="onKey($event, i)"
      >
        {{ option.short ?? option.label }}
      </button>
    </div>

    <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
  </div>
</template>