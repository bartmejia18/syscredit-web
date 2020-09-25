<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Creditos;
use App\HistorialPagos;
use App\DetallePagos;
use App\Planes;
use Session;
use DB;
use App\Http\Traits\DatesTrait;
use App\Http\Traits\detailsPaymentsTrait;
use App\Http\Traits\generateArrayForTicketTrait;

class CreditosController extends Controller
{
    public $statusCode  = 200;
    public $result      = false;
    public $message     = "";
    public $records     = [];

    use DatesTrait;
    use detailsPaymentsTrait;
    use generateArrayForTicketTrait;

    public function index()
    {
        try {
            $registros = Creditos::with('cliente','planes','montos','usuariocobrador','detallePagos')->get();
            
            if( $registros ) {
                $this->statusCode   = 200;
                $this->result       = true;
                $this->message      = "Registros consultados exitosamente";
                $this->records      = $registros;   
            }
            else
                throw new \Exception("No se encontraron registros");
                
            
        } catch (\Exception $e) {
            $this->statusCode   = 200;
            $this->result       = false;  
            $this->message      = env('APP_DEBUG') ? $e->getMessage() : "Ocurrió un problema al consultar los registros"; 
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

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        try {

            $plan = Planes::find($request->input('idplan'));
            
            if($plan->domingo == "1"){
                $lastDate = $this->getLastDayWithoutSunday(\Carbon\Carbon::parse($request->input('fecha_inicio'))->format('Y-m-d'),$plan->dias);
            } else {
                $lastDate = $this->getLastDay(\Carbon\Carbon::parse($request->input('fecha_inicio'))->format('Y-m-d'),$plan->dias);
            }

            $nuevoRegistro = \DB::transaction( function() use ($request, $lastDate){
                                $nuevoRegistro = Creditos::create([
                                                    'clientes_id'           => $request->input('idcliente'),
                                                    'planes_id'             => $request->input('idplan'),
                                                    'montos_prestamo_id'    => $request->input('idmonto'),
                                                    'usuarios_creo'         => $request->session()->get('usuario')->id,
                                                    'usuarios_cobrador'     => $request->input('idusuario'),
                                                    'sucursal_id'           => $request->session()->get('usuario')->sucursales_id,
                                                    'saldo'                 => $request->input('deudatotal'),
                                                    'interes'               => 0,
                                                    'deudatotal'            => $request->input('deudatotal'),
                                                    'cuota_diaria'          => $request->input('cuota_diaria'),
                                                    'cuota_minima'          => $request->input('cuota_minima'),
                                                    'fecha_inicio'          => \Carbon\Carbon::parse($request->input('fecha_inicio'))->format('Y-m-d'),
                                                    'fecha_fin'             => \Carbon\Carbon::parse($lastDate)->format('Y-m-d'),
                                                    'estado'                => 1,
                                                ]);

                                if( !$nuevoRegistro )
                                    throw new \Exception("Error al crear el registro");
                                else{
                                    return $nuevoRegistro;
                                }
                            });

            $this->statusCode   = 200;
            $this->result       = true;
            $this->message      = "Registro creado exitosamente";
            $this->records      = $nuevoRegistro;
                
        } 
        catch (\Exception $e) 
        {
            $this->statusCode   = 200;
            $this->result       = false;
            $this->message      = env('APP_DEBUG') ? $e->getMessage() : "Ocurrió un problema al crear el registro";
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

    public function show($id)
    {
        try {
            $registro = Creditos::with('cliente','planes','montos','usuariocobrador','detallePagos')->find( $id );
        
            if ( $registro ) {
                $cuotas = 0;
                foreach ($registro as $keyRegistro => $valRegistro) {

                    if( $valRegistro['estado'] == 1 )
                    {
                        $cuotas = $cuotas + 1;
                    }
                }
                $registro->cuotasPagadas = $cuotas;
                $registro->cuotasPendientes = $registro->planes->dias - $registro->cuotas;


                $this->statusCode   = 200;
                $this->result       = true;
                $this->message      = "Registro consultado exitosamente";
                $this->records      = $registro;
            }
            else
                throw new \Exception("Error al consultar el registro");
                
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

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        
    }

    public function destroy($id)
    {
        //
    }

    public function payments(Request $request){
        try {
            $credito = Creditos::where('id', $request->input('idcredito'))->with('planes','montos')->first();
            
            if($credito){
                $detallePagos = DetallePagos::where('credito_id', $credito->id)->where('estado', 1)->get();

                if($detallePagos->count() > 0){
                    $totalPayment = $detallePagos->sum('abono') + $request->input('abono');
                    if($totalPayment > $credito->deudatotal){
                        throw new \Exception("El monto ingresado es mayor al saldo pendiente de pago");
                    } else {
                        $detallePagos = new DetallePagos;
                        $detallePagos->credito_id = $credito->id;
                        $detallePagos->fecha_pago = \Carbon\Carbon::parse(date('Y-m-d'));
                        $detallePagos->abono = $request->input('abono');
                        $detallePagos->estado = 1;                        
                    }
                } else {
                    if($request->input('abono') > $credito->deudatotal){
                        throw new \Exception("El monto ingresado es mayor al saldo pendiente de pago");
                    } else {
                        $detallePagos = new DetallePagos;
                        $detallePagos->credito_id = $credito->id;
                        $detallePagos->fecha_pago = \Carbon\Carbon::parse(date('Y-m-d'));
                        $detallePagos->abono = $request->input('abono');
                        $detallePagos->estado = 1;
                    }
                }

                if($detallePagos->save()){
                    $detailPayment = $this->getDetailsPayments($credito->id);     
                    $balance = $credito->deudatotal - $detailPayment->totalPayment;           
                    
                    if($balance == 0){
                        $credito->saldo = $balance;
                        $credito->estado = 0;
                    } else {
                        $credito->saldo = $balance;
                    }
                    
                    $credito->save();
                    $this->statusCode = 200;
                    $this->result = true;
                    $this->message = "Pago realizado con éxito";                    
                } else {
                    throw new \Exception("Ocurrió un error al ingresar el pago");
                }
            }            
        } catch (\Exception $e) {
            $this->statusCode   = 200;
            $this->result       = false;
            $this->message      = env('APP_DEBUG') ? $e->getMessage() : "Ocurrió un error al ingresar el pago";
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

    public function cobradorClientes(Request $request){
        try {
            $registros = Creditos::where('usuarios_cobrador',$request->input('idcobrador'))->with('cliente')->get();
            
            if( $registros ){
                $this->statusCode   = 200;
                $this->result       = true;
                $this->message      = "Registros consultados exitosamente";
                $this->records      = $registros;
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

    public function renovarCredito(Request $request)
    {
        try {
            $registros = Creditos::where('usuarios_cobrador',$request->input('idcobrador'))->with('cliente')->get();
            
            if( $registros ){
                $this->statusCode   = 200;
                $this->result       = true;
                $this->message      = "Registros consultados exitosamente";
                $this->records      = $registros;
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

    public function boletaPDF(Request $request){

        $registro = Creditos::with('cliente','planes','montos')->find( $request->input('credito_id') );
        
        if ( $registro ) {
            $pdf = \App::make('dompdf');
            if($registro->planes->domingo == "1"){
                if($registro->planes->dias >= 45){
                    $pdf = \PDF::loadView('pdf.ticketwithoutsundayplan75', ['data' => $this->getArray($registro)])->setPaper('letter','landscape');
                }
                else{
                    $pdf = \PDF::loadView('pdf.ticketwithoutsunday', ['data' => $this->getArray($registro)])->setPaper('letter','landscape');
                }
            } else{
                if($registro->planes->dias >= 45){
                    $pdf = \PDF::loadView('pdf.ticketwithsundayplan75', ['data' => $this->getArrayWithSunday($registro)])->setPaper('letter','landscape');
                }
                else{
                    $pdf = \PDF::loadView('pdf.ticketwithsunday', ['data' => $this->getArrayWithSunday($registro)])->setPaper('letter','landscape');
                }
            }
    
            $nameBoleta = $registro->cliente->nombre." " .$registro->cliente->apellido;
            
            return $pdf->download($nameBoleta.'.pdf');
        }
    }
}
