<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UsuariosSucursales extends Model
{
    protected $table = 'usuarios_sucursales';
	protected $fillable = [
        'usuario_id',
        'sucursal_id'
    ];
}
