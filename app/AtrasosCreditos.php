<?php
namespace App;

use Illuminate\Database\Eloquent\Model;

class AtrasosCreditos extends Model {

    protected $table = 'atrasos_creditos';
	protected $fillable = [
		'credito_id',
        'cantidad_atrasos',
	];
}