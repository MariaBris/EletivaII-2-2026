<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $tble  = 'clientes';

    protected $fillable = [
        'nome', 'telefone', 'email'
    ];

    public function veiculos()
    {
        return $this->hasMany(Veiculos::class,'cliente_id');
    }

    public function pedidos()
    {
        return $this->hasMany(pedido::class,'cliente_id');
    }
}
