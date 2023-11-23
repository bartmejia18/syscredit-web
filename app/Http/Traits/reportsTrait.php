<?php

namespace App\Http\Traits;

use Illuminate\Http\Request;

use App\Creditos;
use App\DetallePagos;
use App\Http\Traits\detailsPaymentsTrait;
use App\Http\Traits\countDaysTrait;
use Carbon\Carbon;

trait reportsTrait {

    use detailsPaymentsTrait;
    use countDaysTrait;

    public function getCountCustomers(Request $request){
    
        $countCustomers = new \stdClass();        
        $credits = $this->getCustomers($request);                
        
        $countCustomers->withCredit = $credits->groupBy('clientes_id')->count();
        $countCustomers->withCreditToDay = $this->getCustomersWithCreditToDay($credits)->groupBy('clientes_id')->count();
        $countCustomers->withCreditNoToDay = intval($countCustomers->withCredit) - intval($countCustomers->withCreditToDay);
        
        return $countCustomers;
    }

    public function resumenOfCredits(Request $request) {
    
        $dateInit = "";
        $dateFin = "";

        if ($request->input('date-init') != null && $request->input('date-final') != null) {
            $dateInit = Carbon::parse($request->input('date-init'))->format('Y-m-d');
            $dateFin = Carbon::parse($request->input('date-final'))->format('Y-m-d');
        }

        $credits = $this->getCreditWithPlansAmount($request);

        if ($credits->count() > 0) {

            //Se calcula los intereses generados
            $generatedInterests = 0;
            
            foreach ($credits as $credit) {
                $generatedInterests += ($credit->montos->monto * $credit->planes->porcentaje) / 100;
            }
             

            //Se filtra los créditos hasta la fecha final ingresada
            $creditsFilteredDate = new \stdClass();     
            if ($dateFin != "") {                                    
                $creditsFilteredDate = $credits->filter(function ($item) use ($dateFin){
                                                    return $item->fecha_inicio <= date($dateFin) && $item->estado == 1;
                                                });                                            
            } else {
                $creditsFilteredDate = $credits;
            }
            
            $resumenOfCredits = new \stdClass();  
            $resumenOfCredits->pendingReceivable = $creditsFilteredDate->sum('saldo');
            $resumenOfCredits->totalReceivable = $creditsFilteredDate->sum('deudatotal');
            $resumenOfCredits->generatedInterests = $generatedInterests;

            return $resumenOfCredits; 
        } else {
            return null;
        }
    }

    public function getRevenueTotals(Request $request) {
        $dateInit = "";
        $dateFin = "";

        if ($request->input('date-init') != null && $request->input('date-final') != null) {
            $dateInit = Carbon::parse($request->input('date-init'))->format('Y-m-d');
            $dateFin = Carbon::parse($request->input('date-final'))->format('Y-m-d');
        }

        $totalCharged = new \stdClass();         
        $credits = $this->getCreditWithPlansAmount($request);        
        
        if ($credits->count() > 0) {            
            $totalCharged = 0;
            if ($dateInit != "" && $dateFin != "") {
                foreach ($credits as $credit) {
                    $totalCharged += DetallePagos::where('credito_id', $credit->id)
                                                    ->whereBetween('fecha_pago', [$dateInit, $dateFin])
                                                    ->where('estado', 1)->get()->sum('abono');
                }
            } else {
                foreach ($credits as $credit){
                    $totalCharged += DetallePagos::where('credito_id', $credit->id)                                                    
                                                    ->where('estado', 1)->get()->sum('abono');
                }
            }
        } else {        
            $totalCharged = 0;
        }
        
        return $totalCharged;
    }

