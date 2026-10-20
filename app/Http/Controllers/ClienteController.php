<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClienteRequest;
use App\Http\Requests\UpdateClienteRequest;
use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    public function toggleActivo(Cliente $Cliente)
    {
        $Cliente->update(['activo' => ! $Cliente->activo]);

        return response()->json(['status' => 'success', 'data' => $Cliente]);
    }
    public function index(Request $request)
    {
        $Clientes = Cliente::query()
        ->when($request->search, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre_cliente', 'like', "%{$search}%")
                  ->orWhere('cc', 'like', "%{$search}%");
            });
        })
        ->orderByDesc('id')
        ->paginate(10);

        return response()->json([
            'status'      => 'success',
            'total_items' => $Clientes->total(),        // El total real en toda la base de datos
            'per_page'    => $Clientes->perPage(),      // Cuántos hay por página (10)
            'current_page'=> $Clientes->currentPage(),  // En qué página estás parado
            'last_page'   => $Clientes->lastPage(),     // Cuántas páginas hay en total
            'data'        => $Clientes->items()         // Extrae únicamente el array de los productos
        ], 200);
    }

    public function store(StoreClienteRequest $request)
    {

        $Cliente = Cliente::create($request->validated());


        return response()->json([
            'status'  => 'success',
            'message' => 'Cliente creado con éxito',
            'data'    => $Cliente
        ], 201);
    }

    public function show($criterio)
    {

        $Cliente = Cliente::where('id', $criterio)
                           ->orWhere('cc', $criterio)
                           ->first();

        if (!$Cliente) {
            return response()->json([
                'status'  => 'error',
                'message' => 'El Cliente solicitado no existe en el sistema.'
            ], 404);
        }

        return response()->json([
            'status'  => 'success',
            'data'    => $Cliente
        ], 200);
    }

     public function update(UpdateClienteRequest $request, $Cliente)
    {

        $Cliente = Cliente::find($Cliente);


        if (!$Cliente) {
            return response()->json([
                'status'  => 'error',
                'message' => 'El Cliente que intenta actualizar no existe.'
            ], 404);
        }

        $Cliente->update($request->validated());

        return response()->json([
            'status'  => 'success',
            'message' => 'Cliente actualizado con éxito',
            'data'    => $Cliente
        ], 200);
    }

    public function destroy($Cliente)
    {
        $item = Cliente::find($Cliente);

        if (!$item) {
            return response()->json([
                'status'  => 'error',
                'message' => 'El Cliente que intenta eliminar no existe.'
            ], 404);
        }

        $item->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Cliente eliminado del sistema con éxito.'
        ], 200);
    }
}
