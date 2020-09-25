<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TipoUsuarios extends Model
{
    protected $table = 'tipo_usuarios';
	protected $fillable = ['descripcion'];
}