    public function getPendingReceivable(Request $request, $credits) {    
        
        $dateFin = "";    
        if ($request->input('date-final') != null) {            
            $dateFin = Carbon::parse($request->input('date-final'))->format('Y-m-d');                        
        }
        
        $totalPendingReceivable = 0;
        if ($dateFin != "") {                                    
            $totalPendingReceivable = $credits->filter(function ($item) use ($dateFin){
                                                return $item->fecha_inicio <= date($dateFin) && $item->estado == 1;
                                            });                                            
        } else {
            $totalPendingReceivable = $credits;
        }    
        return $totalPendingReceivable->sum('saldo');
    }

    public function getTotalReceivable(Request $request, $credits) {
        
        $dateFin = "";    
        if ($request->input('date-final') != null) {            
            $dateFin = Carbon::parse($request->input('date-final'))->format('Y-m-d');                        
        }

        $totalReceivable = 0;
        if ($dateFin != "") {                                    
            $totalReceivable = $credits->filter(function ($item) use ($dateFin){
                                                return $item->fecha_inicio <= date($dateFin) && $item->estado == 1;
                                            });                                            
        } else {
            $totalReceivable = $credits;
        }   
         
        return $totalReceivable->sum('deudatotal');
    }

    public function getGeneratedInterests(Request $request, $credits){
       
    }

    public function getCustomersWithCreditToDay($credits) {        
        $countCredits = $credits->map(function($item,$key){            
            $today = Carbon::now();
            $startDate = Carbon::parse($item->fecha_inicio);
            $days = $today->diffInDays($startDate);
            $minimumPayment = ($days - 3) * $item->cuota_diaria;            
            $totalPayment = $this->getDetailsPayments($item)->totalPayment; 

            if($totalPayment)
                if($totalPayment > $minimumPayment)
                    return $item;
        });
        
        return $countCredits->filter(function ($item){ return $item != null;});
    }

    public function getAmountToColletedForCollector(Request $request) {
        $dateInit = Carbon::parse($request->input('date-init'))->format('Y-m-d');
        $dateFin = Carbon::parse($request->input('date-final'))->format('Y-m-d');
        $credits = "";
        if ($request->input('collector') != 0 && $request->input('plan') != 0) {
            $credits = Creditos::with('cliente','planes','montos','usuariocobrador')
                                ->where('sucursal_id', $request->input('branch'))
                                ->where('estado', '!=', 2)
                                ->where('usuarios_cobrador', $request->input('collector'))
                                ->where('planes_id', $request->input('plan'))
                                ->get();
        } else if ($request->input('plan') != 0) {
            $credits = Creditos::with('cliente','planes','montos','usuariocobrador')
                                ->where('sucursal_id', $request->input('branch'))
                                ->where('estado', '!=', 2)
                                ->where('planes_id', $request->input('plan'))
                                ->get();
        } else if ($request->input('collector') != 0) {
            $credits = Creditos::with('cliente','planes','montos','usuariocobrador')
                                ->where('sucursal_id', $request->input('branch'))
                                ->where('estado', '!=', 2)
                                ->where('usuarios_cobrador', $request->input('collector'))
                                ->get();
        } else {
            $credits = Creditos::with('cliente','planes','montos','usuariocobrador')
                                ->where('sucursal_id', $request->input('branch'))
                                ->where('estado', '!=', 2)
                                ->get();
        }

        $amountToCollected = 0;
        foreach ($credits as $credit) {
            if (date($dateInit) <= date($credit->fecha_inicio) && date($dateFin) <= date($credit->fecha_fin)) {
                $amountToCollected += $this->countDaysBetweenDates($credit->fecha_inicio, $dateFin, $credit->planes->domingo) * $credit->cuota_diaria;
            } else if (date($dateInit) >= date($credit->fecha_inicio) && date($dateFin) <= date($credit->fecha_fin)) {
                $amountToCollected += $this->countDaysBetweenDates($dateInit, $dateFin, $credit->planes->domingo) * $credit->cuota_diaria;
            } else if (date($dateInit) <= date($credit->fecha_inicio) && date($dateFin) >= date($credit->fecha_fin)) {
                $amountToCollected += $this->countDaysBetweenDates($credit->fecha_inicio, $credit->fecha_fin, $credit->planes->domingo) * $credit->cuota_diaria;
            } else if (date($dateInit) >= date($credit->fecha_inicio) && date($dateFin) >= date($credit->fecha_fin)) {
                $amountToCollected += $this->countDaysBetweenDates($dateInit, $credit->fecha_fin, $credit->planes->domingo) * $credit->cuota_diaria;
            }
        }

        return $amountToCollected;
    }

