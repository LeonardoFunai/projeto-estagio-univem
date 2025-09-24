<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;


use App\Models\Curso;


class CursoSeeder extends Seeder
{
    public function run(): void
    {
        $cursos = [
            ['nome' => 'Administração'],
            ['nome' => 'Ciência da Computação'],
            ['nome' => 'Ciências Contábeis'],
            ['nome' => 'Design Gráfico'],
            ['nome' => 'Direito'],
            ['nome' => 'Engenharia de Produção'],
            ['nome' => 'Sistemas de Informação'],
        ];

        foreach ($cursos as $curso) {
            Curso::create($curso);
        }
    }
}