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
        'autorizacao',
        'autorizacao_em',
        'autorizacao_por',
        'cancelado_em',
        'cancelado_por',
        'motivo_cancelamento',
        'valor_cobrado',
        'interrompido_em',
        'interrompido_por',
        'motivo_interrupcao',
    ];

    protected $casts = [
        'quantidade' => 'integer',
        'preco' => 'decimal:2',
        'autorizacao_em' => 'datetime',
        'cancelado_em' => 'datetime',
        'valor_cobrado' => 'decimal:2',
        'interrompido_em' => 'datetime',
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function servico()
    {
        return $this->belongsTo(Servico::class, 'servico_id');
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