    private function getCustomers(Request $request){        
        
        $collector = $request->input('collector');
        if ($request->input('date-init') != null && $request->input('date-final') != null) {
            $dateInit = Carbon::parse($request->input('date-init'))->format('Y-m-d');
            $dateFin = Carbon::parse($request->input('date-final'))->format('Y-m-d');
        } else {
            $dateInit = "";
            $dateFin = "";
        }
        $plan = $request->input('plan');
        $branch = $request->input('branch');
        $credits = "";        

        if ($collector != "") {
            $fecha = $request->input('date-final') != null ? $dateFin : date('Y-m-d');
            $credits = Creditos::with('planes', 'montos')
                            ->where('sucursal_id', $branch)
                            ->where('usuarios_cobrador', $collector)
                            ->whereDate('fecha_inicio','<=', $fecha)
                            ->where('estado',1)
                            ->get();
        } else if ($dateInit !=  "" && $dateFin != "") {
            if ($branch != 0) {
                $credits = Creditos::with('planes', 'montos')
                            ->where('sucursal_id', $branch)
                            ->where('fecha_inicio','<=', $dateFin)
                            ->where('estado',1)
                            ->get();
            } else {
                $credits = Creditos::with('planes', 'montos')
                            ->where('fecha_inicio','<=', $dateFin)
                            ->where('estado',1)
                            ->get();
            }
        } else if ($branch != 0) {            
            $credits = Creditos::with('planes', 'montos')
                            ->where('sucursal_id', $branch)                            
                            ->where('estado',1)
                            ->get();
        } else {
            $credits = Creditos::with('planes', 'montos')
                            ->where('estado',1)
                            ->get();
        }
        
        if ($plan != "" && $plan != 0) {
            return $credits->filter(function ($item) use ($plan){ 
                    return $item->planes_id == $plan;        
            });
        } else {
            return $credits;
        }
    }

    private function getCreditWithPlansAmount(Request $request){        
        
        $collector = $request->input('collector');
        $plan = $request->input('plan');
        $branch = $request->input('branch');
        $credits = "";        
        if ($collector != "") {
            $credits = Creditos::where('sucursal_id', $branch)
                            ->where('usuarios_cobrador', $collector)
                            ->where('estado','!=',2)
                            ->with('planes', 'montos')
                            ->get();
        } else if ($branch != 0) {            
            $credits = Creditos::where('sucursal_id', $branch)                            
                            ->where('estado','!=',2)
                            ->with('planes', 'montos')
                            ->get();
        } else {
            $credits = Creditos::where('estado','!=',2)
                            ->with('planes', 'montos')
                            ->get();
        }
        
        if ($plan != "" && $plan != 0) {
            return $credits->filter(function ($item) use ($plan){ 
                    return $item->planes_id == $plan;        
            });
        } else {
            return $credits;
        }
    }

