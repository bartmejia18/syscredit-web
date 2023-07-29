<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Planes extends Model
{
    protected $table = 'planes';
	protected $fillable = [
		'descripcion',
		'tipo',
		'dias',
		'porcentaje',
		'domingo',
		'sucursales_id'
	];

	public function sucursal(){
		return $this->hasOne('App\Sucursales','id','sucursales_id');
	}
}
