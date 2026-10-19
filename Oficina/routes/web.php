<?php

use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\VeiculosController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Página inicial.
Route::get('/', function () {
    return view('welcome');
});

// Painel do usuário conectado.
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Páginas que exigem login.
Route::middleware('auth')->group(function () {

    // Rotas dos cadastros da oficina.
    Route::resource('categorias', CategoriaController::class);
    Route::resource('produtos', ProdutoController::class);
    Route::resource('veiculos', VeiculosController::class);

    // Rotas do perfil.
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

// Rotas de autenticação do Breeze.
require __DIR__.'/auth.php';
