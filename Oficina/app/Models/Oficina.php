<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Oficina extends Model
{
    protected $table = 'oficina_configuracoes';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'nome',
        'endereco',
        'telefone',
    ];

    public static function dados(): self
    {
        return static::find(1) ?? new static([
            'nome' => 'Mazotto Soluções Automotivas',
            'endereco' => 'R. Joaquim Pereira de Almeida, 196 - Jardim Cambui, Pres. Prudente - SP, 19061-550',
        ]);
    }
}
