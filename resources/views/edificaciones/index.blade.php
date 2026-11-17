<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Consultar edificaciones - Mapeo de Edificaciones</title>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Leaflet -->
    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
        crossorigin=""
    >

    <style>
        #mapa {
            height: 400px;
            width: 100%;
            border-radius: 8px;
        }

        .fila-edificacion {
            cursor: pointer;
        }

        .fila-edificacion:hover {
            background-color: #f0f4f8;
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
                    {{ Auth::user()->nombre }}
                    - {{ ucfirst(Auth::user()->rol) }}
                </span>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button
                        type="submit"
                        class="btn btn-outline-light btn-sm"
                    >
                        Cerrar sesión
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <main class="container my-4">

        <div class="d-flex justify-content-between
                    align-items-center mb-3">

            <h3>Consultar edificaciones</h3>
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <strong>No se pudieron guardar los cambios.</strong>

                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <a
                href="{{ route('inicio') }}"
                class="btn btn-outline-secondary btn-sm"
            >
                Volver al inicio
            </a>
        </div>

        

        <!-- Lista de edificaciones -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">

                <h5>Edificaciones registradas</h5>

                <p class="text-muted">
                    Seleccione una edificación para visualizar
                    su información y ubicación geográfica.
                </p>

                @if($edificaciones->isEmpty())

                    <div class="alert alert-info">
                        No existen edificaciones registradas.
                    </div>

                @else

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Tipo</th>
                                    <th>Estado</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($edificaciones as $edificacion)
                                    <tr class="fila-edificacion">
                                        <td>
                                            {{ $edificacion->id_edificacion }}
                                        </td>
                                        <td>
                                            {{ $edificacion->nombre }}
                                        </td>
                                        <td>
                                            {{ $edificacion->tipo }}
                                        </td>
                                        <td>
                                            {{ $edificacion->estado_inspeccion }}
                                        </td>
                                        <td>
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-primary btn-ver"
                                                data-id="{{ $edificacion->id_edificacion }}"
                                            >
                                                Ver
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                @endif
            </div>
        </div>

        <!-- Mapa -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">

                <h5>Ubicación geográfica</h5>

                <p class="text-muted">
                    Los marcadores representan las edificaciones
                    registradas en el sistema.
                </p>

                <div id="mapa"></div>
            </div>
        </div>

        <!-- Detalles -->
        <div class="card shadow-sm">
            <div class="card-body">

                <h5>Detalle de edificación</h5>

                <p id="mensaje-seleccion" class="text-muted">
                    Seleccione una edificación de la lista
                    o haga clic en un marcador del mapa.
                </p>

                <div id="detalle" class="d-none">

                    <p>
                        <strong>Nombre:</strong>
                        <span id="detalle-nombre"></span>
                    </p>

                    <p>
                        <strong>Tipo:</strong>
                        <span id="detalle-tipo"></span>
                    </p>

                    <p>
                        <strong>Dirección:</strong>
                        <span id="detalle-direccion"></span>
                    </p>

                    <p>
                        <strong>Descripción:</strong>
                        <span id="detalle-descripcion"></span>
                    </p>

                    <p>
                        <strong>Latitud:</strong>
                        <span id="detalle-latitud"></span>
                    </p>

                    <p>
                        <strong>Longitud:</strong>
                        <span id="detalle-longitud"></span>
                    </p>

                    <p>
                        <strong>Estado de inspección:</strong>
                        <span id="detalle-estado"></span>
                    </p>

                    <p>
                        <strong>Observación:</strong>
                        <span id="detalle-observacion"></span>
                    </p>

                    <p>
                        <strong>Fecha de registro:</strong>
                        <span id="detalle-fecha"></span>
                    </p>

                    <p>
                        <strong>Último cambio de estado:</strong>
                        <span id="detalle-actualizacion"></span>
                    </p>


                    @if(Auth::user()->rol === 'inspector')

                        <hr>

                        <h5>Actualizar inspección</h5>

                        <form
                            id="form-estado"
                            method="POST"
                            action=""
                        >
                            @csrf
                            @method('PATCH')

                            <div class="mb-3">
                                <label for="estado_inspeccion" class="form-label">
                                    Estado de inspección
                                </label>

                                <select
                                    id="estado_inspeccion"
                                    name="estado_inspeccion"
                                    class="form-select"
                                    required
                                >
                                    <option value="Pendiente">
                                        Pendiente
                                    </option>

                                    <option value="En proceso">
                                        En proceso
                                    </option>

                                    <option value="Completada">
                                        Completada
                                    </option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="observacion" class="form-label">
                                    Observación (opcional)
                                </label>

                                <textarea
                                    id="observacion"
                                    name="observacion"
                                    class="form-control"
                                    rows="3"
                                    placeholder="Ingrese una observación..."
                                ></textarea>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button
                                    type="submit"
                                    class="btn btn-primary"
                                >
                                    Guardar cambios
                                </button>
                            </div>
                        </form>

                    @endif
                    

                </div>
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
        // Datos de edificaciones enviados desde Laravel.
        const edificaciones = {{ Illuminate\Support\Js::from($edificaciones) }};

        //incremento 3
        const esInspector = {{ Illuminate\Support\Js::from(Auth::user()->rol === 'inspector') }};

        const rutaActualizacion = {{ Illuminate\Support\Js::from(
            route('edificaciones.estado.update', ['edificacion' => '__ID__'])
        ) }};


        // Crear mapa.
        const mapa = L.map('mapa').setView(
            [13.6929, -89.2182],
            13
        );

        L.tileLayer(
            'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
            {
                maxZoom: 19,
                attribution:
                    '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }
        ).addTo(mapa);

        // Relacionar identificadores con marcadores.
        const marcadores = new Map();
        const limites = [];

        function mostrarDetalle(edificacion) {
            const asignar = (id, valor) => {
                document.getElementById(id).textContent =
                    valor ?? 'No especificado';
            };

            asignar('detalle-nombre', edificacion.nombre);
            asignar('detalle-tipo', edificacion.tipo);
            asignar('detalle-direccion', edificacion.direccion);
            asignar('detalle-descripcion', edificacion.descripcion);

            asignar('detalle-latitud', edificacion.latitud);
            asignar('detalle-longitud', edificacion.longitud);
            asignar('detalle-estado', edificacion.estado_inspeccion);
            asignar('detalle-observacion', edificacion.observacion);

            asignar('detalle-fecha', edificacion.fecha_registro);
            asignar(
                'detalle-actualizacion',
                edificacion.fecha_actualizacion_estado
            );

            document.getElementById('mensaje-seleccion')
                .classList.add('d-none');

            document.getElementById('detalle')
                .classList.remove('d-none');

            //Nuevo incremento 3

            if (esInspector) {
                const formulario = document.getElementById('form-estado');

                formulario.action = rutaActualizacion.replace(
                    '__ID__',
                    encodeURIComponent(edificacion.id_edificacion)
                );

                document.getElementById('estado_inspeccion').value =
                    edificacion.estado_inspeccion;

                document.getElementById('observacion').value =
                    edificacion.observacion ?? '';
            }

        }

        function seleccionarEdificacion(id) {
            const edificacion = edificaciones.find(
                e => Number(e.id_edificacion) === Number(id)
            );

            if (!edificacion) {
                return;
            }

            mostrarDetalle(edificacion);

            const marcador = marcadores.get(
                Number(edificacion.id_edificacion)
            );

            if (marcador) {
                mapa.setView(marcador.getLatLng(), 16);
                marcador.openPopup();

                document.getElementById('mapa')
                    .scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
            }
        }

        // Crear marcadores con las coordenadas almacenadas.
        edificaciones.forEach(edificacion => {
            const latitud = Number(edificacion.latitud);
            const longitud = Number(edificacion.longitud);

            if (
                !Number.isFinite(latitud) ||
                !Number.isFinite(longitud) ||
                latitud < -90 || latitud > 90 ||
                longitud < -180 || longitud > 180
            ) {
                return;
            }

            const marcador = L.marker(
                [latitud, longitud]
            ).addTo(mapa);

            // Construir el popup sin insertar HTML no confiable.
            const contenidoPopup = document.createElement('div');

            const titulo = document.createElement('strong');
            titulo.textContent = edificacion.nombre;

            const estado = document.createElement('p');
            estado.textContent =
                'Estado: ' + edificacion.estado_inspeccion;

            contenidoPopup.appendChild(titulo);
            contenidoPopup.appendChild(estado);

            marcador.bindPopup(contenidoPopup);

            marcador.on('click', function() {
                mostrarDetalle(edificacion);
            });

            marcadores.set(
                Number(edificacion.id_edificacion),
                marcador
            );

            limites.push([latitud, longitud]);
        });

        // Ajustar el mapa a las ubicaciones existentes.
        if (limites.length > 0) {
            mapa.fitBounds(limites, {
                padding: [40, 40],
                maxZoom: 16
            });
        }

        // Vincular los botones "Ver".
        document.querySelectorAll('.btn-ver').forEach(boton => {
            boton.addEventListener('click', function() {
                seleccionarEdificacion(this.dataset.id);
            });
        });
        
        // Recuperar la edificación seleccionada después de guardar.
        const parametros = new URLSearchParams(window.location.search);

        const idSeleccionado = parametros.get('seleccionada');

        if (idSeleccionado) {
            seleccionarEdificacion(idSeleccionado);
        }
    </script>



</body>
</html>
