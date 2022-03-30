<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CreditosEliminados extends Model
{
    protected $table = 'creditos_eliminados';
	protected $fillable = ['credito_id','motivo'];
}