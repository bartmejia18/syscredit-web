<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\CreditosEliminados;
use App\Creditos;

class CreditosEliminadosController extends Controller
{
    public $statusCode = 200;
    public $result = false;
    public $message = "";
    public $records = [];

    public function index(){}

    public function create(){}

    public function store(Request $request) {
        try {
            
            $nuevoRegistro = [];

            $credit = Creditos::find($request->input("creditoid"));
            if ($credit && $credit->estado == 1) {
                $credit->estado = 2;
                
                if ($credit->save()) {
                    $nuevoRegistro = \DB::transaction(function() use ($request){
                        $nuevoRegistro = CreditosEliminados::create([
                            'credito_id'   =>  $request->input("creditoid"),
                            'motivo'     =>  $request->input("motivo"),
                        ]);

                        if ( !$nuevoRegistro )
                            throw new \Exception("Ocurrió un error al crear el registro");
                        else
                            return $nuevoRegistro;
                    });  
                }

                $this->statusCode = 200;
                $this->result     = true;
                $this->message    = "Registro creado exitosamente";
                $this->records    = $nuevoRegistro;

            } else {
                throw new \Exception("No se encontró información del crédito.");
            }

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

    public function show($id){}

    public function edit($id){}

    public function update(Request $request, $id) {}

    public function destroy($id) {}

    public function findByCreditId(Request $request) {
        try {
            $registros = CreditosEliminados::where('credito_id', $request->input('creditoId'))->first();
            
            if ( $registros ) {
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
}
