<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProveedorRequest;
use App\Http\Requests\UpdateProveedorRequest;
use App\Models\Proveedor;
use Illuminate\Http\Request;

class ProveedorController extends Controller
{
    public function toggleActivo(Proveedor $proveedor)
    {
        $proveedor->update(['activo' => ! $proveedor->activo]);

        return response()->json(['status' => 'success', 'data' => $proveedor]);
    }
    public function index(Request $request)
    {
        $proveedores = Proveedor::query()
        ->when($request->search, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre_proveedor', 'like', "%{$search}%")
                  ->orWhere('id_tributaria', 'like', "%{$search}%");
            });
        })
        ->orderByDesc('id')
        ->paginate(10);

        return response()->json([
            'status'      => 'success',
            'total_items' => $proveedores->total(),        // El total real en toda la base de datos
            'per_page'    => $proveedores->perPage(),      // Cuántos hay por página (10)
            'current_page'=> $proveedores->currentPage(),  // En qué página estás parado
            'last_page'   => $proveedores->lastPage(),     // Cuántas páginas hay en total
            'data'        => $proveedores->items()         // Extrae únicamente el array de los productos
        ], 200);
    }

    public function store(StoreProveedorRequest $request)
    {

        $proveedor = Proveedor::create($request->validated());


        return response()->json([
            'status'  => 'success',
            'message' => 'Proveedor creado con éxito',
            'data'    => $proveedor
        ], 201);
    }

    public function show($criterio)
    {

        $proveedor = Proveedor::where('id', $criterio)
                           ->orWhere('id_tributaria', $criterio)
                           ->first();

        if (!$proveedor) {
            return response()->json([
                'status'  => 'error',
                'message' => 'El proveedor solicitado no existe en el sistema.'
            ], 404);
        }

        return response()->json([
            'status'  => 'success',
            'data'    => $proveedor
        ], 200);
    }

    public function update(UpdateProveedorRequest $request, Proveedor $proveedor)
    {
        // Laravel ya inyectó el proveedor y validó que existe (404 si no).
        // UpdateProveedorRequest ya validó los datos de forma segura.
        $proveedor->update($request->validated());

        return response()->json([
            'status'  => 'success',
            'message' => 'Proveedor actualizado con éxito',
            'data'    => $proveedor
        ], 200);
    }

    public function destroy(Proveedor $proveedor)
    {
        // Route Model Binding se encarga de buscarlo y lanzar el 404 si no existe.
        $proveedor->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Proveedor eliminado del sistema con éxito.'
        ], 200);
    }
}
