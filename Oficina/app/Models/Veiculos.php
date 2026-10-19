<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Veiculos extends Model
{
    protected $table = 'veiculos';

    public $incrementing = true;

    protected $fillable = ['placa', 'marca', 'modelo', 'ano', 'user_id'];

    // Cada veículo pertence a um usuário.
    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
