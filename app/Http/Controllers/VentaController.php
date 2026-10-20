<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVentaRequest;
use App\Http\Requests\UpdateVentaRequest;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\VentaAbono;
use App\Models\VentaDetalle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VentaController extends Controller
{
    public function index(Request $request)
    {
        $busqueda = trim((string) $request->query('search', ''));

        $ventas = Venta::with('cliente')
            ->when($busqueda !== '', function ($query) use ($busqueda) {
                $query->where(function ($q) use ($busqueda) {
                    $q->where('numero_venta', 'like', "%{$busqueda}%")
                      ->orWhereHas('cliente', function ($c) use ($busqueda) {
                          $c->where('nombre_cliente', 'like', "%{$busqueda}%");
                      });
                });
            })
            ->latest('id')
            ->paginate(10);

        return response()->json([
            'status'       => 'success',
            'total_items'  => $ventas->total(),
            'per_page'     => $ventas->perPage(),
            'current_page' => $ventas->currentPage(),
            'last_page'    => $ventas->lastPage(),
            'data'         => $ventas->items(),
        ], 200);
    }

    /**
     * 1. Registrar una nueva venta (estado: pendiente, no toca el stock)
     */
    public function store(StoreVentaRequest $request)
    {
        $venta = DB::transaction(function () use ($request) {
            $datos     = $request->validated();
            $esCredito = $datos['tipo_pago'] === 'credito';

            // A. Cabecera (numero_venta queda null hasta tener el ID real)
            $venta = Venta::create([
                'cliente_id'      => $datos['cliente_id'],
                'estado'          => 'pendiente',
                'tipo_pago'       => $datos['tipo_pago'],
                'estado_pago'     => $esCredito ? 'pendiente' : 'pagado',
                'total_venta'     => 0,
                'monto_abonado'   => 0,
                'saldo_pendiente' => 0,
            ]);

            // B. Detalles y total real
            $total = 0;

            foreach ($datos['productos'] as $item) {
                VentaDetalle::create([
                    'venta_id'           => $venta->id,
                    'producto_id'        => $item['producto_id'],
                    'cantidad'           => $item['cantidad'],
                    'precio_venta_final' => $item['precio_venta_final'],
                ]);

                $total += $item['cantidad'] * $item['precio_venta_final'];
            }

            $total = round($total, 2);

            // C. Número correlativo y totales
            $venta->update([
                'numero_venta'    => 'VTA-' . str_pad($venta->id, 4, '0', STR_PAD_LEFT),
                'total_venta'     => $total,
                'saldo_pendiente' => $esCredito ? $total : 0,
            ]);

            return $venta;
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Venta registrada con éxito en estado PENDIENTE.',
            'data'    => $venta->load('detalles'),
        ], 201);
    }

    /**
     * 2. Realizar la venta (descuenta el stock del inventario)
     */
    public function realizar(Request $request, $id)
    {
        $request->validate([
            'tipo_pago' => 'nullable|in:contado,credito',
        ]);

        $venta = DB::transaction(function () use ($request, $id) {
            $venta = Venta::with('detalles')->lockForUpdate()->find($id);

            if (!$venta) {
                abort(404, 'La venta solicitada no existe en el sistema.');
            }

            if ($venta->estado !== 'pendiente') {
                abort(409, 'Esta venta ya fue procesada previamente.');
            }

            $tipoPago = $request->input('tipo_pago', $venta->tipo_pago);
            $total    = 0;

            foreach ($venta->detalles as $detalle) {
                $producto = Producto::lockForUpdate()->find($detalle->producto_id);

                if (!$producto || $producto->stock < $detalle->cantidad) {
                    abort(422, 'Stock insuficiente para el producto: ' . ($producto?->nombre_producto ?? 'Desconocido'));
                }

                $producto->decrement('stock', $detalle->cantidad);

                $total += $detalle->cantidad * $detalle->precio_venta_final;
            }

            $total = round($total, 2);
            $saldo = ($tipoPago === 'credito')
                ? max(round($total - $venta->monto_abonado, 2), 0)
                : 0;

            $venta->update([
                'estado'          => 'realizado',
                'tipo_pago'       => $tipoPago,
                'total_venta'     => $total,
                'saldo_pendiente' => $saldo,
                'estado_pago'     => ($tipoPago === 'credito' && $saldo > 0) ? 'pendiente' : 'pagado',
                'fecha_entrega'   => now(),
            ]);

            return $venta;
        });

        return response()->json([
            'status'  => 'success',
            'message' => '¡Venta procesada con éxito! El inventario ha sido actualizado.',
            'data'    => $venta->load('detalles'),
        ], 200);
    }

    /**
     * 3. Registrar un abono (solo ventas realizadas a crédito)
     */
    public function abonar(Request $request, $id)
    {
        $request->validate([
            'monto_abonado'   => 'required|numeric|min:0.01',
            'nota_referencia' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($request, $id) {
            $venta = Venta::lockForUpdate()->find($id);

            if (!$venta || $venta->estado !== 'realizado') {
                abort(400, 'La venta debe estar REALIZADA para registrarle pagos.');
            }

            if ($venta->tipo_pago !== 'credito') {
                abort(400, 'Esta venta no fue registrada bajo la modalidad de crédito.');
            }

            if ($venta->saldo_pendiente <= 0) {
                abort(400, 'Esta venta ya se encuentra liquidada por completo.');
            }

            $monto = round((float) $request->monto_abonado, 2);

            if ($monto > round((float) $venta->saldo_pendiente, 2)) {
                abort(400, 'El monto ingresado supera el saldo pendiente actual.');
            }

            VentaAbono::create([
                'venta_id'        => $venta->id,
                'monto_abonado'   => $monto,
                'fecha_pago'      => now()->toDateString(),
                'nota_referencia' => $request->nota_referencia,
            ]);

            $nuevoMontoAbonado   = round($venta->monto_abonado + $monto, 2);
            $nuevoSaldoPendiente = max(round($venta->total_venta - $nuevoMontoAbonado, 2), 0);

            $venta->update([
                'monto_abonado'   => $nuevoMontoAbonado,
                'saldo_pendiente' => $nuevoSaldoPendiente,
                'estado_pago'     => $nuevoSaldoPendiente <= 0 ? 'pagado' : 'abono_parcial',
            ]);
        });

        return response()->json([
            'status'  => 'success',
            'message' => 'Abono registrado correctamente.',
            'data'    => Venta::with('abonos')->find($id),
        ], 200);
    }

    public function show($id)
    {
        $venta = Venta::with(['cliente', 'detalles.producto', 'abonos'])->find($id);

        if (!$venta) {
            return response()->json([
                'status'  => 'error',
                'message' => 'La venta solicitada no existe en el sistema.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data'   => $venta,
        ], 200);
    }

    public function update(UpdateVentaRequest $request, $id)
    {
        $venta = Venta::with('detalles')->find($id);

        if (!$venta) {
            return response()->json([
                'status'  => 'error',
                'message' => 'La venta solicitada no existe en el sistema.',
            ], 404);
        }

        DB::transaction(function () use ($request, $venta) {
            $procesada = $venta->estado === 'realizado';
            $items     = collect($request->productos);
            $total     = round($items->sum(fn ($i) => $i['cantidad'] * $i['precio_venta_final']), 2);

            if ($procesada && $venta->monto_abonado > $total) {
                abort(409, 'El nuevo total no puede ser menor a lo ya abonado.');
            }

            // Si ya descontó stock, ajustar solo la diferencia por producto
            if ($procesada) {
                $antes   = $venta->detalles->groupBy('producto_id')->map(fn ($g) => $g->sum('cantidad'));
                $despues = $items->groupBy('producto_id')->map(fn ($g) => $g->sum('cantidad'));
                $ids     = $antes->keys()->merge($despues->keys())->unique();

                $productos = Producto::whereIn('id', $ids)->lockForUpdate()->get()->keyBy('id');

                foreach ($ids as $idProducto) {
                    $producto = $productos->get($idProducto);
                    if (!$producto) continue;

                    // Más cantidad vendida = menos stock. Delta = nueva - vieja
                    $delta = ($despues[$idProducto] ?? 0) - ($antes[$idProducto] ?? 0);

                    if ($producto->stock - $delta < 0) {
                        abort(409, "Stock insuficiente para modificar el producto: \"{$producto->nombre_producto}\".");
                    }

                    if ($delta != 0) {
                        $producto->decrement('stock', $delta);
                    }
                }
            }

            // Reemplazar los detalles
            $venta->detalles()->delete();

            foreach ($items as $item) {
                VentaDetalle::create([
                    'venta_id'           => $venta->id,
                    'producto_id'        => $item['producto_id'],
                    'cantidad'           => $item['cantidad'],
                    'precio_venta_final' => $item['precio_venta_final'],
                ]);
            }

            // Totales (el saldo también se recalcula en ventas pendientes a crédito)
            $saldo = ($venta->tipo_pago === 'credito')
                ? max(round($total - $venta->monto_abonado, 2), 0)
                : 0;

            $datos = [
                'total_venta'     => $total,
                'saldo_pendiente' => $saldo,
            ];

            if ($procesada) {
                $datos['estado_pago'] = match (true) {
                    $venta->tipo_pago !== 'credito' => 'pagado',
                    $saldo <= 0                     => 'pagado',
                    $venta->monto_abonado > 0       => 'abono_parcial',
                    default                         => 'pendiente',
                };
            }

            $venta->update($datos);
        });

        return response()->json([
            'status'  => 'success',
            'message' => '¡Venta recalculada con éxito!',
            'data'    => $venta->load('detalles'),
        ], 200);
    }

    public function destroy($id)
    {
        $venta = Venta::find($id);

        if (!$venta) {
            return response()->json([
                'status'  => 'error',
                'message' => 'La venta que intenta eliminar no existe.',
            ], 404);
        }

        if ($venta->estado !== 'pendiente') {
            return response()->json([
                'status'  => 'error',
                'message' => 'No se puede eliminar una venta procesada porque ya afectó al inventario.',
            ], 400);
        }

        $venta->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'La venta pendiente ha sido eliminada del sistema con éxito.',
        ], 200);
    }
}