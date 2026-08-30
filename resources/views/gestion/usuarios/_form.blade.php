<div class="row g-4">
    <div class="col-md-6">
        <label for="name" class="form-label">Nombre *</label>
        <input type="text" id="name" name="name" value="{{ old('name', $usuario->name ?? '') }}" class="form-control @error('name') is-invalid @enderror" required maxlength="150">
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    @if (!isset($usuario))
        <div class="col-md-6">
            <label for="email" class="form-label">Email *</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" required maxlength="150" autocomplete="email">
            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <div class="form-text">Si ya existe, se asociará sin crear un usuario duplicado.</div>
        </div>
        <div class="col-md-6">
            <label for="password" class="form-label">Contraseña *</label>
            <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" required minlength="8" autocomplete="new-password">
            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label for="password_confirmation" class="form-label">Confirmar contraseña *</label>
            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required minlength="8" autocomplete="new-password">
        </div>
    @else
        <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="email" value="{{ $usuario->email }}" class="form-control" disabled>
            <div class="form-text">El correo y la contraseña no se modifican desde esta sección.</div>
        </div>
    @endif

    <div class="col-md-6">
        <label for="rol" class="form-label">Rol *</label>
        <select id="rol" name="rol" class="form-select @error('rol') is-invalid @enderror" required>
            @php($rolActual = old('rol', isset($usuario) ? $usuario->negocios->first()->pivot->rol : 'usuario'))
            <option value="usuario" @selected($rolActual === 'usuario')>Usuario</option>
            <option value="admin" @selected($rolActual === 'admin')>Administrador</option>
        </select>
        @error('rol')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    @isset($usuario)
        <div class="col-md-6">
            <label for="activo" class="form-label">Estado *</label>
            <select id="activo" name="activo" class="form-select @error('activo') is-invalid @enderror" required>
                @php($activoActual = (string) old('activo', (int) $usuario->negocios->first()->pivot->activo))
                <option value="1" @selected($activoActual === '1')>Activo</option>
                <option value="0" @selected($activoActual === '0')>Inactivo</option>
            </select>
            @error('activo')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    @endisset
</div>
