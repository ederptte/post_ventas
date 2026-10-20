import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'

export const useVentasStore = defineStore('ventas', () => {
  const ventas = ref([])
  const cargando = ref(false)
  const procesandoAbono = ref(false)
  const total = ref(0)
  const pagina = ref(1)
  const busqueda = ref('')
  const error = ref(null)
  const erroresValidacion = ref({})

  function manejarError(err, mensajeDefecto) {
    if (err.response?.status === 422) {
      erroresValidacion.value = err.response.data.errors || {}
      error.value = Object.values(erroresValidacion.value)[0]?.[0] || 'Revisa los datos de la venta'
    } else {
      error.value = err.response?.data?.message || mensajeDefecto
    }
    console.error(err)
  }

  async function obtenerVentas() {
    cargando.value = true
    error.value = null
    try {
      const { data } = await api.get('/ventas', {
        params: { page: pagina.value, search: busqueda.value },
      })
      ventas.value = data.data ?? []
      total.value = data.total_items ?? 0
      return true
    } catch (err) {
      error.value = 'No se pudieron cargar las ventas'
      console.error('Error al cargar las ventas:', err)
      return false
    } finally {
      cargando.value = false
    }
  }

  async function obtenerVentaPorId(id) {
    error.value = null
    try {
      const { data } = await api.get(`/ventas/${id}`)
      return data.data
    } catch (err) {
      error.value = 'No se pudo recuperar la información de la venta'
      console.error(err)
      return null
    }
  }

  async function guardarVenta(id, payload) {
    erroresValidacion.value = {}
    error.value = null
    try {
      if (id) {
        await api.put(`/ventas/${id}`, payload)
        return id
      }
      const { data } = await api.post('/ventas', payload)
      return data.data.id
    } catch (err) {
      manejarError(err, 'No se pudo procesar la venta')
      return null
    }
  }

  async function realizarVenta(id, tipoPago) {
    error.value = null
    try {
      await api.post(`/ventas/${id}/realizar`, { tipo_pago: tipoPago })
      return true
    } catch (err) {
      manejarError(err, 'No se pudo realizar la venta')
      return false
    }
  }

  async function registrarAbono(idVenta, datos) {
    procesandoAbono.value = true
    error.value = null
    try {
      await api.post(`/ventas/${idVenta}/abonar`, datos)
      await obtenerVentas()
      return true
    } catch (err) {
      error.value = err.response?.data?.message || 'No se pudo registrar el abono'
      console.error('Error al abonar:', err)
      return false
    } finally {
      procesandoAbono.value = false
    }
  }

  return {
    ventas,
    cargando,
    procesandoAbono,
    total,
    pagina,
    busqueda,
    error,
    erroresValidacion,
    obtenerVentas,
    obtenerVentaPorId,
    guardarVenta,
    realizarVenta,
    registrarAbono,
  }
})