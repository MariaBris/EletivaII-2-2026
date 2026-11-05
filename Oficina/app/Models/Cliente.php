<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    protected $table = 'clientes';

    protected $fillable = [
        'nome',
        'telefone',
        'email',
        'cpf_cnpj',
    ];

    public function veiculos()
    {
        return $this->hasMany(Veiculos::class, 'cliente_id');
    }

    public function pedidos()
    {
        return $this->hasMany(Pedido::class, 'cliente_id');
    }

    public function enderecos()
    {
        return $this->hasMany(Endereco::class, 'cliente_id');
    }

    public function endereco()
    {
        return $this->hasOne(Endereco::class, 'cliente_id')->orderBy('id');
    }

    public function getCpfCnpjFormatadoAttribute()
    {
        $documento = $this->cpf_cnpj ?? '';

        if (strlen($documento) === 11 && ctype_digit($documento)) {
            return substr($documento, 0, 3) . '.'
                . substr($documento, 3, 3) . '.'
                . substr($documento, 6, 3) . '-'
                . substr($documento, 9, 2);
        }

        if (strlen($documento) === 14) {
            return substr($documento, 0, 2) . '.'
                . substr($documento, 2, 3) . '.'
                . substr($documento, 5, 3) . '/'
                . substr($documento, 8, 4) . '-'
                . substr($documento, 12, 2);
        }

        return $documento;
    }
}
