<?php

namespace Tests\Feature;

use App\Models\Cliente;
use App\Models\Modulo;
use App\Models\Negocio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BusinessUserManagementTest extends TestCase
{
    use RefreshDatabase;

    private Negocio $negocio;
    private Negocio $otroNegocio;
    private User $admin;
    private User $usuario;

    protected function setUp(): void
    {
        parent::setUp();

        $cliente = Cliente::create(['nombre' => 'Cliente usuarios', 'activo' => true]);
        $this->negocio = Negocio::create([
            'cliente_id' => $cliente->id,
            'nombre' => 'Negocio principal',
            'slug' => 'negocio-principal',
            'activo' => true,
        ]);
        $this->otroNegocio = Negocio::create([
            'cliente_id' => $cliente->id,
            'nombre' => 'Otro negocio',
            'slug' => 'otro-negocio',
            'activo' => true,
        ]);

        $ventas = Modulo::create([
            'nombre' => 'Ventas',
            'slug' => 'ventas',
            'ruta' => 'gestion.ventas.index',
            'activo' => true,
        ]);
        $this->negocio->modulos()->attach($ventas->id, ['activo' => true]);

        $this->admin = User::factory()->create(['is_superadmin' => false]);
        $this->usuario = User::factory()->create(['is_superadmin' => false]);
        $this->attach($this->negocio, $this->admin, 'admin');
        $this->attach($this->negocio, $this->usuario, 'usuario');
    }

    public function test_admin_puede_ver_solo_usuarios_de_su_negocio(): void
    {
        $externo = User::factory()->create();
        $this->attach($this->otroNegocio, $externo, 'usuario');

        $this->actingAs($this->admin)
            ->get(route('gestion.usuarios.index', $this->negocio))
            ->assertOk()
            ->assertSee($this->usuario->email)
            ->assertDontSee($externo->email);
    }

    public function test_usuario_normal_recibe_403(): void
    {
        $this->actingAs($this->usuario)
            ->get(route('gestion.usuarios.index', $this->negocio))
            ->assertForbidden();
    }

    public function test_admin_crea_usuario_nuevo_con_password_hasheada(): void
    {
        $this->actingAs($this->admin)
            ->post(route('gestion.usuarios.store', $this->negocio), [
                'name' => 'Nueva Persona',
                'email' => 'nueva@example.com',
                'password' => 'ClaveSegura123',
                'password_confirmation' => 'ClaveSegura123',
                'rol' => 'usuario',
            ])->assertRedirect(route('gestion.usuarios.index', $this->negocio));

        $nuevo = User::where('email', 'nueva@example.com')->firstOrFail();
        $this->assertTrue(Hash::check('ClaveSegura123', $nuevo->password));
        $this->assertDatabaseHas('negocio_user', [
            'negocio_id' => $this->negocio->id,
            'user_id' => $nuevo->id,
            'rol' => 'usuario',
            'activo' => true,
        ]);
    }

    public function test_email_existente_se_asocia_sin_duplicar_user(): void
    {
        $existente = User::factory()->create(['email' => 'existente@example.com']);
        $cantidad = User::count();

        $this->actingAs($this->admin)
            ->post(route('gestion.usuarios.store', $this->negocio), [
                'name' => 'Nombre ignorado',
                'email' => 'existente@example.com',
                'password' => 'ClaveSegura123',
                'password_confirmation' => 'ClaveSegura123',
                'rol' => 'usuario',
            ])->assertRedirect();

        $this->assertSame($cantidad, User::count());
        $this->assertDatabaseHas('negocio_user', [
            'negocio_id' => $this->negocio->id,
            'user_id' => $existente->id,
        ]);
    }

    public function test_no_duplica_relacion_si_usuario_ya_pertenece_al_negocio(): void
    {
        $this->actingAs($this->admin)
            ->from(route('gestion.usuarios.create', $this->negocio))
            ->post(route('gestion.usuarios.store', $this->negocio), [
                'name' => $this->usuario->name,
                'email' => $this->usuario->email,
                'password' => 'ClaveSegura123',
                'password_confirmation' => 'ClaveSegura123',
                'rol' => 'usuario',
            ])->assertSessionHasErrors('email');

        $this->assertDatabaseCount('negocio_user', 2);
    }

    public function test_admin_no_puede_tocar_usuario_de_otro_negocio(): void
    {
        $externo = User::factory()->create();
        $this->attach($this->otroNegocio, $externo, 'usuario');

        $this->actingAs($this->admin)
            ->get(route('gestion.usuarios.edit', [$this->negocio, $externo]))
            ->assertForbidden();
        $this->actingAs($this->admin)
            ->put(route('gestion.usuarios.update', [$this->negocio, $externo]), [
                'name' => 'Alterado', 'rol' => 'admin', 'activo' => 1,
            ])->assertForbidden();
        $this->actingAs($this->admin)
            ->delete(route('gestion.usuarios.destroy', [$this->negocio, $externo]))
            ->assertForbidden();
    }

    public function test_no_puede_crear_rol_superadmin(): void
    {
        $this->actingAs($this->admin)
            ->post(route('gestion.usuarios.store', $this->negocio), [
                'name' => 'Intento',
                'email' => 'intento@example.com',
                'password' => 'ClaveSegura123',
                'password_confirmation' => 'ClaveSegura123',
                'rol' => 'superadmin',
            ])->assertSessionHasErrors('rol');

        $this->assertDatabaseMissing('users', ['email' => 'intento@example.com']);
    }

    public function test_no_puede_dejar_negocio_sin_admin_activo(): void
    {
        $this->actingAs($this->admin)
            ->put(route('gestion.usuarios.update', [$this->negocio, $this->admin]), [
                'name' => $this->admin->name,
                'rol' => 'usuario',
                'activo' => 1,
            ])->assertSessionHasErrors('rol');

        $this->actingAs($this->admin)
            ->delete(route('gestion.usuarios.destroy', [$this->negocio, $this->admin]))
            ->assertSessionHasErrors('rol');

        $this->assertDatabaseHas('negocio_user', [
            'negocio_id' => $this->negocio->id,
            'user_id' => $this->admin->id,
            'rol' => 'admin',
            'activo' => true,
        ]);
    }

    public function test_desactivacion_funciona_si_permanece_otro_admin(): void
    {
        $segundoAdmin = User::factory()->create();
        $this->attach($this->negocio, $segundoAdmin, 'admin');

        $this->actingAs($this->admin)
            ->put(route('gestion.usuarios.update', [$this->negocio, $segundoAdmin]), [
                'name' => $segundoAdmin->name,
                'rol' => 'admin',
                'activo' => 0,
            ])->assertRedirect();

        $this->assertDatabaseHas('negocio_user', [
            'negocio_id' => $this->negocio->id,
            'user_id' => $segundoAdmin->id,
            'activo' => false,
        ]);
    }

    public function test_quitar_relacion_no_borra_usuario_global_de_otros_negocios(): void
    {
        $this->attach($this->otroNegocio, $this->usuario, 'usuario');

        $this->actingAs($this->admin)
            ->delete(route('gestion.usuarios.destroy', [$this->negocio, $this->usuario]))
            ->assertRedirect();

        $this->assertDatabaseHas('users', ['id' => $this->usuario->id]);
        $this->assertDatabaseHas('negocio_user', [
            'negocio_id' => $this->otroNegocio->id,
            'user_id' => $this->usuario->id,
        ]);
    }

    public function test_rol_usuario_mantiene_acceso_a_ventas(): void
    {
        $this->actingAs($this->usuario)
            ->get(route('gestion.ventas.index', $this->negocio))
            ->assertOk();
    }

    private function attach(Negocio $negocio, User $user, string $rol): void
    {
        $negocio->usuarios()->attach($user->id, ['rol' => $rol, 'activo' => true]);
    }
}
