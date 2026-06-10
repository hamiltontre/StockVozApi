@extends('layouts.app')

@section('title', 'Iniciar sesión')

@section('body')
<div class="login-wrap">
  <div class="login-card">
    <div class="login-logo">
      <div class="logo-icon">SV</div>
      <h1 class="h1">StockVoz</h1>
      <p class="subtitle">Dashboard de administrador</p>
    </div>

    <div class="card">
      @if ($errors->any())
        <div class="alert-error">
          {{ $errors->first() }}
        </div>
      @endif

      <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="form-group">
          <label class="form-label" for="email">Correo electrónico</label>
          <input id="email" name="email" type="email" class="form-input"
                 value="{{ old('email') }}" required autofocus
                 placeholder="tu@negocio.com">
        </div>

        <div class="form-group">
          <label class="form-label" for="password">Contraseña</label>
          <input id="password" name="password" type="password" class="form-input"
                 required placeholder="••••••••">
        </div>

        <label style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--subtexto);margin:8px 0;">
          <input type="checkbox" name="remember" value="1">
          Recordarme en este navegador
        </label>

        <button type="submit" class="btn-primary">Entrar</button>
      </form>
    </div>

    <p style="text-align:center;margin-top:18px;font-size:12px;color:var(--subtexto);">
      © {{ date('Y') }} StockVoz · Nicaragua
    </p>
  </div>
</div>
@endsection
