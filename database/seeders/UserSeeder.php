<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

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

        // --- Coordenadores por Curso ---

        // Coordenador de Administração
        User::firstOrCreate(
            ['email' => 'coordenador.adm@univem.edu.br'],
            [
                'name' => 'Nome do Coordenador de Administração',
                'password' => Hash::make('123'),
                'role' => 'coordenador_adm',
            ]
        );

        // Coordenador de Ciência da Computação
        User::firstOrCreate(
            ['email' => 'coordenador.cc@univem.edu.br'],
            [
                'name' => 'Nome do Coordenador de Ciência da Computação',
                'password' => Hash::make('123'),
                'role' => 'coordenador_cc',
            ]
        );

        // Coordenador de Ciências Contábeis
        User::firstOrCreate(
            ['email' => 'coordenador.contabeis@univem.edu.br'],
            [
                'name' => 'Nome do Coordenador de Ciências Contábeis',
                'password' => Hash::make('123'),
                'role' => 'coordenador_contabeis',
            ]
        );

        // Coordenador de Design Gráfico
        User::firstOrCreate(
            ['email' => 'coordenador.design@univem.edu.br'],
            [
                'name' => 'Nome do Coordenador de Design Gráfico',
                'password' => Hash::make('123'),
                'role' => 'coordenador_design',
            ]
        );

        // Coordenador de Direito
        User::firstOrCreate(
            ['email' => 'coordenador.direito@univem.edu.br'],
            [
                'name' => 'Nome do Coordenador de Direito',
                'password' => Hash::make('123'),
                'role' => 'coordenador_direito',
            ]
        );

        // Coordenador de Engenharia de Produção
        User::firstOrCreate(
            ['email' => 'coordenador.producao@univem.edu.br'],
            [
                'name' => 'Nome do Coordenador de Engenharia de Produção',
                'password' => Hash::make('123'),
                'role' => 'coordenador_producao',
            ]
        );
        
        // Coordenador de Sistemas de Informação
        User::firstOrCreate(
            ['email' => 'coordenador.si@univem.edu.br'],
            [
                'name' => 'Nome do Coordenador de Sistemas de Informação',
                'password' => Hash::make('123'),
                'role' => 'coordenador_si',
            ]
        );
    }
}