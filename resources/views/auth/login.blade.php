@extends('layouts.app')

@section('title', 'Iniciar sesión | ACSoft')

@section('robots', 'noindex, nofollow')

@section('content')

<section class="py-5">
    <div class="container">

        <div
            class="card border-0 shadow-sm mx-auto"
            style="max-width: 460px;"
        >

            <div class="card-body p-4 p-md-5">

                <div class="text-center mb-4">

                    <span class="section-eyebrow">
                        ACSoft Gestión
                    </span>

                    <h1 class="h3 mt-2 fw-bold">
                        Iniciar sesión
                    </h1>

                    <p class="text-muted mb-0">
                        Ingresa para acceder a tu negocio.
                    </p>

                </div>

                <form
                    action="{{ route('login.store') }}"
                    method="POST"
                >

                    @csrf

                    <div class="mb-3">

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
                            value="{{ old('email') }}"
                            class="form-control
                                @error('email') is-invalid @enderror"
                            required
                            autofocus
                        >

                        @error('email')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>


                    <div class="mb-3">

                        <label
                            for="password"
                            class="form-label"
                        >
                            Contraseña
                        </label>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control"
                            required
                        >

                    </div>


                    <div class="form-check mb-4">

                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="remember"
                            id="remember"
                        >

                        <label
                            class="form-check-label"
                            for="remember"
                        >
                            Mantener sesión iniciada
                        </label>

                    </div>


                    <button
                        type="submit"
                        class="btn btn-acsoft-primary w-100"
                    >
                        Iniciar sesión
                    </button>

                </form>

                <div id="acsoft-install" class="acsoft-install mt-4 d-none" aria-live="polite">
                    <p class="acsoft-install-lead mb-2">Instala ACSoft en tu teléfono para acceder más rápido.</p>
                    <button type="button" id="installAcsoftButton" class="btn btn-acsoft-outline w-100">
                        Instalar ACSoft
                    </button>
                    <div id="iosInstallInstructions" class="acsoft-ios-instructions d-none mt-3">
                        <p class="mb-2">Para instalarla en tu iPhone o iPad:</p>
                        <ol class="mb-0">
                            <li>Pulsa <strong>Compartir</strong>.</li>
                            <li>Selecciona <strong>Agregar a pantalla de inicio</strong>.</li>
                            <li>Confirma <strong>Agregar</strong>.</li>
                        </ol>
                    </div>
                </div>

            </div>
        </div>

    </div>
</section>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const installArea = document.getElementById('acsoft-install');
    const installButton = document.getElementById('installAcsoftButton');
    const iosInstructions = document.getElementById('iosInstallInstructions');
    if (!installArea || !installButton) return;

    const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
    const isIos = /iphone|ipad|ipod/i.test(window.navigator.userAgent) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
    if (isStandalone) return;

    let deferredPrompt = null;
    if (isIos) {
        installArea.classList.remove('d-none');
        installButton.addEventListener('click', () => iosInstructions.classList.toggle('d-none'));
    }

    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        deferredPrompt = event;
        installArea.classList.remove('d-none');
    });

    installButton.addEventListener('click', async () => {
        if (!deferredPrompt) return;
        deferredPrompt.prompt();
        const result = await deferredPrompt.userChoice;
        if (result.outcome === 'accepted') installArea.classList.add('d-none');
        deferredPrompt = null;
    });

    window.addEventListener('appinstalled', () => installArea.classList.add('d-none'));
});
</script>
@endpush

@endsection
