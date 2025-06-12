<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Chama outros seeders que você criou.
        $this->call([
            CursoSeeder::class,
            // Exemplo: você poderia adicionar ProfessorSeeder::class aqui no futuro.
        ]);

        // 2. Mantém a criação do seu usuário de teste que já existia.
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}