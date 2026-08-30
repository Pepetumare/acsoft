<?php

namespace App\Http\Controllers\Gestion;

use App\Http\Controllers\Controller;
use App\Models\Negocio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

class PersonalizacionController extends Controller
{
    public function edit(Negocio $negocio): View
    {
        return view(
            'gestion.personalizacion.edit',
            compact('negocio')
        );
    }

    public function update(
        Request $request,
        Negocio $negocio
    ): RedirectResponse {
        $validated = $request->validate([
            'color_primario' => [
                'required',
                'regex:/^#[0-9A-Fa-f]{6}$/',
            ],
            'color_secundario' => [
                'required',
                'regex:/^#[0-9A-Fa-f]{6}$/',
            ],
            'logo' => [
                'nullable',
                File::image()
                    ->types(['png', 'jpg', 'jpeg', 'webp'])
                    ->max(2 * 1024)
                    ->dimensions(
                        Rule::dimensions()->maxWidth(4000)->maxHeight(4000)
                    ),
            ],
        ], [
            'color_primario.regex' => 'El color principal debe usar el formato #RRGGBB.',
            'color_secundario.regex' => 'El color secundario debe usar el formato #RRGGBB.',
        ]);

        $oldLogo = $negocio->logo;
        $newLogo = null;

        if ($request->hasFile('logo')) {
            $newLogo = $request->file('logo')->store(
                'business-logos/'.$negocio->id,
                'public'
            );

            if (! $newLogo) {
                throw new RuntimeException('No fue posible guardar el logo del negocio.');
            }
        }

        try {
            $negocio->update([
                'color_primario' => strtoupper($validated['color_primario']),
                'color_secundario' => strtoupper($validated['color_secundario']),
                'logo' => $newLogo ?? $oldLogo,
            ]);
        } catch (\Throwable $exception) {
            if ($newLogo) {
                Storage::disk('public')->delete($newLogo);
            }

            throw $exception;
        }

        if ($newLogo) {
            $this->deleteOwnedLogo($negocio, $oldLogo);
        }

        return back()->with('success', 'Personalización guardada correctamente.');
    }

    public function resetColors(Negocio $negocio): RedirectResponse
    {
        $negocio->update([
            'color_primario' => null,
            'color_secundario' => null,
        ]);

        return back()->with('success', 'Colores ACSoft restaurados correctamente.');
    }

    public function destroyLogo(Negocio $negocio): RedirectResponse
    {
        $oldLogo = $negocio->logo;

        $negocio->update(['logo' => null]);
        $this->deleteOwnedLogo($negocio, $oldLogo);

        return back()->with('success', 'Logo personalizado eliminado correctamente.');
    }

    private function deleteOwnedLogo(Negocio $negocio, ?string $path): void
    {
        if ($path && str_starts_with($path, 'business-logos/'.$negocio->id.'/')) {
            Storage::disk('public')->delete($path);
        }
    }
}
