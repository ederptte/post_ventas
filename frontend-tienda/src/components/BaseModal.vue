<script setup>
import { ref, watch, nextTick, onBeforeUnmount, useId } from 'vue'
import { Icon } from '@iconify/vue'

const props = defineProps({
  title: { type: String, default: '' },
  size: { type: String, default: 'md' },
  closeOnBackdrop: { type: Boolean, default: true },
})

const open = defineModel({ type: Boolean, default: false })
const titleId = useId()
const panel = ref(null)
let focoPrevio = null

const sizes = { sm: 'max-w-sm', md: 'max-w-lg', lg: 'max-w-2xl' }

const cerrar = () => {
  open.value = false
}

const onKey = (e) => {
  if (e.key === 'Escape') return cerrar()
  if (e.key !== 'Tab' || !panel.value) return

  const focuseables = panel.value.querySelectorAll(
    'button:not([disabled]), [href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
  )
  if (!focuseables.length) return

  const primero = focuseables[0]
  const ultimo = focuseables[focuseables.length - 1]

  if (e.shiftKey && document.activeElement === primero) {
    e.preventDefault()
    ultimo.focus()
  } else if (!e.shiftKey && document.activeElement === ultimo) {
    e.preventDefault()
    primero.focus()
  }
}

watch(open, async (abierto) => {
  if (abierto) {
    focoPrevio = document.activeElement
    document.body.style.overflow = 'hidden'
    document.addEventListener('keydown', onKey)
    await nextTick()
    panel.value?.focus()
  } else {
    document.body.style.overflow = ''
    document.removeEventListener('keydown', onKey)
    focoPrevio?.focus()
  }
})

onBeforeUnmount(() => {
  document.body.style.overflow = ''
  document.removeEventListener('keydown', onKey)
})
</script>

<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-150"
      enter-from-class="opacity-0"
      leave-active-class="transition duration-100"
      leave-to-class="opacity-0"
    >
      <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <!-- Fondo oscuro -->
        <div class="absolute inset-0 bg-black/50" @click="closeOnBackdrop && cerrar()" />

        <!-- Cuadro -->
        <div
          ref="panel"
          role="dialog"
          aria-modal="true"
          :aria-labelledby="title ? titleId : undefined"
          tabindex="-1"
          class="relative flex max-h-[90vh] w-full flex-col rounded-lg bg-white shadow-xl focus:outline-none"
          :class="sizes[size]"
        >
          <header class="flex items-center justify-between gap-4 border-b border-gray-200 px-5 py-4">
            <h3 :id="titleId" class="font-black text-gray-800">{{ title }}</h3>
            <button
              type="button"
              aria-label="Cerrar"
              class="cursor-pointer rounded-lg p-1 text-gray-500 transition hover:bg-gray-100
                     focus:outline-none focus-visible:ring-2 focus-visible:ring-green-400"
              @click="cerrar"
            >
              <Icon icon="lucide:x" class="h-5 w-5" />
            </button>
          </header>

          <div class="overflow-y-auto px-5 py-4">
            <slot />
          </div>

          <footer
            v-if="$slots.footer"
            class="flex justify-end gap-3 border-t border-gray-200 px-5 py-4"
          >
            <slot name="footer" />
          </footer>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>