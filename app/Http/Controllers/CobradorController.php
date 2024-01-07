<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Usuarios;
use App\Creditos;
use App\Sucursales;
use App\DetallePagos;
use App\CierreRuta;
use App\Http\Traits\detailsPaymentsTrait;
use App\Http\Traits\detailsCustomerTrait;
use App\Http\Traits\detailsCreditsTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;

class CobradorController extends Controller {
    public $statusCode  = 200;
    public $result      = false;
    public $message     = "";
    public $records     = [];
    
    use detailsPaymentsTrait;
    use detailsCustomerTrait;
    use detailsCreditsTrait;

    public function listCustomers(Request $request) {
        try {
            $credits = $this->getCreditsForCollector($request);

            if ($credits->credits->count() > 0) {
                $datos['total_cobrar'] = $credits->totalacobrar;
                $datos['total_minimo'] = $credits->totalminimocobrar;                             
                $datos['registros'] = $credits->credits;

                $this->statusCode   = 200;
                $this->result       = true;
                $this->message      = "Registros consultados exitosamente";
                $this->records      = $datos;
            } else {
                throw new \Exception("No se encontraron registros");
            }   
        } catch (\Exception $e) {
            $this->statusCode   = 200;
            $this->result       = false;
            $this->message      = env('APP_DEBUG') ? $e->getMessage() : "Ocurrió un problema al consultar los registros";
        } finally {
            $response = [
                'result'    => $this->result,
                'message'   => $this->message,
                'records'   => $this->records,
            ];
            return response()->json($response, $this->statusCode);
        }
    }

    public function generatePdf(Request $request){
        $collector = Usuarios::find($request->input("idusuario"));
        $branch = Sucursales::find($collector->sucursales_id);

        $credits = $this->getCreditsForCollector($request);

        if ($credits->credits->count() > 0 ) {
            $datos = new \stdClass();
            $datos->date = $request->input('fecha');
            $datos->collector = $collector->nombre;
            $datos->branch = $branch->descripcion;
            $datos->total_cobrar = $credits->totalacobrar;
            $datos->total_minimo = $credits->totalminimocobrar;                             
            $datos->total_cobrado = $this->getTotalPaymentCollector($request->input('idusuario'), $request->input('fecha'));   
            $datos->total_catera = $credits->credits->sum('deudatotal');
            $datos->total_pendiente = $credits->credits->sum('saldo');
            $datos->registros = $credits->credits;
        }

        if (intval($request->input("closure_id")) != 0 ) {

            $routeClosure = CierreRuta::find($request->input("closure_id"));
            
            if ($routeClosure) {
                $routeClosure->info_closure = json_encode($datos);
                
                if ($routeClosure->save() ) {
                    $pdf = App::make('dompdf');        
                    $pdf = \PDF::loadView('pdf.resumentodaycollector', ['data' => $datos])->setPaper('legal', 'portrait');
                    return $pdf->download($collector->nombre.'.pdf');
                }
            } else {                
                return null;
            }
        } else {            
            $pdf = App::make('dompdf');        
            $pdf = \PDF::loadView('pdf.resumentodaycollector', ['data' => $datos])->setPaper('legal', 'portrait');
            return $pdf->download($collector->nombre.'.pdf');
        }
    }

    function getCreditsForCollector(Request $request) {
        
        $listCredits = new \stdClass();
        $credits = Creditos::with("cliente", "planes")
                                ->where("usuarios_cobrador", $request->input("idusuario"))
                                ->where('estado', 1)
                                ->where("fecha_inicio", "<=", $request->input('fecha'))
                                ->get();

        $creditsExtra = Creditos::with("cliente", "planes")
                                ->where("usuarios_cobrador", $request->input("idusuario"))
                                ->where("fecha_finalizado", $request->input('fecha'))
                                ->where('estado', 0)
                                ->get();
        
        if ($creditsExtra->count() > 0) {
            $credits = $credits->merge($creditsExtra);
        }

        if ($credits->count() > 0) {
            $totalacobrar = 0;
            $totalminimocobrar = 0;
            $cantidadclientes = 0;
        
            foreach ($credits as $item) {                
                $detailsPaymentsForDay = $this->getDetailsPaymentsForDate($item, $request->input('fecha'));
                $detailsPaymentsGeneral = $this->getDetailsPayments($item);   
                $item['cantidad_cuotas_pagadas'] = $detailsPaymentsForDay->totalFees;
                $item['total_cuotas'] = $item->planes->dias;
                $item['cuotas_atrasadas'] = $this->getTotalDaysArrears($item, $detailsPaymentsGeneral->totalFees);
                $item['cantidad_cuotas_pendientes'] = $item->planes->dias - $detailsPaymentsGeneral->totalFees;
                $item['monto_abonado'] = $detailsPaymentsForDay->paymentPaid;
                $item['monto_pagado'] = $detailsPaymentsForDay->totalPayment;
                $item['fecha_inicio'] = \Carbon\Carbon::parse($item->fecha_inicio)->format('d-m-Y');
                $item['fecha_limite'] = \Carbon\Carbon::parse($item->fecha_limite)->format('d-m-Y');
                $item['pago_hoy'] = DetallePagos::where('credito_id', $item->id)->where('estado',1)->get()->contains('fecha_pago', $request->input('fecha'));                    

                $currentDate = Carbon::createFromFormat('Y-m-d', $request->input('fecha'));
                $dateFirstPay = Carbon::parse($item->fecha_inicio)->format('d-m-Y');
                $diffDays = $currentDate->diffInDays($dateFirstPay);
                $sumarPayToday = false;
                switch ($item->planes->tipo) {
                    case (1) : 
                        $sumarPayToday = $diffDays % 1 == 0;
                        break;
                    case (2) : 
                        $sumarPayToday = $diffDays % 7 == 0;
                        break;
                    case (3) : 
                        $sumarPayToday = $diffDays % 30 == 0;
                        break;
                    default : 
                        $sumarPayToday = false;
                        break;
                }

                if ($sumarPayToday) {
                    $totalacobrar = $totalacobrar + $item->cuota_diaria;
                    $totalminimocobrar = $totalminimocobrar + $item->cuota_minima;
                }
                
                $cantidadclientes = $cantidadclientes + 1;
            }

            $listCredits->totalacobrar = $totalacobrar;
            $listCredits->totalminimocobrar = $totalminimocobrar;
            $listCredits->cantidadclientes = $cantidadclientes;
            $listCredits->credits = $credits;
        }

        return $listCredits;
    }
} 
