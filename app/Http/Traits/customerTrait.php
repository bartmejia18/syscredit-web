<?php

namespace App\Http\Traits;

use App\Creditos;
use App\CuotasClientes;
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

    public function getDayOverdueCustomer($creditId){
        $totalDays = 0;
        $credit = Creditos::where("estado",1)
                    ->where('id', $creditId)
                    ->with('planes')
                    ->first();
        if($credit){
            $today = \Carbon\Carbon::now();
            $startDate = \Carbon\Carbon::parse($credit->fecha_inicio);
            $days = $today->diffInDays($startDate);
            $totalFees = $this->getDetailsPayments($item)->totalFees; 
            if($totalFees)
                $totalDays = $days - $totalFees;
            else
                $totalDays = $days;
        }
        return $totalDays;
    }

    public function getStatusCredits($customerId){
        $detailCredits = new \stdClass();
        $credits = Creditos::where('clientes_id', $customerId)->get();
        if($credits->count() > 0){
            $complete = $credits->where('estado', 0)->count();
            $detailCredits = $complete == $credits->count() ? 3 : 2;
        } else {
            $detailCredits->status = 1;
        }

        return $detailCredits;
    }
}
