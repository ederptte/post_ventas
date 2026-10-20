<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\ClienteController;

Route::apiResource('productos', ProductoController::class);
Route::patch('/productos/{producto}/activo', [ProductoController::class, 'toggleActivo']);
Route::get('/productos/{id}/historial-compras', [ProductoController::class, 'historialCompras']);

Route::apiResource('proveedores', ProveedorController::class);
Route::patch('/proveedores/{proveedor}/activo', [ProveedorController::class, 'toggleActivo']);

Route::apiResource('pedidos', PedidoController::class);
Route::post('/pedidos/{id}/recibir', [PedidoController::class, 'recibir']);
Route::post('/pedidos/{id}/abonar', [PedidoController::class, 'abonar']);

Route::apiResource('ventas', VentaController::class);
Route::post('/ventas/{id}/realizar', [VentaController::class, 'realizar']);
Route::post('/ventas/{id}/abonar', [VentaController::class, 'abonar']);

Route::apiResource('clientes', ClienteController::class);
Route::patch('/clientes/{cliente}/activo', [ClienteController::class, 'toggleActivo']);