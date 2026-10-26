<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemServico extends Model
{
    protected $table = 'itens_servico';

    protected $fillable = [
        'quantidade', 'preco', 'pedido_id', 'servico_id',
    ];

    protected $casts = [
        'quantidade' => 'integer',
        'preco' => 'decimal:2',
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function servico()
    {
        return $this->belongsTo(Servico::class, 'servico_id');
    }
}
