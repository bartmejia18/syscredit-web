<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\ClientesDesbloqueados;
use Illuminate\Support\Facades\DB;

class ClientesDesbloqueadosController extends Controller
{
    public $statusCode = 200;
    public $result = false;
    public $message = "";
    public $records = [];

    public function index(Request $request){
        try {
            $registros = ClientesDesbloqueados::with('cliente','supervisor')->get();
            
            if ($registros) {
                $this->statusCode   = 200;
                $this->result       = true;
                $this->message      = "Registros consultados exitosamente";
                $this->records      = $registros->filter(function ($item) use ($request) {
                    return $item->cliente->sucursal_id == $request->input('branchId');
                });   
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

    public function store(Request $request) {
        try {
            $unlocksClients = ClientesDesbloqueados::where('cliente_id', $request->input('clientId'))
                                                    ->orderBy('created_at', 'DESC')
                                                    ->first();
        
            $nuevoRegistro = DB::transaction(function() use ($request, $unlocksClients) {
                                $nuevoRegistro = ClientesDesbloqueados::create([
                                                    'cliente_id' => $request->input('clientId'),
                                                    'supervisor_id' => $request->input('supervisorId'),
                                                    'razon' => $request->input('reason'),
                                                    'numero' => $unlocksClients ? ($unlocksClients->numero + 1) : 1
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

    public function show($id){}

    public function edit($id){}

    public function update(Request $request, $id) {}

    public function destroy($id) {}

    public function findByClientId(Request $request) {
        try {
            $registros = ClientesDesbloqueados::with('supervisor')
                                                ->where('cliente_id', $request->input('clientId'))
                                                ->get();
            
            if ($registros) {
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
