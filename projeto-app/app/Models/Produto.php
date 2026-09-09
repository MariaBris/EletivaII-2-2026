<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    protected $table = "produtos";//para acessar as classes filhas, permite que a classe que declara ele acesse ele e a classe filha acesse ele
    public $incrementing = true;
    protected $fillable = [
        'nome', 'descricao', 'preco', 'estoque', ' categoria_id'
    ];
    protected $casts = [
        'preco' => 'decimal:2',
        'estoque' => 'integer'
    ];//recebe tbm uma matriz/array onde passa o formato q quer q os valores informados pelo usuario sejao transformados noq o banco aceita, para q nao tenha problema com incompatibilidade de valores...pesquisar tem tbm para email
    public function categoria(){
        return $this->belongsTo(Categoria::class, 'categoria_id');//classe e coluna..precisa para todas as chaves estrangeiras!!(funciona como inner join)
    }
}
