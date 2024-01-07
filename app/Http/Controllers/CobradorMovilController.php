<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Usuarios;
use App\CierreRuta;
use App\ClientesActivos;
use App\Http\Traits\detailsPaymentsTrait;
use App\Http\Traits\countDaysTrait;
use App\Http\Traits\detailsCreditsTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class CobradorMovilController extends Controller {
    public $statusCode  = 200;
    public $result      = false;
    public $message     = "";
    public $records     = null;
    
    use detailsPaymentsTrait;
    use countDaysTrait;
    use detailsCreditsTrait;

    public function loginMovil (Request $request) {
        try {
            $usuario = Usuarios::where("user", $request->input("user"))
                            ->where("estado", 1)
                            ->first();
            
            if ( $usuario ) {
                if(Hash::check($request->input("password"),  $usuario->password)){
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
            $usuarioCobrador = Usuarios::where('id', $request->input('idusuario'))
                                    ->where('estado',1)
                                    ->first();
            if ($usuarioCobrador) {
                
                $routeClosure = CierreRuta::where('cobrador_id', $request->input('idusuario'))
                                ->where('fecha_cierre', $hoy)
                                ->where('estado', 1)
                                ->whereOr('estado', 2)
                                ->first();
                
                if (!$routeClosure) {
                    $registros = ClientesActivos::with('cliente','planes')
                                                ->where("fecha_inicio", "<=", $hoy)
                                                ->where("usuarios_cobrador", $request->input("idusuario"))                     
                                                ->get();
                    if ($registros) {    
                        $totalacobrar = 0;
                        foreach ($registros as $item) {     
                                
                            $detailsPaymentsGeneral = $this->getDetailsPayments($item); 

                            $currentDate = Carbon::parse($request->input('fecha'));
                            $dateFirstPay = Carbon::parse($item->fecha_inicio);
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
                            }

                            $item['deudatotal'] = number_format($item->deudatotal, 2, '.', '');
                            $item['saldo'] = number_format($item->saldo, 2, '.', '');
                            $item['cuota_diaria'] = number_format($item->cuota_diaria, 2, '.', '');
                            $item['cuota_minima'] = number_format($item->cuota_minima, 2, '.', ',');                    
                            $item['fecha_inicio'] = Carbon::parse($item->fecha_inicio)->format('d/m/Y');
                            $item['fecha_fin'] = Carbon::parse($item->fecha_fin)->format('d/m/Y');                    
                            $item['pago_hoy'] = $item->fecha_ultimo_pago == $hoy? true : false;
                            $item['cantidad_cuotas_pagadas'] = $detailsPaymentsGeneral->totalFees; 
                            $item['cuotas_pendientes'] = $item->planes->dias - $detailsPaymentsGeneral->totalFees;
                            $item['cuotas_atrasadas'] = $this->getTotalDaysArrears($item, $detailsPaymentsGeneral->totalFees);
                            $item['monto_abonado'] = $item->monto_abonado == null ? 0 : Intval($item->monto_abonado);
                            $item['fecha_ultimo_pago'] = $item->fecha_ultimo_pago == null ? " -- " : $item->fecha_ultimo_pago;
                            $item['total_pagado'] = number_format($item->deudatotal - $item->saldo, 2, '.', '');
                        }

                        $datos = [];
                        $datos['total_cobrar'] = number_format($totalacobrar, 2, '.', ',');
                        $datos['total_cobrado'] = number_format($this->getTotalPaymentCollector($request->input('idusuario'), $hoy), 2, '.', ',');                         
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
            } else {
                throw new \Exception("Lo sentimos ha ocurrido un problema!");
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
} 

