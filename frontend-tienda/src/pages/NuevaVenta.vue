<script setup>
import { onMounted } from "vue";
import { useClientesStore } from "../stores/useClientes";
import { useConfirmarStore } from "../stores/useConfirmarStore.js";
import BaseButton from "../components/BaseButton.vue";
import { Icon } from "@iconify/vue";
import router from "../router/index.js";
import { useToast } from "../stores/useToast.js";
import BasePagination from "../components/BasePagination.vue";
import BaseCard from "../components/BaseCard.vue";

const clientesStore = useClientesStore();
const confirmarStore = useConfirmarStore();
const toast = useToast();
//const itemsPorPagina = 10;

onMounted(async () => {
    clientesStore.obtenerClientes();
})


const cambiarEstado = async (cliente) => {
    const desactivar = cliente.activo;

    const ok = await confirmarStore.confirmar({
        titulo: desactivar ? "Desactivar cliente" : "Activar cliente",
        mensaje: `¿Quieres ${desactivar ? "desactivar" : "activar"} "${cliente.nombre_cliente}"?`,
        confirmarTexto: desactivar ? "Desactivar" : "Activar",
        variante: desactivar ? "danger" : "primary",
    });

    if (!ok) return;

    const exito = await clientesStore.cambiarEstadoCliente(cliente.id);

    if (exito) {
       
        toast.success(`Cliente ${desactivar ? "desactivado" : "activado"}`);
    } else {
        toast.error(clientesStore.error || "No se pudo cambiar el estado");
    }
};

const IrACrear = () => {
    router.push({ name: "clientes.crear" });
};

const IrAEditar = (id) => {
    router.push({ name: "clientes.editar", params: { id: id } });
};

const ArmarPedido = (cliente) => {
    if (!cliente.activo) {
        toast.error("El cliente está inactivo. Actívalo para hacer pedidos.");
        return;
    }
    router.push({ name: "ventas.crear", params: { clienteId: cliente.id } });
};
</script>


<template>
  <div class="flex h-[calc(100dvh-2.5rem)] min-h-full flex-col rounded bg-white p-5">
    <div class="border-b border-gray-100 pb-4 mb-4">
      <h3 class="font-black text-xl text-gray-800">Módulo General de Clientes</h3>
      <p class="text-sm text-gray-500">En este módulo podrás gestionar los clientes de tu tienda y realizar pedidos a los mismos.</p>
    </div>
    <div class="flex p-3 justify-between items-center gap-4 border-b border-gray-100">
      <div class="w-full max-w-md">
        <input
            type="text"
            v-model="clientesStore.textoBusqueda"
            placeholder="Escribe para buscar clientes en tiempo real..."
            class="border p-2 rounded w-full"
        />
      </div>

      <BaseButton @click="IrACrear">
        <template #icon><Icon icon="lucide:plus" class="w-5 h-5" /></template>
        Crear
      </BaseButton>
    </div>

    <!-- Cargando -->
    <div v-if="clientesStore.cargando" class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 xl:grid-cols-3">
      <div v-for="n in 6" :key="n" class="h-44 animate-pulse rounded-lg bg-gray-100" />
    </div>

    <!-- Sin resultados -->
    <p v-else-if="!clientesStore.clientesFiltrados.length" class="py-10 text-center text-gray-500">
      No se encontraron clientes.
    </p>

    <!-- Tarjetas -->
    <div v-else class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 xl:grid-cols-3">
      <BaseCard
        v-for="cliente in clientesStore.clientesFiltrados"
        :key="cliente.id"
        :clickable="!!cliente.activo"
        :class="{ 'opacity-60': !cliente.activo }"
        @click="ArmarPedido(cliente)"
        
      >
        <div class="flex items-start gap-3 ">
          <div
            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full
                   bg-green-100 font-semibold text-green-700 capitalize"
            aria-hidden="true"
          >
            {{ cliente.nombre_cliente?.charAt(0).toUpperCase() }}
          </div>

          <div class="min-w-0 flex-1">
            <h3 class="truncate font-semibold text-gray-800 capitalize">{{ cliente.nombre_cliente }}</h3>
            <span
              class="mt-1 inline-block rounded-full px-2 py-0.5 text-xs font-medium"
              :class="cliente.activo ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600'"
            >
              {{ cliente.activo ? 'Activo' : 'Inactivo' }}
            </span>
          </div>
        </div>

        <ul class="mt-4 space-y-2 text-sm text-gray-600">
          <li class="flex items-center gap-2">
            <Icon icon="lucide:phone" class="h-4 w-4 shrink-0 text-gray-400" />
            <span class="truncate">{{ cliente.telefono_cliente || '—' }}</span>
          </li>
        </ul>

        <!-- Pega los botones aquí directamente (sin <template #footer>) -->
        <div class="mt-4 flex justify-end gap-1 border-t border-gray-100 pt-3">
            <BaseButton
            variante="ghost"
            size="icon"
            aria-label="Detalles proveedor"
            title="Detalles"
            
          >
            <template #icon><Icon icon="boxicons:pen-alt-filled" class="w-5 h-5" /></template>
          </BaseButton>

          <BaseButton
            variante="ghost"
            size="icon"
            aria-label="Editar proveedor"
            title="Editar"
            @click.stop="IrAEditar(cliente.id)"
          >
            <template #icon><Icon icon="boxicons:pen-alt-filled" class="w-5 h-5" /></template>
          </BaseButton>

          <BaseButton
            :variante="cliente.activo ? 'ghost-danger' : 'ghost'"
            size="icon"
            :aria-label="cliente.activo ? 'Desactivar cliente' : 'Activar cliente'"
            :title="cliente.activo ? 'Desactivar' : 'Activar'"
            @click.stop="cambiarEstado(cliente)"
          >
            <template #icon>
              <Icon
                :icon="cliente.activo ? 'lucide:ban' : 'lucide:check-circle'"
                class="w-5 h-5"
              />
            </template>
          </BaseButton>
        </div>
      </BaseCard>
    </div>

    <div class="shrink-0 border-t border-gray-200 p-5">
    <!-- Paginación -->
    
      <BasePagination
            :model-value="clientesStore.paginaActual"
            :total="clientesStore.totalPaginas"
            :per-page="clientesStore.porPagina"
            @update:model-value="clientesStore.obtenerClientes"
        />

    </div>
  </div>
</template>