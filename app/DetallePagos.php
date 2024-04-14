<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DetallePagos extends Model
{
    protected $table = 'detalle_pagos';
	protected $fillable = [
        'credito_id',
        'fecha_corresponde',
        'fecha_pago',
        'abono',
        'valido',
        'origen',
        'estado'
    ];
}
