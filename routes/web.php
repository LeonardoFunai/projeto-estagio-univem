<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjetoController;
use App\Http\Controllers\ProfileController;

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
    
    // 📄 Exportar relatório em PDF (visível só para NAPEx e Coordenação)
    Route::get('/projetos/pdf', [ProjetoController::class, 'exportarPdf'])->name('projetos.exportarPdf');
    
    Route::get('/projetos/{id}', [ProjetoController::class, 'show'])->name('projetos.show');
    Route::delete('/projetos/{id}', [ProjetoController::class, 'destroy'])->name('projetos.destroy');
    Route::get('/projetos/{id}/edit', [ProjetoController::class, 'edit'])->name('projetos.edit');
    Route::put('/projetos/{id}', [ProjetoController::class, 'update'])->name('projetos.update');
    Route::get('/projetos/{id}/arquivo', [ProjetoController::class, 'downloadArquivo'])->name('projetos.download');
    



    // 📤 Fluxo de envio, edição e parecer
    Route::post('/projetos/{id}/enviar', [ProjetoController::class, 'enviarProjeto'])->name('projetos.enviar');
    Route::post('/projetos/{id}/voltar', [ProjetoController::class, 'voltarParaEdicao'])->name('projetos.voltar');
    Route::post('/projetos/{id}/parecer', [ProjetoController::class, 'darParecer'])->name('projetos.parecer');

    // 📝 Novas rotas de avaliação específicas para show.blade.php
    Route::post('/projetos/{id}/avaliar-napex', [ProjetoController::class, 'avaliarNapex'])->name('projetos.avaliar.napex');
    Route::post('/projetos/{id}/avaliar-coordenador', [ProjetoController::class, 'avaliarCoordenador'])->name('projetos.avaliar.coordenador');

    // 📄 Gerar proposta pdf
    Route::get('/projetos/{id}/gerar-pdf', [ProjetoController::class, 'gerarPdf'])->name('projetos.gerarPdf');


});

// Inclui rotas de login/register do Breeze
require __DIR__.'/auth.php';
