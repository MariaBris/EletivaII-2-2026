<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $table = "categorias";
    public $incrementing = true;
    protected $fillable = [
        'nome', 'descricao'
    ];//coloca todos os campos que o usuario ira manipular/preencher, tem q ter exatamente os mesmos nomes que está na tabela

}
