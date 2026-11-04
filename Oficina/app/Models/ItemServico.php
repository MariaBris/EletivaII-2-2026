<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemServico extends Model
{
    protected $table = 'itens_servico';

    protected $fillable = [
        'quantidade',
        'preco',
        'pedido_id',
        'servico_id',
        'situacao',
        'cancelado_em',
        'cancelado_por',
        'motivo_cancelamento',
    ];

    protected $casts = [
        'quantidade' => 'integer',
        'preco' => 'decimal:2',
        'cancelado_em' => 'datetime',
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function servico()
    {
        return $this->belongsTo(Servico::class, 'servico_id');
    }

    public function canceladoPor()
    {
        return $this->belongsTo(User::class, 'cancelado_por');
    }
}
