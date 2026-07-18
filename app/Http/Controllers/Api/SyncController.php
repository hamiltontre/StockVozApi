<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DetalleVenta;
use App\Models\Producto;
use App\Models\SyncLog;
use App\Models\Venta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Recibe la cola offline que la app móvil acumula localmente.
 * Cada item es independiente — uno puede fallar sin afectar al resto.
 */
class SyncController extends Controller
{
    /**
     * POST /api/sync
     * Body esperado: { items: [{ tabla, operacion, payload }, ...] }
     */
    public function sync(Request $request): JsonResponse
    {
        $data = $request->validate([
            'items' => 'required|array|max:200',
            'items.*.tabla'     => 'required|string',
            'items.*.operacion' => 'required|in:INSERT,UPDATE,DELETE',
            'items.*.payload'   => 'required|array',
        ]);

        $usuario = $request->user();
        $negocioId = $usuario->negocio_id;

        $resultados = [];

        foreach ($data['items'] as $idx => $item) {
            try {
                $this->procesarItem($item, $negocioId);

                SyncLog::create([
                    'negocio_id' => $negocioId,
                    'usuario_id' => $usuario->id,
                    'tabla'      => $item['tabla'],
                    'operacion'  => $item['operacion'],
                    'resultado'  => 'exito',
                    'payload'    => $item['payload'],
                ]);

                $resultados[] = ['index' => $idx, 'ok' => true];
            } catch (Throwable $e) {
                SyncLog::create([
                    'negocio_id'    => $negocioId,
                    'usuario_id'    => $usuario->id,
                    'tabla'         => $item['tabla'],
                    'operacion'     => $item['operacion'],
                    'resultado'     => 'error',
                    'payload'       => $item['payload'],
                    'error_mensaje' => $e->getMessage(),
                ]);

                $resultados[] = ['index' => $idx, 'ok' => false, 'error' => $e->getMessage()];
            }
        }

        return response()->json([
            'procesados' => count($data['items']),
            'exitosos'   => collect($resultados)->where('ok', true)->count(),
            'fallidos'   => collect($resultados)->where('ok', false)->count(),
            'resultados' => $resultados,
        ]);
    }

    /**
     * Despacha cada item al handler correcto según la tabla destino.
     */
    private function procesarItem(array $item, int $negocioId): void
    {
        $tabla = $item['tabla'];
        $payload = $item['payload'];

        match ($tabla) {
            'ventas'    => $this->procesarVenta($payload, $negocioId, $item['operacion']),
            'productos' => $this->procesarProducto($payload, $negocioId, $item['operacion']),
            default     => throw new \InvalidArgumentException("Tabla no soportada: $tabla"),
        };
    }

    /**
     * Una venta llega con su detalle anidado.
     * Idempotente: si ya existe una venta con el mismo cliente_id, se ignora.
     */
    private function procesarVenta(array $p, int $negocioId, string $op): void
    {
        if ($op !== 'INSERT') {
            throw new \InvalidArgumentException("Solo INSERT soportado para ventas");
        }

        // Idempotencia por cliente_id (el id local de la app)
        $existe = Venta::where('negocio_id', $negocioId)
            ->where('cliente_id', $p['id'] ?? null)
            ->exists();
        if ($existe) return;

        DB::transaction(function () use ($p, $negocioId) {
            $venta = Venta::create([
                'negocio_id'  => $negocioId,
                'cliente_id'  => $p['id'] ?? null,
                'total'       => $p['total'] ?? 0,
                'descuento'   => $p['descuento'] ?? 0,
                'metodo_pago' => $p['metodo_pago'] ?? 'efectivo',
                'estado'      => $p['estado'] ?? 'completada',
                'notas'       => $p['notas'] ?? null,
                'es_fiado'    => (bool) ($p['es_fiado'] ?? false),
                'fiador_nombre'   => $p['fiador_nombre'] ?? null,
                'fiado_pagado_en' => $p['fiado_pagado_en'] ?? null,
                'vendido_en'  => $p['creado_en'] ?? now(),
            ]);

            foreach (($p['items'] ?? []) as $linea) {
                DetalleVenta::create([
                    'venta_id'        => $venta->id,
                    'nombre_producto' => $linea['nombre_producto'] ?? '',
                    'cantidad'        => $linea['cantidad'] ?? 0,
                    'precio_unitario' => $linea['precio_unitario'] ?? 0,
                    'subtotal'        => $linea['subtotal'] ?? 0,
                ]);
            }
        });
    }

    /**
     * Productos: upsert por (negocio_id, cliente_id).
     */
    private function procesarProducto(array $p, int $negocioId, string $op): void
    {
        if ($op === 'DELETE') {
            Producto::where('negocio_id', $negocioId)
                ->where('cliente_id', $p['id'] ?? null)
                ->update(['activo' => false]);
            return;
        }

        // Costo mayor al precio de venta suele ser un dedazo del usuario.
        // Se acepta igual (lo corrige después) pero queda registrado.
        if (isset($p['precio'], $p['precio_costo'])
            && $p['precio'] > 0
            && $p['precio_costo'] > $p['precio']) {
            \Log::warning('StockVoz: producto con precio_costo > precio', [
                'negocio_id'   => $negocioId,
                'nombre'       => $p['nombre'] ?? 'desconocido',
                'precio'       => $p['precio'],
                'precio_costo' => $p['precio_costo'],
            ]);
        }

        Producto::updateOrCreate(
            ['negocio_id' => $negocioId, 'cliente_id' => $p['id'] ?? null],
            [
                'nombre'            => $p['nombre'] ?? '',
                'codigo_barras'     => $p['codigo_barras'] ?? null,
                'precio'            => $p['precio'] ?? 0,
                'precio_costo'      => $p['precio_costo'] ?? 0,
                'precio_docena'     => $p['precio_docena'] ?? 0,
                'unidad'            => $p['unidad'] ?? 'unidad',
                'fecha_vencimiento' => $p['fecha_vencimiento'] ?? null,
                'stock'             => $p['stock'] ?? 0,
                'stock_minimo'      => $p['stock_minimo'] ?? 1,
                'activo'            => $p['activo'] ?? true,
            ]
        );
    }
}
