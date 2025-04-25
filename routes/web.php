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
    Route::get('/projetos/{id}', [ProjetoController::class, 'show'])->name('projetos.show');
    Route::delete('/projetos/{id}', [ProjetoController::class, 'destroy'])->name('projetos.destroy');
    Route::get('/projetos/{id}/edit', [ProjetoController::class, 'edit'])->name('projetos.edit');
    Route::put('/projetos/{id}', [ProjetoController::class, 'update'])->name('projetos.update');
    Route::get('/projetos/{id}/arquivo', [ProjetoController::class, 'downloadArquivo'])->name('projetos.download');
});

// Inclui rotas de login/register do Breeze
require __DIR__.'/auth.php';
