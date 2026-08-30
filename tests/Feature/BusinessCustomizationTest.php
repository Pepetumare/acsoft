<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Modulo;
use App\Models\Negocio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BusinessCustomizationTest extends TestCase
{
    use RefreshDatabase;

    private Negocio $business;
    private Negocio $otherBusiness;
    private User $admin;
    private User $user;
    private User $otherAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $client = Cliente::create([
            'nombre' => 'Cliente personalización',
            'activo' => true,
        ]);

        $this->business = $this->createBusiness($client->id, 'Negocio Rojo', 'negocio-rojo');
        $this->otherBusiness = $this->createBusiness($client->id, 'Negocio Verde', 'negocio-verde');

        $salesModule = Modulo::create([
            'nombre' => 'Ventas',
            'slug' => 'ventas',
            'activo' => true,
        ]);
        $this->business->modulos()->attach($salesModule->id, ['activo' => true]);
        $this->otherBusiness->modulos()->attach($salesModule->id, ['activo' => true]);

        $this->admin = $this->createUserFor($this->business, 'admin');
        $this->user = $this->createUserFor($this->business, 'usuario');
        $this->otherAdmin = $this->createUserFor($this->otherBusiness, 'admin');
    }

    public function test_admin_accede_a_personalizacion(): void
    {
        $this->actingAs($this->admin)
            ->get(route('gestion.personalizacion.edit', $this->business))
            ->assertOk()
            ->assertSeeText('Personalización del negocio')
            ->assertSeeText('Vista previa');
    }

    public function test_usuario_normal_no_puede_modificar_personalizacion(): void
    {
        $this->actingAs($this->user)
            ->patch(route('gestion.personalizacion.update', $this->business), [
                'color_primario' => '#AA0000',
                'color_secundario' => '#550000',
            ])
            ->assertForbidden();

        $this->assertNull($this->business->fresh()->color_primario);
    }

    public function test_admin_guarda_colores_principal_y_secundario(): void
    {
        $this->updateCustomization($this->admin, $this->business, '#AA0000', '#550000')
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('negocios', [
            'id' => $this->business->id,
            'color_primario' => '#AA0000',
            'color_secundario' => '#550000',
        ]);
    }

    public function test_admin_sube_logo_valido_con_nombre_generado(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin)
            ->patch(route('gestion.personalizacion.update', $this->business), [
                'color_primario' => '#0F2744',
                'color_secundario' => '#163A63',
                'logo' => UploadedFile::fake()->image('marca.png', 300, 120),
            ])
            ->assertSessionHasNoErrors();

        $path = $this->business->fresh()->logo;
        $this->assertNotNull($path);
        $this->assertStringStartsWith('business-logos/'.$this->business->id.'/', $path);
        $this->assertStringNotContainsString('marca.png', $path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_logo_svg_y_archivo_disfrazado_son_rechazados(): void
    {
        Storage::fake('public');

        $svg = UploadedFile::fake()->createWithContent(
            'logo.svg',
            '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'
        );

        $this->actingAs($this->admin)
            ->patch(route('gestion.personalizacion.update', $this->business), [
                'color_primario' => '#0F2744',
                'color_secundario' => '#163A63',
                'logo' => $svg,
            ])
            ->assertSessionHasErrors('logo');

        $this->assertNull($this->business->fresh()->logo);
        Storage::disk('public')->assertDirectoryEmpty('business-logos');
    }

    public function test_color_invalido_es_rechazado(): void
    {
        $this->actingAs($this->admin)
            ->patch(route('gestion.personalizacion.update', $this->business), [
                'color_primario' => 'javascript:alert(1)',
                'color_secundario' => 'rgb(0,0,0)',
            ])
            ->assertSessionHasErrors([
                'color_primario',
                'color_secundario',
            ]);

        $this->assertNull($this->business->fresh()->color_primario);
    }

    public function test_negocios_mantienen_personalizaciones_independientes(): void
    {
        $this->updateCustomization($this->admin, $this->business, '#AA0000', '#660000');
        $this->updateCustomization($this->otherAdmin, $this->otherBusiness, '#008800', '#005500');

        $this->assertSame('#AA0000', $this->business->fresh()->color_primario);
        $this->assertSame('#008800', $this->otherBusiness->fresh()->color_primario);

        $this->actingAs($this->admin)
            ->get(route('gestion.dashboard', $this->business))
            ->assertSee('--business-primary: #AA0000', false)
            ->assertDontSee('--business-primary: #008800', false);
    }

    public function test_restaurar_colores_no_elimina_logo(): void
    {
        $this->business->update([
            'color_primario' => '#AA0000',
            'color_secundario' => '#550000',
            'logo' => 'business-logos/'.$this->business->id.'/logo.png',
        ]);

        $this->actingAs($this->admin)
            ->delete(route('gestion.personalizacion.colors.destroy', $this->business))
            ->assertSessionHasNoErrors();

        $business = $this->business->fresh();
        $this->assertNull($business->color_primario);
        $this->assertNull($business->color_secundario);
        $this->assertNotNull($business->logo);
        $this->assertSame(Negocio::COLOR_PRIMARIO_DEFAULT, $business->colorPrimario());
    }

    public function test_eliminar_logo_borra_solo_el_archivo_del_negocio(): void
    {
        Storage::fake('public');
        $ownPath = 'business-logos/'.$this->business->id.'/logo.png';
        $otherPath = 'business-logos/'.$this->otherBusiness->id.'/logo.png';
        Storage::disk('public')->put($ownPath, 'propio');
        Storage::disk('public')->put($otherPath, 'ajeno');
        $this->business->update(['logo' => $ownPath]);

        $this->actingAs($this->admin)
            ->delete(route('gestion.personalizacion.logo.destroy', $this->business))
            ->assertSessionHasNoErrors();

        $this->assertNull($this->business->fresh()->logo);
        Storage::disk('public')->assertMissing($ownPath);
        Storage::disk('public')->assertExists($otherPath);
    }

    public function test_color_claro_elige_texto_oscuro_para_contraste(): void
    {
        $this->updateCustomization($this->admin, $this->business, '#FFFFFF', '#EEEEEE');

        $this->actingAs($this->admin)
            ->get(route('gestion.dashboard', $this->business))
            ->assertOk()
            ->assertSee('--business-primary-contrast: #111827', false)
            ->assertSee('--business-secondary-contrast: #111827', false);
    }

    public function test_boleta_muestra_logo_del_negocio(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put(
            'business-logos/'.$this->business->id.'/logo.png',
            'contenido'
        );
        $this->business->update([
            'logo' => 'business-logos/'.$this->business->id.'/logo.png',
        ]);
        $sale = $this->createHistoricalSale($this->business, $this->admin);

        $this->actingAs($this->admin)
            ->get(route('gestion.ventas.receipt', [$this->business, $sale]))
            ->assertOk()
            ->assertSee($this->business->fresh()->logoUrl(), false)
            ->assertSee('alt="Logo de '.$this->business->nombre.'"', false);
    }

    public function test_boleta_sin_logo_mantiene_fallback_con_nombre(): void
    {
        $sale = $this->createHistoricalSale($this->business, $this->admin);

        $this->actingAs($this->admin)
            ->get(route('gestion.ventas.receipt', [$this->business, $sale]))
            ->assertOk()
            ->assertSeeText($this->business->nombre)
            ->assertDontSee('class="receipt-logo"', false);
    }

    public function test_acceso_cross_tenant_es_bloqueado(): void
    {
        $this->actingAs($this->otherAdmin)
            ->get(route('gestion.personalizacion.edit', $this->business))
            ->assertForbidden();

        $this->actingAs($this->otherAdmin)
            ->patch(route('gestion.personalizacion.update', $this->business), [
                'color_primario' => '#000000',
                'color_secundario' => '#111111',
            ])
            ->assertForbidden();
    }

    private function createBusiness(int $clientId, string $name, string $slug): Negocio
    {
        return Negocio::create([
            'cliente_id' => $clientId,
            'nombre' => $name,
            'slug' => $slug,
            'activo' => true,
        ]);
    }

    private function createUserFor(Negocio $business, string $role): User
    {
        $user = User::factory()->create(['is_superadmin' => false]);
        $user->negocios()->attach($business->id, [
            'rol' => $role,
            'activo' => true,
        ]);

        return $user;
    }

    private function updateCustomization(
        User $user,
        Negocio $business,
        string $primary,
        string $secondary
    ) {
        return $this->actingAs($user)
            ->patch(route('gestion.personalizacion.update', $business), [
                'color_primario' => $primary,
                'color_secundario' => $secondary,
            ]);
    }

    private function createHistoricalSale(Negocio $business, User $user)
    {
        $sale = $business->ventas()->create([
            'user_id' => $user->id,
            'fecha' => now()->toDateString(),
            'total' => 1000,
            'metodo_pago' => 'Efectivo',
        ]);
        $sale->detalles()->create([
            'descripcion' => 'Producto de prueba',
            'cantidad' => 1,
            'precio_unitario' => 1000,
            'subtotal' => 1000,
        ]);

        return $sale;
    }
}
