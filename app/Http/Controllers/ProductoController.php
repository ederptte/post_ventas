<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductoRequest;
use App\Http\Requests\UpdateProductoRequest;
use App\Models\PedidoDetalle;
use App\Models\Producto;
use Illuminate\Http\Request;



class ProductoController extends Controller
{

    public function historialCompras($id)
    {
        // Buscamos los detalles de este producto específicos de pedidos que ya estén RECIBIDOS
        $historial = PedidoDetalle::with(['pedido.proveedor'])
            ->where('producto_id', $id)
            ->whereHas('pedido', function ($query) {
                $query->where('estado', 'recibido');
            })
            ->orderBy(function ($query) {
                // Buscamos la fecha del pedido para ordenar las compras más recientes primero
                $query->select('fecha_recibido')
                    ->from('pedidos')
                    ->whereColumn('pedidos.id', 'pedido_detalles.pedido_id')
                    ->limit(1);
            }, 'desc')
            ->paginate(10);

        // Retornamos la información estructurada
        return response()->json([
            'status'      => 'success',
            'total_items' => $historial->total(),
            'per_page'    => $historial->perPage(),
            'current_page'=> $historial->currentPage(),
            'data'        => $historial->items()
        ], 200);
    }
    public function toggleActivo(Producto $producto)
    {
        $producto->update(['activo' => ! $producto->activo]);

        return response()->json(['status' => 'success', 'data' => $producto]);
    }
    public function index(Request $request)
    {
        // Validamos que la columna permitida exista, si no, usa 'id' por defecto
        $sortBy = $request->input('sort_by', 'id');
        $allowedSorts = ['id', 'nombre_producto', 'stock', 'precio_venta', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'id';
        }

        $direction = $request->input('direction', 'desc') === 'asc' ? 'asc' : 'desc';

        $productos = Producto::query()
        ->when($request->search, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre_producto', 'like', "%{$search}%")
                  ->orWhere('codigo_barra', 'like', "%{$search}%");
            });
        })
        ->orderBy($sortBy, $direction)
        ->paginate(8);

        return response()->json([
            'status'       => 'success',
            'total_items'  => $productos->total(),       
            'per_page'     => $productos->perPage(),      
            'current_page' => $productos->currentPage(),  
            'last_page'    => $productos->lastPage(),     
            'data'         => $productos->items()        
        ], 200);
    }

    public function store(StoreProductoRequest $request)
    {

        $producto = Producto::create($request->validated());


        return response()->json([
            'status'  => 'success',
            'message' => 'Producto creado con éxito',
            'data'    => $producto
        ], 201);
    }

    public function show($criterio)
    {

        $producto = Producto::where('id', $criterio)
                           ->orWhere('codigo_barra', $criterio)
                           ->first();

        if (!$producto) {
            return response()->json([
                'status'  => 'error',
                'message' => 'El producto solicitado no existe en el sistema.'
            ], 404);
        }

        return response()->json([
            'status'  => 'success',
            'data'    => $producto
        ], 200);
    }

    public function update(UpdateProductoRequest $request, Producto $producto)
    {

        $producto->update($request->validated());

        return response()->json([
            'status'  => 'success',
            'message' => 'Producto actualizado con éxito',
            'data'    => $producto
        ], 200);
    }

    public function destroy(Producto $producto)
    {
       
        $producto->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Producto eliminado del sistema con éxito.'
        ], 200);
    }
}
