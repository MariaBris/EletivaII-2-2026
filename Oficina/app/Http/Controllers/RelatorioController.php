<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Models\Servico;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RelatorioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $this->autorizarAcesso($request);

        return view('relatorios.index');
    }

    public function faturamento(Request $request): View
    {
        $this->autorizarAcesso($request);

        $dados = $this->validarPeriodo($request);

        $consulta = Pedido::with(['cliente', 'veiculo'])
            ->where('status', 'CONCLUIDO')
            ->whereNotNull('concluido_em');

        $this->aplicarPeriodo($consulta, $dados);

        $totalFaturado = (clone $consulta)->sum('total');
        $quantidadeOs = (clone $consulta)->count();

        $ticketMedio = $quantidadeOs > 0
            ? (float) $totalFaturado / $quantidadeOs
            : 0;

        $pedidos = $consulta->orderByDesc('concluido_em')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('relatorios.faturamento', compact(
            'pedidos',
            'totalFaturado',
            'quantidadeOs',
            'ticketMedio'
        ));
    }

    public function servicos(Request $request): View
    {
        $this->autorizarAcesso($request);

        $dados = $this->validarPeriodo($request);

        $servicos = Servico::withSum([
            'itens as quantidade_executada' => function (Builder $itens) use ($dados): void {
                $itens->where('autorizacao', 'AUTORIZADO')
                    ->where('situacao', 'EXECUTADO')
                    ->whereHas('pedido', function (Builder $pedidos) use ($dados): void {
                        $pedidos->where('status', 'CONCLUIDO')
                            ->whereNotNull('concluido_em');

                        $this->aplicarPeriodo($pedidos, $dados);
                    });
            },
        ], 'quantidade')
            ->orderByDesc('quantidade_executada')
            ->orderBy('nome')
            ->orderBy('id')
            ->paginate(20)
            ->withQueryString();

        return view('relatorios.servicos', compact('servicos'));
    }

    private function autorizarAcesso(Request $request): void
    {
        abort_unless(
            $request->user()
                && in_array($request->user()->role, ['ADM', 'FUNC'], true),
            403
        );
    }

    private function validarPeriodo(Request $request): array
    {
        $regras = [
            'data_inicio' => ['nullable', 'date_format:Y-m-d'],
            'data_fim' => ['nullable', 'date_format:Y-m-d'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];

        if ($request->filled('data_inicio')) {
            $regras['data_fim'][] = 'after_or_equal:data_inicio';
        }

        return $request->validate($regras, [
            'data_inicio.date_format' => 'Informe uma data inicial válida.',
            'data_fim.date_format' => 'Informe uma data final válida.',
            'data_fim.after_or_equal' => 'A data final deve ser igual ou posterior à data inicial.',
        ]);
    }

    /**
     * Filter local calendar dates using the application's storage timezone.
     */
    private function aplicarPeriodo(Builder $consulta, array $dados): void
    {
        $fusoBanco = config('app.timezone', 'UTC');

        if (!empty($dados['data_inicio'])) {
            $inicio = CarbonImmutable::createFromFormat(
                '!Y-m-d',
                $dados['data_inicio'],
                'America/Sao_Paulo'
            )->setTimezone($fusoBanco);

            $consulta->where('concluido_em', '>=', $inicio);
        }

        if (!empty($dados['data_fim'])) {
            $fim = CarbonImmutable::createFromFormat(
                '!Y-m-d',
                $dados['data_fim'],
                'America/Sao_Paulo'
            )->addDay()->setTimezone($fusoBanco);

            $consulta->where('concluido_em', '<', $fim);
        }
    }
}
