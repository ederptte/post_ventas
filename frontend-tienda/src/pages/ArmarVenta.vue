<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { Icon } from '@iconify/vue'
import BaseSearch from '../components/BaseSearch.vue'
import BaseButton from '../components/BaseButton.vue'
import BaseMoneyInput from '../components/BaseMoneyInput.vue'
import { moneda } from '../utils/formatos.js'
import { useToast } from '../stores/useToast.js'
import { useConfirmarStore } from '../stores/useConfirmarStore.js'
import { useVentasStore } from '../stores/useVentas.js'
import { useClientesStore } from '../stores/useClientes.js'
import { useProductosStore } from '../stores/useProductos.js'

const props = defineProps({
  clienteId: { type: [String, Number], required: false },
  id: { type: [String, Number], required: false },
})

const router = useRouter()
const toast = useToast()
const confirmarStore = useConfirmarStore()
const ventasStore = useVentasStore()
const clientesStore = useClientesStore()
const productosStore = useProductosStore()

// Debe coincidir exactamente con el valor del enum en el backend
const ESTADO_PENDIENTE = 'pendiente'
const ESTADO_REALIZADO = 'realizado'

const CAMPO_PRECIO = 'precio_venta'

const cliente = ref(null)
const lista = ref([])
const accion = ref('')
const estadoOriginalVenta = ref(ESTADO_PENDIENTE)

// Si la venta se crea en esta pantalla y luego falla al realizarla,
// se recuerda su ID para no crear una venta duplicada al reintentar
const ventaCreadaId = ref(null)
const idVenta = computed(() => props.id ?? ventaCreadaId.value)

const volver = () => router.push({ name: 'ventas' })

onMounted(async () => {
  if (props.id) {
    const venta = await ventasStore.obtenerVentaPorId(props.id)
    if (!venta) {
      toast.error(ventasStore.error)
      return
    }

    cliente.value = venta.cliente
    estadoOriginalVenta.value = venta.estado

    lista.value = venta.detalles.map((detalle) => ({
      id: detalle.producto_id,
      nombre_producto: detalle.producto?.nombre_producto || 'Producto sin nombre',
      codigo_barra: detalle.producto?.codigo_barra || '—',
      cantidad: detalle.cantidad,
      precio: Number(detalle.precio_venta_final) || 0,
      [CAMPO_PRECIO]: Number(detalle.producto?.[CAMPO_PRECIO]) || 0,
    }))
  } else if (props.clienteId) {
    cliente.value = (await clientesStore.obtenerClientePorId(props.clienteId)) ?? null
  }
})

const buscarProductos = (texto) => productosStore.buscarProductos(texto)

const agregar = (producto) => {
  const existente = lista.value.find((p) => p.id === producto.id)
  if (existente) {
    existente.cantidad++
  } else {
    lista.value.push({
      ...producto,
      cantidad: 1,
      precio: Number(producto[CAMPO_PRECIO]) || 0,
    })
  }
}

const cambiarCantidad = (producto, delta) => {
  producto.cantidad = Math.max(1, producto.cantidad + delta)
}

const normalizar = (producto) => {
  const n = Math.floor(Number(producto.cantidad))
  producto.cantidad = Number.isFinite(n) && n >= 1 ? n : 1
}

const quitar = (id) => {
  lista.value = lista.value.filter((p) => p.id !== id)
}

const cantidadEn = (id) => lista.value.find((p) => p.id === id)?.cantidad ?? 0
const precioDe = (producto) => Number(producto.precio) || 0
const precioOriginal = (producto) => Number(producto[CAMPO_PRECIO]) || 0

const centavos = (producto) =>
  Math.round(precioDe(producto) * (Number(producto.cantidad) || 0) * 100)

const subtotal = (producto) => centavos(producto) / 100

const total = computed(
  () => lista.value.reduce((suma, producto) => suma + centavos(producto), 0) / 100
)

