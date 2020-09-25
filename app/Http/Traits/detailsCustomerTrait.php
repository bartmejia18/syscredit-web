<?php

namespace App\Http\Traits;

use App\Creditos;
use App\CuotasClientes;

trait detailsCustomerTrait {

    public function getDayOverdueCustomer($creditId, $totalFees){
        $totalDays = 0;
        $totalSunday = 0;
        $credit = Creditos::where("estado",1)
                    ->where('id', $creditId)
                    ->with('planes')
                    ->first();
        if($credit){

            $today = \Carbon\Carbon::now();
            $startDate = \Carbon\Carbon::parse($credit->fecha_inicio);
            $days = $today->diffInDays($startDate);
            
            if($credit->planes->domingo == "1")
                $totalSunday = $this->getTotalDaysSunday($days, $credit->fecha_inicio);

            if ( $totalFees > 0)
                $totalDays = $days - $totalFees - $totalSunday;
            else
                $totalDays = $days - $totalSunday;
        }
        return $totalDays;
    }

    private function getTotalDaysSunday($totalDay, $dateInit){
        $countSundayTemporal = 0;
        for ($i=0; $i<$totalDay; $i++)  {  
            $dateTemporal = strtotime ( '+'.$i.' day' , strtotime ( $dateInit) ) ;
            $dateTemporal = date ( 'd-m-Y' , $dateTemporal );
            $dateTemporalNew = new \DateTime($dateTemporal);
            $sundayTemporal = date("D", $dateTemporalNew->getTimestamp());

            if($sundayTemporal == "Sun"){
                ++$countSundayTemporal;
            }
        }

        return $countSundayTemporal;
    }
}