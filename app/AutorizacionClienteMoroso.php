<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AutorizacionClienteMoroso extends Model
{
    protected $table = 'autorizacion_cliente_moroso';
	protected $fillable = [
        'usuario_autorizador_id',
        'comentario'
    ];
}
