<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChecklistItem extends Model
{
    protected $table = 'checklist_itens';

    protected $fillable = [
        'pedido_id',
        'codigo',
        'grupo',
        'nome',
        'ordem',
        'situacao',
        'observacoes',
        'conferido_por',
        'conferido_em',
    ];

    protected $casts = [
        'ordem' => 'integer',
        'conferido_em' => 'datetime',
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function conferente()
    {
        return $this->belongsTo(User::class, 'conferido_por');
    }
}
