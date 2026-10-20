<script setup>
import { ref, computed, onMounted } from 'vue';
import { useProductosStore } from '../stores/useProductos';
import { useRoute, useRouter } from 'vue-router';
import BaseInput from '../components/BaseInput.vue';
import BaseSelect from '../components/BaseSelect.vue';
import BaseMoneyInput from '../components/BaseMoneyInput.vue';
import BaseButton from '../components/BaseButton.vue';
import { formatearCantidad } from "../utils/formatos.js";
import { useToast } from '../stores/useToast.js';

const productosStore = useProductosStore();
const toast = useToast();
const route = useRoute();
const router = useRouter();

const idProducto = route.params.id;
const esEdicion = computed(() => !!idProducto);

const formulario = ref({
    codigo_barra: '',
    nombre_producto: '',
    presentacion: '',
    unidad_medida: '',
    precio_costo: '',
    precio_venta: '',
    alerta_bajo_stock: '',
    stock: '',
});

onMounted(async () => {
    productosStore.erroresValidacion = {};

    if (esEdicion.value) {
        const producto = await productosStore.obtenerProductoPorId(idProducto);
        if (producto) {
            // Determinamos la unidad para saber si debemos redondear el stock
            const unidadMedida = producto.unidad_medida ?? '';
            let stockValor = producto.stock ?? '';

            // Si no es kilogramo y hay stock, nos aseguramos de que sea un número entero puro
            if (unidadMedida !== 'kilogramo' && stockValor !== '') {
                stockValor = Math.round(Number(stockValor));
            }

            formulario.value = {
                codigo_barra: producto.codigo_barra ?? '',
                nombre_producto: producto.nombre_producto ?? '',
                presentacion: producto.presentacion ?? '',
                unidad_medida: unidadMedida,
                precio_costo: producto.precio_costo ?? '',
                precio_venta: producto.precio_venta ?? '',
                alerta_bajo_stock: producto.alerta_bajo_stock ?? '',
                stock: stockValor, // 👈 Aquí ya entra limpio sin decimales si es pieza
            };
        }
    }
});


const opcionesUnidadMedidas = [
    { value: 'pieza', label: 'Pieza' },
    { value: 'kilogramo', label: 'Kilogramo' },
];

const manejarGuardado = async () => {
    let exito = false;

    if (esEdicion.value) {
        exito = await productosStore.actualizarProducto(idProducto, formulario.value);
        if (exito) {
            toast.success('Producto actualizado correctamente');
        }
    } else {
        exito = await productosStore.crearProducto(formulario.value);
        if (exito) {
            toast.success('Producto creado correctamente');
        }
    }

    if (exito) {
        router.push({ name: 'productos' });
    } else {
        toast.error(productosStore.error || 'Por favor, revisa los errores en el formulario');
    }
};
</script>

<template>
    <div class="w-full max-w-5xl mx-auto rounded bg-white p-6 shadow-sm mb-12">

        <!-- Cabecera -->
        <div class="pb-4 mb-6 border-b border-gray-100">
            <h3 class="font-black text-xl text-gray-800">
                {{ esEdicion ? 'Editar Producto' : 'Crear Nuevo Producto' }}
            </h3>
            <p class="text-sm text-gray-500 mt-1">
                Completa los datos del producto para mantener tu inventario actualizado.
            </p>
        </div>

        <!-- Formulario organizado en Grid de 2 columnas -->
        <form @submit.prevent="manejarGuardado" class="space-y-4">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Código de barras -->
                <BaseInput
                    label="Código de Barras"
                    id="codigo_barra"
                    type="text"
                    v-model="formulario.codigo_barra"
                    :error="productosStore.erroresValidacion?.codigo_barra?.[0]"
                />

                <!-- Nombre del producto -->
                <BaseInput
                    label="Nombre Del Producto"
                    id="nombre_producto"
                    type="text"
                    v-model="formulario.nombre_producto"
                    :error="productosStore.erroresValidacion?.nombre_producto?.[0]"
                />

                <!-- Presentación -->
                <BaseInput
                    label="Presentación"
                    id="presentacion"
                    type="text"
                    v-model="formulario.presentacion"
                    :error="productosStore.erroresValidacion?.presentacion?.[0]"
                />

                <!-- Unidad de medida -->
                <BaseSelect
                    label="Unidad de medida"
                    v-model="formulario.unidad_medida"
                    :options="opcionesUnidadMedidas"
                    :error="productosStore.erroresValidacion?.unidad_medida?.[0]"


                >
                <template #option="{ option }">
                        <span>{{ formatearCantidad(option.label) }}</span>
                    </template>

                </BaseSelect>

                <!-- Precio Costo -->
                <BaseMoneyInput
                    label="Precio Costo"
                    id="precio_costo"
                    type="text"
                    v-model="formulario.precio_costo"
                    :error="productosStore.erroresValidacion?.precio_costo?.[0]"
                />

                <!-- Precio Venta -->
                <BaseMoneyInput
                    label="Precio Venta"
                    id="precio_venta"
                    type="text"
                    v-model="formulario.precio_venta"
                    :error="productosStore.erroresValidacion?.precio_venta?.[0]"
                />

                <!-- Alerta de bajo Stock -->
                <BaseInput
                    label="Alerta de bajo Stock"
                    id="alerta_bajo_stock"
                    type="text"
                    v-model="formulario.alerta_bajo_stock"
                    :error="productosStore.erroresValidacion?.alerta_bajo_stock?.[0]"
                />

                <!-- Stock -->
                <BaseInput
                    label="Stock"
                    id="stock"
                    type="number"
                    :step="formulario.unidad_medida === 'kilogramo' ? '0.01' : '1'"
                    :min="0"
                    v-model.number="formulario.stock"
                    :error="productosStore.erroresValidacion?.stock?.[0]"
                    @keypress="(e) => {
                        if (formulario.unidad_medida !== 'kilogramo' && (e.key === '.' || e.key === ',')) {
                            e.preventDefault();
                        }
                    }"
                />
            </div>

            <!-- Botones de acción -->
            <div class="flex gap-3 pt-6 border-t border-gray-100 mt-6">
                <BaseButton
                    variante="primario"
                    type="submit"
                    :disabled="productosStore.cargando"
                >
                    {{ productosStore.cargando ? 'Guardando...' : (esEdicion ? 'Actualizar Producto' : 'Guardar Producto') }}
                </BaseButton>

                <router-link to="/productos">
                    <BaseButton
                        variante="secundario"
                        type="button"
                        :disabled="productosStore.cargando"
                    >
                        Cancelar
                    </BaseButton>
                </router-link>
            </div>
        </form>
    </div>
</template>
