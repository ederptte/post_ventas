<script setup>
import { onMounted, watch } from "vue";
import { useProveedoresStore } from "../stores/useProveedores";
import { useConfirmarStore } from "../stores/useConfirmarStore.js";
import BaseButton from "../components/BaseButton.vue";
import BaseInput from "../components/BaseInput.vue";
import { Icon } from "@iconify/vue";
import router from "../router/index.js";
import { useToast } from "../stores/useToast.js";
import BasePagination from "../components/BasePagination.vue";
import BaseCard from "../components/BaseCard.vue";

const proveedoresStore = useProveedoresStore();
const confirmarStore = useConfirmarStore();
const toast = useToast();
//const itemsPorPagina = 10;

onMounted(async () => {
    proveedoresStore.obtenerProveedores();
})

let timeoutBusqueda = null;
watch(() => proveedoresStore.textoBusqueda, () => {
    clearTimeout(timeoutBusqueda);
    timeoutBusqueda = setTimeout(async () => {
        await proveedoresStore.obtenerProveedores(1);
    }, 400);
});


const cambiarEstado = async (proveedor) => {
    const desactivar = proveedor.activo;

    const ok = await confirmarStore.confirmar({
        titulo: desactivar ? "Desactivar proveedor" : "Activar proveedor",
        mensaje: `¿Quieres ${desactivar ? "desactivar" : "activar"} "${proveedor.nombre_proveedor}"?`,
        confirmarTexto: desactivar ? "Desactivar" : "Activar",
        variante: desactivar ? "danger" : "primary",
    });

    if (!ok) return;

    const exito = await proveedoresStore.cambiarEstadoProveedor(proveedor.id);

    if (exito) {
       
        toast.success(`Proveedor ${desactivar ? "desactivado" : "activado"}`);
    } else {
        toast.error(proveedoresStore.error || "No se pudo cambiar el estado");
    }
};

const IrACrear = () => {
    router.push({ name: "proveedores.crear" });
};

const IrAEditar = (id) => {
    router.push({ name: "proveedores.editar", params: { id: id } });
};

const ArmarPedido = (proveedor) => {
    if (!proveedor.activo) {
        toast.error("El proveedor está inactivo. Actívalo para hacer pedidos.");
        return;
    }
    router.push({ name: "pedidos.crear", params: { proveedorId: proveedor.id } });
};
</script>


<template>
  <div class="flex h-[calc(100dvh-2.5rem)] min-h-full flex-col rounded bg-white p-5">
    <div class="border-b border-gray-100 pb-4 mb-4">
      <h3 class="font-black text-xl text-gray-800">Módulo General de Proveedores</h3>
      <p class="text-sm text-gray-500">En este módulo podrás gestionar los proveedores de tu tienda y realizar pedidos a los mismos.</p>
    </div>
    <div class="flex p-3 justify-between items-center gap-4 border-b border-gray-100">
      <div class="w-full max-w-md">
        <BaseInput
            label="Buscar proveedor"
            id="buscar_proveedor"
            type="text"
            v-model="proveedoresStore.textoBusqueda"
            placeholder="Escribe para buscar proveedores en tiempo real..."
            :clearable="true"
        />
      </div>

      <BaseButton @click="IrACrear">
        <template #icon><Icon icon="lucide:plus" class="w-5 h-5" /></template>
        Crear
      </BaseButton>
    </div>

    <!-- Cargando -->
    <div v-if="proveedoresStore.cargando" class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 xl:grid-cols-3">
      <div v-for="n in 6" :key="n" class="h-44 animate-pulse rounded-lg bg-gray-100" />
    </div>

    <!-- Sin resultados -->
    <p v-else-if="!proveedoresStore.proveedores.length" class="py-10 text-center text-gray-500">
      No se encontraron proveedores.
    </p>

    <!-- Tarjetas -->
    <div v-else class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-2 xl:grid-cols-3">
      <BaseCard
        v-for="proveedor in proveedoresStore.proveedores"
        :key="proveedor.id"
        :clickable="!!proveedor.activo"
        :class="{ 'opacity-60': !proveedor.activo }"
        @click="ArmarPedido(proveedor)"
        
      >
        <div class="flex items-start gap-3 ">
          <div
            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full
                   bg-green-100 font-semibold text-green-700 capitalize"
            aria-hidden="true"
          >
            {{ proveedor.nombre_proveedor?.charAt(0).toUpperCase() }}
          </div>

          <div class="min-w-0 flex-1">
            <h3 class="truncate font-semibold text-gray-800 capitalize">{{ proveedor.nombre_proveedor }}</h3>
            <span
              class="mt-1 inline-block rounded-full px-2 py-0.5 text-xs font-medium"
              :class="proveedor.activo ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600'"
            >
              {{ proveedor.activo ? 'Activo' : 'Inactivo' }}
            </span>
          </div>
        </div>

        <ul class="mt-4 space-y-2 text-sm text-gray-600">
          <li class="flex items-center gap-2">
            <Icon icon="lucide:phone" class="h-4 w-4 shrink-0 text-gray-400" />
            <span class="truncate">{{ proveedor.telefono_vendedor || '—' }}</span>
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
            @click.stop="IrAEditar(proveedor.id)"
          >
            <template #icon><Icon icon="boxicons:pen-alt-filled" class="w-5 h-5" /></template>
          </BaseButton>

          <BaseButton
            :variante="proveedor.activo ? 'ghost-danger' : 'ghost'"
            size="icon"
            :aria-label="proveedor.activo ? 'Desactivar proveedor' : 'Activar proveedor'"
            :title="proveedor.activo ? 'Desactivar' : 'Activar'"
            @click.stop="cambiarEstado(proveedor)"
          >
            <template #icon>
              <Icon
                :icon="proveedor.activo ? 'lucide:ban' : 'lucide:check-circle'"
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
            :model-value="proveedoresStore.paginaActual"
            :total="proveedoresStore.totalPaginas"
            :per-page="proveedoresStore.porPagina"
            @update:model-value="(nuevaPagina) => proveedoresStore.obtenerProveedores(nuevaPagina)"
        />

    </div>
  </div>
</template>