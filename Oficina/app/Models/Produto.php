<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    protected $table = 'produtos';

    public $incrementing = true;

    protected $fillable = [
        'nome',
        'descricao',
        'preco',
        'estoque',
        'estoque_minimo',
        'categoria_id',
        'imagem',
    ];

    protected $casts = [
        'preco' => 'decimal:2',
        'estoque' => 'integer',
        'estoque_minimo' => 'integer',
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }
}
