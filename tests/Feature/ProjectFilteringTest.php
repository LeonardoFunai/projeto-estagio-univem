<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Projeto;

class ProjectFilteringTest extends TestCase
{
    use RefreshDatabase; // Reseta o banco de dados a cada teste

    /** @test */
    public function napex_can_filter_projects_by_status()
    {
        // 1. Arrange (Preparar o cenário)
        $napexUser = User::factory()->create(['role' => 'napex']);

        // Cria dois projetos com status diferentes
        Projeto::factory()->create(['titulo' => 'Projeto que deve aparecer', 'status' => 'entregue']);
        Projeto::factory()->create(['titulo' => 'Projeto que NAO deve aparecer', 'status' => 'editando']);

        // 2. Act (Agir)
        // Simula o login do usuário NAPEX e o acesso à URL com o filtro de status
        $response = $this->actingAs($napexUser)
                         ->get(route('projetos.index', ['status' => 'entregue']));

        // 3. Assert (Verificar)
        $response->assertStatus(200); // Garante que a página carregou
        $response->assertSee('Projeto que deve aparecer'); // Verifica se o projeto correto está na página
        $response->assertDontSee('Projeto que NAO deve aparecer'); // Verifica se o projeto incorreto NÃO está na página
    }
}