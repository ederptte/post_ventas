<script setup>
import { ref, computed, onMounted } from 'vue'
import { useProveedoresStore } from '../stores/useProveedores';
import { useRouter, useRoute } from 'vue-router'
import BaseInput from '../components/BaseInput.vue'
import BaseButton from '../components/BaseButton.vue'
import { useToast } from '../stores/useToast.js';
import BaseSwitch from '../components/BaseSwitch.vue'
import BaseChips from '../components/BaseChips.vue'
import { dias } from '../utils/dias.js'
import BaseChoice from '../components/BaseChoice.vue'
import { frecuencias } from '../utils/frecuencias.js'
import { entregas } from '../utils/entregas.js'

const proveedoresStore = useProveedoresStore();
const toast = useToast();
const route = useRoute();
const router = useRouter();

const idProveedor = route.params.id;
const esEdicion = computed(() => !!idProveedor);


const formulario =  ref({
  nombre_proveedor: '',
  id_tributaria: '',
  es_mayorista: true,
  cuando_viene: ['lun'],
  frecuencia_visita: 'semanal',
  cuando_entrega: 'mismo_dia',
  nombre_vendedor: '',
  telefono_vendedor: '',
  
})

onMounted(async () => {
    if (esEdicion.value) {
        const proveedor = await proveedoresStore.obtenerProveedorPorId(idProveedor);
        if (proveedor) {
            formulario.value = {
                nombre_proveedor: proveedor.nombre_proveedor,
                id_tributaria: proveedor.id_tributaria,
                es_mayorista: proveedor.es_mayorista,
                cuando_viene: proveedor.cuando_viene,
                frecuencia_visita: proveedor.frecuencia_visita,
                cuando_entrega: proveedor.cuando_entrega,
                nombre_vendedor: proveedor.nombre_vendedor,
                telefono_vendedor: proveedor.telefono_vendedor,
            };
        }
    }
});

const manejarGuardado = async () => {
    let exito = false;

    if (esEdicion.value) {
        exito = await proveedoresStore.actualizarProveedor(idProveedor, formulario.value);
        if (exito) {
            toast.success('Proveedor actualizado correctamente');
        }
    } else {
        exito = await proveedoresStore.crearProveedor(formulario.value);
        if (exito) {
            toast.success('Proveedor creado correctamente');
        }
    }

    if (exito) {
        router.push({ name: 'proveedores' });
    } else {
        toast.error(proveedoresStore.error || 'Por favor, revisa los errores en el formulario');
    }
};

</script>
<template>


<div class="flex h-[calc(100dvh-2.5rem)] min-h-full flex-col rounded bg-white p-5">
    <div class="p-5">
        <h3 class=" font-black">{{ esEdicion ? 'Editar Proveedor' : 'Crear Proveedor' }}</h3>
    </div>
    


        <form @submit.prevent="manejarGuardado" class="px-5">

          <div class="flex gap-5 mb-3">
            <div class="w-full">
                <BaseInput
                v-model="formulario.nombre_proveedor"
                label="Nombre del Proveedor"
                placeholder="Ej: Distribuidora JJ Restrepo"
                :error="useProveedoresStore.erroresValidacion?.nombre_proveedor?.[0]" />
            </div>

            
            <div class="w-full">
              <BaseChips
              v-model="formulario.cuando_viene"
              label="¿Qué días viene?"
              :options="dias"
              :error="useProveedoresStore.erroresValidacion?.cuando_viene?.[0]"
            />
            
            </div>
          </div>

          <div class="flex gap-5 mb-3">
              <div class="w-full mb-3">
                <BaseInput
                v-model="formulario.id_tributaria"
                label="ID Tributaria"
                placeholder="Ej: 123456789"
                :error="useProveedoresStore.erroresValidacion?.id_tributaria?.[0]" />
            </div>

            <div class="w-full mb-3">
                  <BaseChoice
                    v-model="formulario.cuando_entrega"
                    label="¿Cuando entrega el producto?"
                    :options="entregas"
                    clearable
                    :error="useProveedoresStore.erroresValidacion?.cuando_entrega?.[0]"
                  />
              </div>
          </div>

          <div class="flex gap-5 mb-3">
              <div class="w-full mb-3">
                <BaseSwitch 
              v-model="formulario.es_mayorista" 
              label="¿Es mayorista?" 
              />
              </div>

              <div class="w-full">
                <BaseChoice
                    v-model="formulario.frecuencia_visita"
                    label="Frecuencia de visitas"
                    :options="frecuencias"
                    clearable
                    :error="useProveedoresStore.erroresValidacion?.frecuencia_visita?.[0]"
                  />
                </div>
          </div>
            

            

            

            <div class="flex gap-5 mb-3">

              <div class="w-full mb-3">
                <BaseInput
                v-model="formulario.nombre_vendedor"
                label="Nombre Vendedor"
                placeholder="Ej: Pedro Perez"
                :error="useProveedoresStore.erroresValidacion?.nombre_vendedor?.[0]" />
            </div>

            

            </div>

            <div class="flex gap-5 mb-3">

                <div class="w-full mb-3">
                <BaseInput
                v-model="formulario.telefono_vendedor"
                label="Telefono Vendedor"
                placeholder="Ej: 3123456789"
                :error="useProveedoresStore.erroresValidacion?.telefono_vendedor?.[0]" />
                </div>


                
            </div>
            
           <div class="flex py-5 gap-3 justify-end">

            <BaseButton
            variante="terciario"
            type="submit"
            :disabled="proveedoresStore.cargando"
            @click="IrACrear">
                {{ proveedoresStore.cargando ? 'Guardando...' : (esEdicion ? 'Actualizar Proveedor' : 'Guardar Proveedor') }}
            </BaseButton>

            <router-link to="/proveedores"">
          <BaseButton
            variante="secundario"
            :disabled="proveedoresStore.cargando"
            >
                cancelar
            </BaseButton>
        </router-link>
           </div>
            

        </form>
</div>

</template>
