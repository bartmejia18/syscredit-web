<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReferenciasPersonalesClientes extends Model
{
    protected $table = 'referencias_personales_clientes';
	protected $fillable = ['nombre','telefono','clientes_id'];
}
