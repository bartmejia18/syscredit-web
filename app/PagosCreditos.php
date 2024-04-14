<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class PagosCreditos extends Model {
    protected $table = 'vista_pagos_fechas';
    protected $fillable = [
        'credito_id',
        'fecha_corresponde',
        'fecha_pago',
        'abono',
        'valido',
        'origen',
        'estado',
        'cliente_id',
        'totalabono',
        'usuarios_cobrador'
    ];
}