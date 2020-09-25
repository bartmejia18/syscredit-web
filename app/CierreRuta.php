<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CierreRuta extends Model
{
	protected $table = 'cierre_ruta';
	protected $fillable = ['sucursal_id',
						'cobrador_id',
						'usuario_id',
						'monto_cierre',
						'fecha_cierre',
						'fecha_cerrado',
						'hora',
						'estado',
						'info_closure'];

	public function sucursal(){
		return $this->hasOne('App\Sucursales', 'id', 'sucursal_id');
	}

	public function cobrador(){
		return $this->hasOne('App\Usuarios', 'id', 'cobrador_id');
	}

	public function usuario(){
		return $this->hasOne('App\Usuarios', 'id', 'usuario_id');
	}
}
