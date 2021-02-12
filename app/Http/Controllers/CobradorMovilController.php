<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Usuarios;
use App\Creditos;
use App\DetallePagos;
use App\CierreRuta;
use App\ClientesActivos;
use Auth;
use DB;
use Session;
use App\Http\Traits\detailsPaymentsTrait;

class CobradorMovilController extends Controller {
    public $statusCode  = 200;
    public $result      = false;
    public $message     = "";
    public $records     = [];
    
    use detailsPaymentsTrait;

    public function loginMovil (Request $request) {
        try {
            $usuario = Usuarios::where("user", $request->input("user"))->first();
            if ( $usuario ) {
                if( \Hash::check($request->input("password"),  $usuario->password)){
                    $this->result   = true;
                    $this->message  = "Bienvenido";
                    $this->records  = $usuario;
                } else
                    throw new \Exception("Datos incorrectos, intenta de nuevo"); 
            } else
                throw new \Exception("El email ingresado no esta registrado"); 
        } catch (\Exception $e) {
            $this->result       =   false;
            $this->message      =  env('APP_DEBUG')?$e->getMessage():'Ocurrio un problema al procesar la solicitud';
        } finally {
            $response = 
            [
                'message'   =>  $this->message,
                'result'    =>  $this->result,
                'records'   =>  $this->records
            ];
        }
        return response()->json($response, $this->statusCode);        
    }

    public function clientesActivos(Request $request) {
        try {
            $hoy = date('Y-m-d');
            $routeClosure = CierreRuta::where('cobrador_id', $request->input('idusuario'))
                                ->where('fecha_cierre', $hoy)
                                ->where('estado', 1)
                                ->whereOr('estado', 2)
                                ->first();
            if (!$routeClosure) {
                $registros = ClientesActivos::where("usuarios_cobrador", $request->input("idusuario"))
                                            ->where("fecha_inicio", "<=", $hoy)
                                            ->with("cliente")                                    
                                            ->get();
                if ($registros) {    
                    $totalacobrar = 0;
                    $totalminimocobrar = 0;
                    $pagohoy = false;
                    foreach ($registros as $item) {                        
                        $item['deudatotal'] = number_format($item->deudatotal, 2, '.', '');
                        $item['saldo'] = number_format($item->saldo, 2, '.', '');
                        $item['cuota_diaria'] = number_format($item->cuota_diaria, 2, '.', ',');
                        $item['cuota_minima'] = number_format($item->cuota_minima, 2, '.', ',');                    
                        $item['fecha_inicio'] = \Carbon\Carbon::parse($item->fecha_inicio)->format('d/m/Y');
                        $item['fecha_fin'] = \Carbon\Carbon::parse($item->fecha_fin)->format('d/m/Y');
                        $item['pago_hoy'] = $item->fecha_ultimo_pago == $hoy? true : false;
                        $item['cantidad_cuotas_pagadas'] =  $item->cantidad_cuotas_pagadas == null ? 0 : $item->cantidad_cuotas_pagadas;
                        $item['cuotas_pendientes'] = $item->cuotas_pendientes == null ? ($item->deudatotal / $item->cuota_diaria) : $item->cuotas_pendientes;
                        $item['monto_abonado'] = $item->monto_abonado == null ? 0 : $item->monto_abonado;
                        $item['fecha_ultimo_pago'] = $item->fecha_ultimo_pago == null ? " -- " : $item->fecha_ultimo_pago;
                        $totalacobrar = $totalacobrar + $item->cuota_diaria;
                    }

                    $datos = [];
                    $datos['total_cobrar'] = number_format($totalacobrar, 2, '.', ',');
                    $datos['total_minimo'] = number_format($totalminimocobrar, 2, '.', ',');                             
                    $datos['registros'] = $registros;

                    $this->statusCode   = 200;
                    $this->result       = true;
                    $this->message      = "Registros consultados exitosamente";
                    $this->records      = $datos;
                } else {
                    throw new \Exception("No se encontraron registros");
                }
            } else {
                throw new \Exception("La ruta del día de hoy ha sido cerrada");
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

    public function listadoClientesCobrador(Request $request) {
        try {
            $hoy = date('Y-m-d');

            $routeClosure = CierreRuta::where('cobrador_id', $request->input('idusuario'))
                                ->where('fecha_cierre', $hoy)
                                ->where('estado', 1)
                                ->whereOr('estado', 2)
                                ->first();

            if (!$routeClosure) {
                $registros = Creditos::where("usuarios_cobrador", $request->input("idusuario"))
                                        ->where("estado",1)
                                        ->where("fecha_inicio", "<=", $hoy)
                                        ->with("cliente", "planes")                                    
                                        ->get();
                if ($registros) {
                    $totalacobrar = 0;
                    $totalminimocobrar = 0;
                    $pagohoy = false;
                    foreach ($registros as $item) {                        
                            $detailsPayments = $this->getDetailsPayments($item);   
                            $item['deudatotal'] = number_format($item->deudatotal, 2, '.', '');
                            $item['saldo'] = number_format($item->saldo, 2, '.', '');
                            $item['cuota_diaria'] = number_format($item->cuota_diaria, 2, '.', ',');
                            $item['cuota_minima'] = number_format($item->cuota_minima, 2, '.', ',');
                            $item['cantidad_cuotas_pagadas'] = $detailsPayments->totalFees;
                            $item['cuotas_pendientes'] = $detailsPayments->pendingFees;
                            $item['monto_abonado'] = $detailsPayments->paymentPaid;                    
                            $item['fecha_inicio'] = \Carbon\Carbon::parse($item->fecha_inicio)->format('d/m/Y');
                            $item['fecha_fin'] = \Carbon\Carbon::parse($item->fecha_fin)->format('d/m/Y');
                            $item['pago_hoy'] = DetallePagos::where('credito_id', $item->id)->where('estado',1)->get()->contains('fecha_pago', $hoy);                    
                            $item['nombre_completo'] = $item->cliente->nombre.' '.$item->cliente->apellido;
                            $totalacobrar = $totalacobrar + $item->cuota_diaria;
                            $totalminimocobrar = $totalminimocobrar + $item->cuota_minima;                       
                    }
                    $datos = [];
                    $datos['total_cobrar'] = number_format($totalacobrar, 2, '.', ',');
                    $datos['total_minimo'] = number_format($totalminimocobrar, 2, '.', ',');                             
                    $datos['registros'] = $registros;
                    
                    $this->statusCode   = 200;
                    $this->result       = true;
                    $this->message      = "Registros consultados exitosamente";
                    $this->records      = $datos;
                } else
                    throw new \Exception("No se encontraron registros");
            } else 
                throw new \Exception("La ruta del día de hoy ha sido cerrada");
                
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
} 

