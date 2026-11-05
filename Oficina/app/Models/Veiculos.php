<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Veiculos extends Model
{
    protected $table = 'veiculos';

    public $incrementing = true;

    protected $fillable = [
        'placa', 'marca', 'modelo', 'ano', 'user_id', 'cliente_id',
        'quilometragem_informada', 'oleo_data_informada', 'oleo_km_informado', 'revisao_data_informada', 'revisao_km_informado',
    ];

    protected $casts = [
        'quilometragem_informada' => 'integer',
        'oleo_data_informada' => 'date',
        'oleo_km_informado' => 'integer',
        'revisao_data_informada' => 'date',
        'revisao_km_informado' => 'integer',
    ];

    // Cada veículo pertence a um usuário.
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function servicosRealizados()
    {
        return $this->hasManyThrough(
            ItemServico::class,
            Pedido::class,
            'veiculo_id',
            'pedido_id',
            'id',
            'id'
        )->where('pedidos.status', 'CONCLUIDO')
            ->where('itens_servico.autorizacao', 'AUTORIZADO')
            ->where('itens_servico.situacao', 'EXECUTADO');
    }

}
