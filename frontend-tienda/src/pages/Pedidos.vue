<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { storeToRefs } from 'pinia'
import { useToast } from '../stores/useToast.js'
import { usePedidosStore } from '../stores/usePedidos.js'
import BaseTable from '../components/BaseTable.vue'
import BasePagination from '../components/BasePagination.vue'
import BaseButton from '../components/BaseButton.vue'
import BaseInput from '../components/BaseInput.vue'
import { moneda } from '../utils/formatos.js'
import { Icon } from '@iconify/vue'

const router = useRouter()
const toast = useToast()
const pedidosStore = usePedidosStore()

// Con storeToRefs el template sigue usando los mismos nombres de antes
const { pedidos, cargando, total, pagina, busqueda, procesandoAbono } = storeToRefs(pedidosStore)

let temporizador = null

// Modal de abonos (estado de UI, se queda en el componente)
const mostrarModalAbono = ref(false)
const pedidoParaAbonar = ref(null)
const abonoForm = ref({
  monto_abonado: 0,
  nota_referencia: '',
})

const columnas = [
  { key: 'numero_pedido', label: 'N° Pedido' },
  { key: 'nombre_proveedor', label: 'Proveedor' },
  { key: 'total_pedido', label: 'Total Pedido' },
  { key: 'monto_abonado', label: 'Monto Abonado' },
  { key: 'saldo_pendiente', label: 'Saldo Pendiente' },
  { key: 'estado', label: 'Estado Pedido' },
  { key: 'estado_pago', label: 'Estado Pago' },
  { key: 'acciones', label: 'Acciones' },
]

const irAArmarPedido = (parametro) => {
  const pedidoSeleccionado = parametro?.row || parametro

  if (!pedidoSeleccionado || !pedidoSeleccionado.id) {
    toast.error('No se pudo identificar el pedido seleccionado')
    return
  }

  router.push({ name: 'pedidos.editar', params: { id: pedidoSeleccionado.id } })
}

const abrirModalAbono = (pedido) => {
  pedidoParaAbonar.value = pedido
  abonoForm.value = {
    monto_abonado: Number(pedido.saldo_pendiente) || 0,
    nota_referencia: '',
  }
  mostrarModalAbono.value = true
}

const ejecutarAbono = async () => {
  if (abonoForm.value.monto_abonado <= 0) {
    toast.error('El monto a abonar debe ser mayor a 0')
    return
  }

  const exito = await pedidosStore.registrarAbono(pedidoParaAbonar.value.id, {
    monto_abonado: abonoForm.value.monto_abonado,
    nota_referencia: abonoForm.value.nota_referencia,
  })

  if (exito) {
    toast.success('¡Abono registrado con éxito!')
    mostrarModalAbono.value = false
  } else {
    toast.error(pedidosStore.error)
  }
}

const cargar = async () => {
  const ok = await pedidosStore.obtenerPedidos()
  if (!ok) toast.error(pedidosStore.error)
}

watch(pagina, cargar)

watch(busqueda, () => {
  clearTimeout(temporizador)
  temporizador = setTimeout(() => {
    if (pagina.value === 1) cargar()
    else pagina.value = 1
  }, 400)
})

