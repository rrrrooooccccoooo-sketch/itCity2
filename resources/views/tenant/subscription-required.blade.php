<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Suscripción requerida</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card shadow-sm border-0">
                <div class="card-body p-5 text-center">
                    <h2 class="mb-3">Suscripción requerida</h2>
                    <p class="text-muted mb-4">El tenant <strong>{{ $tenantName }}</strong> no tiene una suscripción activa.</p>
                    <p class="mb-0">Solicita al administrador central de ITCity activar el plan para habilitar este portal.</p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
