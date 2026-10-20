<script setup>
import { onMounted } from "vue";
import { useClientesStore } from "../stores/useClientes";
import { useConfirmarStore } from "../stores/useConfirmarStore.js";
import BaseTable from "../components/BaseTable.vue";
import BaseButton from "../components/BaseButton.vue";
import { moneda } from "../utils/formatos.js";
import { Icon } from "@iconify/vue";
import router from "../router/index.js";
import { useToast } from "../stores/useToast.js";
import BasePagination from "../components/BasePagination.vue";

const clientesStore = useClientesStore();
const confirmarStore = useConfirmarStore();
const toast = useToast();

onMounted(async () => {
    await clientesStore.obtenerClientes();
});

const columnas = [
    { key: "nombre_cliente", label: "Cliente" },
    { key: "cc", label: "Cédula" },
    { key: "telefono_cliente", label: "Teléfono" },
    { key: "activo", label: "Estado", align: "center" },
    { key: "acciones", label: "", align: "right" },
];

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
        toast.success(`Cliente ${desactivar ? "desactivado" : "activado"} con éxito`);
    } else {
        toast.error(clientesStore.error || "No se pudo cambiar el estado");
    }
};

// Corregido a camelCase por convención
const irACrear = () => {
    router.push({ name: "clientes.crear" });
};

const irAEditar = (id) => {
    router.push({ name: "clientes.editar", params: { id } });
};
</script>

<template>
    <div class="flex h-[calc(100dvh-2.5rem)] min-h-full flex-col rounded bg-white p-5">
        <div class="border-b border-gray-100 pb-4 mb-4">
            <h3 class="font-black text-xl text-gray-800">
                Catálogo de Clientes
            </h3>
            <p class="text-sm text-gray-500">
                Gestiona la lista de tus clientes, controla su estado y configura sus datos.
            </p>
        </div>

        <div class="flex pb-3 justify-between items-center gap-4 border-b border-gray-100">
            <div class="w-full max-w-md">
                <input
                    type="text"
                    v-model="clientesStore.textoBusqueda"
                    placeholder="Escribe para buscar clientes en tiempo real..."
                    class="border p-2 rounded w-full focus:outline-none focus:ring-2 focus:ring-indigo-500"
                />
            </div>
            <BaseButton
                variante="primario"
                type="button"
                @click="irACrear"
            >
                Crear
            </BaseButton>
        </div>

        <BaseTable
            :columns="columnas"
            :rows="clientesStore.clientesFiltrados"
            :loading="clientesStore.cargando"
            emptyText="No se encontraron clientes registrados."
        >


            <template #cell-nombre_cliente="{ value }">
                <span class="font-semibold capitalize text-gray-800">
                    {{ value }}
                </span>
            </template>

            <template #cell-cc="{ value }">
                <span class="font-semibold text-green-600">
                    {{ value }}
                </span>
            </template>

            <template #cell-telefono_cliente="{ value }">
                <span class="font-semibold text-red-600">
                    {{value }}
                </span>
            </template>

            <template #cell-activo="{ value }">
                <span
                    class="inline-block rounded-full px-2 py-0.5 text-xs font-semibold capitalize"
                    :class="
                        value
                            ? 'bg-green-100 text-green-700'
                            : 'bg-gray-200 text-gray-600'
                    "
                >
                    {{ value ? "Activo" : "Inactivo" }}
                </span>
            </template>

            <template #cell-acciones="{ row }">
                <div class="flex justify-end gap-1">
                    <!-- Botón Editar -->
                    <BaseButton
                        variante="ghost"
                        size="icon"
                        aria-label="Editar producto"
                        title="Editar"
                        @click.stop="irAEditar(row.id)"
                    >
                        <template #icon>
                            <Icon icon="boxicons:pen-alt-filled" class="w-5 h-5" />
                        </template>
                    </BaseButton>

                    <!-- Botón Alternar Estado (Activar/Desactivar) -->
                    <BaseButton
                        :variante="row.activo ? 'ghost' : 'ghost-danger'"
                        size="icon"
                        :aria-label="
                            row.activo
                                ? 'Desactivar cliente'
                                : 'Activar cliente'
                        "
                        :title="row.activo ? 'Desactivar' : 'Activar'"
                        @click.stop="cambiarEstado(row)"
                    >
                        <template #icon>
                            <Icon
                                :icon="
                                    row.activo
                                        ? 'lucide:ban'
                                        : 'lucide:check-circle'
                                "
                                class="w-5 h-5"
                            />
                        </template>
                    </BaseButton>
                </div>
            </template>
        </BaseTable>

        <div class="shrink-0 border-t border-gray-200 pt-3">
            <BasePagination
                :model-value="clientesStore.paginaActual"
                :total="clientesStore.totalPaginas"
                :per-page="clientesStore.porPagina"
                @update:model-value="(nuevaPagina) => clientesStore.obtenerClientes(nuevaPagina)"
            />
        </div>
    </div>
</template>