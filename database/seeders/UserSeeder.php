<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Curso; // 1. Importar o Model de Curso

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Usuário Administrador Principal
        User::firstOrCreate(
            ['email' => 'admin@univem.edu.br'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('123'),
                'role' => 'admin',
            ]
        );

        // Usuário do NAPEX
        User::firstOrCreate(
            ['email' => 'napex@univem.edu.br'],
            [
                'name' => 'NAPEX Univem',
                'password' => Hash::make('123'),
                'role' => 'napex',
            ]
        );

        // --- Coordenadores ---
        // 2. Criar os coordenadores e depois associar os cursos

        // Coordenador de Administração
        $coordAdm = User::firstOrCreate(
            ['email' => 'leandrotenorio@univem.edu.br'],
            [
                'name' => 'Prof. Esp. Leandro Machado Tenório',
                'password' => Hash::make('123'),
                'role' => 'coordenador', // Role genérica
            ]
        );
        $cursoAdm = Curso::where('nome', 'Administração')->first();
        if ($cursoAdm) {
            $coordAdm->cursosCoordenados()->syncWithoutDetaching([$cursoAdm->id]);
        }
        
        // Coordenador de Ciências Contábeis
        $coordContabeis = User::firstOrCreate(
            ['email' => 'breda@univem.edu.br'],
            [
                'name' => 'Prof. Esp. Luis Otavio Simões',
                'password' => Hash::make('123'),
                'role' => 'coordenador',
            ]
        );
        $cursoContabeis = Curso::where('nome', 'Ciências Contábeis')->first();
        if ($cursoContabeis) {
            $coordContabeis->cursosCoordenados()->syncWithoutDetaching([$cursoContabeis->id]);
        }

        // Coordenador de CC e SI
        $coordCcSi = User::firstOrCreate(
            ['email' => 'everton.simoes@univem.edu.br'],
            [
                'name' => 'Prof. Ms. Everton Simões da Motta',
                'password' => Hash::make('123'),
                'role' => 'coordenador',
            ]
        );
        $cursoCc = Curso::where('nome', 'Ciência da Computação')->first();
        $cursoSi = Curso::where('nome', 'Sistemas de Informação')->first();
        $cursosParaSincronizar = [];
        if ($cursoCc) $cursosParaSincronizar[] = $cursoCc->id;
        if ($cursoSi) $cursosParaSincronizar[] = $cursoSi->id;
        if (!empty($cursosParaSincronizar)) {
            $coordCcSi->cursosCoordenados()->syncWithoutDetaching($cursosParaSincronizar);
        }

        // Coordenador de Design Gráfico
        $coordDesign = User::firstOrCreate(
            ['email' => 'bertolini@univem.edu.br'],
            [
                'name' => 'Prof. Esp. Rogério Garrido Bertolini',
                'password' => Hash::make('123'),
                'role' => 'coordenador',
            ]
        );
        $cursoDesign = Curso::where('nome', 'Design Gráfico')->first();
        if ($cursoDesign) {
            $coordDesign->cursosCoordenados()->syncWithoutDetaching([$cursoDesign->id]);
        }

        // Coordenador de Direito
        $coordDireito = User::firstOrCreate(
            ['email' => 'teofilo@univem.edu.br'],
            [
                'name' => 'Prof. Dr. Teófilo Marcelo de Arêa Leão Junior',
                'password' => Hash::make('123'),
                'role' => 'coordenador',
            ]
        );
        $cursoDireito = Curso::where('nome', 'Direito')->first();
        if ($cursoDireito) {
            $coordDireito->cursosCoordenados()->syncWithoutDetaching([$cursoDireito->id]);
        }

        // Coordenador de Engenharia de Produção
        $coordProducao = User::firstOrCreate(
            ['email' => 'vania@univem.edu.br'],
            [
                'name' => 'Profa. Dra. Vânia Erica Herrera',
                'password' => Hash::make('123'),
                'role' => 'coordenador',
            ]
        );
        $cursoProducao = Curso::where('nome', 'Engenharia de Produção')->first();
        if ($cursoProducao) {
            $coordProducao->cursosCoordenados()->syncWithoutDetaching([$cursoProducao->id]);
        }
    }
}