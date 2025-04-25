<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjetoController;

// Redireciona para a página de projetos
Route::get('/', function () {
    return redirect()->route('projetos.index');
});

// Exibe a lista de projetos
Route::get('/projetos', [ProjetoController::class, 'index'])->name('projetos.index');

// Exibe o formulário para criar um projeto
Route::get('/projetos/create', [ProjetoController::class, 'create'])->name('projetos.create');

// Recebe os dados do formulário e cria um novo projeto
Route::post('/projetos', [ProjetoController::class, 'store'])->name('projetos.store');

// Recebe o id da linha e mostra os dados na tela show
Route::get('/projetos/{id}', [ProjetoController::class, 'show'])->name('projetos.show');

//excluir linha
Route::delete('/projetos/{id}', [ProjetoController::class, 'destroy'])->name('projetos.destroy');

//editar linha
Route::get('/projetos/{id}/edit', [ProjetoController::class, 'edit'])->name('projetos.edit');


Route::put('/projetos/{id}', [ProjetoController::class, 'update'])->name('projetos.update');

Route::get('/projetos/{id}/arquivo', [ProjetoController::class, 'downloadArquivo'])->name('projetos.download');
