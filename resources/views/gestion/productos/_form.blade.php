<div class="row g-4">

    <div class="col-md-8">

        <label
            for="nombre"
            class="form-label"
        >
            Nombre *
        </label>

        <input
            type="text"
            id="nombre"
            name="nombre"
            value="{{ old(
                'nombre',
                $producto->nombre ?? ''
            ) }}"
            class="form-control
                @error('nombre') is-invalid @enderror"
            required
        >

        @error('nombre')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror

    </div>


    <div class="col-md-4">

        <label
            for="codigo"
            class="form-label"
        >
            Código / SKU
        </label>

        <input
            type="text"
            id="codigo"
            name="codigo"
            value="{{ old(
                'codigo',
                $producto->codigo ?? ''
            ) }}"
            class="form-control
                @error('codigo') is-invalid @enderror"
            placeholder="Opcional"
        >

        @error('codigo')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror

    </div>


    <div class="col-md-4">

        <label
            for="unidad"
            class="form-label"
        >
            Unidad *
        </label>

        <select
            id="unidad"
            name="unidad"
            class="form-select"
            required
        >

            @php
                $unidadActual = old(
                    'unidad',
                    $producto->unidad ?? 'unidad'
                );
            @endphp

            <option
                value="unidad"
                @selected(
                    $unidadActual === 'unidad'
                )
            >
                Unidad
            </option>

            <option
                value="kg"
                @selected(
                    $unidadActual === 'kg'
                )
            >
                Kilogramo
            </option>

            <option
                value="g"
                @selected(
                    $unidadActual === 'g'
                )
            >
                Gramo
            </option>

            <option
                value="litro"
                @selected(
                    $unidadActual === 'litro'
                )
            >
                Litro
            </option>

            <option
                value="ml"
                @selected(
                    $unidadActual === 'ml'
                )
            >
                Mililitro
            </option>

            <option
                value="caja"
                @selected(
                    $unidadActual === 'caja'
                )
            >
                Caja
            </option>

            <option
                value="paquete"
                @selected(
                    $unidadActual === 'paquete'
                )
            >
                Paquete
            </option>

        </select>

    </div>


    <div class="col-md-4">

        <label
            for="precio_venta"
            class="form-label"
        >
            Precio de venta
        </label>

        <input
            type="number"
            id="precio_venta"
            name="precio_venta"
            value="{{ old(
                'precio_venta',
                $producto->precio_venta ?? ''
            ) }}"
            min="0"
            step="1"
            class="form-control"
        >

    </div>


    <div class="col-md-4">

        <label
            for="stock_minimo"
            class="form-label"
        >
            Stock mínimo
        </label>

        <input
            type="number"
            id="stock_minimo"
            name="stock_minimo"
            value="{{ old(
                'stock_minimo',
                $producto->stock_minimo ?? ''
            ) }}"
            min="0"
            step="0.001"
            class="form-control"
        >

        <div class="form-text">
            Se utilizará cuando activemos Stock.
        </div>

    </div>


    @isset($producto)

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
                            $producto->activo
                        )
                    )
                >

                <label
                    class="form-check-label"
                    for="activo"
                >
                    Producto activo
                </label>

            </div>

        </div>

    @endisset

</div>