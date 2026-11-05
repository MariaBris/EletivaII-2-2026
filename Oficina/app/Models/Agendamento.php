<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agendamento extends Model
{
    protected $table = 'agendamentos';

    protected $fillable = [
        'veiculo_id', 'cliente_id', 'usuario_id', 'pedido_id',
        'data_hora', 'motivo', 'status',
    ];

    protected $casts = [
        'data_hora' => 'datetime',
    ];

    public const SITUACOES = [
        'AGENDADO' => 'Agendado',
        'ATENDIDO' => 'Atendido',
        'CANCELADO' => 'Cancelado',
    ];

    public function veiculo()
    {
        return $this->belongsTo(Veiculos::class, 'veiculo_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function operador()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }
}
