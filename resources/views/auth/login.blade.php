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

            </div>
        </div>

    </div>
</section>

@endsection