<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClientesDesbloqueados extends Model {

    protected $table = 'clientes_desbloqueados';
	protected $fillable = [
		'cliente_id',
		'supervisor_id',
		'gerente_id',
		'razon',
		'aprobacion_supervisor',
		'comentario_supervisor',
		'fecha_supervisor',
		'aprobacion_gerente',
		'comentario_gerente',
		'fecha_gerente',
		'estado'
	];

	public function cliente(){
		return $this->hasOne('App\Clientes','id','cliente_id');
	}

    public function supervisor(){
		return $this->hasOne('App\Usuarios','id','supervisor_id');
	}

	public function gerente(){
		return $this->hasOne('App\Usuarios','id','gerente_id');
	}
}
