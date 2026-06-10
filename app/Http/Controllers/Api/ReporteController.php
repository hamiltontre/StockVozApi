<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Venta;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Métricas para el dashboard web.
 * Todas las consultas filtran por negocio_id del usuario autenticado.
 */
class ReporteController extends Controller
{
    /**
     * GET /api/reportes/resumen?periodo=7
     */
    public function resumen(Request $request): JsonResponse
    {
        $negocioId = $request->user()->negocio_id;
        $dias = (int) $request->query('periodo', 7);
        $desde = now()->subDays($dias)->startOfDay();

        $hoy = Venta::where('negocio_id', $negocioId)
            ->where('estado', 'completada')
            ->whereDate('vendido_en', today())
            ->selectRaw('COUNT(*) as ventas, COALESCE(SUM(total),0) as monto')
            ->first();

        $periodo = Venta::where('negocio_id', $negocioId)
            ->where('vendido_en', '>=', $desde)
            ->selectRaw("
                SUM(CASE WHEN estado='completada' THEN 1 ELSE 0 END) as ventas,
                COALESCE(SUM(CASE WHEN estado='completada' THEN total ELSE 0 END),0) as monto,
                COALESCE(AVG(CASE WHEN estado='completada' THEN total END),0) as promedio,
                SUM(CASE WHEN estado='anulada' THEN 1 ELSE 0 END) as anuladas
            ")
            ->first();

        return response()->json([
            'hoy' => [
                'ventas' => (int) $hoy->ventas,
                'monto'  => (int) $hoy->monto,
            ],
            'periodo' => [
                'dias'     => $dias,
                'ventas'   => (int) $periodo->ventas,
                'monto'    => (int) $periodo->monto,
                'promedio' => (int) round($periodo->promedio),
                'anuladas' => (int) $periodo->anuladas,
            ],
        ]);
    }

    /**
     * GET /api/reportes/ventas-por-dia?periodo=7
     */
    public function ventasPorDia(Request $request): JsonResponse
    {
        $negocioId = $request->user()->negocio_id;
        $dias = (int) $request->query('periodo', 7);

        $filas = Venta::where('negocio_id', $negocioId)
            ->where('estado', 'completada')
            ->where('vendido_en', '>=', now()->subDays($dias)->startOfDay())
            ->selectRaw('DATE(vendido_en) as fecha, COUNT(*) as ventas, SUM(total) as monto')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        return response()->json($filas);
    }

    /**
     * GET /api/reportes/top-productos?limite=5
     */
    public function topProductos(Request $request): JsonResponse
    {
        $negocioId = $request->user()->negocio_id;
        $limite = (int) $request->query('limite', 5);

        $top = DB::table('detalle_ventas as dv')
            ->join('ventas as v', 'v.id', '=', 'dv.venta_id')
            ->where('v.negocio_id', $negocioId)
            ->where('v.estado', 'completada')
            ->selectRaw('dv.nombre_producto, SUM(dv.cantidad) as cantidad, SUM(dv.subtotal) as monto')
            ->groupBy('dv.nombre_producto')
            ->orderByDesc('cantidad')
            ->limit($limite)
            ->get();

        return response()->json($top);
    }
}