    public function getCredits(Request $request) {

        $datas = new \stdClass();   
        if ($request->input('dateInit') != null && $request->input('dateFinal') != null) {
            $dateInit = Carbon::parse($request->input('dateInit'))->format('Y-m-d');
            $dateFin = Carbon::parse($request->input('dateFinal'))->format('Y-m-d');
        } else {
            $dateInit = "";
            $dateFin = "";
        }

        $credits = "";   
    
        if ($request->input('status') == 1) {
            if ($request->input('collector') != 0 && $request->input('plan') != 0) {
                $credits = Creditos::with('cliente','planes','montos','usuariocobrador')
                                    ->where('sucursal_id', $request->input('branch'))
                                    ->where('estado', $request->input('status'))
                                    ->where('usuarios_cobrador', $request->input('collector'))
                                    ->where('planes_id', $request->input('plan'))
                                    ->whereBetween('created_at', [$dateInit." 00:00:00", $dateFin." 23:59:59"])
                                    ->get();
            } else if ($request->input('plan') != 0) {
                $credits = Creditos::with('cliente','planes','montos','usuariocobrador')
                                    ->where('sucursal_id', $request->input('branch'))
                                    ->where('estado', $request->input('status'))
                                    ->where('planes_id', $request->input('plan'))
                                    ->whereBetween('created_at', [$dateInit." 00:00:00", $dateFin." 23:59:59"])
                                    ->get();
            } else if ($request->input('collector') != 0) {
                $credits = Creditos::with('cliente','planes','montos','usuariocobrador')
                                    ->where('sucursal_id', $request->input('branch'))
                                    ->where('estado', $request->input('status'))
                                    ->where('usuarios_cobrador', $request->input('collector'))
                                    ->whereBetween('created_at', [$dateInit." 00:00:00", $dateFin." 23:59:59"])
                                    ->get();
            } else {
                $credits = Creditos::with('cliente','planes','montos','usuariocobrador')
                                    ->where('sucursal_id', $request->input('branch'))
                                    ->where('estado', $request->input('status'))
                                    ->whereBetween('created_at', [$dateInit." 00:00:00", $dateFin." 23:59:59"])
                                    ->get();
            }
        } else if ($request->input('status') == 0) {
            if ($request->input('collector') != 0 && $request->input('plan') != 0) {
                $credits = Creditos::with('cliente','planes','montos','usuariocobrador')
                                    ->where('sucursal_id', $request->input('branch'))
                                    ->where('estado', $request->input('status'))
                                    ->where('usuarios_cobrador', $request->input('collector'))
                                    ->where('planes_id', $request->input('plan'))
                                    ->whereBetween('fecha_finalizado', [$dateInit, $dateFin])
                                    ->get();
            } else if ($request->input('plan') != 0) {
                $credits = Creditos::with('cliente','planes','montos','usuariocobrador')
                                    ->where('sucursal_id', $request->input('branch'))
                                    ->where('estado', $request->input('status'))
                                    ->where('planes_id', $request->input('plan'))
                                    ->whereBetween('fecha_finalizado', [$dateInit, $dateFin])
                                    ->get();
            } else if ($request->input('collector') != 0) {
                $credits = Creditos::with('cliente','planes','montos','usuariocobrador')
                                    ->where('sucursal_id', $request->input('branch'))
                                    ->where('estado', $request->input('status'))
                                    ->where('usuarios_cobrador', $request->input('collector'))
                                    ->whereBetween('fecha_finalizado', [$dateInit, $dateFin])
                                    ->get();
            } else {
                $credits = Creditos::with('cliente','planes','montos','usuariocobrador')
                                    ->where('sucursal_id', $request->input('branch'))
                                    ->where('estado', $request->input('status'))
                                    ->whereBetween('fecha_finalizado', [$dateInit, $dateFin])
                                    ->get();
            }
        }
        if ($credits) {
            $datas->sumAmountCredits = $credits->sum(function ($item) { 
                return $item->montos->monto;  
            });

            $datas->sumAmountTotalCredit = $credits->sum(function ($item) {
                return $item->deudatotal;
            });
            
            $datas->credits = $credits->map(function($item, $key){
                $item->fecha_inicio = Carbon::parse($item->fecha_inicio)->format('d-m-Y');
                $item->fecha_creacion = Carbon::parse($item->created_at)->format('d-m-Y');
                return $item;
            });
        }

        return $datas;
    }
}