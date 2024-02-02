<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Clientes;
use App\ClientesDesbloqueados;
use App\Creditos;
use App\Http\Traits\detailsPaymentsTrait;
use App\Http\Traits\detailsCreditsTrait;
use App\Usuarios;
use Illuminate\Support\Facades\DB;

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
                $registros = Clientes::where('sucursal_id', $request->session()->get('usuario')->sucursales_id)
                            ->get();
            }
            
            if ($registros) {
                $registros->map(function ($customer, $key){   
                    if ($customer->status == 1) {
                        $credits =  Creditos::with('planes')
                                        ->where('clientes_id', $customer->id)
                                        ->where('estado','!=',2)
                                        ->get();
                        $countCredits = $this->getTotalActiveCompleted($credits);
                        $customer->statusCredit = $countCredits->status;
                        $customer->totalCreditsActive = $countCredits->creditsActives;
                        $customer->totalCredits = $countCredits->totalCredits;
                    } else {
                        $customer->statusCredit = 4;
                        $customer->totalCredits = 0;
                        $customer->collector = 0;
                    }
                    return $customer;
                });

                $this->statusCode   = 200;
                $this->result       = true;
                $this->message      = "Registros consultados exitosamente";
                $this->records      = $registros;
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

    public function create(){}
    
    public function store(Request $request){
        try {
            $nuevoRegistro = DB::transaction(function() use ($request) {
                                $nuevoRegistro = Clientes::create([
                                                    'sucursal_id'   => $request->session()->get($this->sessionKey)->sucursales_id,
                                                    'nombre'        => $request->input('nombre'),
                                                    'apellido'      => $request->input('apellido'),
                                                    'dpi'           => $request->input('dpi'),
                                                    'telefono'      => $request->input('telefono'),
                                                    'direccion'      => $request->input('direccion'),
                                                    'estado_civil'  => $request->input('estado_civil'),
                                                    'sexo'          => $request->input('sexo'),
                                                    'categoria'     => "A",
                                                    'status'        => 1
                                                ]);

                                if (!$nuevoRegistro) {
                                    throw new \Exception("No se pudo crear el registro");
                                } else {
                                    return $nuevoRegistro;
                                }
                            });

            $this->statusCode   = 200;
            $this->result       = true;
            $this->message      = "Registro creado exitosamente";
            $this->records      = $nuevoRegistro;

        } catch (\Exception $e) {
            $this->statusCode   = 200;
            $this->result       = false;
            $this->message      = env('APP_DEBUG') ? $e->getMessage() : "Ocurrió un problema al crear el registro";
        } finally {
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

            if ($registro) {
                $this->statusCode   = 200;
                $this->result       = true;
                $this->message      = "Registro consultado exitosamente";
                $this->records      = $registro;
            } else {
                throw new \Exception("No se encontró el registro");
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

    public function edit($id){}
    
    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            $registro = Clientes::find($id);
            $registro->nombre       = $request->input('nombre', $registro->nombre);
            $registro->apellido     = $request->input('apellido', $registro->apellido);
            $registro->dpi          = $request->input('dpi', $registro->dpi);
            $registro->telefono     = $request->input('telefono', $registro->telefono);
            $registro->direccion     = $request->input('direccion', $registro->direccion);
            $registro->estado_civil = $request->input('estado_civil', $registro->estado_civil);
            $registro->sexo         = $request->input('sexo', $registro->sexo);
            $registro->categoria    = $request->input('categoria', $registro->categoria);
            $registro->status       = $request->input('status', 1);
            
            $credit = Creditos::where("clientes_id", $id)->where("estado",1)->get();

            if ($credit->count() > 0) {
                $credit->map(function ($item, $key) use ($request){   
                    if ($request->input('collector') != 0) {
                        $item->usuarios_cobrador = $request->input('collector', $item->usuarios_cobrador);
                        $item->save();
                    }
                    return $item;
                });
            }

            if ($registro->save()) {
                DB::commit();
                $this->statusCode   = 200;
                $this->result       = true;
                $this->message      = "Registro editado exitosamente";
                $this->records      = $registro;
            } else {
                throw new \Exception("Error al editar el cliente");
            }
        } catch (\Exception $e) {
            DB::rollback();
            $this->statusCode   = 200;
            $this->result       = false;
            $this->message      = env('APP_DEBUG') ? $e->getMessage() : "Ocurrió un problema al editar el registro";
        } finally {
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
            DB::transaction(function() use ($id) {
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

    public function findCustormerForDPI(Request $request) {
        try {
            $cliente = Clientes::where('dpi', $request->input('dpi') )
                            ->where('sucursal_id', $request->session()->get('usuario')->sucursales_id)
                            ->first();
                            
            if ($cliente) {
                if ($cliente->status == 1) {

                    $unlock = ClientesDesbloqueados::where('cliente_id', $cliente->id)->count();
                    
                    $credits = $this->getCreditsForCustomerId($cliente->id);
                    $countCredits = $this->getTotalActiveCompleted($credits);

                    $cliente->statusCredit = $countCredits->status;
                    $cliente->totalCreditsActive = $countCredits->creditsActives;
                    $cliente->totalCredits = $countCredits->totalCredits;
                    $cliente->arrearsCredits = $this->getArrearsForCredits($credits);
                    $cliente->categoria = $countCredits->creditsActives > 0 ? $this->getArrearsStatus($cliente->arrearsCredits) : $cliente->categoria;
                    $cliente->cobrador = Usuarios::find($credits[0]->usuarios_cobrador);
                    $locked = false;
                    if (in_array($cliente->arrearsCredits['moroso'], [1,2,3,4,5]) && $unlock == $cliente->arrearsCredits['moroso']) {
                        $locked = false;
                    } else if ($cliente->arrearsCredits['moroso'] >= 1) {
                        $locked = true;
                    } else {
                        $locked = false;
                    }
                    $cliente->locked = $locked;
                } else {
                    $cliente->statusCredit = 4;
                    $cliente->totalCredits = 0;
                    $cliente->categoria = 0;
                    $cliente->cobrador = "";
                }  
                $this->statusCode   = 200;
                $this->result       = true;
                $this->message      = "Registro consultado exitosamente";
                $this->records      = $cliente;
            } else {
                throw new \Exception("El cliente ingresado no se encuentra en el sistema, revise los datos y vuelva a intentarlo. ");
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

    public function findCustomerForName(Request $request){
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
                                                        $detailsPayments = $this->getDetailsPayments($item);                                    
                                                        $item->saldo_abonado = $detailsPayments->paymentPaid;
                                                        $item->cuotas_pagados = $detailsPayments->totalFees;
                                                        $item->total_cancelado = $detailsPayments->totalPayment;
                                                        $item->cuotas_atrasadas = $this->getTotalDaysArrearsWithTotalPaid($item, $detailsPayments->totalFees);
                                                        $item->dias_no_pagados = $this->getTotalDaysArrears($item);
                                                        $item->estado_morosidad = $this->getArrearsStatusForDays($item->cuotas_atrasadas);
                                                    }
                                                    return $item;
                                                });                                                        
                    $this->statusCode   = 200;
                    $this->result       = true;
                    $this->message      = "Registro consultado exitosamente";
                    $this->records      =  $creditoCliente;
                } else {
                    throw new \Exception("Cliente no cuenta con crédito");      
                }
            } else {
                throw new \Exception("No cliente ingresado no existe");  
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
                $unlocks = ClientesDesbloqueados::with('supervisor')
                            ->where('cliente_id', $creditoCliente->id)
                            ->orderBy('created_at','desc')
                            ->get();

                if ($creditoCliente->creditos->count() > 0) {
                    $creditsFilters = $creditoCliente->creditos->filter(function ($item, $key) {
                        return $item->estado != 2;
                    });  
                                                      
                    $creditoCliente->arrearsCredits = $this->getArrearsForCredits($creditsFilters);
                    $creditoCliente->categoria = $this->getArrearsStatus($creditoCliente->arrearsCredits);
                    $creditoCliente->cantidadCreditos = $creditsFilters->count();  
                    $creditoCliente->creditos = $creditoCliente->creditos->map(function($item,$key) {
                                                    if ($item->estado != 2) {                                                    
                                                        $detailsPayments = $this->getDetailsPayments($item);                                    
                                                        $item->saldo_abonado = $detailsPayments->paymentPaid;
                                                        $item->cuotas_pagados = $detailsPayments->totalFees;
                                                        $item->total_cancelado = $detailsPayments->totalPayment;
                                                        $item->porcentaje_pago = $detailsPayments->paymentPercentage;
                                                        if ($item->estado == 1) {
                                                            $item->cuotas_atrasadas = $this->getTotalDaysArrearsWithTotalPaid($item, $detailsPayments->totalFees);
                                                            $item->dias_no_pagados = $this->getTotalDaysArrears($item);
                                                            $item->estado_morosidad = $this->getArrearsStatusForDays($item->cuotas_atrasadas);
                                                        }
                                                    }
                                                    return $item;
                                                });
                    $creditoCliente->unlockCount = $unlocks ? $unlocks->count():0;
                    $creditoCliente->unlocks = $unlocks ? $unlocks : [];
                   
                    $this->statusCode   = 200;
                    $this->result       = true;
                    $this->message      = "Registro consultado exitosamente";
                    $this->records      = $creditoCliente;
                } else {
                    throw new \Exception("Cliente no cuenta con crédito");      
                }
            } else {
                throw new \Exception("No cliente ingresado no existe");  
            }
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