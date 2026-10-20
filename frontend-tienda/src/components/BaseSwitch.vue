<script setup>
import { useId } from 'vue'

defineOptions({ inheritAttrs: false })

defineProps({
  label: { type: String, default: '' },
  disabled: { type: Boolean, default: false },
})

const model = defineModel({ type: Boolean, default: false })
const id = useId()
</script>

<template>
  <div class="inline-flex items-center gap-3">
    <button
      v-bind="$attrs"
      :id="id"
      type="button"
      role="switch"
      :aria-checked="model"
      :disabled="disabled"
      class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer items-center rounded-full
             transition-colors duration-200
             focus:outline-none focus-visible:ring-2 focus-visible:ring-green-400 focus-visible:ring-offset-2
             disabled:cursor-not-allowed disabled:opacity-50"
      :class="model ? 'bg-green-600' : 'bg-gray-400'"
      @click="model = !model"
    >
      <span
        class="inline-block h-5 w-5 rounded-full bg-white shadow transition-transform duration-200"
        :class="model ? 'translate-x-5.5' : 'translate-x-0.5'"
      />
    </button>

    <label
      v-if="label"
      :for="id"
      class="cursor-pointer select-none text-sm text-gray-700"
    >
      {{ label }}
    </label>
  </div>
</template>