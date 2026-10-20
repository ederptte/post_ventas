<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePedidoRequest;
use App\Http\Requests\UpdatePedidoRequest;
use App\Models\Pedido;
use App\Models\PedidoAbono;
use App\Models\PedidoDetalle;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PedidoController extends Controller
{
    public function index(Request $request)
    {
        $busqueda = trim((string) $request->query('search', ''));

        $pedidos = Pedido::with('proveedor')
            ->when($busqueda !== '', function ($query) use ($busqueda) {
                $query->where(function ($q) use ($busqueda) {
                    $q->where('numero_pedido', 'like', "%{$busqueda}%")
                      ->orWhereHas('proveedor', function ($p) use ($busqueda) {
                          $p->where('nombre_proveedor', 'like', "%{$busqueda}%");
                      });
                });
            })
            ->latest('id')
            ->paginate(10);

        return response()->json([
            'status'       => 'success',
            'total_items'  => $pedidos->total(),
            'per_page'     => $pedidos->perPage(),
            'current_page' => $pedidos->currentPage(),
            'last_page'    => $pedidos->lastPage(),
            'data'         => $pedidos->items(),
        ], 200);
    }

    /**
     * 1. Registrar un nuevo pedido (estado: pendiente, no toca el stock)
     */
    public function store(StorePedidoRequest $request)
    {
        $pedido = DB::transaction(function () use ($request) {
            $datos     = $request->validated();
            $esCredito = $datos['tipo_pago'] === 'credito';

            // A. Cabecera (numero_pedido queda null hasta tener el ID real)
            $pedido = Pedido::create([
                'proveedor_id'    => $datos['proveedor_id'],
                'estado'          => 'pendiente',
                'tipo_pago'       => $datos['tipo_pago'],
                'estado_pago'     => $esCredito ? 'pendiente' : 'pagado',
                'total_pedido'    => 0,
                'monto_abonado'   => 0,
                'saldo_pendiente' => 0,
            ]);

            // B. Detalles y total real
            $total = 0;

            foreach ($datos['productos'] as $item) {
                PedidoDetalle::create([
                    'pedido_id'             => $pedido->id,
                    'producto_id'           => $item['producto_id'],
                    'cantidad'              => $item['cantidad'],
                    'precio_compra_pactado' => $item['precio_compra_pactado'],
                ]);

                $total += $item['cantidad'] * $item['precio_compra_pactado'];
            }

            $total = round($total, 2);

            // C. Número correlativo y totales
            $pedido->update([
                'numero_pedido'   => 'PED-' . str_pad($pedido->id, 4, '0', STR_PAD_LEFT),
                'total_pedido'    => $total,
                'saldo_pendiente' => $esCredito ? $total : 0,
            ]);

            return $pedido;
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Pedido registrado con éxito en estado PENDIENTE.',
            'data'    => $pedido->load('detalles'),
        ], 201);
    }

    /**
     * 2. Recibir el pedido (suma el stock al inventario)
     */
    public function recibir(Request $request, $id)
    {
        $request->validate([
            'tipo_pago' => 'nullable|in:contado,credito',
        ]);

        $pedido = DB::transaction(function () use ($request, $id) {
            // El bloqueo evita que dos clics simultáneos sumen el stock dos veces
            $pedido = Pedido::with('detalles')->lockForUpdate()->find($id);

            if (!$pedido) {
                abort(404, 'El pedido solicitado no existe en el sistema.');
            }

            if ($pedido->estado !== 'pendiente') {
                abort(409, 'Este pedido ya fue recibido previamente. No se puede volver a procesar.');
            }

            $tipoPago = $request->input('tipo_pago', $pedido->tipo_pago);
            $total    = 0;

            foreach ($pedido->detalles as $detalle) {
                $producto = Producto::find($detalle->producto_id);

                if ($producto) {
                    $producto->increment('stock', $detalle->cantidad);

                    // Regla de negocio: subir el costo general solo si el nuevo es mayor
                    if ($detalle->precio_compra_pactado > $producto->precio_costo) {
                        $producto->update(['precio_costo' => $detalle->precio_compra_pactado]);
                    }
                }

                $total += $detalle->cantidad * $detalle->precio_compra_pactado;
            }

            $total = round($total, 2);

            $pedido->update([
                'estado'          => 'recibido',
                'tipo_pago'       => $tipoPago,
                'total_pedido'    => $total,
                'saldo_pendiente' => $tipoPago === 'credito' ? $total : 0,
                'monto_abonado'   => 0,
                'estado_pago'     => $tipoPago === 'credito' ? 'pendiente' : 'pagado',
                'fecha_recibido'  => now(),
            ]);

            return $pedido;
        });

        return response()->json([
            'status'  => 'success',
            'message' => '¡Pedido recibido con éxito! El stock de los productos ha sido actualizado en el inventario.',
            'data'    => $pedido->load('detalles'),
        ], 200);
    }

    /**
     * 3. Registrar un abono (solo pedidos recibidos a crédito)
     */
    public function abonar(Request $request, $id)
    {
        $request->validate([
            'monto_abonado'   => 'required|numeric|min:0.01',
            'nota_referencia' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($request, $id) {
            $pedido = Pedido::lockForUpdate()->find($id);

            if (!$pedido || $pedido->estado !== 'recibido') {
                abort(400, 'El pedido debe estar RECIBIDO para poder registrarle pagos.');
            }

            if ($pedido->tipo_pago !== 'credito') {
                abort(400, 'Este pedido no fue registrado bajo la modalidad de crédito.');
            }

            if ($pedido->saldo_pendiente <= 0) {
                abort(400, 'Este pedido ya se encuentra liquidado por completo.');
            }

            $monto = round((float) $request->monto_abonado, 2);

            if ($monto > round((float) $pedido->saldo_pendiente, 2)) {
                abort(400, 'El monto ingresado supera el saldo pendiente actual de: $' . number_format($pedido->saldo_pendiente, 2));
            }

            PedidoAbono::create([
                'pedido_id'       => $pedido->id,
                'monto_abonado'   => $monto,
                'fecha_pago'      => now()->toDateString(),
                'nota_referencia' => $request->nota_referencia,
            ]);

            // round evita residuos como 0.0000001
            $nuevoMontoAbonado   = round($pedido->monto_abonado + $monto, 2);
            $nuevoSaldoPendiente = max(round($pedido->total_pedido - $nuevoMontoAbonado, 2), 0);

            $pedido->update([
                'monto_abonado'   => $nuevoMontoAbonado,
                'saldo_pendiente' => $nuevoSaldoPendiente,
                'estado_pago'     => $nuevoSaldoPendiente <= 0 ? 'pagado' : 'abono_parcial',
            ]);
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Abono registrado correctamente.',
            'data'    => Pedido::with('abonos')->find($id),
        ], 200);
    }

    public function show($id)
    {
        $pedido = Pedido::with(['proveedor', 'detalles.producto', 'abonos'])->find($id);

        if (!$pedido) {
            return response()->json([
                'status'  => 'error',
                'message' => 'El pedido solicitado no existe en el sistema.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $pedido,
        ], 200);
    }

    public function update(UpdatePedidoRequest $request, $id)
    {
        $pedido = Pedido::with('detalles')->find($id);

        if (!$pedido) {
            return response()->json([
                'status'  => 'error',
                'message' => 'El pedido solicitado no existe en el sistema.',
            ], 404);
        }

        DB::transaction(function () use ($request, $pedido) {
            $recibido = $pedido->estado === 'recibido';
            $items    = collect($request->productos);
            $total    = round($items->sum(fn ($i) => $i['cantidad'] * $i['precio_compra_pactado']), 2);

            // No permitir un total menor a lo que ya se pagó
            if ($recibido && $pedido->monto_abonado > $total) {
                abort(409, 'El nuevo total ($' . number_format($total, 2) . ') no puede ser menor a lo ya abonado ($' . number_format($pedido->monto_abonado, 2) . ').');
            }

            $productos = collect();

            // Si ya sumó stock, ajustar solo la diferencia por producto
            if ($recibido) {
                $antes   = $pedido->detalles->groupBy('producto_id')->map(fn ($g) => $g->sum('cantidad'));
                $despues = $items->groupBy('producto_id')->map(fn ($g) => $g->sum('cantidad'));
                $ids     = $antes->keys()->merge($despues->keys())->unique();

                // lockForUpdate evita que una venta simultánea cambie el stock a mitad del proceso
                $productos = Producto::whereIn('id', $ids)->lockForUpdate()->get()->keyBy('id');

                foreach ($ids as $idProducto) {
                    $producto = $productos->get($idProducto);
                    if (!$producto) continue;

                    // Más cantidad pedida = más stock. Delta = nueva - vieja
                    $delta = ($despues[$idProducto] ?? 0) - ($antes[$idProducto] ?? 0);

                    if ($producto->stock + $delta < 0) {
                        abort(409, "No se puede modificar el pedido: el stock de \"{$producto->nombre_producto}\" quedaría negativo porque parte de esa mercancía ya salió del inventario.");
                    }

                    if ($delta != 0) {
                        $producto->increment('stock', $delta);
                    }
                }
            }

            // Reemplazar los detalles
            $pedido->detalles()->delete();

            foreach ($items as $item) {
                PedidoDetalle::create([
                    'pedido_id'             => $pedido->id,
                    'producto_id'           => $item['producto_id'],
                    'cantidad'              => $item['cantidad'],
                    'precio_compra_pactado' => $item['precio_compra_pactado'],
                ]);

                // Regla de negocio: subir el costo general solo si el nuevo es mayor
                if ($recibido) {
                    $producto = $productos->get($item['producto_id']);
                    if ($producto && $item['precio_compra_pactado'] > $producto->precio_costo) {
                        $producto->update(['precio_costo' => $item['precio_compra_pactado']]);
                    }
                }
            }

            // Totales (el saldo también se recalcula en pedidos pendientes a crédito)
            $saldo = ($pedido->tipo_pago === 'credito')
                ? max(round($total - $pedido->monto_abonado, 2), 0)
                : 0;

            $datos = [
                'total_pedido'    => $total,
                'saldo_pendiente' => $saldo,
            ];

            if ($recibido) {
                $datos['estado_pago'] = match (true) {
                    $pedido->tipo_pago !== 'credito' => 'pagado',
                    $saldo <= 0                      => 'pagado',
                    $pedido->monto_abonado > 0       => 'abono_parcial',
                    default                          => 'pendiente',
                };
            }

            $pedido->update($datos);
        });

        return response()->json([
            'status'  => 'success',
            'message' => '¡Pedido y stock de inventario recalculados con éxito!',
            'data'    => $pedido->load('detalles'),
        ], 200);
    }

    public function destroy($id)
    {
        $pedido = Pedido::find($id);

        if (!$pedido) {
            return response()->json([
                'status'  => 'error',
                'message' => 'El pedido que intenta eliminar no existe.',
            ], 404);
        }

        // Si ya fue recibido, se bloquea el borrado porque afectó al inventario
        if ($pedido->estado !== 'pendiente') {
            return response()->json([
                'status'  => 'error',
                'message' => 'No se puede eliminar un pedido con estado RECIBIDO porque ya afectó al inventario.',
            ], 400);
        }

        // Por el cascadeOnDelete de la migración, también se borran sus detalles
        $pedido->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'El pedido pendiente ha sido eliminado del sistema con éxito.',
        ], 200);
    }
}