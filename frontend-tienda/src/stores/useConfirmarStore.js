import { defineStore } from 'pinia'
import { reactive, ref } from 'vue'

export const useConfirmarStore = defineStore('confirmar', () => {
  // Estado reactivo global
  const estado = reactive({
    abierto: false,
    titulo: '',
    mensaje: '',
    confirmarTexto: 'Confirmar',
    variante: 'primary',
    verOpcionesPago: false, 
    metodoElegido: 'contado' 
  })

  // Variable para guardar la promesa de forma interna en el store
  let resolver = ref(null)

  const responder = (valor) => {
    estado.abierto = false

    if (valor && estado.verOpcionesPago) {
      resolver.value?.(estado.metodoElegido)
    } else {
      resolver.value?.(valor)
    }
    resolver.value = null
  }

  const confirmar = (opciones = {}) => {
    // Si ya había una promesa abierta, la cerramos por seguridad
    resolver.value?.(false)

    Object.assign(estado, {
      titulo: '¿Estás seguro?',
      mensaje: '',
      confirmarTexto: 'Confirmar',
      variante: 'primary',
      verOpcionesPago: false,
      metodoElegido: 'contado',
      ...opciones,
      abierto: true,
    })

    return new Promise((resolve) => {
      resolver.value = resolve
    })
  }

  return {
    estado,
    confirmar,
    responder
  }
})