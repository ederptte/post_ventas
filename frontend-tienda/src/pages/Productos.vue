<script setup>
import { ref, onMounted, watch, computed } from "vue";
import { useProductosStore } from "../stores/useProductos";
import { useConfirmarStore } from "../stores/useConfirmarStore.js";
import BaseTable from "../components/BaseTable.vue";
import BaseButton from "../components/BaseButton.vue";
import BaseSearch from "../components/BaseSearch.vue";
import BaseInput from "../components/BaseInput.vue";
import { moneda, formatearCantidad } from "../utils/formatos.js";
import { Icon } from "@iconify/vue";
import router from "../router/index.js";
import { useToast } from "../stores/useToast.js";
import BasePagination from "../components/BasePagination.vue";

// Subir a git hub
//git add .
//git commit -m "Descripción de lo que cambiaste"
//git push

const productosStore = useProductosStore();
const confirmarStore = useConfirmarStore();
const toast = useToast();

onMounted(async () => {
    await productosStore.obtenerProductos();
});

let timeoutBusqueda = null;
watch(() => productosStore.textoBusqueda, (nuevoTexto) => {
    clearTimeout(timeoutBusqueda);
    timeoutBusqueda = setTimeout(async () => {
        // Al buscar, siempre volvemos a la página 1
        await productosStore.obtenerProductos(1);
    }, 400); // Espera 400ms después de que el usuario deja de escribir
});

const columnas = [
    { key: "codigo_barra", label: "Código de barra" },
    { key: "nombre_producto", label: "Producto", sortable: true },
    { key: "presentacion", label: "Presentación" },
    { key: "stock", label: "Stock", align: "right", sortable: true },
    { key: "precio_venta", label: "Precio venta", align: "right" },
    { key: "precio_costo", label: "Precio costo", align: "right" },
    { key: "activo", label: "Estado", align: "center" },
    { key: "acciones", label: "", align: "right" },
];

const manejarOrden = (columnaKey) => {
    if (productosStore.sortBy === columnaKey) {
        productosStore.direction = productosStore.direction === 'asc' ? 'desc' : 'asc';
    } else {
        productosStore.sortBy = columnaKey;
        productosStore.direction = 'asc';
    }
    
    // Recargamos los datos desde la base de datos manteniendo la página actual
    productosStore.obtenerProductos(productosStore.paginaActual);
};




const cambiarEstado = async (producto) => {
    const desactivar = producto.activo;

    const ok = await confirmarStore.confirmar({
        titulo: desactivar ? "Desactivar producto" : "Activar producto",
        mensaje: `¿Quieres ${desactivar ? "desactivar" : "activar"} "${producto.nombre_producto}"?`,
        confirmarTexto: desactivar ? "Desactivar" : "Activar",
        variante: desactivar ? "danger" : "primary",
    });

    if (!ok) return;

    const exito = await productosStore.cambiarEstadoProducto(producto.id);

    if (exito) {
        toast.success(`Producto ${desactivar ? "desactivado" : "activado"} con éxito`);
    } else {
        toast.error(productosStore.error || "No se pudo cambiar el estado");
    }
};

// Corregido a camelCase por convención
const irACrear = () => {
    router.push({ name: "productos.crear" });
};

const irAEditar = (id) => {
    router.push({ name: "productos.editar", params: { id } });
};
</script>

<template>
    <div class="flex h-[calc(100dvh-2.5rem)] min-h-full flex-col rounded bg-white p-5">
        <div class="border-b border-gray-100 pb-4 mb-4">
            <h3 class="font-black text-xl text-gray-800">
                Catálogo de Productos
            </h3>
            <p class="text-sm text-gray-500">
                Gestiona el inventario de tu tienda, controla alertas de bajo
                stock y configura precios de venta.
            </p>
        </div>

        <div class="flex pb-3 justify-between items-center gap-4 border-b border-gray-100">
            <div class="w-full max-w-md">
                <BaseInput
                    label="Buscar producto"
                    id="buscar_producto"
                    type="text"
                    v-model="productosStore.textoBusqueda"
                    placeholder="Escribe para buscar productos en tiempo real..."
                    :clearable="true"
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
            :rows="productosStore.productos"
            :loading="productosStore.cargando"
            :sort-column="productosStore.sortBy"
            :sort-direction="productosStore.direction"
            @sort="manejarOrden"
        >
            <template #cell-stock="{ row, value }">
                <span
                    class="font-medium"
                    :class="
                        value <= row.alerta_bajo_stock
                            ? 'text-red-600 font-bold bg-red-50 px-2 py-0.5 rounded'
                            : 'text-gray-700'
                    "
                >
                    {{ formatearCantidad(value, row.unidad_medida) }}
                </span>
            </template>

            <template #cell-nombre_producto="{ value }">
                <span class="font-semibold capitalize text-gray-800">
                    {{ value }}
                </span>
            </template>

            <template #cell-presentacion="{ value }">
                <span class="font-semibold capitalize text-gray-800">
                    {{ value }}
                </span>
            </template>

            <template #cell-precio_venta="{ value }">
                <span class="font-semibold text-green-600">
                    {{ moneda(value) }}
                </span>
            </template>

            <template #cell-precio_costo="{ value }">
                <span class="font-semibold text-red-600">
                    {{ moneda(value) }}
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
                                ? 'Desactivar producto'
                                : 'Activar producto'
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
                v-model="productosStore.paginaActual"
                :total-pages="productosStore.totalPaginas"
                :per-page="productosStore.porPagina"
                @update:model-value="(nuevaPagina) => productosStore.obtenerProductos(nuevaPagina)"
            />
        </div>
    </div>
</template>
