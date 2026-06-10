<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $usuario = Auth::user();
        $negocioId = $usuario->negocio_id;
        $dias = (int) $request->query('periodo', 7);

        // Resumen del día
        $hoy = Venta::where('negocio_id', $negocioId)
            ->where('estado', 'completada')
            ->whereDate('vendido_en', today())
            ->selectRaw('COUNT(*) as ventas, COALESCE(SUM(total),0) as monto')
            ->first();

        // Resumen del período
        $desde = now()->subDays($dias)->startOfDay();
        $periodo = Venta::where('negocio_id', $negocioId)
            ->where('vendido_en', '>=', $desde)
            ->selectRaw("
                SUM(CASE WHEN estado='completada' THEN 1 ELSE 0 END) as ventas,
                COALESCE(SUM(CASE WHEN estado='completada' THEN total ELSE 0 END),0) as monto,
                COALESCE(AVG(CASE WHEN estado='completada' THEN total END),0) as promedio,
                SUM(CASE WHEN estado='anulada' THEN 1 ELSE 0 END) as anuladas
            ")
            ->first();

        // Serie diaria para el gráfico
        $serie = Venta::where('negocio_id', $negocioId)
            ->where('estado', 'completada')
            ->where('vendido_en', '>=', $desde)
            ->selectRaw('DATE(vendido_en) as fecha, COUNT(*) as ventas, SUM(total) as monto')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        // Top productos
        $top = DB::table('detalle_ventas as dv')
            ->join('ventas as v', 'v.id', '=', 'dv.venta_id')
            ->where('v.negocio_id', $negocioId)
            ->where('v.estado', 'completada')
            ->where('v.vendido_en', '>=', $desde)
            ->selectRaw('dv.nombre_producto, SUM(dv.cantidad) as cantidad, SUM(dv.subtotal) as monto')
            ->groupBy('dv.nombre_producto')
            ->orderByDesc('cantidad')
            ->limit(5)
            ->get();

        // Stock bajo
        $stockBajo = Producto::where('negocio_id', $negocioId)
            ->where('activo', true)
            ->whereColumn('stock', '<=', 'stock_minimo')
            ->orderBy('stock')
            ->limit(10)
            ->get();

        // Últimas ventas
        $ultimas = Venta::with('detalle')
            ->where('negocio_id', $negocioId)
            ->orderByDesc('vendido_en')
            ->limit(15)
            ->get();

        return view('dashboard.index', compact(
            'usuario', 'dias', 'hoy', 'periodo', 'serie', 'top', 'stockBajo', 'ultimas'
        ));
    }
}
