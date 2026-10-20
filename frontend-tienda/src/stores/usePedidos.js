import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'

export const usePedidosStore = defineStore('pedidos', () => {
  const pedidos = ref([])
  const cargando = ref(false)
  const procesandoAbono = ref(false)
  const total = ref(0)
  const pagina = ref(1)
  const busqueda = ref('')
  const error = ref(null)
  const erroresValidacion = ref({})

  async function obtenerPedidos() {
    cargando.value = true
    error.value = null
    try {
      const { data } = await api.get('/pedidos', {
        params: { page: pagina.value, search: busqueda.value },
      })
      pedidos.value = data.data ?? []
      total.value = data.total_items ?? 0
      return true
    } catch (err) {
      error.value = 'No se pudieron cargar los pedidos'
      console.error('Error al cargar los pedidos:', err)
      return false
    } finally {
      cargando.value = false
    }
  }

  async function registrarAbono(idPedido, datos) {
    procesandoAbono.value = true
    error.value = null
    try {
      await api.post(`/pedidos/${idPedido}/abonar`, datos)
      await obtenerPedidos()
      return true
    } catch (err) {
      error.value = err.response?.data?.message || 'No se pudo registrar el abono'
      console.error('Error al abonar:', err)
      return false
    } finally {
      procesandoAbono.value = false
    }
  }

  function manejarError(err, mensajeDefecto) {
    if (err.response?.status === 422) {
        erroresValidacion.value = err.response.data.errors || {}
        error.value = Object.values(erroresValidacion.value)[0]?.[0] || 'Revisa los datos del pedido'
    } else {
        error.value = err.response?.data?.message || mensajeDefecto
    }
    console.error(err)
    }

    async function obtenerPedidoPorId(id) {
        error.value = null
        try {
            const { data } = await api.get(`/pedidos/${id}`)
            return data.data
        } catch (err) {
            error.value = 'No se pudo recuperar la información del pedido'
            console.error(err)
            return null
        }
    }

    async function guardarPedido(id, payload) {
        erroresValidacion.value = {}
        error.value = null
        try {
            if (id) {
            await api.put(`/pedidos/${id}`, payload)
            return id
            }
            const { data } = await api.post('/pedidos', payload)
            return data.data.id
        } catch (err) {
            manejarError(err, 'No se pudo procesar el pedido')
            return null
        }
        }
    
    async function recibirPedido(id, tipoPago) {
        error.value = null
        try {
            await api.post(`/pedidos/${id}/recibir`, { tipo_pago: tipoPago })
            return true
        } catch (err) {
            manejarError(err, 'No se pudo recibir el pedido')
            return false
        }
        }

  return {
    pedidos,
    cargando,
    procesandoAbono,
    total,
    pagina,
    busqueda,
    error,
    obtenerPedidos,
    registrarAbono,
    erroresValidacion,
    obtenerPedidoPorId,
    guardarPedido,
    recibirPedido,
  }
})