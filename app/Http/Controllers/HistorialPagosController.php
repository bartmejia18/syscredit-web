<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Session;
use App\DetallePagos;
use App\Clientes;
use App\Creditos;
use DB;
use App\Http\Traits\detailsPaymentsTrait;

class HistorialPagosController extends Controller
{
    public $statusCode = 200;
    public $result = false;
    public $message = '';
    public $records = [];

    use detailsPaymentsTrait;

    public function paymentHistory(Request $request)
    {
        try {
            $items = DB::table('detalle_pagos')->select("detalle_pagos.*", "creditos.clientes_id")
                        ->join('creditos', 'detalle_pagos.credito_id','=','creditos.id')
                        ->where('creditos.usuarios_cobrador',$request->input('cobrador_id'))
                        ->where('detalle_pagos.fecha_pago',$request->input('fecha_pago'))                        
                        ->where('detalle_pagos.estado', 1)
                        ->get();

            if($items){
                $colletion = collect($items);
                $colletion->map(function ($item, $key){
                    $dateTime = explode(" ", $item->created_at);
                    $item->hour_payment = $dateTime[1];
                    $item->customer = Clientes::find($item->clientes_id);
                    return $item;
                });

                $this->statusCode   = 200;
                $this->result       = true;
                $this->message      = "Registro consultados exitosamente";
                $this->records      = $items;
            }
            else
                throw new \Exception("No se encontraron registros");
                
        } 
        catch (\Exception $e) {
            $this->statusCode = 200;
            $this->result = false;
            $this->message = env('APP_DEBUG') ? $e->getMessage() : "Ocurrió un problema al consultar los datos";
            
        } finally {
            $response = [
                'result'    => $this->result,
                'message'   => $this->message,
                'records'   => $this->records,
            ];
            return response()->json($response, $this->statusCode);
        }
    }

    public function deletePayment(Request $request){
        try{

            $detallePago = DetallePagos::where('id', $request->input('detalle_id'))
                                        ->where('estado', 1)
                                        ->first();
            
            if($detallePago){
                
                $detallePago->estado = 2;
                
                if($detallePago->save()){                    
                    $credito = Creditos::find($detallePago->credito_id);
                    $credito->saldo = $credito->saldo + $detallePago->abono;
                    $credito->estado = 1;
                    
                    if($credito->save()){
                        $this->statusCode   = 200;
                        $this->result       = true;
                        $this->message      = "Cobro eliminado correctamente";
                    } else {
                        throw new \Exception("Error al eliminar el pago");        
                    }
                }else {
                    throw new \Exception("Error al eliminar el pago");
                }    
            } else {
                throw new \Exception("No se encontró el pago a eliminar");
            }
        }
        catch (\Exception $e) {
            $this->statusCode = 200;
            $this->result = false;
            $this->message = env('APP_DEBUG') ? $e->getMessage() : "Ocurrió un problema al consultar los datos";
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

    public function totalColletion(Request $request){
        try {                    
            $this->statusCode   = 200;
            $this->result       = true;
            $this->message      = "Registro consultados exitosamente";
            $this->records      = $this->getTotalPaymentCollector($request->input('cobrador_id'), $request->input('fecha'));   
        } 
        catch (\Exception $e) {
            $this->statusCode = 200;
            $this->result = false;
            $this->message = env('APP_DEBUG') ? $e->getMessage() : "Ocurrió un problema al consultar los datos";
            
        } finally {
            $response = [
                'result'    => $this->result,
                'message'   => $this->message,
                'records'   => $this->records,
            ];
            return response()->json($response, $this->statusCode);
        }
    }

    public function historyForCustomer(Request $request) {
        try {
            $records = DetallePagos::where('credito_id', $request->input('credito_id'))
                                        ->where('estado', 1)
                                        ->get();

            if ($records) {
                $colletion = collect($records);
                $colletion->map(function ($item, $key){
                    return $item->fecha_pago = \Carbon\Carbon::parse($item->fecha_pago)->format('d/m/Y');
                });

                $this->statusCode   = 200;
                $this->result       = true;
                $this->message      = "Registro consultados exitosamente";
                $this->records      = $records;
            } else {
                throw new \Exception("No se encontraron registros");
            }   
        } 
        catch (\Exception $e) {
            $this->statusCode = 200;
            $this->result = false;
            $this->message = env('APP_DEBUG') ? $e->getMessage() : "Ocurrió un problema al consultar los datos";
            
        } finally {
            $response = [
                'result'    => $this->result,
                'message'   => $this->message,
                'records'   => $this->records,
            ];
            return response()->json($response, $this->statusCode);
        }
    }
}
