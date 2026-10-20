<script setup>
import { ref, useId } from 'vue'
import { Icon } from '@iconify/vue'

const props = defineProps({
  tabs: { type: Array, required: true },
})

const model = defineModel()
const uid = useId()
const buttons = ref([])

// Si el padre no define la pestaña inicial, abre la primera
if (model.value === undefined) model.value = props.tabs[0]?.key

const onKey = (e, index) => {
  const habilitadas = props.tabs
    .map((t, i) => (t.disabled ? -1 : i))
    .filter((i) => i >= 0)
  const pos = habilitadas.indexOf(index)

  let siguiente
  if (e.key === 'ArrowRight') siguiente = habilitadas[(pos + 1) % habilitadas.length]
  else if (e.key === 'ArrowLeft') siguiente = habilitadas[(pos - 1 + habilitadas.length) % habilitadas.length]
  else if (e.key === 'Home') siguiente = habilitadas[0]
  else if (e.key === 'End') siguiente = habilitadas[habilitadas.length - 1]
  else return

  e.preventDefault()
  model.value = props.tabs[siguiente].key
  buttons.value[siguiente]?.focus()
}
</script>

<template>
  <div>
    <div role="tablist" class="flex gap-1 overflow-x-auto shadow-[inset_0_-1px_0_0_#9ca3af]">
      <button
        v-for="(tab, i) in tabs"
        :key="tab.key"
        :ref="(el) => (buttons[i] = el)"
        type="button"
        role="tab"
        :id="`${uid}-tab-${tab.key}`"
        :aria-selected="model === tab.key"
        :aria-controls="`${uid}-panel-${tab.key}`"
        :tabindex="model === tab.key ? 0 : -1"
        :disabled="tab.disabled"
        class="inline-flex items-center gap-2 whitespace-nowrap border-b-2 px-4 py-2 text-sm font-medium
                cursor-pointer transition duration-150
                focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-green-400
                disabled:cursor-not-allowed disabled:opacity-50"
        :class="model === tab.key
          ? 'border-green-600 text-green-700'
          : 'border-transparent text-gray-600 hover:border-gray-500 hover:text-gray-800'"
        @click="model = tab.key"
        @keydown="onKey($event, i)"
      >
        <Icon v-if="tab.icon" :icon="tab.icon" class="h-5 w-5" />
        {{ tab.label }}
      </button>
    </div>

    <div
      role="tabpanel"
      :id="`${uid}-panel-${model}`"
      :aria-labelledby="`${uid}-tab-${model}`"
      tabindex="0"
      class="pt-4 focus:outline-none"
    >
      <slot :name="model" />
    </div>
  </div>
</template>