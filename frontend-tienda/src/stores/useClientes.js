import { defineStore } from "pinia";
import { ref, computed } from 'vue';
import api from "@/services/api";

export const useClientesStore = defineStore('clientes', () =>{
    const clientes = ref([])
    const clienteActual = ref({})
    const cargando = ref(false)
    const error = ref(null)
    const textoBusqueda = ref('')

    const paginaActual = ref(1);
    const porPagina = ref(10);
    const totalPaginas = ref(1)

    const erroresValidacion = ref({});

    async function obtenerClientes(pagina = 1) {
        cargando.value = true;
        error.value = null;
        paginaActual.value = pagina;
        

        try {
            const respuesta = await api.get(`/clientes?page=${paginaActual.value}&limit=${porPagina.value}`)

            clientes.value = respuesta.data.data;
            totalPaginas.value = respuesta.data.last_page || 1;
        } catch (err) {
            error.value = 'Hubo un error al cargar los clientes'
            console.error(err)
        } finally {
            cargando.value = false
        }
    }

    async function crearCliente(nuevoCliente) {
        cargando.value = true;
        error.value = null;
        erroresValidacion.value = {};

        try {
            const respuesta = await api.post('/clientes', nuevoCliente);
            clientes.value.push(respuesta.data.data || respuesta.data);
            return true;
        } catch (err) {
            if (err.response && err.response.status === 422) {
                erroresValidacion.value = err.response.data.errors;
                error.value = 'Por favor, corrige los errores en el formulario.';
            } else {
                error.value = 'Hubo un error al crear el cliente';
            }
            console.error(err);
            return false;
        } finally{
            cargando.value = false
        }
    }

    async function obtenerClientePorId(id) {
        cargando.value = true;
        try {
            const respuesta = await api.get(`/clientes/${id}`);
            clienteActual.value = respuesta.data.data || respuesta.data;
            return clienteActual.value;
        } catch (err) {
            error.value = 'No se pudo cargar el cliente';
            console.error(err);
        } finally {
            cargando.value = false;
        }
    }

    async function actualizarCliente(id, datosActualizados) {
        cargando.value = true;
        error.value = null;
        erroresValidacion.value = {};
        try {
            const respuesta = await api.put(`/clientes/${id}`, datosActualizados);
            const index = clientes.value.findIndex(c => c.id === Number(id));
            if (index !== -1) {
                clientes.value[index] = respuesta.data.data || respuesta.data;
            }
            return true;
        } catch (err) {
            if (err.response && err.response.status === 422) {
                erroresValidacion.value = err.response.data.errors;
                error.value = 'Por favor, corrige los errores en el formulario.';
            } else {
                error.value = 'Error al actualizar el cliente';
            }
            console.error(err);
            return false;
        } finally {
            cargando.value = false;
        }
    }

    async function cambiarEstadoCliente(id) {
        try {
            // Llama a tu ruta de Laravel (no requiere enviar datos en el body si hace toggle)
            const respuesta = await api.patch(`/clientes/${id}/activo`);
            
            // Actualizamos el estado local del cliente basándonos en la respuesta de Laravel
            const index = clientes.value.findIndex(c => c.id === id);
            if (index !== -1) {
                // Si Laravel devuelve el cliente actualizado o el nuevo estado
                if (respuesta.data && (respuesta.data.data || respuesta.data.cliente)) {
                    clientes.value[index] = respuesta.data.data || respuesta.data.cliente;
                } else if (typeof respuesta.data.activo !== 'undefined') {
                    clientes.value[index].activo = respuesta.data.activo;
                } else {
                    // Fallback: invierte el estado actual si la respuesta solo fue un mensaje de éxito
                    clientes.value[index].activo = !clientes.value[index].activo;
                }
            }
            return true;
        } catch (err) {
            error.value = 'Error al cambiar el estado del cliente';
            console.error(err);
            return false;
        }
    }

    const clientesFiltrados = computed(()=>{
        if(!textoBusqueda.value.trim()){
            return clientes.value;
        }
        const busqueda = textoBusqueda.value.toLocaleLowerCase();
        return clientes.value.filter(cliente =>
            cliente.nombre_cliente.toLocaleLowerCase().includes(busqueda)
        )
    })

    return {
        clientes,
        clienteActual,
        cargando,
        error,
        textoBusqueda,
        paginaActual,
        porPagina,
        totalPaginas,
        clientesFiltrados,
        obtenerClientes,
        crearCliente,
        actualizarCliente,
        cambiarEstadoCliente,
        erroresValidacion,
        obtenerClientePorId
    }
})