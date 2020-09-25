<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MontosPrestamo extends Model
{
    protected $table = 'montos_prestamo';
	protected $fillable = ['monto','sucursales_id'];

	public function sucursal(){
		return $this->hasOne('App\Sucursales','id','sucursales_id');
	}
}
