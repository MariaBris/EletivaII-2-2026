<?php

use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\VeiculosController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServicoController;
use App\Http\Controllers\PedidoController;
use App\Http\Middleware\VerificarAdministrador;
use App\Models\Produto;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $produtosEstoqueBaixo = null;

    if (in_array(Auth::user()->role, ['ADM', 'FUNC'], true)) {
        $produtosEstoqueBaixo = Produto::whereColumn(
            'estoque',
            '<=',
            'estoque_minimo'
        )->orderBy('estoque')
            ->orderBy('nome')
            ->orderBy('id')
            ->paginate(10, ['*'], 'estoque_page')
            ->withQueryString();
    }

    return view('dashboard', compact('produtosEstoqueBaixo'));
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
        Route::resource('servicos', ServicoController::class);
    });

    Route::resource('veiculos', VeiculosController::class);

    Route::get('/pedidos/{id}/imprimir', [PedidoController::class, 'imprimir'])
        ->name('pedidos.imprimir');

    Route::resource('clientes', ClienteController::class);
    Route::resource('pedidos', PedidoController::class);
});

require __DIR__ . '/auth.php';
