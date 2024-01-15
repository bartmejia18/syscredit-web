<?php

namespace App\Http\Traits;

use App\Creditos;
use App\DetallePagos;
use stdClass;
use App\Http\Traits\datesUtilsTrait;
use Carbon\Carbon;
use DateTime;

trait detailsCreditsTrait {

    use datesUtilsTrait;

    public function getCreditsForCustomerId($customerId) {
        $credits = Creditos::with('planes')
                        ->where('clientes_id', $customerId)
                        ->where('estado','!=',2)
                        ->orderBy('id', 'desc')
                        ->get();
        
        $credits->map(function ($item, $key) {
            if ($item->estado == 1) {
                $item->cuotas_atrasadas = $this->getTotalDaysArrears($item);
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
        $dateFinal = $this->getDateFinalCredit($credit);
    

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

    public function getTotalDaysArrears($credit) {
        $dateInitial = $credit->fecha_inicio;
        $dateFinal = $this->getDateFinalCredit($credit);

        $detailsPayments = DetallePagos::where('credito_id',$credit->id)->get();

        $dtInit = strtotime($dateInitial);
        $dtFin = strtotime($dateFinal);
        $countDaysArrears = 0;
        if ($detailsPayments->count() > 0) {
            
            if ($credit->planes->tipo == 0 || $credit->planes->tipo == 1) {
                /*
                    Ciclo for que aumenta día a día (86400) para plan diario
                */
                for($i = $dtInit; $i <= $dtFin; $i+=86400){
                    
                    $detailPayment = $this->findDateInPayments($detailsPayments, $i);

                    if ($credit->planes->domingo == 1) {
                        $sunday = new DateTime(date('d-m-Y', $i));
                        if (date("D", $sunday->getTimestamp()) != "Sun") {
                            if ($this->isValidPayment($detailPayment, $credit) == false) {
                                ++$countDaysArrears;
                            }
                        }
                    } else {
                        if ($this->isValidPayment($detailPayment, $credit) == false) {
                            ++$countDaysArrears;
                        }
                    }
                }
            } else if ($credit->planes->tipo == 2) {
                /*
                    Ciclo for que aumenta cada 7 día (604800) para plan semanal
                */
                for($i = strtotime($dateInitial); $i <= strtotime($dateFinal); $i+=604800) {
                    $detailPayment = $this->findDateInPayments($detailsPayments, $i);

                    if ($this->isValidPayment($detailPayment, $credit) == false) {
                        ++$countDaysArrears;
                    }                    
                }
            }
        } else {
            if ($dateFinal > $dateInitial) {
                $currentDate = Carbon::createFromFormat('Y-m-d', $dateFinal);
                $dateFirstPay = Carbon::createFromFormat('Y-m-d', $dateInitial);
                $countDaysArrears = $currentDate->diffInDays($dateFirstPay);
            }
        }
        return $countDaysArrears;
    }

    public function findDateInPayments($detailsPayments, $date) {
        return $detailsPayments->filter(function($payment) use ($date) {
                    return $payment->fecha_pago == date("Y-m-d", $date);
                })->first();
    }

    public function isValidPayment($detailPayment, $credit) {
        if ($detailPayment && $detailPayment->abono >= $credit->cuota_diaria && strtotime($detailPayment->fecha_pago) <= strtotime($credit->fecha_fin)) {
            return true;
        } else {
            return false;
        }
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