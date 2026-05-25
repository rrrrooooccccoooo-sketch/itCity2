<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ITCity | Central</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-5">
                    <h1 class="display-6 mb-3">ITCity</h1>
                    <p class="lead mb-4">Portal central SaaS para administrar tenants, campus y nodos de infraestructura.</p>

                    <div class="d-flex gap-2">
                        @auth
                            <a href="{{ route('admin.tenants.index') }}" class="btn btn-primary">Administrar tenants</a>
                            <a href="{{ route('home') }}" class="btn btn-outline-secondary">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-primary">Iniciar sesión</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="btn btn-outline-secondary">Registrarse</a>
                            @endif
                        @endauth
                    </div>

                    <hr class="my-4">
                    <p class="text-muted mb-0">Los clientes accederán por su dominio tenant (ejemplo: acme.localhost).</p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
