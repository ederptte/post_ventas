<script setup>
import { ref, watch, useId } from 'vue'

defineOptions({ inheritAttrs: false })

const props = defineProps({
  label: { type: String, default: '' },
  error: { type: String, default: '' },
  disabled: { type: Boolean, default: false },
  decimals: { type: Number, default: 2 }, // 0 = solo enteros
})

const model = defineModel() // número, o '' si está vacío
const id = useId()
const texto = ref('')
let emitido

// Deja solo dígitos y, si se permiten, un punto decimal
const limpiar = (str) => {
  const limpio = str.replace(/[^\d.]/g, '')
  if (props.decimals === 0) return limpio.replace(/\./g, '')

  const [entero, ...resto] = limpio.split('.')
  if (!resto.length) return entero
  return entero + '.' + resto.join('').slice(0, props.decimals)
}

// "2000" -> "$2,000"   "2000.5" -> "$2,000.5"
const aTexto = (limpio) => {
  if (limpio === '') return ''
  const tienePunto = limpio.includes('.')
  let [entero, decimal = ''] = limpio.split('.')
  entero = entero.replace(/^0+(?=\d)/, '') || '0'
  entero = entero.replace(/\B(?=(\d{3})+(?!\d))/g, ',')
  return '$' + entero + (tienePunto ? '.' + decimal : '')
}

// Convierte el valor que llega de afuera (2000, "2000.00") en texto
const mostrar = (valor) => {
  if (valor === '' || valor === null || valor === undefined) return ''
  const n = Number(valor)
  if (Number.isNaN(n)) return ''
  const fijo = props.decimals > 0 ? String(n) : String(Math.round(n))
  return aTexto(limpiar(fijo))
}

// Si el valor cambia desde afuera (cargar para editar, limpiar el formulario), actualiza el texto
watch(
  model,
  (valor) => {
    if (valor === emitido) return // el cambio vino de lo que el usuario escribió
    texto.value = mostrar(valor)
  },
  { immediate: true }
)

const onInput = (e) => {
  const el = e.target
  let valor = el.value
  let cursor = el.selectionStart

  // Si con Backspace se borró una coma, borra también el dígito anterior
  if (
    e.inputType === 'deleteContentBackward' &&
    texto.value[cursor] === ',' &&
    valor.length < texto.value.length &&
    cursor > 1
  ) {
    valor = valor.slice(0, cursor - 1) + valor.slice(cursor)
    cursor -= 1
  }

  const digitosIzquierda = limpiar(valor.slice(0, cursor)).length
  const limpio = limpiar(valor)
  const nuevoTexto = aTexto(limpio)

  el.value = nuevoTexto
  texto.value = nuevoTexto

  // Devuelve el cursor a la misma posición entre dígitos
  let pos = 0
  let cuenta = 0
  while (pos < nuevoTexto.length && cuenta < digitosIzquierda) {
    if (/[\d.]/.test(nuevoTexto[pos])) cuenta++
    pos++
  }
  pos = Math.max(pos, nuevoTexto ? 1 : 0) // nunca antes del "$"
  el.setSelectionRange(pos, pos)

  const numero = parseFloat(limpio)
  emitido = Number.isNaN(numero) ? '' : numero
  model.value = emitido
}
</script>

<template>
  <div class="flex flex-col gap-1">
    <label v-if="label" :for="id" class="text-sm font-medium text-gray-700">
      {{ label }}
    </label>

    <input
      :id="id"
      v-bind="$attrs"
      type="text"
      :inputmode="decimals > 0 ? 'decimal' : 'numeric'"
      autocomplete="off"
      :value="texto"
      :disabled="disabled"
      :aria-invalid="!!error"
      class="px-3 py-2 rounded-lg border bg-white text-gray-900
             transition duration-150
             focus:outline-none focus:ring-2
             disabled:bg-gray-100 disabled:cursor-not-allowed"
      :class="error
        ? 'border-red-500 focus:ring-red-300'
        : 'border-gray-300 focus:ring-green-400 focus:border-green-500'"
      @input="onInput"
    />

    <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
  </div>
</template>