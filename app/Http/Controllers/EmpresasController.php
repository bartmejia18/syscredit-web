<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Empresas;
use Exception;

class EmpresasController extends Controller
{
    public $statusCode  = 200;
    public $result      = false;
    public $message     = "";
    public $records     = [];

    public function index()
    {
        try {
            $company = Empresas::where("status", 1)->get();

            if ($company) {
                $this->statusCode   = 200;
                $this->result       = true;
                $this->message      = "Registos consultados exitosamente";
                $this->records      = $company;
            }
            else
                throw new Exception("No se encontraron registros");  
        } catch (Exception $e) {
            $this->statusCode   = 200;
            $this->result       = false;
            $this->message      = env('APP_DEBUG') ? $e->getMessage() : "Ocurrió un problema al consultar los datos";
        } finally {
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
            $nuevoRegistro = \DB::transaction( function() use ( $request ) {
                                $nuevoRegistro = Empresas::create([
                                                    'nombre'         => $request->input('nombre'),
                                                    'status' => 1,
                                                ]);

                                if(!$nuevoRegistro)
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

    public function show($id)
    {
        try {
            $registro = Empresas::find( $id );

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

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        try {
            \DB::beginTransaction();
            $registro = Empresas::find($id);
            $registro->nombre = $request->input('nombre',$registro->nombre);
            $registro->status = $request->input('status',$registro->status);
            $registro->save();

            \DB::commit();
            $this->statusCode   = 200;
            $this->result       = true;
            $this->message      = "Registro editado correctamente";
            $this->records      = $registro;
            
        } catch (\Exception $e) {
            \DB::rollback();
            $this->statusCode   = 200;
            $this->result       = false;
            $this->message      = env('APP_DEBUG') ? $id : "Ocurrió un problema al editar el registro";
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
}
