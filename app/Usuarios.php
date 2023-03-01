<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class Usuarios extends Model implements AuthenticatableContract, CanResetPasswordContract {
	
	use Authenticatable, CanResetPassword;


	protected $table = 'usuarios';
	protected $fillable = ['tipo_usuarios_id','nombre','user','password','password_2','estado','sucursales_id'];

	protected $hidden = ['password', 'password_2','remember_token'];

	public function tipoUsuarios()
	{
		return $this->hasOne('App\TipoUsuarios', 'id', 'tipo_usuarios_id');
	}

	public function sucursal()
	{
		return $this->hasOne('App\Sucursales', 'id', 'sucursales_id');
	}

	public function cobradorClientes()
	{
		return $this->hasMany('App\Creditos','usuarios_cobrador','id')->with('cliente');
	}
}

