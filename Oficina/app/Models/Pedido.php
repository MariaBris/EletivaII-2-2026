<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $table = 'pedidos';

    protected $fillable = [
        'status',
        'total',
        'total_orcamento',
        'user_id',
        'cliente_id',
        'usuario_id',
        'veiculo_id',
        'quilometragem_entrada',
        'combustivel_entrada',
        'avarias_entrada',
        'saida_em',
        'concluido_em',
        'responsavel',
        'observacoes',
        'estoque_baixado',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'total_orcamento' => 'decimal:2',
        'quilometragem_entrada' => 'integer',
        'saida_em' => 'datetime',
        'concluido_em' => 'datetime',
        'estoque_baixado' => 'boolean',
    ];

    public const COMBUSTIVEIS = [
        'VAZIO' => 'Vazio / reserva',
        'UM_QUARTO' => '1/4 do tanque',
        'METADE' => 'Metade do tanque',
        'TRES_QUARTOS' => '3/4 do tanque',
        'CHEIO' => 'Cheio',
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

    public function fotosEntrada()
    {
        return $this->hasMany(FotoEntrada::class, 'pedido_id');
    }

    public function checklistItens()
    {
        return $this->hasMany(ChecklistItem::class, 'pedido_id');
    }
}
