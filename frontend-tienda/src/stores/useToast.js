import { ref } from 'vue'
import { defineStore } from 'pinia'

export const useToast = defineStore('toast', () => {
  const toasts = ref([])
  let contador = 0

  const quitar = (id) => {
    const i = toasts.value.findIndex((t) => t.id === id)
    if (i !== -1) toasts.value.splice(i, 1)
  }

  const mostrar = (mensaje, tipo, duracion = 4000) => {
    const id = ++contador
    toasts.value.push({ id, mensaje, tipo })
    if (duracion > 0) setTimeout(() => quitar(id), duracion)
  }

  const success = (mensaje, duracion) => mostrar(mensaje, 'success', duracion)
  const error = (mensaje, duracion) => mostrar(mensaje, 'error', duracion)
  const warning = (mensaje, duracion) => mostrar(mensaje, 'warning', duracion)
  const info = (mensaje, duracion) => mostrar(mensaje, 'info', duracion)

  return { toasts, quitar, success, error, warning, info }
})