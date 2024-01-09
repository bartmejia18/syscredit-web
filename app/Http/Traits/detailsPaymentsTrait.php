<?php

namespace App\Http\Traits;

use App\DetallePagos;

trait detailsPaymentsTrait {
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
}