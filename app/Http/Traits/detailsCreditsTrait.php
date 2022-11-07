<?php

namespace App\Http\Traits;

use App\Creditos;
use App\CuotasClientes;

trait detailsCreditsTrait {
    public function getStatusCredits($customerId){
        $detailCredits = new \stdClass();
        
        $credits = Creditos::where('clientes_id', $customerId)
            ->where('estado','!=',2)
            ->orderBy('id', 'desc')
            ->get();

        $totalCredits = $credits->count();
        if ($totalCredits > 0) {
            $complete = $credits->where('estado', 0)->count();
            $detailCredits->status = $complete == $totalCredits ? 3 : 2;
            $detailCredits->total =  $totalCredits - $complete;
            $detailCredits->collector = $credits[0]->usuarios_cobrador;
        } else {
            $detailCredits->status = 1;
            $detailCredits->total = 0;
            $detailCredits->collector = 0;
        }

        return $detailCredits;
    }
}