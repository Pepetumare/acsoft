<div class="row g-4">

    <div class="col-md-6">

        <label
            for="name"
            class="form-label"
        >
            Nombre *
        </label>

        <input
            type="text"
            id="name"
            name="name"
            value="{{ old(
                'name',
                $usuario->name ?? ''
            ) }}"
            class="form-control
                @error('name') is-invalid @enderror"
            required
        >

        @error('name')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror

    </div>


    <div class="col-md-6">

        <label
            for="email"
            class="form-label"
        >
            Correo *
        </label>

        <input
            type="email"
            id="email"
            name="email"
            value="{{ old(
                'email',
                $usuario->email ?? ''
            ) }}"
            class="form-control
                @error('email') is-invalid @enderror"
            required
        >

        @error('email')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror

    </div>


    <div class="col-md-6">

        <label
            for="password"
            class="form-label"
        >
            Contraseña
            @empty($usuario)
                *
            @endempty
        </label>

        <input
            type="password"
            id="password"
            name="password"
            class="form-control
                @error('password') is-invalid @enderror"
            @empty($usuario)
                required
            @endempty
        >

        @isset($usuario)
            <div class="form-text">
                Déjala vacía para mantener la contraseña actual.
            </div>
        @endisset

        @error('password')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror

    </div>


    <div class="col-md-6">

        <label
            for="password_confirmation"
            class="form-label"
        >
            Confirmar contraseña
        </label>

        <input
            type="password"
            id="password_confirmation"
            name="password_confirmation"
            class="form-control"
        >

    </div>


    <div class="col-md-6">

        <label
            for="rol"
            class="form-label"
        >
            Rol *
        </label>

        <select
            id="rol"
            name="rol"
            class="form-select
                @error('rol') is-invalid @enderror"
            required
        >

            <option
                value="admin"
                @selected(
                    old(
                        'rol',
                        isset($usuario)
                            ? optional(
                                $usuario->negocios->first()
                            )->pivot?->rol
                            : 'admin'
                    ) === 'admin'
                )
            >
                Administrador del negocio
            </option>

            <option
                value="usuario"
                @selected(
                    old(
                        'rol',
                        isset($usuario)
                            ? optional(
                                $usuario->negocios->first()
                            )->pivot?->rol
                            : ''
                    ) === 'usuario'
                )
            >
                Usuario
            </option>

        </select>

        @error('rol')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror

    </div>

</div>


<hr class="my-5">


<div>

    <span class="section-eyebrow">
        Acceso
    </span>

    <h2 class="h4 mt-2">
        Negocios disponibles
    </h2>

    <p class="text-muted mb-4">
        Selecciona los negocios a los que este usuario puede acceder.
    </p>


    @php

        $negociosSeleccionados = old(
            'negocios',
            isset($usuario)
                ? $usuario
                    ->negocios
                    ->pluck('id')
                    ->all()
                : []
        );

    @endphp


    <div class="admin-module-grid">

        @forelse($negocios as $negocio)

            <label class="admin-module-option">

                <input
                    type="checkbox"
                    name="negocios[]"
                    value="{{ $negocio->id }}"
                    @checked(
                        in_array(
                            $negocio->id,
                            $negociosSeleccionados
                        )
                    )
                >

                <span class="admin-module-check">
                    ✓
                </span>

                <span class="admin-module-content">

                    <strong>
                        {{ $negocio->nombre }}
                    </strong>

                    <small>
                        {{ $negocio->cliente->nombre ?? '' }}
                    </small>

                </span>

            </label>

        @empty

            <div class="alert alert-warning">
                Primero debes crear un negocio.
            </div>

        @endforelse

    </div>

</div>