const construirPayload = (tipoPago) => {
  const productos = lista.value.map((p) => ({
    producto_id: p.id,
    cantidad: p.cantidad,
    precio_venta_final: p.precio,
  }))

  // Al editar, el backend solo acepta los productos (cliente y tipo de pago no cambian)
  if (idVenta.value) return { productos }

  return {
    cliente_id: Number(cliente.value?.id ?? props.clienteId),
    tipo_pago: tipoPago,
    productos,
  }
}

const enviar = async ({ realizar = false, tipoPago = 'contado' } = {}) => {
  if (!idVenta.value && !(cliente.value?.id ?? props.clienteId)) {
    toast.error('No se pudo identificar al cliente')
    return
  }

  const yaRealizada = estadoOriginalVenta.value === ESTADO_REALIZADO

  if (yaRealizada) {
    const ok = await confirmarStore.confirmar({
      titulo: '¡Advertencia de Inventario!',
      mensaje:
        'Esta venta ya fue realizada y descontó stock de tu inventario. Si modificas las cantidades o quitas productos, el stock se ajustará automáticamente para coincidir con los cambios. ¿Deseas continuar?',
      confirmarTexto: 'Sí, ajustar inventario',
      variante: 'danger',
    })
    if (!ok) return
  }

  accion.value = realizar ? 'realizar' : 'guardar'

  try {
    const ventaId = await ventasStore.guardarVenta(idVenta.value, construirPayload(tipoPago))

    if (!ventaId) {
      toast.error(ventasStore.error)
      return
    }

    if (!props.id) ventaCreadaId.value = ventaId

    if (realizar && !yaRealizada) {
      // El tipo de pago se envía aquí porque al editar una venta existente el backend no lo toma del update
      const realizada = await ventasStore.realizarVenta(ventaId, tipoPago)
      if (!realizada) {
        toast.error(ventasStore.error)
        return
      }
      toast.success('Venta realizada e inventario actualizado correctamente')
    } else if (yaRealizada) {
      toast.success('Venta y stock de inventario actualizados con éxito')
    } else {
      toast.success('Venta guardada con éxito')
    }

    volver()
  } finally {
    accion.value = ''
  }
}

const onGuardar = () => enviar()

const onRealizar = async () => {
  if (!lista.value.length) {
    toast.error('Debes agregar al menos un producto')
    return
  }

  if (estadoOriginalVenta.value === ESTADO_REALIZADO) {
    enviar({ realizar: true })
    return
  }

  const metodoPago = await confirmarStore.confirmar({
    titulo: 'Realizar Venta',
    mensaje: `¿Confirmas la venta de ${lista.value.length} ${lista.value.length === 1 ? 'producto' : 'productos'} por un total de ${moneda(total.value)}? Selecciona la modalidad de pago:`,
    confirmarTexto: 'Confirmar y Realizar',
    verOpcionesPago: true,
  })

  if (!metodoPago) return

  enviar({ realizar: true, tipoPago: metodoPago })
}
</script>

