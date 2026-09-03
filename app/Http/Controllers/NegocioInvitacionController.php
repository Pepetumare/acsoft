<?php

namespace App\Http\Controllers;

use App\Models\NegocioInvitacion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class NegocioInvitacionController extends Controller
{
    public function show(Request $request, string $token): View
    {
        $invitation = $this->find($token);

        abort_if($invitation->accepted_at, 410, 'Esta invitación ya fue utilizada.');
        abort_if($invitation->expires_at->isPast(), 410, 'Esta invitación ha expirado.');
        abort_unless(mb_strtolower($request->user()->email) === $invitation->email, 403);

        $invitation->load('negocio');

        return view('invitations.business', compact('invitation', 'token'));
    }

    public function accept(Request $request, string $token): RedirectResponse
    {
        $business = DB::transaction(function () use ($request, $token) {
            $invitation = NegocioInvitacion::query()
                ->where('token_hash', hash('sha256', $token))
                ->lockForUpdate()
                ->firstOrFail();

            abort_if($invitation->accepted_at, 410, 'Esta invitación ya fue utilizada.');
            abort_if($invitation->expires_at->isPast(), 410, 'Esta invitación ha expirado.');
            abort_unless(mb_strtolower($request->user()->email) === $invitation->email, 403);
            abort_if($request->user()->is_superadmin, 403);

            $invitation->negocio->usuarios()->syncWithoutDetaching([
                $request->user()->id => ['rol' => $invitation->rol, 'activo' => true],
            ]);
            $invitation->update(['accepted_at' => now()]);

            return $invitation->negocio;
        });

        return redirect()->route('gestion.dashboard', $business)
            ->with('success', 'Invitación aceptada correctamente.');
    }

    private function find(string $token): NegocioInvitacion
    {
        return NegocioInvitacion::query()
            ->where('token_hash', hash('sha256', $token))
            ->firstOrFail();
    }
}
