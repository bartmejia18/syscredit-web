<?php

namespace App\Http\Traits;

use App\Creditos;
use App\DetallePagos;

trait detailsPaymentsTrait {
    public function getDetailsPayments($creditId) {
        
        $detailPayment = new \stdClass();
        $credit = Creditos::where("id", $creditId)->where("estado", 1)->with('planes', 'montos')->first();        
        if ($credit) {
            $detailsPayments = DetallePagos::where('credito_id', $creditId)->where('estado', 1)->get();
            
            if ($detailsPayments->count() > 0) {    
                $detailPayment->allPayments = $detailsPayments; 
                $detailPayment->totalPayment = $detailsPayments->sum('abono');
                $detailPayment->totalFees =  intval($detailPayment->totalPayment/$credit->cuota_diaria);   
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
                $detailPayment->paymentPaid = 0;
                $detailPayment->paymentPercentage = 0;
            }
        }
        return $detailPayment;
    }

    public function getDetailsPaymentsForReportCollector($creditId) {
        
        $detailPayment = new \stdClass();
        $credit = Creditos::where("id", $creditId)->where("estado", "!=", 2)->with('planes', 'montos')->first();        
        if($credit){
            $detailsPayments = DetallePagos::where('credito_id', $creditId)->where('estado', 1)->get();
            
            if($detailsPayments->count() > 0){     
                $detailPayment->totalPayment = $detailsPayments->sum('abono');
                $detailPayment->totalFees =  intval($detailPayment->totalPayment/$credit->cuota_diaria);   
                $paymentPaid = $detailPayment->totalPayment % $credit->cuota_diaria;
                if($paymentPaid != 0){ 
                    $detailPayment->paymentPaid = $paymentPaid;
                } else {
                    $detailPayment->paymentPaid = 0;
                }
                $detailPayment->paymentPercentage = round(($detailPayment->totalFees * 100)/($credit->planes->dias), 2);
            } else {
                $detailPayment->totalPayment = 0;
                $detailPayment->totalFees = 0;
                $detailPayment->paymentPaid = 0;
                $detailPayment->paymentPercentage = 0;
            }
        }
        
        return $detailPayment;
    }

    public function getDetailsForCollector($creditId, $date) {
        
        $detailPayment = new \stdClass();
        $credit = Creditos::where("id", $creditId)->with('planes', 'montos')->first();        
        if($credit && $credit->estado != 2){
            $detailsPayments = DetallePagos::where('credito_id', $credit->id)->where('estado', 1)->where('fecha_pago', $date)->get();
            
            if($detailsPayments->count() > 0){     
                $detailPayment->totalPayment = $detailsPayments->sum('abono');
                $detailPayment->totalFees =  intval($detailPayment->totalPayment/$credit->cuota_diaria);   
                $paymentPaid = $detailPayment->totalPayment % $credit->cuota_diaria;
                if($paymentPaid != 0){ 
                    $detailPayment->paymentPaid = $paymentPaid;
                } else {
                    $detailPayment->paymentPaid = 0;
                }
                $detailPayment->paymentPercentage = round(($detailPayment->totalFees * 100)/($credit->planes->dias), 2);
            } else {
                $detailPayment->totalPayment = 0;
                $detailPayment->totalFees = 0;
                $detailPayment->paymentPaid = 0;
                $detailPayment->paymentPercentage = 0;
            }
        }
        
        return $detailPayment;
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
}
