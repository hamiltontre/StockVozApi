<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $negocioId = $request->user()->negocio_id;
        $productos = Producto::where('negocio_id', $negocioId)
            ->where('activo', true)
            ->orderBy('nombre')
            ->paginate(50);

        return response()->json($productos);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $producto = Producto::where('negocio_id', $request->user()->negocio_id)
            ->findOrFail($id);
        return response()->json($producto);
    }

    public function stockBajo(Request $request): JsonResponse
    {
        $items = Producto::where('negocio_id', $request->user()->negocio_id)
            ->where('activo', true)
            ->whereColumn('stock', '<=', 'stock_minimo')
            ->orderBy('stock')
            ->get();
        return response()->json($items);
    }
}
