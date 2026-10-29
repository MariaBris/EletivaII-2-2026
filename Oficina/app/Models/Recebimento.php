<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recebimento extends Model
{
    protected $table = 'recebimentos';

    protected $fillable = [
        'pedido_id',
        'usuario_id',
        'forma_pagamento',
        'valor',
        'valor_entregue',
        'recebido_em',
        'observacoes',
        'estornado_em',
        'estornado_por',
        'motivo_estorno',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'valor_entregue' => 'decimal:2',
        'recebido_em' => 'datetime',
        'estornado_em' => 'datetime',
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function operador()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function operadorEstorno()
    {
        return $this->belongsTo(User::class, 'estornado_por');
    }
}
