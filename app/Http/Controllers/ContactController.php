<?php

namespace App\Http\Controllers;

use App\Models\ContactRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class ContactController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        /*
        |--------------------------------------------------------------------------
        | Honeypot
        |--------------------------------------------------------------------------
        */

        if ($request->filled('website')) {
            return redirect()
                ->route('home')
                ->with(
                    'contact_success',
                    'Tu consulta fue enviada correctamente.'
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Rate limit
        |--------------------------------------------------------------------------
        */

        $key = 'contact-form:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'message' => 'Has enviado demasiadas consultas. Intenta nuevamente más tarde.',
            ]);
        }

        RateLimiter::hit($key, 60 * 15);


        /*
        |--------------------------------------------------------------------------
        | Validación
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'min:2',
                'max:100',
            ],

            'business' => [
                'nullable',
                'string',
                'max:150',
            ],

            'contact' => [
                'required',
                'string',
                'min:5',
                'max:150',
            ],

            'message' => [
                'required',
                'string',
                'min:10',
                'max:2000',
            ],
        ], [
            'name.required' => 'Ingresa tu nombre.',
            'name.min' => 'El nombre debe tener al menos 2 caracteres.',

            'contact.required' => 'Ingresa un teléfono o correo de contacto.',
            'contact.min' => 'Ingresa un teléfono o correo válido.',

            'message.required' => 'Cuéntame brevemente en qué necesitas ayuda.',
            'message.min' => 'El mensaje debe tener al menos 10 caracteres.',
            'message.max' => 'El mensaje no puede superar los 2000 caracteres.',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Guardar solicitud
        |--------------------------------------------------------------------------
        */

        $contact = trim($validated['contact']);

        $isEmail = filter_var($contact, FILTER_VALIDATE_EMAIL);

        $isPhone = preg_match(
            '/^[+\d\s\-()]{8,20}$/',
            $contact
        );

        if (!$isEmail && !$isPhone) {
            throw ValidationException::withMessages([
                'contact' => 'Ingresa un teléfono o correo válido.',
            ]);
        }

        ContactRequest::create([
            'name' => $validated['name'],
            'business' => $validated['business'] ?? null,
            'contact' => $validated['contact'],
            'message' => $validated['message'],
            'status' => 'pending',
        ]);


        return redirect()
            ->route('home')
            ->with(
                'contact_success',
                'Tu consulta fue enviada correctamente. Te contactaré a la brevedad.'
            );
    }
}
