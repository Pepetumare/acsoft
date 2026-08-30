@extends('layouts.gestion')

@section('title', 'Personalización | ' . $negocio->nombre)

@section('content')
    <div class="container-fluid p-4 p-lg-5">
        <div class="mb-4">
            <span class="section-eyebrow">Identidad visual</span>
            <h1 class="mt-2">Personalización del negocio</h1>
            <p class="text-muted mb-0">
                Estos cambios serán visibles para todos los usuarios de {{ $negocio->nombre }}.
            </p>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row g-4 align-items-start">
            <div class="col-lg-6">
                <div class="contact-form-card">
                    <form
                        action="{{ route('gestion.personalizacion.update', $negocio) }}"
                        method="POST"
                        enctype="multipart/form-data"
                    >
                        @csrf
                        @method('PATCH')

                        <div class="mb-4">
                            <label for="business-logo" class="form-label">Logo / icono</label>

                            <div class="business-current-logo mb-3">
                                @if ($negocio->logoUrl())
                                    <img src="{{ $negocio->logoUrl() }}" alt="Logo actual de {{ $negocio->nombre }}">
                                @else
                                    <span>Sin logo personalizado</span>
                                @endif
                            </div>

                            <input
                                type="file"
                                id="business-logo"
                                name="logo"
                                class="form-control @error('logo') is-invalid @enderror"
                                accept="image/png,image/jpeg,image/webp"
                            >
                            <div class="form-text">PNG, JPG/JPEG o WebP. Máximo 2 MB.</div>
                        </div>

                        @foreach ([
                            'color_primario' => ['Color principal', $negocio->colorPrimario()],
                            'color_secundario' => ['Color secundario', $negocio->colorSecundario()],
                        ] as $field => [$label, $value])
                            <div class="mb-4">
                                <label for="{{ $field }}-text" class="form-label">{{ $label }}</label>
                                <div class="business-color-control">
                                    <input
                                        type="color"
                                        id="{{ $field }}-picker"
                                        value="{{ old($field, $value) }}"
                                        aria-label="Selector de {{ strtolower($label) }}"
                                    >
                                    <input
                                        type="text"
                                        id="{{ $field }}-text"
                                        name="{{ $field }}"
                                        value="{{ old($field, $value) }}"
                                        class="form-control font-monospace @error($field) is-invalid @enderror"
                                        maxlength="7"
                                        pattern="#[0-9A-Fa-f]{6}"
                                        required
                                    >
                                </div>
                            </div>
                        @endforeach

                        <button type="submit" class="btn btn-acsoft-primary">
                            Guardar cambios
                        </button>
                    </form>

                    <hr class="my-4">

                    <div class="d-flex flex-wrap gap-2">
                        <form
                            action="{{ route('gestion.personalizacion.colors.destroy', $negocio) }}"
                            method="POST"
                            onsubmit="return confirm('¿Restaurar los colores predeterminados de ACSoft?')"
                        >
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-secondary">
                                Restaurar colores ACSoft
                            </button>
                        </form>

                        @if ($negocio->logo)
                            <form
                                action="{{ route('gestion.personalizacion.logo.destroy', $negocio) }}"
                                method="POST"
                                onsubmit="return confirm('¿Eliminar el logo personalizado?')"
                            >
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger">
                                    Eliminar logo
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="business-preview-card sticky-lg-top">
                    <span class="section-eyebrow">Vista previa</span>
                    <p class="text-muted small">La vista previa no se guarda automáticamente.</p>

                    <div id="business-preview" class="business-preview">
                        <div class="business-preview-header">
                            <div class="business-preview-logo">
                                <img
                                    id="business-preview-image"
                                    src="{{ $negocio->logoUrl() ?? '' }}"
                                    alt="Vista previa del logo"
                                    @if (! $negocio->logoUrl()) hidden @endif
                                >
                                <span id="business-preview-fallback" @if ($negocio->logoUrl()) hidden @endif>
                                    {{ mb_strtoupper(mb_substr($negocio->nombre, 0, 1)) }}
                                </span>
                            </div>
                            <strong>{{ $negocio->nombre }}</strong>
                        </div>

                        <div class="business-preview-body">
                            <nav>
                                <a href="#" class="active" onclick="return false">Dashboard</a>
                                <a href="#" onclick="return false">Ventas</a>
                                <a href="#" onclick="return false">Caja</a>
                            </nav>
                            <div class="business-preview-content">
                                <div></div>
                                <div></div>
                                <button type="button">Botón principal</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const preview = document.getElementById('business-preview');
            const logoInput = document.getElementById('business-logo');
            const previewImage = document.getElementById('business-preview-image');
            const previewFallback = document.getElementById('business-preview-fallback');
            const colorPattern = /^#[0-9A-Fa-f]{6}$/;

            const contrastFor = color => {
                const channels = [1, 3, 5].map(index => parseInt(color.slice(index, index + 2), 16) / 255)
                    .map(channel => channel <= 0.04045
                        ? channel / 12.92
                        : Math.pow((channel + 0.055) / 1.055, 2.4));
                const luminance = 0.2126 * channels[0] + 0.7152 * channels[1] + 0.0722 * channels[2];
                return 1.05 / (luminance + 0.05) >= (luminance + 0.05) / 0.056
                    ? '#FFFFFF'
                    : '#111827';
            };

            const updatePreview = () => {
                const primary = document.getElementById('color_primario-text').value;
                const secondary = document.getElementById('color_secundario-text').value;

                if (colorPattern.test(primary)) {
                    preview.style.setProperty('--preview-primary', primary);
                    preview.style.setProperty('--preview-primary-contrast', contrastFor(primary));
                }
                if (colorPattern.test(secondary)) {
                    preview.style.setProperty('--preview-secondary', secondary);
                    preview.style.setProperty('--preview-secondary-contrast', contrastFor(secondary));
                }
            };

            ['color_primario', 'color_secundario'].forEach(field => {
                const picker = document.getElementById(`${field}-picker`);
                const text = document.getElementById(`${field}-text`);

                picker.addEventListener('input', () => {
                    text.value = picker.value.toUpperCase();
                    updatePreview();
                });
                text.addEventListener('input', () => {
                    if (colorPattern.test(text.value)) {
                        picker.value = text.value;
                    }
                    updatePreview();
                });
            });

            logoInput.addEventListener('change', () => {
                const file = logoInput.files[0];
                const allowedTypes = ['image/png', 'image/jpeg', 'image/webp'];

                if (!file || !allowedTypes.includes(file.type)) {
                    return;
                }

                const reader = new FileReader();
                reader.addEventListener('load', () => {
                    previewImage.src = reader.result;
                    previewImage.hidden = false;
                    previewFallback.hidden = true;
                });
                reader.readAsDataURL(file);
            });

            updatePreview();
        });
    </script>
@endpush
