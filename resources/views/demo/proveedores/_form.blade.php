<div class="demo-form-group">

    <label
        for="nombre"
        class="form-label"
    >
        Nombre del proveedor
        <span>*</span>
    </label>

    <input
        type="text"
        id="nombre"
        name="nombre"
        value="{{ old('nombre', $proveedor->nombre ?? '') }}"
        class="form-control
            @error('nombre') is-invalid @enderror"
        placeholder="Ej: Distribuidora Sur"
        maxlength="150"
        autocomplete="organization"
        required
        autofocus
    >

    @error('nombre')

        <div class="invalid-feedback">
            {{ $message }}
        </div>

    @enderror


    <div class="demo-form-help">
        Usa un nombre que puedas identificar fácilmente
        cuando registres mercadería.
    </div>

</div>