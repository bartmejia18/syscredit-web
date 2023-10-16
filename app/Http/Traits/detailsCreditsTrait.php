<?php

namespace App\Http\Traits;

use App\Creditos;
use stdClass;

trait detailsCreditsTrait {

    public function getStatusCredits($customerId){
        $detailCredits = new stdClass();
        
        $credits = Creditos::where('clientes_id', $customerId)
            ->where('estado','!=',2)
            ->orderBy('id', 'desc')
            ->get();

        $totalCredits = $credits->count();
        if ($totalCredits > 0) {
            $complete = $credits->where('estado', 0)->count();
            $detailCredits->status = $complete == $totalCredits ? 3 : 2;
            $detailCredits->creditsActives =  $totalCredits - $complete;
            $detailCredits->totalCredits = $totalCredits;
            $detailCredits->collector = $credits[0]->usuarios_cobrador;

            $arrersCredits = $this->getArrearsForCredits($credits);
            $detailCredits->arrearsStatus = $this->getArrearsStatus($arrersCredits);
        } else {
            $detailCredits->status = 1;
            $detailCredits->creditsActives =  0;
            $detailCredits->totalCredits = 0;
            $detailCredits->collector = 0;
            $detailCredits->arrearsStatus = "";
        }

        return $detailCredits;
    }

    public function getArrearsForCredits($credits) {
        $credits->map(function($item, $key) {
            if ($item->cuotas_atrasadas == 0) {
                $totalFeesPaid = $this->getDetailsPayments($item)->totalFees;
                $item->cuotas_atrasadas = $this->getTotalDaysArrears($item, $totalFeesPaid);
            }
            return $item;
        });

        $arrearsStatus = [
            'moroso' => 0,
            'bueno' => 0,
            'excelente' => 0
        ];

        foreach ($credits as $item) {
            if ($item->estado_morosidad != null && $item->estado_morosidad != "") {
                if ($item->estado_morosidad == "Moroso") {
                    $arrearsStatus['moroso'] += 1;
                } else if ($item->estado_morosidad == "Bueno") {
                    $arrearsStatus['bueno'] += 1;
                } else {
                    $arrearsStatus['excelente'] += 1;
                }
            } else {
                if ($item->cuotas_atrasadas > 9) {
                    $arrearsStatus['moroso'] += 1;
                } else if ($item->cuotas_atrasadas >= 4 && $item->cuotas_atrasadas <= 9 ) {
                        $arrearsStatus['bueno'] += 1;
                } else {
                        $arrearsStatus['excelente'] += 1;
                }
            }
        }
        return $arrearsStatus;
    }

    public function getArrearsStatus($arrearsCredits) {
        if ($arrearsCredits['moroso'] > 0) {
            return 'Moroso';
        } else if ($arrearsCredits['bueno'] > 0) {
            return 'Bueno';
        } else if ($arrearsCredits['excelente'] > 0) {
            return 'Excelente';
        }
    }

    public function getArrearsForCustomerId($customerId) {
        $credits = Creditos::where('clientes_id', $customerId)
            ->where('estado','!=',2)
            ->orderBy('id', 'desc')
            ->get();

        return $this->getArrearsForCredits($credits);
    }

    public function getArrearsStatusForCustomerId($customerId) {
        $arrearsCredits = $this->getArrearsForCustomerId($customerId);

        return $this->getArrearsStatus($arrearsCredits);
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

    public function getArrearsStatusForDays($days) {
        if ($days > 9) {
            return 'Moroso';
        } else if ($days >= 4 && $days <= 9) {
            return 'Bueno';
        } else {
            return 'Excelente';
        }
    }
}