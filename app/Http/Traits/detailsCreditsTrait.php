<?php

namespace App\Http\Traits;

use App\Creditos;
use App\DetallePagos;
use App\VersionSistema;
use stdClass;
use App\Http\Traits\datesUtilsTrait;
use App\Http\Traits\detailsPaymentsTrait;
use App\Http\Traits\configUtilsTrait;
use Carbon\Carbon;


trait detailsCreditsTrait {

    use datesUtilsTrait;
    use detailsPaymentsTrait;
    use configUtilsTrait;

    public function getCreditsForCustomerId($customerId) {
        $credits = Creditos::with('planes')
                        ->where('clientes_id', $customerId)
                        ->where('estado','!=',2)
                        ->orderBy('id', 'desc')
                        ->get();
        
        $credits->map(function ($item, $key) {
            if ($item->estado == 1) {
                $item->cuotas_atrasadas = $this->getTotalDaysArrearsByVersion($item);
                $item->estado_morosidad = $this->getArrearsStatusForDays($item->cuotas_atrasadas);
            }
        });
        return $credits;
    }

    public function getTotalActiveCompleted($credits) {
        $total = new stdClass();
        
        $countCredits = $credits->count();

        $total->totalCredits = $countCredits;
        $total->creditsCompleted = $credits->where('estado', 0)->count();
        $total->creditsActives =  $countCredits - $total->creditsCompleted;
        $total->status = $total->creditsCompleted == $countCredits ? 3 : 2;
        
        return $total;
    }

    public function getGeneralStatusCustomer($credits) {
        $arrersCredits = $this->getArrearsForCredits($credits);
        return $this->getArrearsStatus($arrersCredits);
    }


    public function getArrearsForCredits($credits) {

        $arrearsStatus = [
            'moroso' => 0,
            'bueno' => 0,
            'excelente' => 0
        ];

        foreach ($credits as $item) {
            if ($item->estado == 0) {
                if ($item->estado_morosidad == "Moroso") {
                    $arrearsStatus['moroso'] += 1;
                } else if ($item->estado_morosidad == "Bueno") {
                    $arrearsStatus['bueno'] += 1;
                } else {
                    $arrearsStatus['excelente'] += 1;
                }
            } else {
                $atrasos = $this->getTotalDaysArrearsByVersion($item);
                if ($atrasos > 9) {
                    $arrearsStatus['moroso'] += 1;
                } else if ($atrasos >= 4 && $atrasos <= 9 ) {
                    $arrearsStatus['bueno'] += 1;
                } else {
                    $arrearsStatus['excelente'] += 1;
                }
            }
        }
        return $arrearsStatus;
    }

    public function getArrearsStatus($arrearsCredits) {
        if ($arrearsCredits['moroso'] == 0 && $arrearsCredits['bueno'] == 0 && $arrearsCredits['excelente'] > 0) {
            return "A";
        } else if ($arrearsCredits['moroso'] == 1 && $arrearsCredits['bueno'] >= 0 && $arrearsCredits['excelente'] >= 0) {
            return "B";
        } else if ($arrearsCredits['moroso'] >= 2 && $arrearsCredits['moroso'] <= 3) {
            return "C";
        } else if ($arrearsCredits['moroso'] >= 4 && $arrearsCredits['moroso'] <= 5) {
            return "D";
        } else if ($arrearsCredits['moroso'] > 5) {
            return "E";
        }
    }

    /*
        Obtiene la cantidad de atrasos menos el total pagado
    */
    public function getTotalDaysArrearsWithTotalPaid($credit, $feePaid) {
        $dateInitial = $credit->fecha_inicio;
        $dateFinal = date('Y-m-d') <= $credit->fecha_fin ? date('Y-m-d') : $credit->fecha_fin;
        $totalDays = $this->countDaysBetweenDatesWithPlanes($dateInitial, $dateFinal, $credit->planes);
        return $totalDays - $feePaid;
    }

    public function setArrearsToCreditComplete($credit) {
        $daysLate = $this->getTotalDaysArrears($credit);
        $credit->cuotas_atrasadas = $daysLate;
        $credit->estado_morosidad = $this->getArrearsStatusForDays($daysLate);
        $credit->save();
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