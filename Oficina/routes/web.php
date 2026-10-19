<?php

use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\VeiculosController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\VerificarAdministrador;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    Route::middleware(VerificarAdministrador::class)->group(function () {
        Route::resource('categorias', CategoriaController::class);
        Route::resource('produtos', ProdutoController::class);

        Route::resource('veiculos', VeiculosController::class)
            ->only(['edit', 'update', 'destroy']);
    });

    Route::resource('veiculos', VeiculosController::class)
        ->only(['index', 'create', 'store', 'show']);
});

require __DIR__.'/auth.php';
