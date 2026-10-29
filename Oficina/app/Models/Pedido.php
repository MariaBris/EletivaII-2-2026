<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $table = 'pedidos';

    protected $fillable = [
        'status',
        'total',
        'user_id',
        'cliente_id',
        'usuario_id',
        'veiculo_id',
        'responsavel',
        'observacoes',
        'estoque_baixado'
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'estoque_baixado' => 'boolean'
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function operador()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function veiculo()
    {
        return $this->belongsTo(Veiculos::class, 'veiculo_id');
    }

    public function itens()
    {
        return $this->hasMany(ItemPedido::class, 'pedido_id');
    }

    public function servicos()
    {
        return $this->hasMany(ItemServico::class, 'pedido_id');
    }

    public function recebimentos()
    {
        return $this->hasMany(Recebimento::class, 'pedido_id');
    }
}