onMounted(cargar)
</script>
<template>
  <div class="flex h-[calc(100dvh-2.5rem)] min-h-full flex-col rounded bg-white p-5">
    <!-- Encabezado -->
    <div class="border-b border-gray-100 pb-4 mb-4">
      <h3 class="font-black text-xl text-gray-800">Módulo General de Pedidos</h3>
      <p class="text-sm text-gray-500">Administra tus órdenes de compra, realiza seguimiento de inventario y registra abonos de cuentas por pagar.</p>
    </div>

    <!-- Barra de búsqueda -->
    <div class="flex pb-5 justify-between items-center gap-4 border-b border-gray-100">
      <div class="w-full max-w-md">
        <BaseInput
          v-model="busqueda"
          placeholder="Buscar por N° pedido o proveedor..."
          type="search"
        />
      </div>
    </div>

    <!-- Tabla de Pedidos -->
    <BaseTable
      :columns="columnas"
      :rows="pedidos"
      :loading="cargando"
      clickable
      empty-text="No se encontraron pedidos registrados."
      @row-click="irAArmarPedido"
    >
      <template #cell-nombre_proveedor="{ row }">
        <span class="font-medium text-gray-800 capitalize">
          {{ row.proveedor?.nombre_proveedor || 'Sin proveedor' }}
        </span>
      </template>

      <template #cell-total_pedido="{ value }">
        <span class="font-semibold text-gray-700">{{ moneda(value) }}</span>
      </template>

      <template #cell-monto_abonado="{ value }">
        <span class="text-blue-600 font-medium">{{ moneda(value) }}</span>
      </template>

      <template #cell-saldo_pendiente="{ value }">
        <span class="text-amber-600 font-bold">{{ moneda(value) }}</span>
      </template>

      <template #cell-estado="{ value }">
        <span
          class="inline-block rounded-full px-2 py-0.5 text-xs font-semibold capitalize"
          :class="value === 'recibido' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'"
        >
          {{ value }}
        </span>
      </template>

      <template #cell-estado_pago="{ value }">
        <span
          class="inline-block rounded-full px-2 py-0.5 text-xs font-semibold capitalize"
          :class="value === 'pagado' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700'"
        >
          {{ value }}
        </span>
      </template>

      <template #cell-acciones="{ row }">
        <BaseButton
          v-if="row.tipo_pago === 'credito' && Number(row.saldo_pendiente) > 0"
          variante="ghost"
          size="sm"
          title="Registrar Abono"
          @click.stop="abrirModalAbono(row)"
        >
          <template #icon><Icon icon="lucide:dollar-sign" class="w-4 h-4 text-green-600" /></template>
          Abonar
        </BaseButton>
        <span v-else-if="row.tipo_pago === 'credito'" class="text-xs font-bold text-green-600 flex items-center gap-1">
          <Icon icon="lucide:check-circle" class="w-4 h-4" /> Liquidado
        </span>
        <span v-else class="text-xs text-gray-400">
          Contado
        </span>
      </template>
    </BaseTable>

    <!-- Paginación -->
    <div class="mt-4">
      <BasePagination v-model="pagina" :total="total" :per-page="10" />
    </div>

    <!-- MODAL FLOTANTE: REGISTRO DE ABONOS -->
    <div
      v-if="mostrarModalAbono"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
    >
      <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-2xl transition-all">
        <div class="flex items-center gap-3 border-b border-gray-100 pb-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 text-blue-700">
            <Icon icon="lucide:receipt" class="h-5 w-5" />
          </div>
          <div>
            <h4 class="text-lg font-bold text-gray-900">Registrar Abono</h4>
            <p class="text-xs text-gray-500">Pedido: <span class="font-bold text-gray-700">{{ pedidoParaAbonar?.numero_pedido }}</span></p>
          </div>
        </div>

        <div class="mt-4 grid grid-cols-2 gap-3 bg-gray-50 rounded-lg p-3 text-sm">
          <div>
            <span class="text-gray-500 block text-xs">Total Factura:</span>
            <span class="font-semibold text-gray-800">{{ moneda(pedidoParaAbonar?.total_pedido) }}</span>
          </div>
          <div>
            <span class="text-gray-500 block text-xs">Saldo Pendiente:</span>
            <span class="font-bold text-amber-600">{{ moneda(pedidoParaAbonar?.saldo_pendiente) }}</span>
          </div>
        </div>

        <div class="mt-4 space-y-4">
          <div>
            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Monto a Abonar ($)</label>
            <input
              v-model.number="abonoForm.monto_abonado"
              type="number"
              step="0.01"
              min="0.01"
              :max="pedidoParaAbonar?.saldo_pendiente"
              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-green-400"
              placeholder="0.00"
            />
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Nota de Referencia / Comprobante</label>
            <textarea
              v-model="abonoForm.nota_referencia"
              rows="2"
              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400"
              placeholder="Ej: Transferencia Bancaria, Efectivo caja..."
            ></textarea>
          </div>
        </div>

        <div class="mt-6 flex justify-end gap-2 border-t border-gray-100 pt-4">
          <BaseButton variant="secondary" :disabled="procesandoAbono" @click="mostrarModalAbono = false">
            Cancelar
          </BaseButton>
          <BaseButton :loading="procesandoAbono" @click="ejecutarAbono">
            Aplicar Pago
          </BaseButton>
        </div>
      </div>
    </div>
  </div>
</template>
