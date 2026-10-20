import { defineStore } from "pinia";
import { ref, computed } from 'vue';
import api from "@/services/api";

export const useProductosStore = defineStore('productos', () => {
    // State
    const productos = ref([]);
    const productoActual = ref({});
    const cargando = ref(false);
    const error = ref(null);
    const textoBusqueda = ref('');

    const paginaActual = ref(1);
    const porPagina = ref(10);
    const totalPaginas = ref(1);

    const erroresValidacion = ref({});

    // Actions
    async function obtenerProductos(pagina = 1) {
        cargando.value = true;
        error.value = null;
        paginaActual.value = pagina;

        try {
            const params = {
                page: paginaActual.value,
                limit: porPagina.value,
            };
            if (textoBusqueda.value.trim()) {
                params.search = textoBusqueda.value.trim();
            }

            const respuesta = await api.get('/productos', { params });

            productos.value = respuesta.data.data;
            totalPaginas.value = respuesta.data.last_page || 1;
        } catch (err) {
            error.value = 'Hubo un error al cargar los productos';
            console.error(err);
        } finally {
            cargando.value = false;
        }
    }

    async function buscarProductos(texto) {
        try {
            const respuesta = await api.get('/productos', { params: { search: texto } });
            return respuesta.data.data ?? respuesta.data ?? [];
        } catch (err) {
            console.error("Error al buscar productos:", err);
            return [];
        }
    }

    async function crearProducto(nuevoProducto) {
        cargando.value = true;
        error.value = null;
        erroresValidacion.value = {};

        try {
            const respuesta = await api.post('/productos', nuevoProducto);
            productos.value.unshift(respuesta.data.data || respuesta.data);
            return true;
        } catch (err) {
            if (err.response?.status === 422) {
                erroresValidacion.value = err.response.data.errors;
                error.value = 'Por favor, corrige los errores en el formulario.';
            } else {
                error.value = 'Hubo un error al crear el producto';
            }
            console.error(err);
            return false;
        } finally {
            cargando.value = false;
        }
    }

    async function obtenerProductoPorId(id) {
        cargando.value = true;
        error.value = null;
        try {
            const respuesta = await api.get(`/productos/${id}`);
            productoActual.value = respuesta.data.data || respuesta.data;
            return productoActual.value;
        } catch (err) {
            error.value = 'No se pudo cargar el producto';
            console.error(err);
        } finally {
            cargando.value = false;
        }
    }

    async function actualizarProducto(id, datosActualizados) {
        cargando.value = true;
        error.value = null;
        erroresValidacion.value = {};
        
        try {
            const respuesta = await api.put(`/productos/${id}`, datosActualizados);
            const index = productos.value.findIndex(p => Number(p.id) === Number(id));
            if (index !== -1) {
                productos.value[index] = respuesta.data.data || respuesta.data;
            }
            return true;
        } catch (err) {
            if (err.response?.status === 422) {
                erroresValidacion.value = err.response.data.errors;
                error.value = 'Por favor, corrige los errores en el formulario.';
            } else {
                error.value = 'Error al actualizar el producto';
            }
            console.error(err);
            return false;
        } finally {
            cargando.value = false;
        }
    }

    async function cambiarEstadoProducto(id) {
        cargando.value = true;
        error.value = null;
        
        try {
            const respuesta = await api.patch(`/productos/${id}/activo`);
            const index = productos.value.findIndex(p => Number(p.id) === Number(id));
            
            if (index !== -1) {
                if (respuesta.data && (respuesta.data.data || respuesta.data.producto)) {
                    productos.value[index] = respuesta.data.data || respuesta.data.producto;
                } else if (typeof respuesta.data.activo !== 'undefined') {
                    productos.value[index].activo = respuesta.data.activo;
                } else {
                    productos.value[index].activo = !productos.value[index].activo;
                }
            }
            return true;
        } catch (err) {
            error.value = 'Error al cambiar el estado del producto';
            console.error(err);
            return false;
        } finally {
            cargando.value = false;
        }
    }

    function limpiarErrores() {
        erroresValidacion.value = {};
        error.value = null;
    }

    return {
        productos,
        productoActual,
        cargando,
        error,
        textoBusqueda,
        paginaActual,
        porPagina,
        totalPaginas,
        erroresValidacion,
        obtenerProductos,
        buscarProductos,
        crearProducto,
        obtenerProductoPorId,
        actualizarProducto,
        cambiarEstadoProducto,
        limpiarErrores
    };
});