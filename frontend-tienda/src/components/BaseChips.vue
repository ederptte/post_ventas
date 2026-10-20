<script setup>
const props = defineProps({
  label: { type: String, default: '' },
  options: { type: Array, required: true }, // { value, label, short? }
  error: { type: String, default: '' },
  disabled: { type: Boolean, default: false },
})

const model = defineModel({ type: Array, default: () => [] })

const alternar = (value) => {
  const nuevo = model.value.includes(value)
    ? model.value.filter((v) => v !== value)
    : [...model.value, value]

  // Mantiene el orden de las opciones (lun, mar, mié...) sin importar cómo se marquen
  model.value = props.options.map((o) => o.value).filter((v) => nuevo.includes(v))
}
</script>

<template>
  <fieldset class="flex flex-col gap-1" :disabled="disabled">
    <legend v-if="label" class="mb-1 text-sm font-medium text-gray-700">
      {{ label }}
    </legend>

    <div class="flex flex-wrap gap-2">
      <button
        v-for="option in options"
        :key="option.value"
        type="button"
        :aria-pressed="model.includes(option.value)"
        :aria-label="option.label"
        class="cursor-pointer rounded-lg border px-3 py-1.5 text-sm font-medium transition duration-150
               active:scale-95
               focus:outline-none focus-visible:ring-2 focus-visible:ring-green-400 focus-visible:ring-offset-1
               disabled:cursor-not-allowed disabled:opacity-50"
        :class="model.includes(option.value)
          ? 'border-green-600 bg-green-600 text-white'
          : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50'"
        @click="alternar(option.value)"
      >
        {{ option.short ?? option.label }}
      </button>
    </div>

    <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
  </fieldset>
</template>