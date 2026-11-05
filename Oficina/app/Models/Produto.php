<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

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

    public function itensReservados()
    {
        return $this->hasMany(ItemPedido::class, 'produto_id')
            ->where('quantidade_reservada', '>', 0);
    }

    public function scopeComReservas(Builder $consulta): Builder
    {
        return $consulta->withSum('itensReservados as estoque_reservado', 'quantidade_reservada');
    }

    public function getEstoqueReservadoAttribute($valor): int
    {
        if (array_key_exists('estoque_reservado', $this->attributes)) {
            return (int) $valor;
        }

        return (int) $this->itensReservados()->sum('quantidade_reservada');
    }

    public function getEstoqueDisponivelAttribute(): int
    {
        return max(0, $this->estoque - $this->estoque_reservado);
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }
}
