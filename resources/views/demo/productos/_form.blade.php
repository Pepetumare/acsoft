<div class="demo-form-group mb-4">

    <label for="nombre" class="form-label">
        Nombre del producto
        <span>*</span>
    </label>

    <input
        type="text"
        id="nombre"
        name="nombre"
        value="{{ old('nombre', $producto->nombre ?? '') }}"
        class="form-control @error('nombre') is-invalid @enderror"
        placeholder="Ej: Pechuga de pollo"
        maxlength="150"
        required
    >

    @error('nombre')
        <div class="invalid-feedback">
            {{ $message }}
        </div>
    @enderror

</div>


<div class="demo-form-group mb-4">

    <label for="unidad" class="form-label">
        Unidad de registro
        <span>*</span>
    </label>

    <select
        id="unidad"
        name="unidad"
        class="form-select @error('unidad') is-invalid @enderror"
        required
    >

        <option value="">
            Selecciona una unidad
        </option>

        <option
            value="kg"
            @selected(
                old('unidad', $producto->unidad ?? 'kg') === 'kg'
            )
        >
            Kilogramos
        </option>

        <option
            value="unidad"
            @selected(
                old('unidad', $producto->unidad ?? '') === 'unidad'
            )
        >
            Unidades
        </option>

        <option
            value="caja"
            @selected(
                old('unidad', $producto->unidad ?? '') === 'caja'
            )
        >
            Cajas
        </option>

    </select>

    @error('unidad')
        <div class="invalid-feedback">
            {{ $message }}
        </div>
    @enderror

</div>


<div class="demo-form-group">

    <label class="form-label">
        Proveedores asociados
    </label>

    <p class="demo-form-help mb-3">
        Selecciona los proveedores que pueden entregar este producto.
        Puedes elegir más de uno.
    </p>


    @if($proveedores->isEmpty())

        <div class="demo-form-warning">

            <strong>
                Todavía no tienes proveedores.
            </strong>

            <span>
                Puedes crear el producto ahora y asociar proveedores después.
            </span>

            <a href="{{ route('demo.proveedores.create') }}">
                Crear proveedor
            </a>

        </div>

    @else

        @php

            $seleccionados = old(
                'proveedores',
                isset($producto)
                    ? $producto->proveedores->pluck('id')->all()
                    : []
            );

        @endphp

        <div class="demo-checkbox-grid">

            @foreach($proveedores as $proveedor)

                <label class="demo-provider-option">

                    <input
                        type="checkbox"
                        name="proveedores[]"
                        value="{{ $proveedor->id }}"
                        @checked(
                            in_array(
                                $proveedor->id,
                                $seleccionados
                            )
                        )
                    >

                    <span class="demo-provider-option-check">
                        ✓
                    </span>

                    <span>
                        {{ $proveedor->nombre }}
                    </span>

                </label>

            @endforeach

        </div>

    @endif


    @error('proveedores.*')
        <div class="text-danger small mt-2">
            {{ $message }}
        </div>
    @enderror

</div>