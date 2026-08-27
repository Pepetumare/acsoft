<div class="row g-3">

    <div class="col-md-6">
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
            value="{{ old('nombre', $cliente->nombre ?? '') }}"
            class="form-control @error('nombre') is-invalid @enderror"
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
            for="email"
            class="form-label"
        >
            Correo
        </label>

        <input
            type="email"
            id="email"
            name="email"
            value="{{ old('email', $cliente->email ?? '') }}"
            class="form-control @error('email') is-invalid @enderror"
        >

        @error('email')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror
    </div>


    <div class="col-md-6">
        <label
            for="telefono"
            class="form-label"
        >
            Teléfono
        </label>

        <input
            type="text"
            id="telefono"
            name="telefono"
            value="{{ old('telefono', $cliente->telefono ?? '') }}"
            class="form-control @error('telefono') is-invalid @enderror"
        >

        @error('telefono')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror
    </div>


    @isset($cliente)

        <div class="col-md-6 d-flex align-items-end">

            <div class="form-check mb-2">

                <input
                    class="form-check-input"
                    type="checkbox"
                    name="activo"
                    value="1"
                    id="activo"
                    @checked(old('activo', $cliente->activo))
                >

                <label
                    class="form-check-label"
                    for="activo"
                >
                    Cliente activo
                </label>

            </div>

        </div>

    @endisset


    <div class="col-12">

        <label
            for="notas"
            class="form-label"
        >
            Notas internas
        </label>

        <textarea
            id="notas"
            name="notas"
            rows="5"
            class="form-control @error('notas') is-invalid @enderror"
            placeholder="Información relevante del cliente..."
        >{{ old('notas', $cliente->notas ?? '') }}</textarea>

        @error('notas')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
        @enderror

    </div>

</div>