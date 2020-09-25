<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Clientes;
use App\Creditos;
use App\DetallePagos;
use App\Usuarios;
use DB;
use App\Http\Traits\detailsPaymentsTrait;
use App\Http\Traits\detailsCreditsTrait;

class ClientesController extends Controller {
    public $statusCode  = 200;
    public $result      = false;
    public $message     = "";
    public $records     = [];
    protected $sessionKey = 'usuario';
    
    use detailsPaymentsTrait;
    use detailsCreditsTrait;

    public function index(Request $request) {
        try {
            $userId = $request->session()->get('usuario')->tipo_usuarios_id == 4 ? $request->session()->get('usuario')->id : 0;

            if ($userId != 0) {
                $registros = Clientes::select("clientes.*")
                            ->join('creditos', 'clientes.id', '=', 'creditos.clientes_id')
                            ->where('creditos.usuarios_cobrador', $userId)
                            ->groupBy('clientes.id')
                            ->get();
            } else {
                $registros = Clientes::all();
            }
            
            if ($registros) {
                $registros->map(function ($item, $key){   
                    if($item->status == 1){
                        $detailCredits = $this->getStatusCredits($item->id);
                        $item->statusCredit = $detailCredits->status;
                        $item->totalCredits = $detailCredits->total;
                        $item->collector = $detailCredits->collector;
                    } else {
                        $item->statusCredit = 4;
                        $item->totalCredits = 0;
                        $item->collector = 0;
                    }
                    return $item;
                });

                $this->statusCode   = 200;
                $this->result       = true;
                $this->message      = "Registros consultados exitosamente";
                $this->records      = $registros;
            } else
                throw new \Exception("No se encontraron registros");
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

    public function create(){}
    
    public function store(Request $request){
        try {
            $nuevoRegistro = \DB::transaction( function() use ($request) {
                                $nuevoRegistro = Clientes::create([
                                                    'sucursal_id'   => $request->session()->get($this->sessionKey)->sucursales_id,
                                                    'nombre'        => $request->input('nombre'),
                                                    'apellido'      => $request->input('apellido'),
                                                    'dpi'           => $request->input('dpi'),
                                                    'telefono'      => $request->input('telefono'),
                                                    'direccion'      => $request->input('direccion'),
                                                    'estado_civil'  => $request->input('estado_civil'),
                                                    'sexo'          => $request->input('sexo'),
                                                    'categoria'     => 'A',
                                                    'color'         => 'verde',
                                                    'status'        => 1
                                                ]);

                                if( !$nuevoRegistro )
                                    throw new \Exception("No se pudo crear el registro");
                                else
                                    return $nuevoRegistro;
                            });

            $this->statusCode   = 200;
            $this->result       = true;
            $this->message      = "Registro creado exitosamente";
            $this->records      = $nuevoRegistro;

        } catch (\Exception $e) {
            $this->statusCode   = 200;
            $this->result       = false;
            $this->message      = env('APP_DEBUG') ? $e->getMessage() : "Ocurrió un problema al crear el registro";
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

    public function show($id){
        try {
            $registro = Clientes::find( $id );

            if( $registro ){
                $this->statusCode   = 200;
                $this->result       = true;
                $this->message      = "Registro consultado exitosamente";
                $this->records      = $registro;
            }
            else
                throw new \Exception("No se encontró el registro");
                
        } catch (\Exception $e) {
            $this->statusCode   = 200;
            $this->result       = false;
            $this->message      = env('APP_DEBUG') ? $e->getMessage() : "Ocurrió un problema al consultar el registro";
        }
        finally
        {
            $response = [
                'result'    => $this->result,
                'message'   => $this->message,
                'records'   => $this->records,
            ];

            return response()->json($response, $this->statusCode);
        }
    }

    public function edit($id){}
    
    public function update(Request $request, $id)
    {
        try {
            \DB::beginTransaction();
            $registro = Clientes::find( $id );
            $registro->nombre       = $request->input('nombre', $registro->nombre);
            $registro->apellido     = $request->input('apellido', $registro->apellido);
            $registro->dpi          = $request->input('dpi', $registro->dpi);
            $registro->telefono     = $request->input('telefono', $registro->telefono);
            $registro->direccion     = $request->input('direccion', $registro->direccion);
            $registro->estado_civil = $request->input('estado_civil', $registro->estado_civil);
            $registro->sexo         = $request->input('sexo', $registro->sexo);
            $registro->categoria    = $request->input('categoria', $registro->categoria);
            $registro->color        = $request->input('color', $registro->color);
            $registro->status       = $request->input('status', 1);
            
            $credit = Creditos::where("clientes_id", $id)->where("estado","!=",2)->get();
            
            if($credit->count() > 0){
                $credit->map(function ($item, $key) use ($request){   
                    if ($request->input('collector') != 0)
                        $item->usuarios_cobrador = $request->input('collector', $item->usuarios_cobrador);
                    else
                        $item->usuarios_cobrador = $item->usuarios_cobrador;
                    $item->save();
                    return $item;
                });
            }

            if($registro->save()){
                \DB::commit();
                $this->statusCode   = 200;
                $this->result       = true;
                $this->message      = "Registro editado exitosamente";
                $this->records      = $registro;
            } else {
                throw new \Exception("Error al editar el cliente");
            }
                
        } catch (\Exception $e) {
            \DB::rollback();
            $this->statusCode   = 200;
            $this->result       = false;
            $this->message      = env('APP_DEBUG') ? $e->getMessage() : "Ocurrió un problema al editar el registro";
        }
        finally
        {
            $response = [
                'result'    => $this->result,
                'message'   => $this->message,
                'records'   => $this->records,
            ];

            return response()->json($response, $this->statusCode);
        }
    }

    
    public function destroy($id) {
        try {
            $credit = Creditos::where("clientes_id", $id)->get();

            $deleteRegistro = \DB::transaction( function() use ( $id ) {
                                $credit = Creditos::where('clientes_id', $id)->where('estado', 1)->get();

                                if ($credit->count() > 0) {
                                    $credit->map(function ($item, $key) {   
                                        $item->estado = 2;
                                        $item->save();
                                        return $item;
                                    });
                                }
                                $registro = Clientes::find( $id );
                                $registro->status = 2;
                                $registro->save();
                            });

            $this->statusCode   = 200;
            $this->result       = true;
            $this->message      = "Registro eliminado exitosamente";
            
        } catch (\Exception $e) {
            $this->statusCode   = 200;
            $this->result       = false;
            $this->message      = env('APP_DEBUG') ? $e->getMessage() : "Ocurrió un problema al eliminar el registro";
        } finally {
            $response = [
                'result'    => $this->result,
                'message'   => $this->message,
            ];
            return response()->json($response, $this->statusCode);
        }
    }

    public function buscarCliente(Request $request) {
        try {
            $cliente = Clientes::where('dpi', $request->input('dpi') )->where('sucursal_id', $request->session()->get('usuario')->sucursales_id)->first();
            if ($cliente) {
                if ($cliente->status == 1) {
                    $detailCredits = $this->getStatusCredits($cliente->id);
                    $cliente->statusCredit = $detailCredits->status;
                    $cliente->totalCredits = $detailCredits->total;
                    $cliente->collector = $detailCredits->collector;
                    $cliente->cobrador = Usuarios::find($detailCredits->collector);
                } else {
                    $cliente->statusCredit = 4;
                    $cliente->totalCredits = 0;
                    $cliente->collector = 0;
                    $cliente->cobrador = "";
                }  
                $this->statusCode   = 200;
                $this->result       = true;
                $this->message      = "Registro consultado exitosamente";
                $this->records      = $cliente;
                
            } else
                throw new \Exception("El cliente ingresado no se encuentra en el sistema, revise los datos y vuelva a intentarlo. ");
        } catch (\Exception $e) {
            $this->statusCode   = 200;
            $this->result       = false;
            $this->message      = env('APP_DEBUG') ? $e->getMessage() : "Ocurrió un problema al consultar el registro";
        } finally {
            $response = [
                'result'    => $this->result,
                'message'   => $this->message,
                'records'   => $this->records,
            ];
            return response()->json($response, $this->statusCode);
        }
    }

    public function buscarCreditoCliente(Request $request){
        try {            
            $creditoCliente = Clientes::with(['creditos' => function($item) {
                                            $item->where('estado', 1);
                                        }])
                                        ->where('nombre', $request->input('name'))
                                        ->where('apellido', $request->input('lastname'))                                        
                                        ->where('sucursal_id', $request->session()->get('usuario')->sucursales_id)                                    
                                        ->first();
            
            if ($creditoCliente) {
                if ($creditoCliente->creditos->count() > 0) {
                    $creditoCliente->creditos = $creditoCliente->creditos->map(function($item,$key) {
                                                    if ($item->estado == 1) {
                                                        $detailsPayments = $this->getDetailsPayments($item->id);                                    
                                                        $item->saldo_abonado = $detailsPayments->paymentPaid;
                                                        $item->cuotas_pagados = $detailsPayments->totalFees;
                                                        $item->total_cancelado = $detailsPayments->totalPayment;
                                                    }
                                                    return $item;
                                                });                                                        
                    $this->statusCode   = 200;
                    $this->result       = true;
                    $this->message      = "Registro consultado exitosamente";
                    $this->records      =  $creditoCliente;
                } else 
                    throw new \Exception("Cliente no cuenta con crédito");      
            } else
                throw new \Exception("No cliente ingresado no existe");  
        } catch (\Exception $e) {
            $this->statusCode   = 200;
            $this->result       = false;
            $this->message      = env('APP_DEBUG') ? $e->getMessage() : "Ocurrió un problema al consultar el registro";
        } finally {
            $response = [
                'result'    => $this->result,
                'message'   => $this->message,
                'records'   => $this->records,
            ];
            return response()->json($response, $this->statusCode);
        }
    }

    public function detalleCreditoCliente(Request $request) {
        try {            
            /*$creditoCliente = Clientes::with(['creditos' => function($item) {
                                            $item->where('estado', 1);
                                        }])                                
                                        ->where('sucursal_id', $request->session()->get('usuario')->sucursales_id)
                                        ->where('id', $request->input('cliente_id'))                                        
                                        ->first();*/
                                        
            $creditoCliente = Clientes::with("creditos")                                
                                        ->where('sucursal_id', $request->session()->get('usuario')->sucursales_id)
                                        ->where('id', $request->input('cliente_id'))                                        
                                        ->first();
            if ($creditoCliente) {                
                if ($creditoCliente->creditos->count() > 0) {                                                                                
                    $creditoCliente->creditos = $creditoCliente->creditos->map(function($item,$key){
                                                    if($item->estado == 1){                                                    
                                                        $detailsPayments = $this->getDetailsPayments($item->id);                                    
                                                        //$item->allPayments = $detailsPayments->allPayments;
                                                        $item->saldo_abonado = $detailsPayments->paymentPaid;
                                                        $item->cuotas_pagados = $detailsPayments->totalFees;
                                                        $item->total_cancelado = $detailsPayments->totalPayment;
                                                        $item->porcentaje_pago = $detailsPayments->paymentPercentage;
                                                    }
                                                    return $item;
                                                });
                   
                    $this->statusCode   = 200;
                    $this->result       = true;
                    $this->message      = "Registro consultado exitosamente";
                    $this->records      = $creditoCliente;
                } else 
                    throw new \Exception("Cliente no cuenta con crédito");      
            } else
                throw new \Exception("No cliente ingresado no existe");  
        } finally {
            $response = [
                'result'    => $this->result,
                'message'   => $this->message,
                'records'   => $this->records,
            ];
            return response()->json($response, $this->statusCode);
        }
    }

    public function customersByBranch(Request $request) {
        try {
            $customers = Clientes::with('creditos')   
                                ->whereHas('creditos', function($credit){
                                    $credit->where('estado', 1);
                                })
                                ->where('sucursal_id',$request->session()->get('usuario')->sucursales_id)
                                ->get();

            if ($customers) {
                $this->statusCode   = 200;
                $this->result       = true;
                $this->message      = "Registro consultado exitosamente";
                $this->records      = $customers;
            }
        } catch (\Exception $e) {
            $this->statusCode   = 200;
            $this->result       = false;
            $this->message      = env('APP_DEBUG') ? $e->getMessage() : "Ocurrió un problema al consultar el registro";
        } finally {
            $response = [
                'result'    => $this->result,
                'message'   => $this->message,
                'records'   => $this->records,
            ];

            return response()->json($response, $this->statusCode);
        }
    }

   /* public function printPDFAccountStatus(Request $request) {
        $datos = json_decode($this->detalleCreditoCliente($request));
        $newDatos = $datos->creditos->filter(function ($item) use ($request){return $item->id == 7;});
        return $datos;
    }*/
}