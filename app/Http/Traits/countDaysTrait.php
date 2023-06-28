<?php

namespace App\Http\Traits;

use App\Creditos;

trait countDaysTrait {
    public function getTotalDaysArrears($dateInitial, $feePaid, $sunday) {
        $dateInitial = $dateInitial;
        $dateFinal = date('Y-m-d');

        $totalDays = (strtotime($dateInitial)-strtotime($dateFinal))/86400;
        $totalDays = abs($totalDays); 
        $totalDays = floor($totalDays + 1 );	
    
        $dateInit = new \DateTime($dateInitial);
        $countSundayTemporal = 0;
        
        if ($sunday == 1) {
            for ($i=0; $i<$totalDays; $i++)  {  
                $dateTemporal = strtotime ( '+'.$i.' day' , strtotime ( $dateInitial) ) ;
                $dateTemporal = date ( 'd-m-Y' , $dateTemporal );
                $dateTemporalNew = new \DateTime($dateTemporal);
                $sundayTemporal = date("D", $dateTemporalNew->getTimestamp());

                if($sundayTemporal == "Sun"){
                    ++$countSundayTemporal;
                }
            }
        }

        return ($totalDays - $countSundayTemporal) - $feePaid;
    }
}