<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DetallePagos extends Model
{
    protected $table = 'detalle_pagos';
	protected $fillable = [
        'credito_id',
        'fecha_pago',
        'abono',
        'origen',
        'estado'
    ];
}
