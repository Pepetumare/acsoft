<?php

namespace App\Http\Controllers\Gestion;

use App\Http\Controllers\Controller;
use App\Models\Negocio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CajaController extends Controller
{
    public function index(Negocio $negocio): View
    {
        $caja = $negocio
            ->cajas()
            ->where('estado', 'abierta')
            ->latest('id')
            ->first();

        if ($caja) {
            $caja->load([
                'movimientos.usuario',
                'usuarioApertura',
            ]);
        }

        return view('gestion.caja.index', compact('negocio', 'caja'));
    }

    public function create(Negocio $negocio): View|RedirectResponse
    {
        $existeCajaAbierta = $negocio
            ->cajas()
            ->where('estado', 'abierta')
            ->exists();

        if ($existeCajaAbierta) {
            return redirect()
                ->route('gestion.caja.index', $negocio)
                ->with('error', 'Ya existe una caja abierta.');
        }

        return view('gestion.caja.create', compact('negocio'));
    }

    public function store(Request $request, Negocio $negocio): RedirectResponse
    {
        $validated = $request->validate([
            'saldo_inicial' => ['required', 'numeric', 'min:0'],
            'observacion_apertura' => ['nullable', 'string', 'max:1000'],
        ]);

        $abierta = DB::transaction(function () use ($validated, $request, $negocio) {
            Negocio::query()
                ->whereKey($negocio->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($negocio->cajas()->where('estado', 'abierta')->exists()) {
                return false;
            }

            $negocio->cajas()->create([
                'user_apertura_id' => $request->user()->id,
                'fecha' => now()->toDateString(),
                'saldo_inicial' => $validated['saldo_inicial'],
                'estado' => 'abierta',
                'abierta_en' => now(),
                'observacion_apertura' => $validated['observacion_apertura'] ?? null,
            ]);

            return true;
        });

        if (!$abierta) {
            return redirect()
                ->route('gestion.caja.index', $negocio)
                ->with('error', 'Ya existe una caja abierta.');
        }

        return redirect()
            ->route('gestion.caja.index', $negocio)
            ->with('success', 'Caja abierta correctamente.');
    }

    public function storeMovimiento(
        Request $request,
        Negocio $negocio
    ): RedirectResponse {
        $validated = $request->validate([
            'tipo' => ['required', 'in:ingreso,egreso'],
            'concepto' => ['required', 'string', 'max:255'],
            'monto' => ['required', 'numeric', 'min:0.01'],
            'observacion' => ['nullable', 'string', 'max:1000'],
        ]);

        $registrado = DB::transaction(function () use ($validated, $request, $negocio) {
            $caja = $negocio
                ->cajas()
                ->where('estado', 'abierta')
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if (!$caja || !$caja->estaAbierta()) {
                return false;
            }

            $caja->movimientos()->create([
                'user_id' => $request->user()->id,
                'tipo' => $validated['tipo'],
                'concepto' => $validated['concepto'],
                'monto' => $validated['monto'],
                'observacion' => $validated['observacion'] ?? null,
            ]);

            return true;
        });

        if (!$registrado) {
            return redirect()
                ->route('gestion.caja.index', $negocio)
                ->with('error', 'La caja está cerrada y no puede recibir movimientos.');
        }

        return redirect()
            ->route('gestion.caja.index', $negocio)
            ->with('success', 'Movimiento registrado correctamente.');
    }

    public function close(Negocio $negocio): View
    {
        $caja = $negocio
            ->cajas()
            ->where('estado', 'abierta')
            ->latest('id')
            ->firstOrFail();

        $saldoEsperado = $caja->calcularSaldoEsperado();

        return view(
            'gestion.caja.close',
            compact('negocio', 'caja', 'saldoEsperado')
        );
    }

    public function destroy(Request $request, Negocio $negocio): RedirectResponse
    {
        $validated = $request->validate([
            'saldo_contado' => ['required', 'numeric', 'min:0'],
            'observacion_cierre' => ['nullable', 'string', 'max:1000'],
        ]);

        $cerrada = DB::transaction(function () use ($validated, $request, $negocio) {
            $caja = $negocio
                ->cajas()
                ->where('estado', 'abierta')
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if (!$caja || !$caja->estaAbierta()) {
                return false;
            }

            $saldoEsperado = $caja->calcularSaldoEsperado();
            $saldoContado = (float) $validated['saldo_contado'];

            $caja->update([
                'user_cierre_id' => $request->user()->id,
                'saldo_esperado' => $saldoEsperado,
                'saldo_contado' => $saldoContado,
                'diferencia' => $saldoContado - $saldoEsperado,
                'estado' => 'cerrada',
                'cerrada_en' => now(),
                'observacion_cierre' => $validated['observacion_cierre'] ?? null,
            ]);

            return true;
        });

        if (!$cerrada) {
            return redirect()
                ->route('gestion.caja.index', $negocio)
                ->with('error', 'La caja ya se encuentra cerrada.');
        }

        return redirect()
            ->route('gestion.caja.index', $negocio)
            ->with('success', 'Caja cerrada correctamente.');
    }

    public function history(Negocio $negocio): View
    {
        $cajas = $negocio
            ->cajas()
            ->with(['usuarioApertura', 'usuarioCierre'])
            ->where('estado', 'cerrada')
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->paginate(20);

        return view('gestion.caja.history', compact('negocio', 'cajas'));
    }
}
