<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Session;
use App\Http\Traits\reportsTrait;

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
}