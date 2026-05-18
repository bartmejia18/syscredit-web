<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sucursales extends Model
{
    protected $table = 'sucursales';
	protected $fillable = [
        'descripcion',
        'direccion',
        'telefono',
        'departamento',
        'municipio',
        'empresa_id'
    ];

    public function empresa(){
		return $this->hasOne('App\Empresas','id','empresa_id');
	}
}
