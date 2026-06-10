@extends('layouts.app')

@section('title', 'Dashboard')

@php
  $fmt = fn ($centavos) => 'C$ ' . number_format($centavos / 100, 2);
@endphp

@section('body')
<div class="app">
  {{-- Sidebar --}}
  <aside class="sidebar">
    <div class="logo">
      <div class="logo-icon">SV</div>
      <div class="logo-text">StockVoz</div>
    </div>

    <nav>
      <a class="nav-item active" href="{{ route('dashboard') }}">
        <span class="nav-icon">📊</span> Dashboard
      </a>
    </nav>

    <div class="user-panel">
      <div class="user-name">{{ $usuario->nombre }}</div>
      <div class="user-rol">{{ $usuario->negocio?->nombre }}</div>
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="logout-btn">Cerrar sesión</button>
      </form>
    </div>
  </aside>

  {{-- Main --}}
  <main class="main">
    <div style="display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:20px;">
      <div>
        <h1 class="h1">Resumen del negocio</h1>
        <p class="subtitle">{{ now()->locale('es')->isoFormat('dddd D [de] MMMM, YYYY') }}</p>
      </div>
    </div>

    {{-- Hoy --}}
    <div class="section-label">HOY</div>
    <div class="grid grid-2" style="margin-bottom:6px;">
      <div class="card">
        <div class="metric-label">Ventas registradas</div>
        <div class="metric-value azul">{{ (int) $hoy->ventas }}</div>
      </div>
      <div class="card">
        <div class="metric-label">Ingresos del día</div>
        <div class="metric-value verde">{{ $fmt($hoy->monto) }}</div>
      </div>
    </div>

    {{-- Selector período --}}
    <div class="section-label">Período</div>
    <div class="periodo-bar">
      <a href="?periodo=7"  class="periodo-btn {{ $dias === 7 ? 'active' : '' }}">Últimos 7 días</a>
      <a href="?periodo=30" class="periodo-btn {{ $dias === 30 ? 'active' : '' }}">Últimos 30 días</a>
    </div>

    {{-- Métricas del período --}}
    <div class="grid grid-4">
      <div class="card">
        <div class="metric-label">Total ventas</div>
        <div class="metric-value azul">{{ (int) $periodo->ventas }}</div>
      </div>
      <div class="card">
        <div class="metric-label">Ingresos</div>
        <div class="metric-value verde">{{ $fmt($periodo->monto) }}</div>
      </div>
      <div class="card">
        <div class="metric-label">Ticket promedio</div>
        <div class="metric-value amarillo">{{ $fmt(round($periodo->promedio)) }}</div>
      </div>
      <div class="card">
        <div class="metric-label">Ventas anuladas</div>
        <div class="metric-value rojo">{{ (int) $periodo->anuladas }}</div>
      </div>
    </div>

    {{-- Gráfico de ventas + Top productos --}}
    <div class="grid grid-2" style="margin-top:20px;">
      <div class="card">
        <h2 class="h2">Ventas por día</h2>
        <canvas id="chartVentas" height="120"></canvas>
      </div>

      <div class="card">
        <h2 class="h2">Productos más vendidos</h2>
        @if ($top->isEmpty())
          <p class="subtitle">Sin datos aún en este período.</p>
        @else
          <table>
            <thead>
              <tr><th>#</th><th>Producto</th><th style="text-align:right">Uds</th><th style="text-align:right">Monto</th></tr>
            </thead>
            <tbody>
              @foreach ($top as $i => $p)
                <tr>
                  <td style="color:var(--subtexto);width:24px;">{{ $i + 1 }}</td>
                  <td>{{ $p->nombre_producto }}</td>
                  <td style="text-align:right">{{ (int) $p->cantidad }}</td>
                  <td style="text-align:right;color:var(--acento);font-weight:600;">{{ $fmt($p->monto) }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        @endif
      </div>
    </div>

    {{-- Stock bajo --}}
    @if ($stockBajo->isNotEmpty())
      <div class="section-label" style="color:var(--amarillo);">⚠ Stock bajo</div>
      <div class="card">
        <table>
          <thead>
            <tr><th>Producto</th><th style="text-align:right">Stock</th><th style="text-align:right">Mínimo</th></tr>
          </thead>
          <tbody>
            @foreach ($stockBajo as $p)
              <tr>
                <td>{{ $p->nombre }}</td>
                <td style="text-align:right;color:var(--amarillo);font-weight:700;">{{ $p->stock }}</td>
                <td style="text-align:right;color:var(--subtexto);">{{ $p->stock_minimo }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif

    {{-- Últimas ventas --}}
    <div class="section-label">Últimas ventas</div>
    <div class="card">
      @if ($ultimas->isEmpty())
        <p class="subtitle">Sin ventas registradas todavía. Conecta una app StockVoz para empezar a sincronizar.</p>
      @else
        <table>
          <thead>
            <tr>
              <th>Venta</th><th>Fecha</th><th>Items</th>
              <th>Método</th><th>Estado</th><th style="text-align:right">Total</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($ultimas as $v)
              <tr>
                <td>#{{ $v->id }}</td>
                <td style="color:var(--subtexto);">{{ $v->vendido_en?->format('d M H:i') }}</td>
                <td>{{ $v->detalle->count() }}</td>
                <td><span class="badge badge-azul">{{ $v->metodo_pago }}</span></td>
                <td>
                  @if ($v->estado === 'anulada')
                    <span class="badge badge-roja">Anulada</span>
                  @else
                    <span class="badge badge-azul" style="background:#0a2a14;color:var(--verde);">Completada</span>
                  @endif
                </td>
                <td style="text-align:right;font-weight:700;">{{ $fmt($v->total) }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      @endif
    </div>
  </main>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
  const labels = @json($serie->pluck('fecha'));
  const data   = @json($serie->pluck('monto')->map(fn ($c) => $c / 100));

  const ctx = document.getElementById('chartVentas');
  if (ctx && labels.length) {
    new Chart(ctx, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
          label: 'Ingresos (C$)',
          data: data,
          borderColor: '#38bdf8',
          backgroundColor: 'rgba(56, 189, 248, 0.18)',
          tension: 0.35,
          fill: true,
          pointRadius: 4,
          pointBackgroundColor: '#38bdf8',
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { labels: { color: '#94a3b8' } },
          tooltip: {
            backgroundColor: '#1e293b',
            borderColor: '#334155',
            borderWidth: 1,
          }
        },
        scales: {
          x: { ticks: { color: '#94a3b8' }, grid: { color: '#334155' } },
          y: { ticks: { color: '#94a3b8' }, grid: { color: '#334155' } }
        }
      }
    });
  } else if (ctx) {
    ctx.parentNode.innerHTML += '<p class="subtitle" style="margin-top:8px;">Sin datos en este período.</p>';
  }
</script>
@endpush
