<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\ClientesDesbloqueados;
use Exception;
use Illuminate\Support\Facades\DB;

class ClientesDesbloqueadosController extends Controller
{
    public $statusCode = 200;
    public $result = false;
    public $message = "";
    public $records = [];

    public function index(Request $request){
        try {
            $registros = ClientesDesbloqueados::with('cliente','supervisor','gerente')
                            ->orderBy('created_at', 'desc')
                            ->get();
            
            if ($registros) {
                $registros->map(function($item, $key) {
                    if ($item->supervisor == null) {
                        $item->nombre_supervisor = "Pendiente de aprobación";
                    } else {
                        $item->nombre_supervisor = $item->supervisor->nombre;
                    }

                    if ($item->gerente == null) {
                        $item->nombre_gerente = "Pendiente de aprobación";
                    } else {
                        $item->nombre_gerente = $item->gerente->nombre;
                    }

                    $item->revisado = $item->supervisor != null || $item->gerente != null;

                    return $item;
                });

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
            $nuevoRegistro = DB::transaction(function() use ($request) {
                                $nuevoRegistro = ClientesDesbloqueados::create([
                                                    'cliente_id' => $request->input('clientId'),
                                                    'razon' => $request->input('reason'),
                                                    'estado' => 0
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
                                                ->orderBy('created_at', 'DESC')
                                                ->get();
            
            if ($registros) {

                $registros->map(function($item, $key) {
                    $item->aprobado = $item->aprobacion_supervisor == 1 && $item->aprobacion_gerente == 1 ? 1 : 2;
                    return $item;
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

    public function checkUnlocks(Request $request) {
        try {
            $unlocks = ClientesDesbloqueados::find($request->input('id'));

            if ($unlocks) {
                DB::beginTransaction();
                $unlocks->supervisor_id = $request->input('supervisorId', $unlocks->supervisor_id);
                $unlocks->gerente_id = $request->input('gerenteId', $unlocks->gerente_id);
                $unlocks->aprobacion_supervisor = $request->input('aprobacionSupervisor', $unlocks->aprobacion_supervisor);
                $unlocks->comentario_supervisor = $request->input('comentarioSupervisor', $unlocks->comentario_supervisor);
                $unlocks->fecha_supervisor = $request->input('fechaSupervisor', $unlocks->fecha_supervisor);
                $unlocks->aprobacion_gerente = $request->input('aprobacionGerente', $unlocks->aprobacion_gerente);
                $unlocks->comentario_gerente = $request->input('comentarioGerente', $unlocks->comentario_gerente);
                $unlocks->fecha_gerente = $request->input('fechaGerente', $unlocks->fecha_gerente);
                
                if ($unlocks->save()) {
                    DB::commit();
                    $this->statusCode   = 200;
                    $this->result       = true;
                    $this->message      = "Registro editado exitosamente";
                    $this->records      = $unlocks;
                }
            } else {
                throw new \Exception("No se encontró el registro");
            }
        } catch (Exception $e) {
            DB::rollback();
            $this->statusCode   = 200;
            $this->result       = false;  
            $this->message      = env('APP_DEBUG') ? $e->getMessage() : "Ocurrió un problema al guardar los registros"; 
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
