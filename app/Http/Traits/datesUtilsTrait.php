<?php

namespace App\Http\Traits;

trait datesUtilsTrait {
    public function getDateFinalCredit($credit) {
        $dateFinal = "";
        if ($credit->estado == 0) {
            if ($credit->fecha_finalizado != null) {
                $dateFinal = $credit->fecha_finalizado;
            } else {
                $dateFinal = $credit->fecha_fin;
            }
        } else {
            $dateFinal = date('Y-m-d');
        }

        return $dateFinal;
    }

    public function daysBetweenDates($dateStart, $dateEnd) {
        $totalDays = (strtotime($dateStart) - strtotime($dateEnd))/86400;
        $totalDays = abs($totalDays); 
        $totalDays = floor($totalDays + 1);	
        return $totalDays;
    }

    public function countDaysBetweenDatesWithPlanes($dateStart, $dateEnd, $plan) {
        $totalDays = 0;
        if ($dateStart < $dateEnd) {    
            $totalDays = $this->daysBetweenDates($dateStart, $dateEnd);
            if ($plan->tipo == 1) {
                if ($plan->domingo == 1) {
                    $totalDays = $this->countDaysWithoutSunday($dateStart, $totalDays);
                }
            } else if ($plan->tipo == 2) {
                $totalDays = floor($totalDays / 7);
            } else if ($plan->tipo == 3) {
                $totalDays = floor($totalDays / 30);
            }
        }
        return $totalDays;
    }

    public function countDaysWithoutSunday($dateStart, $totalDays) {
        $countSundayTemporal = 0;
        for ($i=0; $i < $totalDays; $i++) {
            $dateTemporal = strtotime('+'.$i.'day', strtotime($dateStart));
            $dateTemporal = date('d-m-Y', $dateTemporal);
            $dateTemporalNew = new \DateTime($dateTemporal);
            $sundayTemporal = date("D", $dateTemporalNew->getTimestamp());

            if ($sundayTemporal == "Sun") {
                ++$countSundayTemporal;
            }
        }
        return $totalDays - $countSundayTemporal;
    }

    public function sumDaysToDate($date, $days) {
        $newDate = strtotime('+'.$days.'day', strtotime($date));
        $newDate = date('Y-m-d', $newDate);
        return $newDate;
    }
}