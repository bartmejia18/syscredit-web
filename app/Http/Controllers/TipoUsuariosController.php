<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Session;
use App\TipoUsuarios;

class TipoUsuariosController extends Controller
{
    public $statusCode = 200;
    public $result = false;
    public $message = '';
    public $records = [];

    public function index(Request $request)
    {
        try {
            if($request->session()->get('usuario')->tipo_usuarios_id == 1)
                $registros = TipoUsuarios::all();
            else
                $registros = TipoUsuarios::where('id','<>',1)->get();

            if($registros)
            {
                $this->statusCode   = 200;
                $this->result       = true;
                $this->message      = "Registro consultados exitosamente";
                $this->records      = $registros;
            }
            else
                throw new \Exception("No se encontraron registros");
                
        } 
        catch (\Exception $e) 
        {
            $this->statusCode = 200;
            $this->result = false;
            $this->message = env('APP_DEBUG') ? $e->getMessage() : "Ocurrió un problema al consultar los datos";
            
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
            $nuevoRegistro = \DB::transaction(function() use ($request)
                            {
                                $nuevoRegistro = TipoUsuarios::create([
                                    'descripcion' => $request->input('descripcion'),
                                ]);

                                if ( !$nuevoRegistro) 
                                    throw new \Exception("No se pudo crear el registro");
                                else
                                    return $nuevoRegistro;
                            });

            $this->statusCode   =   200;
            $this->result       =   true;
            $this->message      =   "Registro crear exitosamente";
            $this->records      =   $nuevoRegistro;
            
        } 
        catch (\Exception $e) 
        {
            $this->statusCode   =   200;
            $this->result       =   false;
            $this->message      =   env('APP_DEBUG') ? $e->getMessage() : "Ocurrio un problema al crear el registro";
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
            $registro = TipoUsuarios::find($id);

            if ($registro) 
            {
                $this->statusCode   = 200;
                $this->result       = true;
                $this->message      = "Registro consultado exitosamente";
                $this->records      = $registro;
            }
            else
                throw new \Exception("No se encontro el registro");
                    
        } 
        catch (\Exception $e) 
        {
            $this->statusCode = 200;
            $this->result = false;
            $this->message = env('APP_DEBUG') ? $e->getMessage() : "Ocurrió un problema al consultar el registro";
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
        try 
        {
            \DB::beginTransaction();
            $registro = TipoUsuarios::find( $id );
            $registro->descripcion = $request->input('descripcion', $registro->descripcion);
            $registro->save();

            \DB::commit();
            $this->statusCode   = 200;
            $this->result       = true;
            $this->message      = "Registro actualizado exitosamente";
            $this->records      = $registro;
        } 
        catch (\Exception $e) 
        {
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

    public function destroy($id)
    {
        try {
            $deleteRegistro = \DB::transaction(function() use( $id )
                        {
                            $registro = TipoUsuarios::find( $id );
                            $registro->delete();
                        });

            $this->statusCode   = 200;
            $this->result       = true;
            $this->message      = "El registro fue eliminado exitosamente";
            
        } catch (\Exception $e) {
            $this->statusCode   = 200;
            $this->result       = false;
            $this->message      = env('APP_DEBUG') ? $e->getMessage() : "Ocurrió un problema al eliminar el registro";
        }
        finally
        {
            $response = [
                'result'    => $this->result,
                'message'   => $this->message,
            ];

            return response()->json($response, $this->statusCode);
        }
    }
}
