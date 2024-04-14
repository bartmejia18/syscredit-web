<?php

namespace App\Http\Traits;

use App\Creditos;
use App\Http\Traits\detailsPaymentsTrait;

trait customerTrait {

    use detailsPaymentsTrait;
    
    public function getCustomersWithCreditToDay($branchId) {
        
        $credits = Creditos::where("estado",1)->where("sucursal_id", $branchId)->with('planes')->groupBy("clientes_id") ->get();
        
        $countCredits = $credits->map(function($item,$key){
            
            $today = \Carbon\Carbon::now();
            $startDate = \Carbon\Carbon::parse($item->fecha_inicio);
            $days = $today->diffInDays($startDate);
            $minimumPayment = ($days - 3) * $item->cuota_diaria;            
            $totalPayment = $this->getDetailsPayments($item)->totalPayment; 

            if($totalPayment)
                if($totalPayment > $minimumPayment)
                    return $item;
        });
        
        return $countCredits->filter(function ($item){ return $item != null;});
    }

    public function getCustomersWithCreditToDayForCollector($collectorId) {
        
        $credits = Creditos::where("estado",1)->where("usuarios_cobrador", $collectorId)->with('planes')->groupBy("clientes_id")->get();
        
        $countCredits = $credits->map(function($item,$key){
            
            $today = \Carbon\Carbon::now();
            $startDate = \Carbon\Carbon::parse($item->fecha_inicio);
            $days = $today->diffInDays($startDate);
            $minimumPayment = ($days - 3) * $item->cuota_diaria;            
            $totalPayment = $this->getDetailsPayments($item)->totalPayment; 

            if($totalPayment)
                if($totalPayment > $minimumPayment)
                    return $item;
        });
        
        return $countCredits->filter(function ($item){ return $item != null;});
    }
}
