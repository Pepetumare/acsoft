<?php

namespace Tests\Feature;

use App\Enums\ContactRequestStatus;
use App\Enums\ContactRequestType;
use App\Models\ContactRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminContactRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_solicitud_publica_se_guarda_como_demostracion(): void
    {
        $this->post(route('contact.store'), [
            'name' => 'Ana Pérez',
            'business' => 'Almacén Central',
            'email' => 'ana@example.com',
            'phone' => '+56 9 1234 5678',
            'message' => 'Quiero probar ventas, caja y control de stock.',
        ])->assertRedirect(route('contacto'));

        $this->assertDatabaseHas('contact_requests', [
            'name' => 'Ana Pérez',
            'email' => 'ana@example.com',
            'phone' => '+56 9 1234 5678',
            'business' => 'Almacén Central',
            'type' => ContactRequestType::Demo->value,
            'status' => ContactRequestStatus::Pending->value,
        ]);
    }

    public function test_superadmin_puede_ver_una_solicitud(): void
    {
        $request = $this->createRequest(['name' => 'Solicitud visible']);

        $this->actingAs($this->superadmin())
            ->get(route('admin.solicitudes.show', $request))
            ->assertOk()
            ->assertSee('Solicitud visible')
            ->assertSee($request->message);
    }

    public function test_usuario_normal_no_puede_acceder_a_solicitudes(): void
    {
        $user = User::factory()->create(['is_superadmin' => false]);

        $this->actingAs($user)
            ->get(route('admin.solicitudes.index'))
            ->assertForbidden();
    }

    public function test_superadmin_puede_cambiar_a_un_estado_valido(): void
    {
        $request = $this->createRequest();

        $this->actingAs($this->superadmin())
            ->patch(route('admin.solicitudes.update', $request), [
                'status' => ContactRequestStatus::Contacted->value,
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('contact_requests', [
            'id' => $request->id,
            'status' => ContactRequestStatus::Contacted->value,
        ]);
    }

    public function test_estado_invalido_es_rechazado(): void
    {
        $request = $this->createRequest();

        $this->actingAs($this->superadmin())
            ->from(route('admin.solicitudes.show', $request))
            ->patch(route('admin.solicitudes.update', $request), [
                'status' => 'estado_inventado',
            ])
            ->assertRedirect(route('admin.solicitudes.show', $request))
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('contact_requests', [
            'id' => $request->id,
            'status' => ContactRequestStatus::Pending->value,
        ]);
    }

    public function test_filtros_por_estado_fecha_y_busqueda_funcionan(): void
    {
        $matching = $this->createRequest([
            'name' => 'María González',
            'business' => 'Comercial Norte',
            'status' => ContactRequestStatus::Contacted,
            'created_at' => '2026-08-30 10:00:00',
        ]);
        $this->createRequest([
            'name' => 'Pedro Soto',
            'email' => 'pedro@example.com',
            'status' => ContactRequestStatus::Pending,
            'created_at' => '2026-08-29 10:00:00',
        ]);

        $this->actingAs($this->superadmin())
            ->get(route('admin.solicitudes.index', [
                'status' => ContactRequestStatus::Contacted->value,
                'date' => '2026-08-30',
                'search' => 'Comercial Norte',
            ]))
            ->assertOk()
            ->assertSee($matching->name)
            ->assertDontSee('Pedro Soto');
    }

    public function test_dashboard_muestra_contador_correcto_de_pendientes(): void
    {
        $this->createRequest(['status' => ContactRequestStatus::Pending]);
        $this->createRequest(['status' => ContactRequestStatus::Pending]);
        $this->createRequest(['status' => ContactRequestStatus::Customer]);

        $this->actingAs($this->superadmin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Solicitudes pendientes')
            ->assertSee('>2</strong>', false);
    }

    public function test_solicitud_antigua_con_estado_null_usa_pendiente(): void
    {
        $request = $this->createLegacyRequest(null, ContactRequestType::Contact->value);

        $this->assertSame('Pendiente', $request->statusLabel());
        $this->assertSame('pendiente', $request->statusStyleValue());
    }

    public function test_solicitud_antigua_con_tipo_null_usa_contacto(): void
    {
        $request = $this->createLegacyRequest(ContactRequestStatus::Pending->value, null);

        $this->assertSame('Contacto', $request->typeLabel());
    }

    public function test_solicitud_normal_conserva_etiquetas_de_enum(): void
    {
        $request = $this->createRequest([
            'status' => ContactRequestStatus::DemoCreated,
            'type' => ContactRequestType::Demo,
        ]);

        $this->assertSame('Demo creada', $request->statusLabel());
        $this->assertSame('Demostración', $request->typeLabel());
    }

    public function test_listado_carga_con_solicitudes_antiguas_null(): void
    {
        $this->createLegacyRequest(null, ContactRequestType::Contact->value);
        $this->createLegacyRequest(ContactRequestStatus::Pending->value, null);

        $this->actingAs($this->superadmin())
            ->get(route('admin.solicitudes.index'))
            ->assertOk()
            ->assertSee('Pendiente')
            ->assertSee('Contacto');
    }

    public function test_detalle_carga_con_solicitud_antigua_null(): void
    {
        $request = $this->createLegacyRequest(null, null);

        $this->actingAs($this->superadmin())
            ->get(route('admin.solicitudes.show', $request))
            ->assertOk()
            ->assertSee('Pendiente')
            ->assertSee('Contacto');
    }

    private function superadmin(): User
    {
        return User::factory()->create(['is_superadmin' => true]);
    }

    private function createRequest(array $attributes = []): ContactRequest
    {
        $createdAt = $attributes['created_at'] ?? null;
        unset($attributes['created_at']);

        $request = ContactRequest::create(array_merge([
            'name' => 'Persona de prueba',
            'email' => 'persona@example.com',
            'phone' => null,
            'business' => 'Negocio de prueba',
            'contact' => 'persona@example.com',
            'message' => 'Mensaje completo enviado desde el sitio público.',
            'type' => ContactRequestType::Demo,
            'status' => ContactRequestStatus::Pending,
        ], $attributes));

        if ($createdAt) {
            $request->timestamps = false;
            $request->forceFill([
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ])->saveQuietly();
        }

        return $request;
    }

    private function createLegacyRequest(
        ?string $status,
        ?string $type
    ): ContactRequest {
        $id = DB::table('contact_requests')->insertGetId([
            'name' => 'Solicitud antigua',
            'email' => 'antigua@example.com',
            'phone' => null,
            'business' => null,
            'contact' => 'antigua@example.com',
            'message' => 'Solicitud anterior a la normalización de datos.',
            'type' => $type,
            'status' => $status,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return ContactRequest::findOrFail($id);
    }
}