<template>
  <div class="flex h-[calc(100dvh-2.5rem)] min-h-full flex-col rounded bg-white">
    <!-- Encabezado (fijo) -->
    <div class="flex shrink-0 items-center justify-between gap-4 p-5">
      <div class="min-w-0">
        <h3 class="font-black">{{ idVenta ? 'Editar venta' : 'Nueva venta' }}</h3>
        <p class="truncate text-sm capitalize text-gray-600">
          {{ cliente?.nombre_cliente ?? 'Cargando cliente...' }}
        </p>
      </div>

      <BaseButton variant="secondary" @click="volver">
        <template #icon><Icon icon="lucide:arrow-left" class="w-5 h-5" /></template>
        Volver
      </BaseButton>
    </div>

    <!-- Buscador (fijo) -->
    <div class="shrink-0 px-5 pb-4">
      <BaseSearch
        label="Agregar producto"
        placeholder="Buscar por nombre o código..."
        label-key="nombre_producto"
        :fetcher="buscarProductos"
        clear-on-select
        @select="agregar"
      >
        <template #item="{ item }">
          <div class="flex items-center justify-between gap-3">
            <span class="truncate">{{ item.nombre_producto }}</span>
            <span
              v-if="cantidadEn(item.id)"
              class="shrink-0 text-xs font-medium text-green-600"
            >
              En lista: {{ cantidadEn(item.id) }}
            </span>
            <span v-else class="shrink-0 text-gray-400">{{ item.codigo_barra }}</span>
          </div>
        </template>
      </BaseSearch>
    </div>

    <!-- Lista (con scroll) -->
    <div class="min-h-0 flex-1 overflow-y-auto px-5">
      <ul
        v-if="lista.length"
        class="divide-y divide-gray-100 rounded-lg border border-gray-200 bg-white"
      >
        <li
          v-for="producto in lista"
          :key="producto.id"
          class="flex flex-wrap items-center justify-between gap-3 px-4 py-2"
        >
          <div class="min-w-0 flex-1 basis-40">
            <p class="truncate font-medium text-gray-800">{{ producto.nombre_producto }}</p>
            <p
              v-if="precioDe(producto) !== precioOriginal(producto)"
              class="text-xs text-amber-600"
            >
              Precio original: {{ moneda(precioOriginal(producto)) }}
            </p>
          </div>

          <div class="w-32 shrink-0">
            <BaseMoneyInput
              v-model="producto.precio"
              :aria-label="`Precio de ${producto.nombre_producto}`"
            />
          </div>

          <div class="flex shrink-0 items-center gap-1">
            <BaseButton
              variant="ghost"
              size="icon"
              :disabled="producto.cantidad <= 1"
              :aria-label="`Quitar una unidad de ${producto.nombre_producto}`"
              @click="cambiarCantidad(producto, -1)"
            >
              <template #icon><Icon icon="lucide:minus" class="w-4 h-4" /></template>
            </BaseButton>

            <input
              v-model.number="producto.cantidad"
              type="number"
              min="1"
              inputmode="numeric"
              :aria-label="`Cantidad de ${producto.nombre_producto}`"
              class="w-14 rounded-lg border border-gray-300 py-1 text-center text-sm
                     focus:outline-none focus:ring-2 focus:ring-green-400"
              @blur="normalizar(producto)"
            />

            <BaseButton
              variant="ghost"
              size="icon"
              :aria-label="`Agregar una unidad de ${producto.nombre_producto}`"
              @click="cambiarCantidad(producto, 1)"
            >
              <template #icon><Icon icon="lucide:plus" class="w-4 h-4" /></template>
            </BaseButton>
          </div>

          <p class="w-24 shrink-0 text-right font-medium tabular-nums text-gray-800">
            {{ moneda(subtotal(producto)) }}
          </p>

          <BaseButton
            variant="ghost-danger"
            size="icon"
            :aria-label="`Quitar ${producto.nombre_producto} de la lista`"
            @click="quitar(producto.id)"
          >
            <template #icon><Icon icon="lucide:trash-2" class="w-4 h-4" /></template>
          </BaseButton>
        </li>
      </ul>

      <p v-else class="py-6 text-center text-sm text-gray-500">
        Aún no has agregado productos.
      </p>
    </div>

    <!-- Total y botones (fijos) -->
    <div class="shrink-0 border-t border-gray-200 p-5">
      <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
          <p class="text-xs text-gray-500">
            {{ lista.length }} {{ lista.length === 1 ? 'producto' : 'productos' }} · Total
          </p>
          <p class="text-lg font-bold tabular-nums text-gray-800">{{ moneda(total) }}</p>
        </div>

        <div class="flex gap-3">
          <BaseButton
            variant="outline"
            :loading="accion === 'guardar'"
            :disabled="!lista.length || !!accion"
            @click="onGuardar"
          >
            <template #icon><Icon icon="lucide:save" class="w-5 h-5" /></template>
            Guardar venta
          </BaseButton>

          <BaseButton
            :loading="accion === 'realizar'"
            :disabled="!lista.length || !!accion"
            @click="onRealizar"
          >
            <template #icon><Icon icon="lucide:package-check" class="w-5 h-5" /></template>
            Realizar Venta
          </BaseButton>
        </div>
      </div>
    </div>
  </div>
</template>