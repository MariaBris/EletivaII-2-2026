<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FotoEntrada extends Model
{
    protected $table = 'fotos_entrada';

    public const POSICOES = [
        'FRENTE' => 'Frente',
        'TRASEIRA' => 'Traseira',
        'LATERAL_ESQUERDA' => 'Lateral esquerda',
        'LATERAL_DIREITA' => 'Lateral direita',
    ];

    protected $fillable = [
        'pedido_id',
        'usuario_id',
        'posicao',
        'caminho',
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function operador()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
