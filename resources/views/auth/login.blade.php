<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Iniciar sesión - Mapeo de Edificaciones</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>
<body class="bg-light">

    <div class="container min-vh-100 d-flex
                justify-content-center align-items-center">

        <div class="card shadow-sm" style="width: 100%; max-width: 420px;">

            <div class="card-body p-4">

                <h3 class="text-center mb-2">
                    Mapeo de Edificaciones
                </h3>

                <p class="text-center text-muted mb-4">
                    Inicio de sesión
                </p>

                <form method="POST" action="{{ route('login.procesar') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="correo" class="form-label">
                            Correo electrónico
                        </label>

                        <input
                            type="email"
                            name="correo"
                            id="correo"
                            class="form-control @error('correo') is-invalid @enderror"
                            value="{{ old('correo') }}"
                            required
                            autofocus
                        >

                        @error('correo')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">
                            Contraseña
                        </label>

                        <input
                            type="password"
                            name="password"
                            id="password"
                            class="form-control @error('password') is-invalid @enderror"
                            required
                        >

                        @error('password')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        Ingresar
                    </button>

                </form>

            </div>
        </div>

    </div>

</body>
</html>
