@extends('layouts.gestion')

@section(
    'title',
    'Productos | ' . $negocio->nombre
)

@section('content')

<div class="container-fluid p-4 p-lg-5">

    <div
        class="d-flex flex-column flex-md-row
        justify-content-between
        align-items-md-center
        gap-3 mb-4"
    >

        <div>

            <span class="section-eyebrow">
                Inventario
            </span>

            <h1 class="mt-2 mb-1">
                Productos
            </h1>

            <p class="text-muted mb-0">
                Administra el catálogo del negocio.
            </p>

        </div>


        <a
            href="{{ route(
                'gestion.productos.create',
                $negocio
            ) }}"
            class="btn btn-acsoft-primary"
        >
            + Nuevo producto
        </a>

    </div>


    @if(session('success'))

        <div class="alert alert-success">
            {{ session('success') }}
        </div>

    @endif


    <div class="contact-form-card mb-4">

        <form
            method="GET"
            action="{{ route(
                'gestion.productos.index',
                $negocio
            ) }}"
            class="row g-3 align-items-end"
        >

            <div class="col-md-8">

                <label
                    for="buscar"
                    class="form-label"
                >
                    Buscar
                </label>

                <input
                    type="text"
                    id="buscar"
                    name="buscar"
                    value="{{ request('buscar') }}"
                    class="form-control"
                    placeholder="Nombre o código..."
                >

            </div>


            <div class="col-md-4">

                <div class="d-flex flex-wrap gap-2">

                    <button
                        type="submit"
                        class="btn btn-acsoft-primary"
                    >
                        Buscar
                    </button>

                    <a
                        href="{{ route(
                            'gestion.productos.index',
                            $negocio
                        ) }}"
                        class="btn btn-outline-secondary"
                    >
                        Limpiar
                    </a>

                </div>

            </div>

        </form>

    </div>


    <div class="demo-content-card">

        @if($productos->isEmpty())

            <div class="demo-empty-state">

                <h2>
                    No hay productos para mostrar
                </h2>

                <p>
                    Agrega el primer producto
                    al catálogo del negocio.
                </p>

                <a
                    href="{{ route(
                        'gestion.productos.create',
                        $negocio
                    ) }}"
                    class="btn btn-acsoft-primary"
                >
                    Crear producto
                </a>

            </div>

        @else

            <div class="table-responsive">

                <table
                    class="table demo-crud-table
                    align-middle mb-0"
                >

                    <thead>

                        <tr>
                            <th>Producto</th>
                            <th>Código</th>
                            <th>Unidad</th>
                            <th>Precio</th>
                            <th>Stock mínimo</th>
                            <th>Estado</th>

                            <th class="text-end">
                                Acciones
                            </th>
                        </tr>

                    </thead>


                    <tbody>

                        @foreach($productos as $producto)

                            <tr>

                                <td>

                                    <strong>
                                        {{ $producto->nombre }}
                                    </strong>

                                </td>


                                <td>
                                    {{ $producto->codigo ?: '—' }}
                                </td>


                                <td>
                                    {{ ucfirst($producto->unidad) }}
                                </td>


                                <td>

                                    @if(
                                        $producto->precio_venta
                                        !== null
                                    )

                                        ${{ number_format(
                                            $producto->precio_venta,
                                            0,
                                            ',',
                                            '.'
                                        ) }}

                                    @else

                                        —

                                    @endif

                                </td>


                                <td>

                                    @if(
                                        $producto->stock_minimo
                                        !== null
                                    )

                                        {{ (float)
                                            $producto->stock_minimo }}

                                        {{ $producto->unidad }}

                                    @else

                                        —

                                    @endif

                                </td>


                                <td>

                                    @if($producto->activo)

                                        <span
                                            class="badge
                                            text-bg-success"
                                        >
                                            Activo
                                        </span>

                                    @else

                                        <span
                                            class="badge
                                            text-bg-secondary"
                                        >
                                            Inactivo
                                        </span>

                                    @endif

                                </td>


                                <td class="text-end">

                                    <div
                                        class="d-flex flex-wrap
                                        justify-content-end
                                        gap-2"
                                    >

                                        <a
                                            href="{{ route(
                                                'gestion.productos.edit',
                                                [
                                                    $negocio,
                                                    $producto
                                                ]
                                            ) }}"
                                            class="btn btn-sm
                                                btn-demo-edit"
                                        >
                                            Editar
                                        </a>


                                        @if ($producto->activo)
                                        <form
                                            action="{{ route(
                                                'gestion.productos.destroy',
                                                [
                                                    $negocio,
                                                    $producto
                                                ]
                                            ) }}"
                                            method="POST"
                                            onsubmit="return confirm(
                                                'El producto dejará de estar disponible para nuevas operaciones, pero su historial se conservará. ¿Continuar?'
                                            )"
                                        >

                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="btn btn-sm
                                                    btn-demo-delete"
                                            >
                                                Desactivar
                                            </button>

                                        </form>
                                        @endif

                                    </div>

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        @endif

    </div>


    @if($productos->hasPages())

        <div class="mt-4">
            {{ $productos->links() }}
        </div>

    @endif

</div>

@endsection
