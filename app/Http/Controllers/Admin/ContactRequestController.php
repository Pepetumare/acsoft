<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ContactRequestStatus;
use App\Enums\ContactRequestType;
use App\Http\Controllers\Controller;
use App\Models\ContactRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContactRequestController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'status' => ['nullable', Rule::enum(ContactRequestStatus::class)],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'search' => ['nullable', 'string', 'max:150'],
        ]);

        $requests = ContactRequest::query()
            ->when(
                $validated['status'] ?? null,
                fn ($query, string $status) => $query->where('status', $status)
            )
            ->when(
                $validated['date'] ?? null,
                fn ($query, string $date) => $query->whereDate('created_at', $date)
            )
            ->when(
                $validated['search'] ?? null,
                function ($query, string $search): void {
                    $query->where(function ($query) use ($search): void {
                        $query
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('business', 'like', "%{$search}%");
                    });
                }
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.requests.index', [
            'requests' => $requests,
            'statuses' => ContactRequestStatus::cases(),
            'types' => ContactRequestType::cases(),
        ]);
    }

    public function show(ContactRequest $solicitud): View
    {
        return view('admin.requests.show', [
            'contactRequest' => $solicitud,
            'statuses' => ContactRequestStatus::cases(),
        ]);
    }

    public function update(
        Request $request,
        ContactRequest $solicitud
    ): RedirectResponse {
        $validated = $request->validate([
            'status' => ['required', Rule::enum(ContactRequestStatus::class)],
        ]);

        $solicitud->update($validated);

        return back()->with('success', 'Estado actualizado correctamente.');
    }
}
