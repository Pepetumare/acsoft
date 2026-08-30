@extends('layouts.admin')

@section('title', 'Administración | ACSoft')

@section('robots', 'noindex, nofollow')

@section('content')

    <div class="container py-5">

        <div class="mb-5">

            <span class="section-eyebrow">
                ACSoft
            </span>

            <h1 class="mt-2">
                Panel de administración
            </h1>

            <p class="text-muted">
                Gestiona clientes, negocios, usuarios y módulos desde un solo lugar.
            </p>

        </div>


        <div class="row g-4">

            <div class="col-md-6 col-xl-3">

                <a href="{{ route('admin.clientes.index') }}" class="text-decoration-none">

                    <div class="benefit-card h-100">

                        <div class="benefit-icon">
                            C
                        </div>

                        <h3>
                            Clientes
                        </h3>

                        <p>
                            Crear y administrar clientes.
                        </p>

                    </div>

                </a>

            </div>


            <div class="col-md-6 col-xl-3">

                <a href="{{ route('admin.negocios.index') }}" class="text-decoration-none">
                    <div class="benefit-card h-100">

                        <div class="benefit-icon">
                            N
                        </div>

                        <h3>
                            Negocios
                        </h3>

                        <p>
                            Configura negocios y módulos.
                        </p>

                    </div>
                </a>

            </div>


            <div class="col-md-6 col-xl-3">

                <a href="{{ route('admin.usuarios.index') }}" class="text-decoration-none">
                    <div class="benefit-card h-100">

                        <div class="benefit-icon">
                            U
                        </div>

                        <h3>
                            Usuarios
                        </h3>

                        <p>
                            Cuentas y accesos a negocios.
                        </p>

                    </div>
                </a>

            </div>


            <div class="col-md-6 col-xl-3">

                <div class="benefit-card h-100">

                    <div class="benefit-icon">
                        M
                    </div>

                    <h3>
                        Módulos
                    </h3>

                    <p>
                        Próximamente.
                    </p>

                </div>

            </div>

        </div>


        <form action="{{ route('logout') }}" method="POST" class="mt-5">
            @csrf

            <button class="btn btn-outline-danger">
                Cerrar sesión
            </button>

        </form>

    </div>

@endsection
