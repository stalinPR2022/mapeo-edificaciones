<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Registrar edificación - Mapeo de Edificaciones</title>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Leaflet CSS -->
    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
        crossorigin=""
    >

    <style>
        #mapa {
            height: 380px;
            width: 100%;
            border-radius: 8px;
        }
    </style>
</head>

<body class="bg-light">

    <!-- Encabezado -->
    <nav class="navbar navbar-dark bg-primary">
        <div class="container">
            <span class="navbar-brand">
                Mapeo de Edificaciones
            </span>

            <div class="d-flex align-items-center gap-3">
                <span class="text-white">
                    {{ Auth::user()->nombre }} - Inspector
                </span>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button
                        class="btn btn-outline-light btn-sm"
                        type="submit"
                    >
                        Cerrar sesión
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <main class="container my-4">

        <div class="mb-3">
            <a href="{{ route('inicio') }}"
               class="btn btn-outline-secondary btn-sm">
                Volver al inicio
            </a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">

                <h3>Registrar edificación</h3>

                <p class="text-muted">
                    Ingrese los datos de la edificación y seleccione
                    su ubicación haciendo clic sobre el mapa.
                </p>

                <!-- Confirmación -->
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <!-- Errores -->
                @if($errors->any())
                    <div class="alert alert-danger">
                        Verifique los datos ingresados.
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form
                    method="POST"
                    action="{{ route('edificaciones.store') }}"
                >
                    @csrf

                    <!-- Nombre -->
                    <div class="mb-3">
                        <label for="nombre" class="form-label">
                            Nombre de edificación *
                        </label>

                        <input
                            type="text"
                            id="nombre"
                            name="nombre"
                            class="form-control"
                            value="{{ old('nombre') }}"
                            maxlength="150"
                            required
                        >
                    </div>

                    <!-- Tipo -->
                    <div class="mb-3">
                        <label for="tipo" class="form-label">
                            Tipo de edificación *
                        </label>

                        <select
                            id="tipo"
                            name="tipo"
                            class="form-select"
                            required
                        >
                            <option value="">
                                Seleccione un tipo
                            </option>

                            @foreach([
                                'Residencial',
                                'Comercial',
                                'Educativa',
                                'Industrial',
                                'Otra'
                            ] as $tipo)
                                <option
                                    value="{{ $tipo }}"
                                    @selected(old('tipo') === $tipo)
                                >
                                    {{ $tipo }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Dirección -->
                    <div class="mb-3">
                        <label for="direccion" class="form-label">
                            Dirección o referencia *
                        </label>

                        <input
                            type="text"
                            id="direccion"
                            name="direccion"
                            class="form-control"
                            maxlength="255"
                            value="{{ old('direccion') }}"
                            required
                        >
                    </div>

                    <!-- Descripción -->
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">
                            Descripción (opcional)
                        </label>

                        <textarea
                            id="descripcion"
                            name="descripcion"
                            class="form-control"
                            rows="3"
                        >{{ old('descripcion') }}</textarea>
                    </div>

                    <!-- Mapa -->
                    <div class="mb-3">
                        <h5>Ubicación geográfica *</h5>

                        <p class="text-muted small">
                            Haga clic en el mapa para colocar
                            el marcador de la edificación.
                        </p>

                        <div id="mapa"></div>
                    </div>

                    <!-- Coordenadas -->
                    <div class="row mb-3">

                        <div class="col-md-6">
                            <label for="latitud" class="form-label">
                                Latitud
                            </label>

                            <input
                                type="text"
                                id="latitud"
                                name="latitud"
                                class="form-control"
                                value="{{ old('latitud') }}"
                                readonly
                                required
                            >
                        </div>

                        <div class="col-md-6">
                            <label for="longitud" class="form-label">
                                Longitud
                            </label>

                            <input
                                type="text"
                                id="longitud"
                                name="longitud"
                                class="form-control"
                                value="{{ old('longitud') }}"
                                readonly
                                required
                            >
                        </div>

                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a
                            href="{{ route('inicio') }}"
                            class="btn btn-secondary"
                        >
                            Cancelar
                        </a>

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            Guardar edificación
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </main>

    <!-- Leaflet JavaScript -->
    <script
        src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""
    ></script>

    <script>
        // Centro inicial: San Salvador (solo referencia visual).
        const mapa = L.map('mapa').setView(
            [13.6929, -89.2182],
            13
        );

        // Capa cartográfica de OpenStreetMap
        L.tileLayer(
            'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
            {
                maxZoom: 19,
                attribution:
                    '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }
        ).addTo(mapa);

        let marcador = null;

        const campoLatitud = document.getElementById('latitud');
        const campoLongitud = document.getElementById('longitud');

        // Colocar o mover marcador.
        function seleccionarUbicacion(latitud, longitud) {
            if (marcador) {
                marcador.setLatLng([latitud, longitud]);
            } else {
                marcador = L.marker(
                    [latitud, longitud]
                ).addTo(mapa);
            }

            campoLatitud.value = latitud.toFixed(7);
            campoLongitud.value = longitud.toFixed(7);
        }

        // Selección al hacer clic.
        mapa.on('click', function(evento) {
            seleccionarUbicacion(
                evento.latlng.lat,
                evento.latlng.lng
            );
        });

        // Recuperar coordenadas si hubo error de validación.
        const latitudAnterior = Number.parseFloat(
            campoLatitud.value
        );

        const longitudAnterior = Number.parseFloat(
            campoLongitud.value
        );

        if (
            Number.isFinite(latitudAnterior) &&
            Number.isFinite(longitudAnterior) &&
            latitudAnterior >= -90 &&
            latitudAnterior <= 90 &&
            longitudAnterior >= -180 &&
            longitudAnterior <= 180
        ) {
            seleccionarUbicacion(
                latitudAnterior,
                longitudAnterior
            );

            mapa.setView(
                [latitudAnterior, longitudAnterior],
                16
            );
        }
    </script>

</body>
</html>
