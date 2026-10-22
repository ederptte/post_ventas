<script setup>
import { computed } from 'vue'
import { Icon } from '@iconify/vue'

const props = defineProps({
  totalPages: { type: Number, required: true },
  perPage: { type: Number, default: 10 },
})

const page = defineModel({ type: Number, default: 1 })

const from = computed(() => (props.totalPages === 0 ? 0 : (page.value - 1) * props.perPage + 1))
const to = computed(() => Math.min(page.value * props.perPage, props.totalPages * props.perPage))

// Muestra primera, última y las cercanas a la actual, con "…" en los saltos
const pages = computed(() => {
  const last = props.totalPages
  const base = [...new Set([1, last, page.value - 1, page.value, page.value + 1])]
    .filter((p) => p >= 1 && p <= last)
    .sort((a, b) => a - b)

  const resultado = []
  base.forEach((p, i) => {
    if (i > 0 && p - base[i - 1] > 1) resultado.push('...')
    resultado.push(p)
  })
  return resultado
})

const ir = (p) => {
  if (p >= 1 && p <= props.totalPages) page.value = p
}

const botonBase =
  'inline-flex h-8 min-w-8 items-center justify-center rounded-lg px-2 text-sm cursor-pointer transition ' +
  'focus:outline-none focus-visible:ring-2 focus-visible:ring-green-400 ' +
  'disabled:cursor-not-allowed disabled:opacity-40'
</script>

<template>
  <nav
    v-if="totalPages > 0"
    class="flex flex-col items-center justify-between gap-3 sm:flex-row"
    aria-label="Paginación"
  >
    <!-- Usamos totalPages * perPage o simplemente indicamos las páginas -->
    <p class="text-sm text-gray-600">Mostrando {{ from }}–{{ to }} (Página {{ page }} de {{ totalPages }})</p>

    <div class="flex items-center gap-1 h-4">
      <button
        type="button"
        aria-label="Página anterior"
        :disabled="page === 1"
        :class="[botonBase, 'text-gray-600 hover:bg-gray-100']"
        @click="ir(page - 1)"
      >
        <Icon icon="lucide:chevron-left" class="h-4 w-4" />
      </button>

      <template v-for="(p, i) in pages" :key="i">
        <span v-if="p === '...'" class="px-1 text-gray-400">…</span>
        <button
          v-else
          type="button"
          :aria-current="p === page ? 'page' : undefined"
          :aria-label="`Página ${p}`"
          :class="[
            botonBase,
            p === page ? 'bg-blue-600 font-medium text-white' : 'text-gray-700 hover:bg-gray-100',
          ]"
          @click="ir(p)"
        >
          {{ p }}
        </button>
      </template>

      <button
        type="button"
        aria-label="Página siguiente"
        :disabled="page === totalPages"
        :class="[botonBase, 'text-gray-600 hover:bg-gray-100']"
        @click="ir(page + 1)"
      >
        <Icon icon="lucide:chevron-right" class="h-4 w-4" />
      </button>
    </div>
  </nav>
</template>
