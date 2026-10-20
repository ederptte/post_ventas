<script setup>
import { useSlots } from 'vue'

const slots = useSlots() 

defineProps({
  clickable: { type: Boolean, default: false },
})

defineEmits(['click'])
</script>

<template>
  <article
    class="flex flex-col rounded-lg border border-gray-200 bg-white p-4 shadow-md transition duration-150 hover:shadow-lg"
    :class="clickable
      ? 'cursor-pointer hover:border-green-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-green-400'
      : ''"
    :tabindex="clickable ? 0 : undefined"
    @click="clickable && $emit('click')"
    @keydown.enter.self="clickable && $emit('click')"
  >
    <slot />

    <footer
      v-if="slots.footer"
      class="mt-4 flex justify-end gap-1 border-t border-gray-100 pt-3"
    >
      <slot name="footer" />
    </footer>
  </article>
</template>