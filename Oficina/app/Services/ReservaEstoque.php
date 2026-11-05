<?php

namespace App\Services;

use App\Models\ItemPedido;
use App\Models\Produto;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReservaEstoque
{
    public static function transacao(callable $operacao, int $tentativas = 3): mixed
    {
        return DB::transaction(function () use ($operacao) {
            // Todas as gravações de estoque/reservas seguem a mesma ordem de bloqueio.
            $controle = DB::table('controle_estoque')->where('id', 1)->lockForUpdate()->first();

            if (!$controle) {
                throw new \RuntimeException('Execute o SQL de instalação das reservas de estoque.');
            }

            self::redistribuirPendentes();
            $resultado = $operacao();
            self::redistribuirPendentes();

            return $resultado;
        }, $tentativas);
    }

    public static function conferirInicio($registros): void
    {
        foreach ($registros as $item) {
            if (!$item instanceof ItemPedido) {
                continue;
            }

            if (
                $item->situacao !== 'PENDENTE'
                || $item->autorizacao !== 'AUTORIZADO'
                || $item->estoque_baixado
                || $item->quantidade <= 0
                || $item->quantidade_reservada < $item->quantidade
            ) {
                $nome = $item->produto?->nome ?? 'peça não encontrada';

                throw ValidationException::withMessages([
                    'estoque' => 'Não foi possível iniciar: ' . $nome . '. Reservado: '
                        . $item->quantidade_reservada . ' de ' . $item->quantidade
                        . '. Confira a reposição e as reservas. Nenhum dos itens selecionados foi iniciado.',
                ]);
            }
        }
    }

    private static function redistribuirPendentes(): void
    {
        $ids = ItemPedido::reservaveis()->distinct()->pluck('produto_id')
            ->merge(ItemPedido::where('quantidade_reservada', '>', 0)->distinct()->pluck('produto_id'))
            ->unique()->sort()->values();

        if ($ids->isEmpty()) {
            return;
        }

        $produtos = Produto::whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get();

        $itensPorProduto = ItemPedido::with('pedido:id,status')
            ->whereIn('produto_id', $ids)
            ->where(function ($consulta) {
                $consulta->where('quantidade_reservada', '>', 0)
                    ->orWhere(function ($pendentes) {
                        $pendentes->reservaveis();
                    });
            })
            ->orderBy('produto_id')
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->groupBy('produto_id');

        foreach ($produtos as $produto) {
            $itens = $itensPorProduto->get($produto->id);

            if (!$itens) {
                continue;
            }

            $ativos = $itens->filter(function ($item) {
                return $item->situacao === 'PENDENTE'
                    && in_array($item->autorizacao, ['AGUARDANDO', 'AUTORIZADO'], true)
                    && !$item->estoque_baixado
                    && $item->quantidade > 0
                    && in_array($item->pedido?->status, ['ABERTO', 'EM_EXECUCAO'], true);
            });

            $ativosIds = $ativos->pluck('id')->all();
            $totalReservado = 0;

            // Libera recusas/cancelamentos e o excedente de quantidades reduzidas.
            foreach ($itens as $item) {
                $limite = in_array($item->id, $ativosIds, true) ? $item->quantidade : 0;
                $item->quantidade_reservada = min(max(0, $item->quantidade_reservada), $limite);
                $totalReservado += $item->quantidade_reservada;
            }

            $fisico = max(0, $produto->estoque);
            $excesso = max(0, $totalReservado - $fisico);

            // Uma correção para baixo no estoque físico preserva primeiro as reservas antigas.
            foreach ($ativos->reverse() as $item) {
                if ($excesso === 0) {
                    break;
                }

                $retirar = min($excesso, $item->quantidade_reservada);
                $item->quantidade_reservada -= $retirar;
                $totalReservado -= $retirar;
                $excesso -= $retirar;
            }

            $disponivel = $fisico - $totalReservado;

            // Mantém as reservas existentes e completa as faltantes na ordem de inclusão.
            foreach ($ativos as $item) {
                if ($disponivel <= 0) {
                    break;
                }

                $falta = $item->quantidade - $item->quantidade_reservada;
                $adicionar = min($disponivel, $falta);
                $item->quantidade_reservada += $adicionar;
                $disponivel -= $adicionar;
            }

            foreach ($itens as $item) {
                if ($item->isDirty('quantidade_reservada')) {
                    $item->save();
                }
            }
        }
    }
}
