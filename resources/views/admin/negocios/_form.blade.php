<div class="row g-4">

    <div class="col-md-6">

        <label
            for="cliente_id"
            class="form-label"
        >
            Cliente *
        </label>

        <select
            id="cliente_id"
            name="cliente_id"
            class="form-select
                @error('cliente_id') is-invalid @enderror"
            required
        >

            <option value="">
                Selecciona un cliente
            </option>

            @foreach($clientes as $cliente)

                <option
                    value="{{ $cliente->id }}"
                    @selected(
                        old(
                            'cliente_id',
                            $negocio->cliente_id ?? null
                        ) == $cliente->id
                    )
                >
                    {{ $cliente->nombre }}
                </option>

            @endforeach

        </select>

        @error('cliente_id')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror

    </div>


    <div class="col-md-6">

        <label
            for="nombre"
            class="form-label"
        >
            Nombre del negocio *
        </label>

        <input
            type="text"
            id="nombre"
            name="nombre"
            value="{{ old(
                'nombre',
                $negocio->nombre ?? ''
            ) }}"
            class="form-control
                @error('nombre') is-invalid @enderror"
            placeholder="Ej: Almacén Juanito"
            required
        >

        @error('nombre')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror

    </div>


    <div class="col-md-6">

        <label
            for="slug"
            class="form-label"
        >
            Identificador URL
        </label>

        <input
            type="text"
            id="slug"
            name="slug"
            value="{{ old(
                'slug',
                $negocio->slug ?? ''
            ) }}"
            class="form-control
                @error('slug') is-invalid @enderror"
            placeholder="Se genera automáticamente"
        >

        <div class="form-text">
            Ejemplo:
            <strong>almacen-juanito</strong>
        </div>

        @error('slug')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror

    </div>


    <div class="col-md-6">

        <label
            for="subdominio"
            class="form-label"
        >
            Subdominio
        </label>

        <div class="input-group">

            <input
                type="text"
                id="subdominio"
                name="subdominio"
                value="{{ old(
                    'subdominio',
                    $negocio->subdominio ?? ''
                ) }}"
                class="form-control
                    @error('subdominio') is-invalid @enderror"
                placeholder="juanito"
            >

            <span class="input-group-text">
                .acsoft.cl
            </span>

        </div>

        <div class="form-text">
            Lo utilizaremos más adelante.
        </div>

        @error('subdominio')
            <div class="invalid-feedback d-block">
                {{ $message }}
            </div>
        @enderror

    </div>


    @isset($negocio)

        <div class="col-12">

            <div class="form-check">

                <input
                    class="form-check-input"
                    type="checkbox"
                    name="activo"
                    value="1"
                    id="activo"
                    @checked(
                        old(
                            'activo',
                            $negocio->activo
                        )
                    )
                >

                <label
                    class="form-check-label"
                    for="activo"
                >
                    Negocio activo
                </label>

            </div>

        </div>

    @endisset

</div>


<hr class="my-5">


<div>

    <span class="section-eyebrow">
        Módulos
    </span>

    <h2 class="h4 mt-2">
        Funciones habilitadas
    </h2>

    <p class="text-muted mb-4">
        Selecciona únicamente los módulos
        que utilizará este negocio.
    </p>


    @php

        $modulosSeleccionados = old(
            'modulos',
            isset($negocio)
                ? $negocio
                    ->modulos
                    ->where('pivot.activo', true)
                    ->pluck('id')
                    ->all()
                : []
        );

    @endphp


    @forelse($modulos as $categoria => $items)

        <div class="admin-module-category mb-4">

            <h3 class="admin-module-category-title">
                {{ $categoria ?: 'Otros' }}
            </h3>


            <div class="admin-module-grid">

                @foreach($items as $modulo)

                    <label class="admin-module-option">

                        <input
                            type="checkbox"
                            name="modulos[]"
                            value="{{ $modulo->id }}"
                            @checked(
                                in_array(
                                    $modulo->id,
                                    $modulosSeleccionados
                                )
                            )
                        >

                        <span class="admin-module-check">
                            ✓
                        </span>

                        <span class="admin-module-content">

                            <strong>
                                {{ $modulo->nombre }}
                            </strong>

                            @if($modulo->descripcion)

                                <small>
                                    {{ $modulo->descripcion }}
                                </small>

                            @endif

                        </span>

                    </label>

                @endforeach

            </div>

        </div>

    @empty

        <div class="alert alert-warning">
            No hay módulos disponibles.
        </div>

    @endforelse

</div>