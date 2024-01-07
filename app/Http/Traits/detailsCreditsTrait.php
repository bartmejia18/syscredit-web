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

    public function getTotalDaysArrears($credit, $feePaid) {
        $dateInitial = $credit->fecha_inicio;
        $dateFinal = $credit->estado == 0 ? $credit->fecha_finalizado : date('Y-m-d');

        $totalDays = (strtotime($dateInitial) - strtotime($dateFinal))/86400;
        $totalDays = abs($totalDays); 
        $totalDays = floor($totalDays + 1);	
    
        
        if ($credit->planes->tipo == 0 || $credit->planes->tipo == 1) {
            $countSundayTemporal = 0;
            if ($credit->planes->domingo == 1) {
                for ($i=0; $i<$totalDays; $i++)  {  
                    $dateTemporal = strtotime('+'.$i.'day', strtotime($dateInitial));
                    $dateTemporal = date('d-m-Y', $dateTemporal);
                    $dateTemporalNew = new \DateTime($dateTemporal);
                    $sundayTemporal = date("D", $dateTemporalNew->getTimestamp());

                    if ($sundayTemporal == "Sun") {
                        ++$countSundayTemporal;
                    }
                }
            }
            $totalDays = $totalDays - $countSundayTemporal;
        } else if ($credit->planes->tipo == 2) {
            $totalDays = floor($totalDays / 7);
        } else if ($credit->planes->tipo == 3) {
            $totalDays = floor($totalDays / 30);
        }
        return $totalDays - $feePaid;
    }
}