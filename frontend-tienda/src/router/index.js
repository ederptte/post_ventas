import { createRouter, createWebHistory } from 'vue-router';
import Productos from '@/pages/Productos.vue';
import Proveedores from '@/pages/Proveedores.vue';
import Clientes from '../pages/Clientes.vue';
import Pedidos from '@/pages/Pedidos.vue';
import Ventas from '@/pages/Ventas.vue';
import ArmarVenta from '@/pages/ArmarVenta.vue';
import ArmarPedido from '@/pages/ArmarPedido.vue';
import FormularioProducto from '../pages/FormularioProducto.vue';
import FormularioProveedor from '../pages/FormularioProveedor.vue';
import FormularioCliente from '../pages/FormularioCliente.vue';
import MainLayout from '../layouts/MainLayout.vue';
import NuevaVenta from '../pages/NuevaVenta.vue';






const routes = [
    {
        path: '/',
        component: MainLayout,
        children: [
            { path: 'productos', name: 'productos', component: Productos },
            { path: 'productos/crear', name: 'productos.crear', component: FormularioProducto },
            { path: 'productos/editar/:id', name: 'productos.editar', component: FormularioProducto },
            { path: 'proveedores', name: 'proveedores', component: Proveedores },
            { path: 'proveedores/crear', name: 'proveedores.crear', component: FormularioProveedor },
            { path: 'proveedores/editar/:id', name: 'proveedores.editar', component: FormularioProveedor },
            { path: 'pedidos', name: 'pedidos', component: Pedidos },
            { path: 'pedidos/nuevo/:proveedorId', name: 'pedidos.crear', component: ArmarPedido, props: true },
            { path: 'pedidos/editar/:id', name: 'pedidos.editar', component: ArmarPedido, props: true },
            { path: 'ventas', name: 'ventas', component: Ventas },
            { path: 'ventas/nueva/:clienteId', name: 'ventas.crear', component: ArmarVenta, props: true },
            { path: 'ventas/editar/:id', name: 'ventas.editar', component: ArmarVenta, props: true },
            { path: 'clientes', name: 'clientes', component: Clientes },
            { path: 'clientes/ventas', name: 'clientes.ventas', component: NuevaVenta },
            { path: 'clientes/crear', name: 'clientes.crear', component: FormularioCliente },
            { path: 'clientes/editar/:id', name: 'clientes.editar', component: FormularioCliente },

        ]
    },

];

const router = createRouter({
    history: createWebHistory(),
    routes
});

export default router;
