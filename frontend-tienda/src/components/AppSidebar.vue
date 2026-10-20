<script setup>
import { Icon } from "@iconify/vue";

const items = [
    { label: "Nueva Venta", icon: "lucide:users", to: "/clientes/ventas" },
    { label: "Productos", icon: "lucide:package", to: "/productos" },
    { label: "Pedidos", icon: "lucide:package", to: "/pedidos" },
    { label: "ventas", icon: "lucide:package", to: "/ventas" },
    { label: "Nuevo Pedido", icon: "lucide:package", to: "/proveedores" },
    { label: "Clientes", icon: "lucide:users", to: "/clientes" },
];
</script>

<template>
    <aside
        class="flex h-screen w-64 shrink-0 flex-col bg-slate-900 text-slate-300"
    >
        <!-- Logotipo o Nombre del Sistema -->
        <div class="flex h-16 items-center px-4 border-b border-slate-800">
            <span class="text-lg font-semibold text-white tracking-wide"
                >Mi Sistema</span
            >
        </div>

        <!-- Menú de Navegación -->
        <nav class="flex-1 space-y-1 px-3 py-4" aria-label="Principal">
            <RouterLink
                v-for="item in items"
                :key="item.to"
                :to="item.to"
                class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition duration-150 hover:bg-slate-800 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-green-400"
                active-class="!bg-green-600 !text-white"
            >
                <Icon :icon="item.icon" class="h-5 w-5 shrink-0" />
                <span>{{ item.label }}</span>
            </RouterLink>
        </nav>
    </aside>
</template>
