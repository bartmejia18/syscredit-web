<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Usuarios;
use App\Creditos;
use App\Sucursales;
use App\DetallePagos;
use App\CierreRuta;
use App\Http\Traits\detailsPaymentsTrait;
use App\Http\Traits\detailsCustomerTrait;

class CobradorController extends Controller
{
    public $statusCode  = 200;
    public $result      = false;
    public $message     = "";
    public $records     = [];
    
    use detailsPaymentsTrait;
    use detailsCustomerTrait;

    public function listCustomers(Request $request)
    {
        try {
            $hoy = date('Y-m-d');
            $registros = Creditos::with("cliente")
                                ->where("usuarios_cobrador", $request->input("idusuario"))
                                ->where('estado', 1)
                                ->where("fecha_inicio", "<=", $request->input('fecha'))
                                ->get();

            $registroextra = Creditos::with(["cliente","detallePagos"])
                                ->whereHas('detallePagos', function($q) use ($request) {                                        
                                    $q->where('estado', 1)->where('fecha_pago', $request->input('fecha'));
                                    })
                                ->where("usuarios_cobrador", $request->input("idusuario"))
                                ->where('estado', 0)
                                ->get();
            
            if($registroextra->count() > 0){
                $registros = $registros->merge($registroextra);
            }

            if( $registros ){
                $totalacobrar = 0;
                $totalminimocobrar = 0;
                $cantidadclientes = 0;
                $pagohoy = false;
                
                foreach ($registros as $item) {                
                    $detailsPayments = $this->getDetailsForCollector($item->id, $request->input('fecha'));   
                    $item['cantidad_cuotas_pagadas'] = $detailsPayments->totalFees;
                    $item['monto_abonado'] = $detailsPayments->paymentPaid;
                    $item['monto_pagado'] = $detailsPayments->totalPayment;
                    $item['fecha_inicio'] = \Carbon\Carbon::parse($item->fecha_inicio)->format('d-m-Y');
                    $item['fecha_limite'] = \Carbon\Carbon::parse($item->fecha_limite)->format('d-m-Y');
                    $item['pago_hoy'] = DetallePagos::where('credito_id', $item->id)->where('estado',1)->get()->contains('fecha_pago', $request->input('fecha'));                    

                    $totalacobrar = $totalacobrar + $item->cuota_diaria;
                    $totalminimocobrar = $totalminimocobrar + $item->cuota_minima;
                    $cantidadclientes = $cantidadclientes + 1;
                }

                $datos = [];
                $datos['total_cobrar'] = $totalacobrar;
                $datos['total_minimo'] = $totalminimocobrar;                             
                $datos['registros'] = $registros;
                

                $this->statusCode   = 200;
                $this->result       = true;
                $this->message      = "Registros consultados exitosamente";
                $this->records      = $datos;
            }
            else
                throw new \Exception("No se encontraron registros");
                
        } catch (\Exception $e) {
            $this->statusCode   = 200;
            $this->result       = false;
            $this->message      = env('APP_DEBUG') ? $e->getMessage() : "Ocurrió un problema al consultar los registros";
        }
        finally{
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

        $hoy = date('Y-m-d');
        $registros = Creditos::with("cliente")
                                ->where("usuarios_cobrador", $request->input("idusuario"))
                                ->where('estado', 1)
                                ->where("fecha_inicio", "<=", $request->input('fecha'))
                                ->get();

        $registroextra = Creditos::with(["cliente","detallePagos"])
                            ->whereHas('detallePagos', function($q) use ($request) {                                        
                                $q->where('estado', 1)->where('fecha_pago', $request->input('fecha'));
                                })
                            ->where("usuarios_cobrador", $request->input("idusuario"))
                            ->where('estado', 0)
                            ->get();
        
        if($registroextra->count() > 0){
            $registros = $registros->merge($registroextra);
        }

        if( $registros ){
            $totalacobrar = 0;
            $totalminimocobrar = 0;
            $cantidadclientes = 0;
            $pagohoy = false;
            
            foreach ($registros as $item) {                
                $detailsPaymentsForDay = $this->getDetailsForCollector($item->id, $request->input('fecha'));
                $detailsPaymentsGeneral = $this->getDetailsPaymentsForReportCollector($item->id);   
                $item['cantidad_cuotas_pagadas'] = $detailsPaymentsForDay->totalFees;
                $item['total_cuotas'] = $item->planes->dias;
                $item['cuotas_atrasadas'] = $this->getDayOverdueCustomer($item->id, $detailsPaymentsGeneral->totalFees) + 1;
                $item['cantidad_cuotas_pendientes'] = $item->planes->dias - $detailsPaymentsGeneral->totalFees;
                $item['monto_abonado'] = $detailsPaymentsForDay->paymentPaid;
                $item['monto_pagado'] = $detailsPaymentsForDay->totalPayment;
                $item['fecha_inicio'] = \Carbon\Carbon::parse($item->fecha_inicio)->format('d-m-Y');
                $item['fecha_limite'] = \Carbon\Carbon::parse($item->fecha_limite)->format('d-m-Y');
                $item['pago_hoy'] = DetallePagos::where('credito_id', $item->id)->where('estado',1)->get()->contains('fecha_pago', $request->input('fecha'));                    

                $totalacobrar = $totalacobrar + $item->cuota_diaria;
                $totalminimocobrar = $totalminimocobrar + $item->cuota_minima;
                $cantidadclientes = $cantidadclientes + 1;
            }

            $datos = new \stdClass();
            $datos->date = $request->input('fecha');
            $datos->collector = $collector->nombre;
            $datos->branch = $branch->descripcion;
            $datos->total_cobrar = $totalacobrar;
            $datos->total_minimo = $totalminimocobrar;                             
            $datos->total_cobrado = $this->getTotalPaymentCollector($request->input('idusuario'), $request->input('fecha'));   
            $datos->total_catera = $registros->sum('deudatotal');
            $datos->total_pendiente = $registros->sum('saldo');
            $datos->registros = $registros;
            
        }
        if (intval($request->input("closure_id")) != 0 ) {
            $routeClosure = CierreRuta::find($request->input("closure_id"));
            
            if ($routeClosure) {
                $routeClosure->info_closure = json_encode($datos);
                
                if ($routeClosure->save() ) {
                    $pdf = \App::make('dompdf');        
                    $pdf = \PDF::loadView('pdf.resumentodaycollector', ['data' => $datos])->setPaper('legal', 'portrait');
                    return $pdf->download($collector->nombre.'.pdf');
                }
            } else {                
                return null;
            }
        } else {            
            $pdf = \App::make('dompdf');        
            $pdf = \PDF::loadView('pdf.resumentodaycollector', ['data' => $datos])->setPaper('legal', 'portrait');
            return $pdf->download($collector->nombre.'.pdf');
        }
    }
    
} 

