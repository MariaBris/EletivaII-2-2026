<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class ItemPedido extends Model
{
    protected $table = 'itens_pedido';

    protected $fillable = [
        'quantidade',
        'quantidade_reservada',
        'preco',
        'pedido_id',
        'produto_id',
        'situacao',
        'autorizacao',
        'autorizacao_em',
        'autorizacao_por',
        'estoque_baixado',
        'cancelado_em',
        'cancelado_por',
        'motivo_cancelamento',
        'valor_cobrado',
        'quantidade_devolvida',
        'interrompido_em',
        'interrompido_por',
        'motivo_interrupcao',
    ];

    protected $casts = [
        'quantidade' => 'integer',
        'quantidade_reservada' => 'integer',
        'preco' => 'decimal:2',
        'autorizacao_em' => 'datetime',
        'estoque_baixado' => 'boolean',
        'cancelado_em' => 'datetime',
        'valor_cobrado' => 'decimal:2',
        'quantidade_devolvida' => 'integer',
        'interrompido_em' => 'datetime',
    ];

    public function scopeReservaveis(Builder $consulta): Builder
    {
        return $consulta->where('itens_pedido.situacao', 'PENDENTE')
            ->whereIn('itens_pedido.autorizacao', ['AGUARDANDO', 'AUTORIZADO'])
            ->where('itens_pedido.estoque_baixado', false)
            ->where('itens_pedido.quantidade', '>', 0)
            ->whereHas('pedido', function ($pedidos) {
                $pedidos->whereIn('status', ['ABERTO', 'EM_EXECUCAO']);
            });
    }

    public function getQuantidadePendenteAttribute(): int
    {
        if (
            $this->situacao !== 'PENDENTE'
            || $this->autorizacao === 'RECUSADO'
            || $this->estoque_baixado
        ) {
            return 0;
        }

        return max(0, $this->quantidade - $this->quantidade_reservada);
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class, 'produto_id');
    }

    public function autorizacaoPor()
    {
        return $this->belongsTo(User::class, 'autorizacao_por');
    }

    public function canceladoPor()
    {
        return $this->belongsTo(User::class, 'cancelado_por');
    }

    public function interrompidoPor()
    {
        return $this->belongsTo(User::class, 'interrompido_por');
    }
}
