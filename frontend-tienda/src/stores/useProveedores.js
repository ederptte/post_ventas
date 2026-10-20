import { defineStore } from "pinia";
import { ref, computed } from 'vue';
import api from "@/services/api"; // o import api from "@/services/api";

export const useProveedoresStore = defineStore('proveedores', () => {
    const proveedores = ref([]);
    const proveedorActual = ref({});
    const cargando = ref(false);
    const error = ref(null);
    const textoBusqueda = ref('');

    const paginaActual = ref(1);
    const porPagina = ref(10);
    const totalPaginas = ref(1);

    const erroresValidacion = ref({});

    async function obtenerProveedores(pagina = 1) {
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

            const respuesta = await api.get('/proveedores', { params });

            proveedores.value = respuesta.data.data;
            totalPaginas.value = respuesta.data.last_page || 1;
        } catch (err) {
            error.value = 'Hubo un error al cargar los proveedores';
            console.error(err);
        } finally {
            cargando.value = false;
        }
    }

    async function crearProveedor(nuevoProveedor) {
        cargando.value = true;
        error.value = null;
        erroresValidacion.value = {};

        try {
            const respuesta = await api.post('/proveedores', nuevoProveedor);
            proveedores.value.unshift(respuesta.data.data || respuesta.data);
            return true;
        } catch (err) {
            if (err.response && err.response.status === 422) {
                erroresValidacion.value = err.response.data.errors;
                error.value = 'Por favor, corrige los errores en el formulario.';
            } else {
                error.value = 'Hubo un error al crear el proveedor';
            }
            console.error(err);
            return false;
        } finally {
            cargando.value = false;
        }
    }

    async function obtenerProveedorPorId(id) {
        cargando.value = true;
        error.value = null;
        try {
            const respuesta = await api.get(`/proveedores/${id}`);
            proveedorActual.value = respuesta.data.data || respuesta.data;
            return proveedorActual.value;
        } catch (err) {
            error.value = 'No se pudo cargar el proveedor';
            console.error(err);
        } finally {
            cargando.value = false;
        }
    }

    async function actualizarProveedor(id, datosActualizados) {
        cargando.value = true;
        error.value = null;
        erroresValidacion.value = {};

        try {
            const respuesta = await api.put(`/proveedores/${id}`, datosActualizados);
            const index = proveedores.value.findIndex(p => Number(p.id) === Number(id));
            if (index !== -1) {
                proveedores.value[index] = respuesta.data.data || respuesta.data;
            }
            return true;
        } catch (err) {
            if (err.response && err.response.status === 422) {
                erroresValidacion.value = err.response.data.errors;
                error.value = 'Por favor, corrige los errores en el formulario.';
            } else {
                error.value = 'Error al actualizar el proveedor'; // ⭐ Corregido aquí
            }
            console.error(err);
            return false;
        } finally {
            cargando.value = false;
        }
    }

    async function cambiarEstadoProveedor(id) {
        cargando.value = true;
        error.value = null;

        try {
            const respuesta = await api.patch(`/proveedores/${id}/activo`);
            const index = proveedores.value.findIndex(p => Number(p.id) === Number(id));

            if (index !== -1) {
                if (respuesta.data && (respuesta.data.data || respuesta.data.proveedor)) {
                    proveedores.value[index] = respuesta.data.data || respuesta.data.proveedor;
                } else if (typeof respuesta.data.activo !== 'undefined') {
                    proveedores.value[index].activo = respuesta.data.activo;
                } else {
                    proveedores.value[index].activo = !proveedores.value[index].activo;
                }
            }
            return true;
        } catch (err) {
            error.value = 'Error al cambiar el estado del proveedor';
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
        proveedores,
        proveedorActual,
        cargando,
        error,
        textoBusqueda,
        paginaActual,
        porPagina,
        totalPaginas,
        erroresValidacion,
        obtenerProveedores,
        crearProveedor,
        actualizarProveedor,
        cambiarEstadoProveedor,
        obtenerProveedorPorId,
        limpiarErrores
    };
});