<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Http\Traits\reportsTrait;
use Carbon\Carbon;

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
            
            $resumenOfCredits = $this->resumenOfCredits($request);
            $general->revenueTotals =  $this->getRevenueTotals($request);
            $general->totalPendingReceivable =  $resumenOfCredits->pendingReceivable;
            $general->totalReceivable =  $resumenOfCredits->totalReceivable;
            $general->totalGeneratedInterests = $resumenOfCredits->generatedInterests;

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

            $resumenOfCredits = $this->resumenOfCredits($request);
            $general->revenueTotals =  $this->getRevenueTotals($request);
            $general->totalPendingReceivable =  $resumenOfCredits->pendingReceivable;
            $general->totalReceivable =  $resumenOfCredits->totalReceivable;
            $general->totalGeneratedInterests = $resumenOfCredits->generatedInterests;
            $general->totalAmountToCollected = $this->getAmountToColletedForCollector($request);

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

            $resumenOfCredits = $this->resumenOfCredits($request);
            $general->revenueTotals =  $this->getRevenueTotals($request);
            $general->totalPendingReceivable =  $resumenOfCredits->pendingReceivable;
            $general->totalReceivable =  $resumenOfCredits->totalReceivable;
            $general->totalGeneratedInterests = $resumenOfCredits->generatedInterests;

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
            $this->statusCode   = 200;
            $this->result       = true;
            $this->message      = "Registros consultados exitosamente";
            $this->records      = $this->getCredits($request);


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

    public function amountToColletedForCollector(Request $request) {
        try {
            $this->statusCode   = 200;
            $this->result       = true;
            $this->message      = "Registros consultados exitosamente";
            $this->records      = $this->getAmountToColletedForCollector($request);


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

    public function reportCreditsPDF(Request $request){

        $datos = new \stdClass();   
        
        switch ($request->status) {
            case 0:
                $datos->status = "Completados";
                break;
            case 1:
                $datos->status = "Nuevos";
                break;
            case 2:
                $datos->status = "Eliminados";
                break;
        }

        $datos->fecha_inicio = Carbon::parse($request->input('dateInit'))->format('d-m-Y');
        $datos->fecha_fin = Carbon::parse($request->input('dateFinal'))->format('d-m-Y');
        $datos->credits = $this->getCredits($request);

        $today = \Carbon\Carbon::parse(\Carbon\Carbon::now())->format('d-m-Y');
        $namePdf = "reporte-{$today}";

        $pdf = \App::make('dompdf');        
        $pdf = \PDF::loadView('pdf.credits', ['data' => $datos])->setPaper('legal', 'portrait');
        return $pdf->download($namePdf.'.pdf');
    }
}