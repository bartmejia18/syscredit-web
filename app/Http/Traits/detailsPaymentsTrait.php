<?php

namespace App\Http\Traits;

use App\DetallePagos;
use App\VersionSistema;
use DateTime;
use App\Http\Traits\configUtilsTrait;

trait detailsPaymentsTrait {

    use configUtilsTrait;

    public function getDetailsPayments($credit) {
        
        $detailsPayments = DetallePagos::where('credito_id', $credit->id)->where('estado', 1)->get();
        
        return $this->getAllDetails($credit, $detailsPayments);
    }

    public function getDetailsPaymentsForDate($credit, $date) {

        $detailsPayments = DetallePagos::where('credito_id', $credit->id)
                                        ->where('estado', 1)
                                        ->where('fecha_pago', $date)
                                        ->get();

        return $this->getAllDetails($credit, $detailsPayments);
    }

    public function getTotalPaymentCollector($cobradorId, $date){

        $colletion = DetallePagos::join('creditos', 'detalle_pagos.credito_id','=','creditos.id')
                        ->where('creditos.usuarios_cobrador',$cobradorId)                        
                        ->where('detalle_pagos.fecha_pago',$date)                        
                        ->where('detalle_pagos.estado', 1)
                        ->get();
            
        if($colletion)
            return $colletion->sum('abono');
        else 
            return 0;
    }

    public function getAllDetails($credit, $detailsPayments) {
        $detailPayment = new \stdClass();
        if ($detailsPayments->count() > 0) {    
            $detailPayment->allPayments = $detailsPayments; 
            $detailPayment->totalPayment = $detailsPayments->sum('abono');
            $detailPayment->totalFees =  intval($detailPayment->totalPayment/$credit->cuota_diaria);   
            $detailPayment->pendingFees = $credit->planes->dias - $detailPayment->totalFees; 
            $paymentPaid = $detailPayment->totalPayment % $credit->cuota_diaria;
            if ($paymentPaid != 0) { 
                $detailPayment->paymentPaid = $paymentPaid;
            } else {
                $detailPayment->paymentPaid = 0;
            }
            $detailPayment->paymentPercentage = round(($detailPayment->totalFees * 100)/($credit->planes->dias), 2);
        } else {
            $detailPayment->allPayments = [];
            $detailPayment->totalPayment = 0;
            $detailPayment->totalFees = 0;
            $detailPayment->pendingFees = $credit->planes->dias;
            $detailPayment->paymentPaid = 0;
            $detailPayment->paymentPercentage = 0;
        }

        return $detailPayment;
    }

    public function newGetTotalDaysArrears($credit) {
        $detailsPayments = DetallePagos::where('credito_id', $credit->id)->where('estado', 1)->get();
        $totalLatePayments = 0;
        if ($detailsPayments->count() > 0) {
            //Conteo de pagos atrasados
            $paymentsLateStatus = $detailsPayments->groupBy('fecha_corresponde')->map(function($item, $key) {
                $result = $item->contains(function($item, $key) {
                    return $item->valido == 2;
                });
                return $key = $result;
            });

            $latePaymentCount = $paymentsLateStatus->filter(function ($item, $key) {
                return $item;
            })->count();
        
            //Conteo de pagos adelantados
            $paymentsAdvanceStatus = $detailsPayments->groupBy('fecha_corresponde')->map(function($item, $key) {
                $result = $item->contains(function($item, $key) {
                    return $item->valido == 3;
                });
                return $key = $result;
            });

            $advancePaymentCount = $paymentsAdvanceStatus->filter(function ($item, $key) {
                return $item;
            })->count();

            $latePaymentByDate = $this->countDaysBetweenDatesWithPlanes(
                $this->sumDaysToDate($detailsPayments->last()->fecha_corresponde, 1),
                $this->getDateFinalCredit($credit),
                $credit->planes,
            );

            $totalLatePayments = $latePaymentCount + $latePaymentByDate;

            if ($totalLatePayments == 0) {
                $totalLatePayments = -$advancePaymentCount;
            }

        } else {
            $totalLatePayments = $this->countDaysBetweenDatesWithPlanes(
                $credit->fecha_inicio,
                $this->getDateFinalCredit($credit),
                $credit->planes,
            );
        }
        return intval($totalLatePayments);
    }

    public function getTotalDaysArrears($credit) {
        $dateInitial = $credit->fecha_inicio;
        $dateFinal = $this->getDateFinalCredit($credit);

        $detailsPayments = DetallePagos::where('credito_id',$credit->id)->get();

        $dtInit = strtotime($dateInitial);
        $dtFin = strtotime($dateFinal);
        
        $countDaysArrears = 0;
        if ($detailsPayments->count() > 0) {
            if ($credit->planes->tipo == 1) {
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
                $countDaysArrears = $this->getTotalDaysArrearsWithTotalPaid($credit, 0);
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

    public function getTotalDaysArrearsByVersion($credit) {
        return $credit->version_update == $this->getVersionSystem() ? $this->newGetTotalDaysArrears($credit) : $this->getTotalDaysArrears($credit);
    }
}