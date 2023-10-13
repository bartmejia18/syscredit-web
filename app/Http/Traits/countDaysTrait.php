<?php

namespace App\Http\Traits;

trait countDaysTrait {
    /*public function getTotalDaysArrears($credit, $feePaid) {
        $dateInitial = $credit->fecha_inicio;
        $dateFinal = $credit->estado == 0 ? $credit->fecha_finalizado : date('Y-m-d');

        $totalDays = (strtotime($dateInitial)-strtotime($dateFinal))/86400;
        $totalDays = abs($totalDays); 
        $totalDays = floor($totalDays + 1);	
    
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
        return ($totalDays - $countSundayTemporal) - $feePaid;
    }*/
}