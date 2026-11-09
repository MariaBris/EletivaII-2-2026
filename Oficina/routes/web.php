<?php

use App\Http\Controllers\AgendamentoController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\OficinaController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RelatorioController;
use App\Http\Controllers\ServicoController;
use App\Http\Controllers\VeiculosController;
use App\Http\Middleware\VerificarAdministrador;
use App\Models\Agendamento;
use App\Models\Produto;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function (Request $request) {
    $produtosEstoqueBaixo = null;
    $agendamentosPainel = null;

    $periodoAgenda = $request->query('periodo_agenda', 'dia');

    if (!in_array($periodoAgenda, ['dia', 'semana', 'mes'], true)) {
        $periodoAgenda = 'dia';
    }

    $hoje = Carbon::now('America/Sao_Paulo');

    $inicioAgenda = $hoje->copy()->startOfDay();
    $fimAgenda = $hoje->copy()->endOfDay();

    if ($periodoAgenda === 'semana') {
        $inicioAgenda = $hoje->copy()->startOfWeek(Carbon::MONDAY);
        $fimAgenda = $inicioAgenda->copy()->addDays(6)->endOfDay();
    } elseif ($periodoAgenda === 'mes') {
        $inicioAgenda = $hoje->copy()->startOfMonth()->startOfDay();
        $fimAgenda = $hoje->copy()->endOfMonth()->endOfDay();
    }

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

        $agendamentosPainel = Agendamento::with(['veiculo', 'cliente'])
            ->whereBetween('data_hora', [
                $inicioAgenda->format('Y-m-d H:i:s'),
                $fimAgenda->format('Y-m-d H:i:s'),
            ])
            ->orderBy('data_hora')
            ->orderBy('id')
            ->paginate(5, ['*'], 'agenda_page')
            ->withQueryString()
            ->fragment('agenda-painel');
    }

    return view('dashboard', compact(
        'produtosEstoqueBaixo',
        'agendamentosPainel',
        'periodoAgenda',
        'inicioAgenda',
        'fimAgenda'
    ));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    Route::middleware(VerificarAdministrador::class)->group(function () {
        Route::get('/oficina/dados', [OficinaController::class, 'edit'])
            ->name('oficina.edit');

        Route::put('/oficina/dados', [OficinaController::class, 'update'])
            ->name('oficina.update');

        Route::resource('categorias', CategoriaController::class);

        Route::resource('produtos', ProdutoController::class)
            ->except(['index']);

        Route::resource('servicos', ServicoController::class);

        Route::get('/checklist/modelo', [PedidoController::class, 'modelo'])
            ->name('checklist.modelo');
    });

    Route::get('/produtos', [ProdutoController::class, 'index'])
        ->name('produtos.index');

    Route::prefix('relatorios')->name('relatorios.')->group(function () {
        Route::get('/', [RelatorioController::class, 'index'])
            ->name('index');

        Route::get('/faturamento', [RelatorioController::class, 'faturamento'])
            ->name('faturamento');

        Route::get('/servicos', [RelatorioController::class, 'servicos'])
            ->name('servicos');
    });

    Route::resource('agendamentos', AgendamentoController::class);
    Route::resource('veiculos', VeiculosController::class);
    Route::resource('clientes', ClienteController::class);

    Route::get('/pedidos/{id}/imprimir', [PedidoController::class, 'imprimir'])
        ->whereNumber('id')
        ->name('pedidos.imprimir');

    Route::get('/pedidos/{id}/fotos/{foto}', [PedidoController::class, 'foto'])
        ->whereNumber(['id', 'foto'])
        ->name('pedidos.fotos.show');

    Route::patch('/pedidos/{id}/itens/finalizar-lote', [PedidoController::class, 'finalizarSelecionados'])
        ->whereNumber('id')
        ->name('pedidos.itens.finalizar-lote');

    Route::patch('/pedidos/{id}/itens/autorizar-iniciar', [PedidoController::class, 'autorizarEIniciarSelecionados'])
        ->whereNumber('id')
        ->name('pedidos.itens.autorizar-iniciar');

    Route::patch('/pedidos/{id}/itens/{tipo}/{item}', [PedidoController::class, 'atualizarItem'])
        ->whereNumber(['id', 'item'])
        ->whereIn('tipo', ['peca', 'servico'])
        ->name('pedidos.itens.update');

    Route::resource('pedidos', PedidoController::class);
});

require __DIR__ . '/auth.php';
