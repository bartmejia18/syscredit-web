<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DetallePagos extends Model
{
    protected $table = 'detalle_pagos';
	protected $fillable = ['creditos_id','fecha_pago','abono','estado'];
}
