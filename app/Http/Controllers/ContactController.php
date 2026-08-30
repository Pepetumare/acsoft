<?php

namespace App\Http\Controllers;

use App\Enums\ContactRequestStatus;
use App\Enums\ContactRequestType;
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
                ->route('contact')
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

            'email' => [
                'required',
                'email',
                'max:150',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:50',
                'regex:/^[+\d\s\-()]{8,20}$/',
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

            'email.required' => 'Ingresa tu correo de contacto.',
            'email.email' => 'Ingresa un correo válido.',
            'phone.regex' => 'Ingresa un teléfono válido.',

            'message.required' => 'Cuéntame brevemente en qué necesitas ayuda.',
            'message.min' => 'El mensaje debe tener al menos 10 caracteres.',
            'message.max' => 'El mensaje no puede superar los 2000 caracteres.',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Guardar solicitud
        |--------------------------------------------------------------------------
        */

        ContactRequest::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'business' => $validated['business'] ?? null,
            'contact' => $validated['email'],
            'message' => $validated['message'],
            'type' => ContactRequestType::Demo,
            'status' => ContactRequestStatus::Pending,
        ]);


        return redirect()
            ->route('contact')
            ->with(
                'contact_success',
                'Tu consulta fue enviada correctamente. Te contactaré a la brevedad.'
            );
    }
}
