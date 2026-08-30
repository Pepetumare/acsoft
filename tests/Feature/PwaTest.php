<?php

namespace Tests\Feature;

use Tests\TestCase;

class PwaTest extends TestCase
{
    public function test_manifest_es_accesible_y_apunta_al_login(): void
    {
        $response = $this->get(route('pwa.manifest'));

        $response
            ->assertOk()
            ->assertHeader('Content-Type', 'application/manifest+json');

        $manifest = json_decode($response->getContent(), true);

        $this->assertSame('ACSoft', $manifest['name']);
        $this->assertSame('ACSoft', $manifest['short_name']);
        $this->assertSame('/login', $manifest['start_url']);
        $this->assertSame('/', $manifest['scope']);
        $this->assertSame('standalone', $manifest['display']);
        $this->assertNotEmpty($manifest['icons']);
    }

    public function test_service_worker_publico_es_accesible(): void
    {
        $this->get(route('pwa.service-worker'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/javascript')
            ->assertSee('/admin')
            ->assertSee("request.method !== 'GET'", false);
    }

    public function test_login_sigue_siendopublico_y_contiene_formulario(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('action="' . route('login.store') . '"', false)
            ->assertSee('name="_token"', false)
            ->assertSee('Instalar ACSoft');
    }

    public function test_rutas_pwa_publicas_no_requieren_autenticacion(): void
    {
        $this->get(route('login'))->assertOk();
        $this->get(route('pwa.manifest'))->assertOk();
        $this->get(route('pwa.service-worker'))->assertOk();
    }
}
