<!DOCTYPE html>
<html lang="es">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['resources/js/app.js'])
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Supervisión - Mapeo de Edificaciones</title>

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
            height: 440px;
            width: 100%;
            border-radius: 8px;
        }

        .fila-edificacion {
            cursor: pointer;
        }

        .marcador-estado {
            width: 20px;
            height: 20px;
            border: 3px solid white;
            border-radius: 50%;
            box-shadow: 0 0 3px #444;
        }

        .color-pendiente {
            background-color: #ffc107;
        }

        .color-proceso {
            background-color: #0d6efd;
        }

        .color-completada {
            background-color: #198754;
        }

        .leyenda {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 5px;
        }
    </style>
</head>

<body class="bg-light">

    <nav class="navbar navbar-dark bg-primary">
        <div class="container">
            <span class="navbar-brand">
                Mapeo de Edificaciones
            </span>

            <div class="d-flex align-items-center gap-3">
                <span class="text-white">
                    {{ Auth::user()->nombre }} - Supervisor
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
                    align-items-center mb-3 flex-wrap gap-2">

            <h3>Vista de supervisión</h3>

            <a
                href="{{ route('inicio') }}"
                class="btn btn-outline-secondary btn-sm"
            >
                Volver al inicio
            </a>
        </div>

        <!-- Indicador de actualización -->
        <div class="alert alert-info">
            <strong>Actualización en tiempo real mediante WebSockets</strong>

            <div class="small mt-1">
                Estado:
                <span id="estado-conexion">
                    Conectando...
                </span>
            </div>

            <div class="small">
                Última consulta exitosa:
                <span id="ultima-consulta">
                    Pendiente
                </span>
            </div>
        </div>

        <!-- Mapa -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">

                <h5>Mapa de edificaciones</h5>

                <p class="text-muted">
                    Seleccione un marcador para consultar
                    la información de una edificación.
                </p>

                <div id="mapa"></div>

                <div class="d-flex gap-3 flex-wrap mt-3">
                    <span>
                        <i class="leyenda color-pendiente"></i>
                        Pendiente
                    </span>

                    <span>
                        <i class="leyenda color-proceso"></i>
                        En proceso
                    </span>

                    <span>
                        <i class="leyenda color-completada"></i>
                        Completada
                    </span>
                </div>

            </div>
        </div>

        <!-- Listado -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">

                <h5>Edificaciones registradas</h5>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Tipo</th>
                                <th>Estado</th>
                                <th>Acción</th>
                            </tr>
                        </thead>

                        <tbody id="tabla-edificaciones">
                            <tr>
                                <td colspan="4">
                                    Cargando edificaciones...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

        <!-- Detalles -->
        <div class="card shadow-sm">
            <div class="card-body">

                <h5>Detalle de edificación</h5>

                <p id="mensaje-seleccion" class="text-muted">
                    Seleccione una edificación del mapa
                    o del listado.
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
                        <strong>Estado:</strong>
                        <span id="detalle-estado"></span>
                    </p>

                    <p>
                        <strong>Observación:</strong>
                        <span id="detalle-observacion"></span>
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
                        <strong>Último cambio de estado:</strong>
                        <span id="detalle-fecha"></span>
                    </p>
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
        // URL de consulta generada desde Laravel.
        const urlDatos = {{ Illuminate\Support\Js::from(
            route('supervision.datos')
        ) }};

        // Mapa inicial.
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

        // Información y marcadores existentes.
        let edificaciones = [];
        let seleccionadaId = null;
        let primeraCarga = true;
        let consultando = false;
        let actualizacionPendiente = false;

        const marcadores = new Map();

        const colores = {
            'Pendiente': 'color-pendiente',
            'En proceso': 'color-proceso',
            'Completada': 'color-completada'
        };

        // Mostrar valores sin interpretar HTML.
        function asignarTexto(id, valor) {
            document.getElementById(id).textContent =
                valor ?? 'No especificado';
        }

        // Buscar registro por identificador.
        function buscarEdificacion(id) {
            return edificaciones.find(
                e => Number(e.id_edificacion) === Number(id)
            );
        }

        // Crear un icono cuyo color depende del estado.
        function crearIcono(estado) {
            const clase = colores[estado] || 'color-pendiente';

            return L.divIcon({
                className: '',
                html: '<div class="marcador-estado ' +
                      clase + '"></div>',
                iconSize: [20, 20],
                iconAnchor: [10, 10]
            });
        }

        function mostrarDetalle(edificacion) {
            seleccionadaId = Number(edificacion.id_edificacion);

            asignarTexto('detalle-nombre', edificacion.nombre);
            asignarTexto('detalle-tipo', edificacion.tipo);
            asignarTexto('detalle-direccion', edificacion.direccion);
            asignarTexto('detalle-descripcion', edificacion.descripcion);
            asignarTexto('detalle-estado', edificacion.estado_inspeccion);
            asignarTexto('detalle-observacion', edificacion.observacion);
            asignarTexto('detalle-latitud', edificacion.latitud);
            asignarTexto('detalle-longitud', edificacion.longitud);

            asignarTexto(
                'detalle-fecha',
                edificacion.fecha_actualizacion_estado
            );

            document.getElementById('mensaje-seleccion')
                .classList.add('d-none');

            document.getElementById('detalle')
                .classList.remove('d-none');
        }

        // Seleccionar desde el listado o marcador.
        function seleccionarEdificacion(id, centrar = true) {
            const edificacion = buscarEdificacion(id);

            if (!edificacion) {
                return;
            }

            mostrarDetalle(edificacion);

            const marcador = marcadores.get(
                Number(edificacion.id_edificacion)
            );

            if (centrar && marcador) {
                mapa.setView(marcador.getLatLng(), 16);
                marcador.openPopup();
            }
        }

        // Crear contenido seguro para el popup.
        function crearPopup(edificacion) {
            const contenedor = document.createElement('div');

            const nombre = document.createElement('strong');
            nombre.textContent = edificacion.nombre;

            const estado = document.createElement('p');
            estado.textContent =
                'Estado: ' + edificacion.estado_inspeccion;

            contenedor.appendChild(nombre);
            contenedor.appendChild(estado);

            return contenedor;
        }

        // Actualizar los marcadores sin reconstruir el mapa.
        function actualizarMapa() {
            const idsActuales = new Set();
            const limites = [];

            edificaciones.forEach(edificacion => {
                const id = Number(edificacion.id_edificacion);
                const lat = Number(edificacion.latitud);
                const lng = Number(edificacion.longitud);

                if (
                    !Number.isFinite(lat) ||
                    !Number.isFinite(lng) ||
                    lat < -90 || lat > 90 ||
                    lng < -180 || lng > 180
                ) {
                    return;
                }

                idsActuales.add(id);
                limites.push([lat, lng]);

                const existente = marcadores.get(id);

                if (existente) {
                    // Actualizar posición, color y contenido.
                    existente.setLatLng([lat, lng]);
                    existente.setIcon(
                        crearIcono(edificacion.estado_inspeccion)
                    );

                    existente.setPopupContent(
                        crearPopup(edificacion)
                    );
                } else {
                    // Crear un marcador nuevo.
                    const marcador = L.marker([lat, lng], {
                        icon: crearIcono(
                            edificacion.estado_inspeccion
                        )
                    }).addTo(mapa);

                    marcador.bindPopup(
                        crearPopup(edificacion)
                    );

                    marcador.on('click', () => {
                        seleccionarEdificacion(id, false);
                    });

                    marcadores.set(id, marcador);
                }
            });

            // Eliminar marcadores que ya no existan.
            for (const [id, marcador] of marcadores) {
                if (!idsActuales.has(id)) {
                    mapa.removeLayer(marcador);
                    marcadores.delete(id);
                }
            }

            // Ajustar el zoom solo durante la primera carga.
            if (primeraCarga && limites.length > 0) {
                mapa.fitBounds(limites, {
                    padding: [40, 40],
                    maxZoom: 16
                });
            }
        }

        // Actualizar la lista de edificaciones.
        function actualizarTabla() {
            const cuerpo = document.getElementById(
                'tabla-edificaciones'
            );

            cuerpo.replaceChildren();

            if (edificaciones.length === 0) {
                const fila = document.createElement('tr');
                const celda = document.createElement('td');

                celda.colSpan = 4;
                celda.textContent =
                    'No existen edificaciones registradas.';

                fila.appendChild(celda);
                cuerpo.appendChild(fila);
                return;
            }

            edificaciones.forEach(edificacion => {
                const fila = document.createElement('tr');
                fila.className = 'fila-edificacion';

                const valores = [
                    edificacion.nombre,
                    edificacion.tipo,
                    edificacion.estado_inspeccion
                ];

                valores.forEach(valor => {
                    const celda = document.createElement('td');
                    celda.textContent = valor ?? '';
                    fila.appendChild(celda);
                });

                const celdaAccion = document.createElement('td');
                const boton = document.createElement('button');

                boton.type = 'button';
                boton.className = 'btn btn-primary btn-sm';
                boton.textContent = 'Ver';

                boton.addEventListener('click', () => {
                    seleccionarEdificacion(
                        edificacion.id_edificacion
                    );
                });

                celdaAccion.appendChild(boton);
                fila.appendChild(celdaAccion);
                cuerpo.appendChild(fila);
            });
        }

        // Obtener datos actualizados desde Laravel.
        async function actualizarDatos() {
            
            if (consultando) {
                return;
            }

            consultando = true;
            
            
            try {
               // asignarTexto(
                    //'estado-conexion',
                  //  'Consultando servidor...'
                //);

                const respuesta = await fetch(urlDatos, {
                    method: 'GET',
                    credentials: 'same-origin',
                    cache: 'no-store',
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                if (!respuesta.ok) {
                    throw new Error(
                        'Error HTTP ' + respuesta.status
                    );
                }

                const datos = await respuesta.json();

                if (!Array.isArray(datos.edificaciones)) {
                    throw new Error(
                        'Formato de respuesta incorrecto'
                    );
                }

                edificaciones = datos.edificaciones;

                actualizarMapa();
                actualizarTabla();

                // Mantener la edificación seleccionada.
                if (seleccionadaId !== null) {
                    const seleccionada = buscarEdificacion(
                        seleccionadaId
                    );

                    if (seleccionada) {
                        mostrarDetalle(seleccionada);
                    } else {
                        seleccionadaId = null;

                        document.getElementById('detalle')
                            .classList.add('d-none');

                        document.getElementById('mensaje-seleccion')
                            .classList.remove('d-none');
                    }
                }

                //asignarTexto('estado-conexion', 'WebSocket conectado');

                asignarTexto(
                    'ultima-consulta',
                    new Date().toLocaleTimeString('es-SV')
                );

                primeraCarga = false;

            } catch (error) {
                console.error(
                    'Error al actualizar supervisión:',
                    error
                );

               // asignarTexto(
                 //   'estado-conexion',
                   // 'Error de conexión. Se intentará nuevamente.'
                //);
            } finally {
                consultando = false;

                // Si llegó otro evento mientras consultábamos,
                // procesar una nueva actualización.
                if (actualizacionPendiente) {
                    actualizacionPendiente = false;
                    actualizarDatos();
                }
            }
        }
        //Aqui
        function solicitarActualizacion() {

            // Si existe una consulta en curso,
            // dejar marcada una actualización pendiente.
            if (consultando) {
                actualizacionPendiente = true;
                return;
            }

            // Consultar inmediatamente los datos actualizados.
            actualizarDatos();
        }

        //Aqui
        window.addEventListener('supervision:actualizar', (evento) => {

            console.log(
                'Actualizando supervisión por evento WebSocket.',
                evento.detail ?? null
            );

            solicitarActualizacion();
        });



        
       
        // Primera consulta al abrir la pantalla.
        actualizarDatos();
    </script>

</body>
</html>
