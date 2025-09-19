<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User; // Importar o modelo User

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Projeto>
 */
class ProjetoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // Associa o projeto a um usuário existente ou cria um novo
            'user_id' => User::factory(), 
            'titulo' => $this->faker->sentence(6),
            'area_conhecimento' => 'Ciências Exatas',
            'curso_proponente' => 'Ciência da Computação',
            'resumo' => $this->faker->paragraph(3),
            'introducao' => $this->faker->paragraph(5),
            'justificativa' => $this->faker->paragraph(4),
            'fundamentacao' => $this->faker->paragraph(5),
            'objetivo_geral' => $this->faker->sentence(10),
            'metodologia' => $this->faker->paragraph(5),
            'referencias' => $this->faker->sentence(5),
            'data_inicio' => $this->faker->date(),
            'data_fim' => $this->faker->date(),
            'carga_horaria_semanal' => $this->faker->randomElement([4, 6, 8, 10]),
            'status' => 'editando', // Um status padrão
            'etapa' => 'Proposta', // Uma etapa padrão
        ];
    }
}