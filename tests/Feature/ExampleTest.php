<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_redirects_to_login(): void
{
    $response = $this->get('/');

    // Verifica que redireciona com status 302
    $response->assertStatus(302);

    // Verifica que o redirecionamento é para a rota 'filament.admin.login'
    $response->assertRedirect(route('filament.admin.auth.login'));
}

}
