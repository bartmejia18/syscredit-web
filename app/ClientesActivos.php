<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class ClientesActivos extends Model {
    protected $table = 'vista_clientes_activos';
    protected $fillable = [
        'clientes_id',
        'usuarios_cobrador',
        'saldo',
        'deudatotal',
        'cuota_diaria',
        'cuota_minima',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'nombre_completo',
        'cantidad_cuotas_pagadas',
        'cuotas_pendientes',
        'monto_abonado',
        'fecha_ultimo_pago'
    ];

    public function cliente() {
        return $this->hasOne('App\Clientes', 'id','clientes_id');
    }
}