<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemPedido extends Model
{
    protected $table = 'itens_pedido';

    protected $fillable = [
        'quantidade',
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
        'preco' => 'decimal:2',
        'autorizacao_em' => 'datetime',
        'estoque_baixado' => 'boolean',
        'cancelado_em' => 'datetime',
        'valor_cobrado' => 'decimal:2',
        'quantidade_devolvida' => 'integer',
        'interrompido_em' => 'datetime',
    ];

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
