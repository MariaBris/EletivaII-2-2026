<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Servico extends Model
{
    protected $table = 'servicos';

    protected $fillable = [
        'nome', 'descricao', 'preco'
    ];

    protected $casts = [
        'preco' => 'decimal:2',
    ];

    public function itens()
    {
        return $this->hasMany(ItemServico::class, 'servico_id');
    }
}
