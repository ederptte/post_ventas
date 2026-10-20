<script setup>
import { ref, computed, onMounted } from 'vue';
import { useClientesStore } from '../stores/useClientes';
import { useRoute, useRouter } from 'vue-router';
import BaseInput from '../components/BaseInput.vue'
import BaseButton from '../components/BaseButton.vue';
import { useToast } from '../stores/useToast.js';


const clientesStore = useClientesStore();
const toast = useToast();
const route = useRoute();
const router = useRouter();

const idCliente = route.params.id;
const esEdicion = computed(() => !!idCliente);


const formulario = ref({
    nombre_cliente: '',
    cc: '',
    telefono_cliente: '',

})

onMounted(async () => {
    if (esEdicion.value) {
        const cliente = await clientesStore.obtenerClientePorId(idCliente);
        if (cliente) {
            formulario.value = {
                nombre_cliente: cliente.nombre_cliente,
                cc: cliente.cc,
                telefono_cliente: cliente.telefono_cliente,
            };
        }
    }
});


const manejarGuardado = async () => {
    let exito = false;

    if (esEdicion.value) {
        exito = await clientesStore.actualizarCliente(idCliente, formulario.value);
        if (exito) {
            toast.success('Cliente actualizado correctamente');
        }
        
        
    } else {
        exito = await clientesStore.crearCliente(formulario.value);
        if (exito) {
            toast.success('Cliente creado correctamente');
        }
    }

    if (exito) {
        router.push({ name: 'clientes' });
    } else {
        toast.error(clientesStore.error || 'Por favor, revisa los errores en el formulario');
    }
};
</script>

<template>

<div class="flex h-[calc(100dvh-2.5rem)] min-h-full flex-col rounded bg-white p-5">
    <div class="p-5">
        <h3 class=" font-black">{{ esEdicion ? 'Editar Cliente' : 'Crear Nuevo Cliente' }}</h3>
    </div>


    <form @submit.prevent="manejarGuardado" class="form-producto">

        <div class="w-full mb-3">
            <BaseInput
            label="Nombre del Cliente"
            id="nombre_cliente"
            type="text"
            v-model="formulario.nombre_cliente"
            :error="clientesStore.erroresValidacion?.nombre_cliente?.[0]"
            />

        </div>

        <BaseInput
        label="Numero de Identificación (CC)"
        id="cc"
        type="text"
        v-model="formulario.cc"
        :error="clientesStore.erroresValidacion?.cc?.[0]"
        />



        <BaseInput
        label="Telefono del Cliente"
        id="telefono_cliente"
        type="text"
        v-model="formulario.telefono_cliente"
        :error="clientesStore.erroresValidacion?.telefono_cliente?.[0]"
        />




      <div class="flex gap-3 mt-5">
        <BaseButton
            variante="terciario"
            type="submit"
            :disabled="clientesStore.cargando"
            @click="IrACrear">
                {{ clientesStore.cargando ? 'Guardando...' : (esEdicion ? 'Actualizar Cliente' : 'Guardar Cliente') }}
            </BaseButton>

        

        <!-- Botón para regresar sin guardar -->
        <router-link to="/clientes"">
          <BaseButton
            variante="secundario"
            :disabled="clientesStore.cargando"
            >
                cancelar
            </BaseButton>
        </router-link>
      </div>
    </form>
</div>


</template>
