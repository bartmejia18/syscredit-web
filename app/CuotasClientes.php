<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CuotasClientes extends Model
{
    protected $table = 'cuotasclientes';
	protected $fillable = ['totalabono','cantidadabonos','creditos_id'];
}
