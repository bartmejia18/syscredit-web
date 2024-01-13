<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClientesDesbloqueados extends Model {

    protected $table = 'clientes_desbloqueados';
	protected $fillable = [
		'cliente_id',
		'supervisor_id',
		'razon'
	];

	public function cliente(){
		return $this->hasOne('App\Clientes','id','cliente_id');
	}

    public function supervisor(){
		return $this->hasOne('App\Usuarios','id','supervisor_id');
	}
}
