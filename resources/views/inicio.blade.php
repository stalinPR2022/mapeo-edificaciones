<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Inicio - Mapeo de Edificaciones</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>
<body class="bg-light">

    <nav class="navbar navbar-dark bg-primary">
        <div class="container">
            <span class="navbar-brand">
                Mapeo de Edificaciones
            </span>

            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button class="btn btn-outline-light btn-sm" type="submit">
                    Cerrar sesión
                </button>
            </form>
        </div>
    </nav>

    <main class="container mt-5">

        <div class="card">
            <div class="card-body">

                <h3>Bienvenido, {{ auth()->user()->nombre }}</h3>

                <p>
                    Rol:
                    <strong>
                        {{ ucfirst(auth()->user()->rol) }}
                    </strong>
                </p>

                @if(auth()->user()->rol === 'inspector')
                    <div class="alert alert-info">
                        Panel de inspector.

                        Próximamente: registro de edificaciones
                        y actualización de inspecciones.
                    </div>
                @elseif(auth()->user()->rol === 'supervisor')
                    <div class="alert alert-info">
                        Panel de supervisor.

                        Próximamente: mapa de supervisión
                        con actualización automática cada 5 segundos.
                    </div>
                @endif

            </div>
        </div>

    </main>

</body>
</html>
