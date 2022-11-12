<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Session;
use App\Http\Traits\reportsTrait;
use App\Creditos;

class ReportsController extends Controller
{
    public $statusCode = 200;
    public $result = false;
    public $message = '';
    public $records;

    use reportsTrait;

    public function general(Request $request){
        try {        
            $general = new \stdClass();          
            $general->customers = $this->getCountCustomers($request);
            $general->revenueTotals =  $this->getRevenueTotals($request);
            $general->totalPendingReceivable =  $this->getPendingReceivable($request);
            $general->totalReceivable =  $this->getTotalReceivable($request);
            $general->totalGeneratedInterests = $this->getGeneratedInterests($request);

            $this->statusCode   = 200;
            $this->result       = true;
            $this->message      = "Registro consultado exitosamente";
            $this->records      = $general;
        } catch (\Exception $e) {
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

    public function collector(Request $request){
        try {
            $general = new \stdClass();          
            $general->customers = $this->getCountCustomers($request);
            $general->revenueTotals =  $this->getRevenueTotals($request);
            $general->totalPendingReceivable =  $this->getPendingReceivable($request);
            $general->totalReceivable =  $this->getTotalReceivable($request);
            $general->totalGeneratedInterests = $this->getGeneratedInterests($request);

            $this->statusCode   = 200;
            $this->result       = true;
            $this->message      = "Registro consultado exitosamente";
            $this->records      = $general;
        } catch (\Exception $e) {
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

    public function dates(Request $request){
        try {
            $general = new \stdClass();          
            $general->customers = $this->getCountCustomers($request);
            $general->revenueTotals =  $this->getRevenueTotals($request);
            $general->totalPendingReceivable =  $this->getPendingReceivable($request);
            $general->totalReceivable =  $this->getTotalReceivable($request);
            $general->totalGeneratedInterests = $this->getGeneratedInterests($request);

            $this->statusCode   = 200;
            $this->result       = true;
            $this->message      = "Registro consultado exitosamente";
            $this->records      = $general;
        } catch (\Exception $e) {
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

    public function credits(Request $request) {
        try {

            if ($request->input('date-init') != null && $request->input('date-final') != null) {
                $dateInit = \Carbon\Carbon::parse($request->input('date-init'))->format('Y-m-d');
                $dateFin = \Carbon\Carbon::parse($request->input('date-final'))->format('Y-m-d');
            } else {
                $dateInit = "";
                $dateFin = "";
            }

            $credits = "";   
            $plan = $request->input('plan');

            if ($request->input('collector') != "") {
                $credits = Creditos::with('cliente','planes','montos','usuariocobrador')
                                    ->where('sucursal_id', $request->input('branch'))
                                    ->where('estado', $request->input('status'))
                                    ->where('usuarios_cobrador', $request->input('collector'))
                                    ->whereBetween('created_at', [$dateInit, $dateFin])
                                    ->get();
            } else {
                $credits = Creditos::with('cliente','planes','montos','usuariocobrador')
                                    ->where('sucursal_id', $request->input('branch'))
                                    ->where('estado', $request->input('status'))
                                    ->whereBetween('created_at', [$dateInit, $dateFin])
                                    ->get();
            }

            if ($plan != "" && $plan != 0) {
                $this->records = $credits->filter(function ($item) use ($plan){ 
                    return $item->planes_id == $plan;   
                });
            } else {
                $this->records      = $credits;   
            }

            if ($credits) {
                $this->statusCode   = 200;
                $this->result       = true;
                $this->message      = "Registros consultados exitosamente";
            } else {
                throw new \Exception("No se encontraron registros");
            }
        } catch (\Exception $e) {
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