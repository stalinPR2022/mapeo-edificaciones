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
                        Desde aquí puede registrar nuevas edificaciones
                    </div>
                    <a
                        href="{{ route('edificaciones.create') }}"
                        class="btn btn-primary"
                    >
                    Registrar edificación
                    </a>
                    <a
                        href="{{ route('edificaciones.index') }}"
                        class="btn btn-outline-primary"
                    >
                        Consultar edificaciones
                    </a>
                @elseif(auth()->user()->rol === 'supervisor')
                    <div class="alert alert-info">
                        Panel de supervisor.

                        
                        <div class="alert alert-info">
                            Panel de supervisor.
                            Desde aquí puede consultar el mapa de edificaciones
                            y visualizar los estados de inspección actualizados.
                        </div>

                        <a
                            href="{{ route('supervision.index') }}"
                            class="btn btn-primary"
                        >
                            Abrir vista de supervisión
                        </a>

                    </div>
                @endif

            </div>
        </div>

    </main>

</body>
</html>
