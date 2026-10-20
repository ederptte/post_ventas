<script setup>
import { ref, watch, nextTick, onMounted, onBeforeUnmount, useId } from 'vue'
import { Icon } from '@iconify/vue'

defineOptions({ inheritAttrs: false })

const props = defineProps({
  label: { type: String, default: '' },
  placeholder: { type: String, default: 'Buscar...' },
  fetcher: { type: Function, required: true }, // (texto) => Promise<array>
  labelKey: { type: String, default: 'nombre' },
  minChars: { type: Number, default: 2 },
  delay: { type: Number, default: 300 },
  emptyText: { type: String, default: 'Sin resultados' },
  clearOnSelect: { type: Boolean, default: false },
  error: { type: String, default: '' },
})

const emit = defineEmits(['select'])

const id = useId()
const raiz = ref(null)
const texto = ref('')
const resultados = ref([])
const abierto = ref(false)
const cargando = ref(false)
const buscado = ref(false)
const activo = ref(-1)
let temporizador = null
let ultimaPeticion = 0

const buscar = async (valor) => {
  const peticion = ++ultimaPeticion
  cargando.value = true
  try {
    const lista = await props.fetcher(valor)
    if (peticion !== ultimaPeticion) return // llegó una respuesta vieja
    resultados.value = lista ?? []
  } catch (error) {
    if (peticion !== ultimaPeticion) return
    resultados.value = []
    console.error('Error en la búsqueda:', error)
  } finally {
    if (peticion === ultimaPeticion) {
      cargando.value = false
      buscado.value = true
      activo.value = -1
    }
  }
}

const onInput = (e) => {
  texto.value = e.target.value
  clearTimeout(temporizador)

  const valor = texto.value.trim()
  if (valor.length < props.minChars) {
    ultimaPeticion++ // descarta peticiones en curso
    resultados.value = []
    cargando.value = false
    buscado.value = false
    abierto.value = false
    return
  }

  abierto.value = true
  cargando.value = true
  temporizador = setTimeout(() => buscar(valor), props.delay)
}

const elegir = (item) => {
  emit('select', item)
  abierto.value = false
  if (props.clearOnSelect) {
    texto.value = ''
    resultados.value = []
    buscado.value = false
  } else {
    texto.value = String(item[props.labelKey] ?? '')
  }
}

const onKeydown = (e) => {
  const n = resultados.value.length

  if (e.key === 'ArrowDown' && n) {
    e.preventDefault()
    abierto.value = true
    activo.value = (activo.value + 1) % n
  } else if (e.key === 'ArrowUp' && n) {
    e.preventDefault()
    activo.value = (activo.value - 1 + n) % n
  } else if (e.key === 'Enter' && abierto.value && activo.value >= 0) {
    e.preventDefault() // evita enviar el formulario
    elegir(resultados.value[activo.value])
  } else if (e.key === 'Escape') {
    abierto.value = false
  }
}

const onFocus = () => {
  if (resultados.value.length) abierto.value = true
}

// Mantiene visible la opción marcada con el teclado
watch(activo, async (i) => {
  if (i < 0) return
  await nextTick()
  document.getElementById(`${id}-opt-${i}`)?.scrollIntoView({ block: 'nearest' })
})

// Cierra al hacer clic fuera
const onDocClick = (e) => {
  if (!raiz.value?.contains(e.target)) abierto.value = false
}

onMounted(() => document.addEventListener('mousedown', onDocClick))
onBeforeUnmount(() => {
  document.removeEventListener('mousedown', onDocClick)
  clearTimeout(temporizador)
})
</script>

<template>
  <div ref="raiz" class="flex flex-col gap-1">
    <label v-if="label" :for="id" class="text-sm font-medium text-gray-700">
      {{ label }}
    </label>

    <div class="relative">
      <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-gray-400">
        <Icon icon="lucide:search" class="h-5 w-5" />
      </span>

      <input
        :id="id"
        v-bind="$attrs"
        type="text"
        role="combobox"
        autocomplete="off"
        :value="texto"
        :placeholder="placeholder"
        aria-autocomplete="list"
        :aria-expanded="abierto"
        :aria-controls="`${id}-lista`"
        :aria-activedescendant="activo >= 0 ? `${id}-opt-${activo}` : undefined"
        :aria-invalid="!!error"
        class="w-full rounded-lg border bg-white py-2 pl-10 pr-10 text-gray-900
               transition duration-150 focus:outline-none focus:ring-2"
        :class="error
          ? 'border-red-500 focus:ring-red-300'
          : 'border-gray-300 focus:ring-green-400 focus:border-green-500'"
        @input="onInput"
        @keydown="onKeydown"
        @focus="onFocus"
      />

      <span v-if="cargando" class="absolute inset-y-0 right-3 flex items-center">
        <Icon icon="lucide:loader-2" class="h-4 w-4 animate-spin text-gray-400" />
      </span>

      <!-- Lista de resultados -->
      <ul
        v-if="abierto"
        :id="`${id}-lista`"
        role="listbox"
        class="absolute left-0 right-0 top-full z-20 mt-1 max-h-64 overflow-y-auto
               rounded-lg border border-gray-200 bg-white py-1 shadow-lg"
      >
        <li
          v-for="(item, i) in resultados"
          :id="`${id}-opt-${i}`"
          :key="item.id ?? i"
          role="option"
          :aria-selected="i === activo"
          class="cursor-pointer px-3 py-2 text-sm"
          :class="i === activo ? 'bg-green-50 text-green-800' : 'text-gray-700 hover:bg-gray-50'"
          @mousedown.prevent="elegir(item)"
          @mousemove="activo = i"
        >
          <slot name="item" :item="item">{{ item[labelKey] }}</slot>
        </li>

        <li v-if="cargando && !resultados.length" class="px-3 py-2 text-sm text-gray-500">
          Buscando...
        </li>
        <li
          v-else-if="buscado && !resultados.length"
          role="option"
          aria-disabled="true"
          class="px-3 py-2 text-sm text-gray-500"
        >
          {{ emptyText }}
        </li>
      </ul>
    </div>

    <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
  </div>
</template>