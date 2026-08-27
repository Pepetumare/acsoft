<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    <title>
        Reporte | {{ $negocio->nombre }}
    </title>

    <style>

        body {
            font-family: DejaVu Sans, sans-serif;
            color: #172033;
            font-size: 12px;
            line-height: 1.5;
        }

        .header {
            margin-bottom: 24px;
            padding-bottom: 14px;
            border-bottom: 2px solid #0F2744;
        }

        .brand {
            color: #0F2744;
            font-size: 20px;
            font-weight: bold;
        }

        .subtitle {
            color: #657084;
            margin-top: 4px;
        }

        .period {
            margin-top: 10px;
            color: #657084;
        }

        .summary-table {
            width: 100%;
            margin-bottom: 24px;
            border-collapse: collapse;
        }

        .summary-table td {
            width: 25%;
            padding: 12px;
            border: 1px solid #E3E8EF;
            vertical-align: top;
        }

        .summary-label {
            display: block;
            color: #657084;
            font-size: 10px;
            margin-bottom: 4px;
        }

        .summary-value {
            font-size: 18px;
            font-weight: bold;
            color: #0F2744;
        }

        h2 {
            margin-top: 24px;
            margin-bottom: 10px;
            color: #0F2744;
            font-size: 15px;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }

        table.data-table th,
        table.data-table td {
            padding: 8px;
            border-bottom: 1px solid #E3E8EF;
        }

        table.data-table th {
            text-align: left;
            background: #F7F9FC;
            color: #0F2744;
        }

        .text-right {
            text-align: right;
        }

        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #E3E8EF;
            color: #657084;
            font-size: 10px;
        }

    </style>
</head>

<body>

    <div class="header">

        <div class="brand">
            {{ $negocio->nombre }}
        </div>

        <div class="subtitle">
            Reporte generado por ACSoft Gestión
        </div>

        <div class="period">
            Período:
            {{ $desde->format('d/m/Y') }}
            al
            {{ $hasta->format('d/m/Y') }}
        </div>

    </div>


    <table class="summary-table">

        <tr>

            <td>
                <span class="summary-label">
                    Ventas
                </span>

                <span class="summary-value">
                    ${{ number_format(
                        $totalVentas,
                        0,
                        ',',
                        '.'
                    ) }}
                </span>
            </td>


            <td>
                <span class="summary-label">
                    Gastos
                </span>

                <span class="summary-value">
                    ${{ number_format(
                        $totalGastos,
                        0,
                        ',',
                        '.'
                    ) }}
                </span>
            </td>


            <td>
                <span class="summary-label">
                    Resultado
                </span>

                <span class="summary-value">
                    ${{ number_format(
                        $resultado,
                        0,
                        ',',
                        '.'
                    ) }}
                </span>
            </td>


            <td>
                <span class="summary-label">
                    Ticket promedio
                </span>

                <span class="summary-value">
                    ${{ number_format(
                        $ticketPromedio,
                        0,
                        ',',
                        '.'
                    ) }}
                </span>
            </td>

        </tr>

    </table>


    <p>
        Cantidad de ventas:
        <strong>
            {{ $cantidadVentas }}
        </strong>
    </p>


    <h2>
        Ventas por método de pago
    </h2>

    <table class="data-table">

        <thead>

            <tr>
                <th>Método</th>

                <th class="text-right">
                    Total
                </th>
            </tr>

        </thead>

        <tbody>

            @forelse($ventasPorMetodo as $item)

                <tr>

                    <td>
                        {{ $item->metodo }}
                    </td>

                    <td class="text-right">
                        ${{ number_format(
                            $item->total,
                            0,
                            ',',
                            '.'
                        ) }}
                    </td>

                </tr>

            @empty

                <tr>

                    <td colspan="2">
                        No hay ventas en este período.
                    </td>

                </tr>

            @endforelse

        </tbody>

    </table>


    <h2>
        Gastos por categoría
    </h2>

    <table class="data-table">

        <thead>

            <tr>
                <th>Categoría</th>

                <th class="text-right">
                    Total
                </th>
            </tr>

        </thead>

        <tbody>

            @forelse($gastosPorCategoria as $item)

                <tr>

                    <td>
                        {{ $item->categoria_nombre }}
                    </td>

                    <td class="text-right">
                        ${{ number_format(
                            $item->total,
                            0,
                            ',',
                            '.'
                        ) }}
                    </td>

                </tr>

            @empty

                <tr>

                    <td colspan="2">
                        No hay gastos en este período.
                    </td>

                </tr>

            @endforelse

        </tbody>

    </table>


    <div class="footer">

        Generado el
        {{ now()->format('d/m/Y H:i') }}

        · ACSoft Gestión

    </div>

</body>

</html>