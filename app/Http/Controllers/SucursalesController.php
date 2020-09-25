<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Sucursales;

class SucursalesController extends Controller
{
    public $statusCode = 200;
    public $result = false;
    public $message = "";
    public $records = [];

    public function index()
    {
        try {
            $registros = Sucursales::all();

            if($registros)
            {
                $this->statusCode   = 200;
                $this->result       = true;
                $this->message      = "Registros consultados exitosamente";
                $this->records      = $registros;
            }
            else
                throw new \Exception("No se encontraron registros");
                
        } catch (\Exception $e) {
            $this->statusCode       = 200;
            $this->result           = false;
            $this->message          = env('APP_DEBUG') ? $e->getMessage() : "Ocurrió un problema al consultar los datos";
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
        try 
        {
            $nuevoRegistro = \DB::transaction(function() use ($request){
                                $nuevoRegistro = Sucursales::create([
                                    'descripcion'   =>  $request->input("descripcion"),
                                    'direccion'     =>  $request->input("direccion"),
                                    'telefono'      =>  $request->input("telefono"),     
                                ]);

                                if ( !$nuevoRegistro )
                                    throw new \Exception("No se pudo crear el registro");
                                else
                                    return $nuevoRegistro;
                            });    

            $this->statusCode = 200;
            $this->result     = true;
            $this->message    = "Registro creado exitosamente";
            $this->records    = $nuevoRegistro;
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
        try 
        {
            $registro = Sucursales::find( $id );

            if ( $registro ) {
                $this->statusCode   = 200;
                $this->result       = true;
                $this->message      = "Registro consultado exitosamente";
                $this->records      = $registro;
            }
            else
                throw new \Exception("No se encontró el registro");
                
        } 
        catch (\Exception $e) 
        {
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
        try {
            \DB::beginTransaction();
            $registro = Sucursales::find( $id );
            $registro->descripcion  = $request->input('descripcion', $registro->descripcion);
            $registro->direccion    = $request->input('direccion', $registro->direccion);
            $registro->telefono     = $request->input('telefono', $registro->telefono);
            $registro->save();

            \DB::commit();
            $this->statusCode   = 200;
            $this->result       = true;
            $this->message      = "Registro actualizado exitosamente";
            $this->records      = $registro;
            
        } catch (\Exception $e) {
            $this->statusCode   = 200;
            $this->result       = false;
            $this->message      = env('APP_DEBUG') ? $e->getMessage() : "Ocurrió un problema al actualizar el registro";
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
                                $registro = Sucursales::find( $id );
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
