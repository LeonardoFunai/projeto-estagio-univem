<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjetoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResultadoController;
use App\Http\Controllers\Admin\UserController; 
use App\Http\Controllers\NotificationController;

// Redireciona a raiz para a tela de login
Route::get('/', function () {
    if (auth()->check()) {
        return redirect('/projetos'); // se estiver logado, manda para projetos
    }
    return redirect('/login'); // se não, manda para login
});

// Área logada
Route::middleware('auth')->group(function () {
    // Rotas de perfil do usuário (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // 🔒 ROTAS DOS PROJETOS (acessíveis apenas logado)
    Route::get('/projetos', [ProjetoController::class, 'index'])->name('projetos.index');
    Route::get('/projetos/create', [ProjetoController::class, 'create'])->name('projetos.create');
    Route::post('/projetos', [ProjetoController::class, 'store'])->name('projetos.store');
    Route::get('/users/search', [ProjetoController::class, 'searchUsers'])->name('users.search');
    
    // 📄 Exportar relatório em PDF (visível só para NAPEx e Coordenação)
    Route::get('/projetos/pdf', [ProjetoController::class, 'exportarPdf'])->name('projetos.exportarPdf');
    
    Route::get('/projetos/{id}', [ProjetoController::class, 'show'])->name('projetos.show');
    Route::delete('/projetos/{id}', [ProjetoController::class, 'destroy'])->name('projetos.destroy');
    Route::get('/projetos/{id}/edit', [ProjetoController::class, 'edit'])->name('projetos.edit');
    Route::put('/projetos/{id}', [ProjetoController::class, 'update'])->name('projetos.update');
    Route::get('/projetos/{id}/arquivo', [ProjetoController::class, 'downloadArquivo'])->name('projetos.download');
    
    /*
    |--------------------------------------------------------------------------
    | Rotas de Resultados 
    |--------------------------------------------------------------------------
    */
    Route::get('/projetos/{projeto}/resultados/create', [ResultadoController::class, 'create'])->name('resultados.create');
    Route::post('/projetos/{projeto}/resultados', [ResultadoController::class, 'store'])->name('resultados.store');
    Route::get('/resultados/{resultado}', [ResultadoController::class, 'show'])->name('resultados.show');
    Route::get('/resultados/{resultado}/edit', [ResultadoController::class, 'edit'])->name('resultados.edit');
    Route::put('/resultados/{resultado}', [ResultadoController::class, 'update'])->name('resultados.update');
    Route::post('/resultados/{resultado}/enviar', [ResultadoController::class, 'enviar'])->name('resultados.enviar');
    Route::post('/resultados/{resultado}/voltar-rascunho', [ResultadoController::class, 'voltarParaRascunho'])->name('resultados.voltarParaRascunho');
    Route::post('/resultados/{resultado}/avaliar', [ResultadoController::class, 'avaliar'])->name('resultados.avaliar');

    Route::get('/resultados/{resultado}/gerar-pdf', [ResultadoController::class, 'gerarPdf'])->name('resultados.gerarPdf');


    // 📤 Fluxo de envio, edição e parecer
    Route::post('/projetos/{id}/enviar', [ProjetoController::class, 'enviarProjeto'])->name('projetos.enviar');
    Route::post('/projetos/{id}/voltar', [ProjetoController::class, 'voltarParaEdicao'])->name('projetos.voltar');
    Route::post('/projetos/{id}/parecer', [ProjetoController::class, 'darParecer'])->name('projetos.parecer');

    // 📝 Novas rotas de avaliação específicas para show.blade.php
    Route::post('/projetos/{id}/avaliar-napex', [ProjetoController::class, 'avaliarNapex'])->name('projetos.avaliar.napex');
    Route::post('/projetos/{id}/avaliar-coordenador', [ProjetoController::class, 'avaliarCoordenador'])->name('projetos.avaliar.coordenador');

    // 📄 Gerar proposta pdf
    Route::get('/projetos/{id}/gerar-pdf', [ProjetoController::class, 'gerarPdf'])->name('projetos.gerarPdf');

    Route::get('/projetos/{projeto}/logs/pdf', [ProjetoController::class, 'exportarLogPdf'])->name('projetos.exportarLogPdf');

    Route::get('/notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
});


Route::middleware(['auth', 'role:admin,napex,coordenador'])->prefix('admin')->name('admin.')->group(function () {
    
    // --- CORREÇÃO AQUI ---
    // Rotas específicas devem vir ANTES do Route::resource
    Route::get('users/import', [UserController::class, 'showImportForm'])->name('users.showImportForm');
    Route::post('users/import', [UserController::class, 'import'])->name('users.import');

    Route::resource('users', UserController::class);
});


// Inclui rotas de login/register do Breeze
require __DIR__.'/auth.php';