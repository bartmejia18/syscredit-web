<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Clientes extends Model
{
	protected $table = 'clientes';
	protected $fillable = ['sucursal_id','nombre','apellido','dpi','telefono','direccion','estado_civil','sexo','categoria','color','status'];

	public function creditos()
	{
		return $this->hasMany('App\Creditos', 'clientes_id', 'id')->with('planes','montos','usuariocreador','usuariocobrador');
	}

	public function referenciasPersonales()
	{
		return $this->hasMany('App\ReferenciasPersonalesClientes','clientes_id','id');
	}
}
