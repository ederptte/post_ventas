<script setup>
import BaseModal from "./BaseModal.vue";
import BaseButton from "./BaseButton.vue";
import { useConfirmarStore } from '../stores/useConfirmarStore.js';

const confirmarStore = useConfirmarStore();
</script>

<template>
    <BaseModal
        :model-value="confirmarStore.estado.abierto"
        :title="confirmarStore.estado.titulo"
        size="sm"
        @update:model-value="(abierto) => !abierto && confirmarStore.responder(false)"
    >
        <!-- Mensaje tradicional del modal -->
        <p class="text-sm text-gray-600">{{ confirmarStore.estado.mensaje }}</p>

        <!-- 🌟 SECCIÓN DE OPCIONES DE PAGO -->
        <div v-if="confirmarStore.estado.verOpcionesPago" class="mt-4 space-y-2">
            <!-- Opción Contado -->
            <label
                class="flex cursor-pointer items-start gap-3 rounded-lg border p-3 transition hover:bg-gray-50"
                :class="
                    confirmarStore.estado.metodoElegido === 'contado'
                        ? 'border-green-500 bg-green-50/20'
                        : 'border-gray-200'
                "
            >
                <input
                    v-model="confirmarStore.estado.metodoElegido"
                    type="radio"
                    value="contado"
                    class="mt-1 h-4 w-4 text-green-600 focus:ring-green-500"
                />
                <div class="min-w-0 flex-1">
                    <span class="block text-sm font-semibold text-gray-800"
                        >Pago de Contado</span
                    >
                    <span class="block text-xs text-gray-500"
                        >Se registrará como liquidado de inmediato.</span
                    >
                </div>
            </label>

            <!-- Opción Crédito -->
            <label
                class="flex cursor-pointer items-start gap-3 rounded-lg border p-3 transition hover:bg-gray-50"
                :class="
                    confirmarStore.estado.metodoElegido === 'credito'
                        ? 'border-green-500 bg-green-50/20'
                        : 'border-gray-200'
                "
            >
                <input
                    v-model="confirmarStore.estado.metodoElegido"
                    type="radio"
                    value="credito"
                    class="mt-1 h-4 w-4 text-green-600 focus:ring-green-500"
                />
                <div class="min-w-0 flex-1">
                    <span class="block text-sm font-semibold text-gray-800"
                        >Cuenta por Pagar (Crédito)</span
                    >
                    <span class="block text-xs text-gray-500"
                        >Se guardará como una deuda en el sistema.</span
                    >
                </div>
            </label>
        </div>

        <template #footer>
            <BaseButton variante="secundario" @click="confirmarStore.responder(false)">
                Cancelar
            </BaseButton>
            <BaseButton :variante="confirmarStore.estado.variante" @click="confirmarStore.responder(true)">
                {{ confirmarStore.estado.confirmarTexto }}
            </BaseButton>
        </template>
    </BaseModal>
</template>