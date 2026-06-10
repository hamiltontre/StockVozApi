<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'StockVoz') · Dashboard</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    /* ─── Variables del tema (espejo de la app móvil) ───────────────────── */
    :root {
      --fondo: #0f172a;
      --tarjeta: #1e293b;
      --borde: #334155;
      --texto: #f1f5f9;
      --subtexto: #94a3b8;
      --acento: #38bdf8;
      --acento-suave: #0c2233;
      --verde: #4ade80;
      --rojo: #f87171;
      --amarillo: #fbbf24;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    html, body { background: var(--fondo); color: var(--texto); font-family: 'Inter', system-ui, sans-serif; min-height: 100vh; }
    a { color: var(--acento); text-decoration: none; }
    button { font-family: inherit; cursor: pointer; }

    /* ─── Layout ───────────────────────────────────────────────────────── */
    .app { display: flex; min-height: 100vh; }
    .sidebar {
      width: 240px; background: var(--tarjeta);
      border-right: 1px solid var(--borde); padding: 24px 16px;
      display: flex; flex-direction: column;
    }
    .logo { display: flex; align-items: center; gap: 10px; margin-bottom: 32px; }
    .logo-icon {
      width: 40px; height: 40px; border-radius: 12px;
      background: var(--acento-suave); border: 2px solid var(--acento);
      display: flex; align-items: center; justify-content: center;
      font-weight: 800; color: var(--acento); font-size: 18px;
    }
    .logo-text { font-size: 18px; font-weight: 800; letter-spacing: -0.5px; }

    .nav-item {
      display: flex; align-items: center; gap: 10px;
      padding: 10px 12px; border-radius: 10px; color: var(--subtexto);
      font-size: 14px; font-weight: 500; margin-bottom: 4px;
    }
    .nav-item.active, .nav-item:hover { background: var(--acento-suave); color: var(--acento); }
    .nav-icon { width: 18px; height: 18px; display: inline-block; }

    .user-panel { margin-top: auto; padding-top: 16px; border-top: 1px solid var(--borde); }
    .user-name { font-size: 13px; font-weight: 700; }
    .user-rol  { font-size: 11px; color: var(--subtexto); }
    .logout-btn {
      width: 100%; margin-top: 12px;
      background: transparent; color: var(--rojo);
      border: 1px solid #5a1010; border-radius: 10px;
      padding: 8px 12px; font-size: 13px; font-weight: 600;
    }
    .logout-btn:hover { background: #3a0808; }

    .main { flex: 1; padding: 24px 32px; max-width: 1400px; }

    /* ─── Cards ────────────────────────────────────────────────────────── */
    .h1 { font-size: 26px; font-weight: 800; margin-bottom: 6px; }
    .h2 { font-size: 18px; font-weight: 700; margin-bottom: 12px; }
    .subtitle { font-size: 13px; color: var(--subtexto); }
    .section-label {
      font-size: 11px; font-weight: 700; color: var(--subtexto);
      text-transform: uppercase; letter-spacing: 1.2px; margin: 24px 0 10px;
    }

    .card {
      background: var(--tarjeta); border: 1px solid var(--borde);
      border-radius: 14px; padding: 18px;
    }
    .grid { display: grid; gap: 14px; }
    .grid-4 { grid-template-columns: repeat(4, 1fr); }
    .grid-2 { grid-template-columns: repeat(2, 1fr); }
    @media (max-width: 900px) {
      .grid-4 { grid-template-columns: repeat(2, 1fr); }
      .grid-2 { grid-template-columns: 1fr; }
    }

    .metric-label { font-size: 12px; color: var(--subtexto); font-weight: 600; }
    .metric-value { font-size: 22px; font-weight: 800; margin-top: 6px; }
    .metric-value.azul { color: var(--acento); }
    .metric-value.verde { color: var(--verde); }
    .metric-value.amarillo { color: var(--amarillo); font-size: 18px; }
    .metric-value.rojo { color: var(--rojo); }

    /* ─── Tablas ───────────────────────────────────────────────────────── */
    table { width: 100%; border-collapse: collapse; font-size: 13px; }
    th { text-align: left; padding: 10px 12px; color: var(--subtexto);
         font-size: 11px; font-weight: 700; letter-spacing: 0.6px;
         text-transform: uppercase; border-bottom: 1px solid var(--borde); }
    td { padding: 12px; border-bottom: 1px solid var(--borde); }
    tr:last-child td { border-bottom: none; }
    .badge {
      display: inline-block; padding: 3px 8px; border-radius: 6px;
      font-size: 11px; font-weight: 700; text-transform: capitalize;
    }
    .badge-azul { background: var(--acento-suave); color: var(--acento); }
    .badge-roja { background: #3a0808; color: var(--rojo); }
    .badge-amarilla { background: #3a1a00; color: var(--amarillo); }

    /* ─── Selector de período ──────────────────────────────────────────── */
    .periodo-bar { display: flex; gap: 8px; align-items: center; margin-bottom: 18px; }
    .periodo-btn {
      padding: 6px 14px; border-radius: 20px;
      border: 1px solid var(--borde); background: transparent;
      color: var(--subtexto); font-weight: 600; font-size: 13px;
    }
    .periodo-btn.active { background: var(--acento); color: var(--fondo); border-color: var(--acento); }

    /* ─── Login ────────────────────────────────────────────────────────── */
    .login-wrap {
      min-height: 100vh; display: flex;
      align-items: center; justify-content: center; padding: 24px;
    }
    .login-card { width: 100%; max-width: 380px; }
    .login-logo { text-align: center; margin-bottom: 24px; }
    .login-logo .logo-icon { margin: 0 auto 12px; width: 56px; height: 56px; font-size: 24px; }
    .form-group { margin-bottom: 14px; }
    .form-label {
      display: block; font-size: 11px; font-weight: 700;
      text-transform: uppercase; letter-spacing: 0.6px;
      color: var(--subtexto); margin-bottom: 6px;
    }
    .form-input {
      width: 100%; background: var(--fondo); color: var(--texto);
      border: 1px solid var(--borde); border-radius: 10px;
      padding: 11px 14px; font-size: 15px; font-family: inherit;
    }
    .form-input:focus { outline: none; border-color: var(--acento); }
    .btn-primary {
      width: 100%; padding: 13px; border-radius: 12px;
      background: var(--acento); color: var(--fondo);
      border: none; font-weight: 700; font-size: 15px; margin-top: 8px;
    }
    .alert-error {
      background: #3a0808; border: 1px solid #5a1010;
      color: var(--rojo); padding: 10px 12px; border-radius: 10px;
      font-size: 13px; margin-bottom: 14px;
    }
  </style>
  @stack('head')
</head>
<body>
  @yield('body')
  @stack('scripts')
</body>
</html>
