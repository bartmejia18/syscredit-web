<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Planes;
use Exception;
use Illuminate\Support\Facades\DB;

class PlanesController extends Controller
{
    public $statusCode  = 200;
    public $result      = false;
    public $message     = "";
    public $records     = [];
    
    public function index()
    {
        try {
            $plans = Planes::with('sucursal')->get();            
            
            if ($plans) {
                $colletion = collect($plans);
                $colletion->map(function ($item, $key){
                    if($item->domingo == "1")
                        $item->descripcion = $item->descripcion." - No cobrar domingo";
                    return $item;
                });

                $this->statusCode = 200;
                $this->result     = true;
                $this->message    = "Registros consultados exitosamente";
                $this->records    = $plans;
            }
            else
                throw new Exception("No se encontraron registros");
                
        } catch (Exception $e) {
            $this->statusCode = 200;
            $this->result     = false;
            $this->message    = env('APP_DEBUG') ? $e->getMessage() : "Ocurrió un problema la consultar los datos"; 
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
            $nuevoRegistro = DB::transaction(function() use ($request){
                                $nuevoRegistro = Planes::create([
                                    'descripcion'   => $request->input('descripcion'),
                                    'tipo'          => $request->input('tipo'),
                                    'dias'          => $request->input('dias'),
                                    'porcentaje'    => $request->input('porcentaje'),
                                    'domingo'       => $request->input('domingo'),
                                    'sucursales_id' => $request->input('idsucursal'),                        
                                ]);

                                if( !$nuevoRegistro )
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
            $registro = Planes::find( $id );

            if( $registro )
            {
                $this->statusCode   = 200;
                $this->result       = true;
                $this->message      = "Registro consultado exitosamente";
                $this->records      = $registro;
            }
            else
                throw new \Exception("No se encontró el registro");
                
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
        try {
            DB::beginTransaction();
            $registro = Planes::find( $id );
            $registro->descripcion   = $request->input('descripcion',$registro->descripcion);
            $registro->tipo          = $request->input('tipo',$registro->tipo);
            $registro->dias          = $request->input('dias',$registro->dias);
            $registro->porcentaje    = $request->input('porcentaje',$registro->porcentaje);
            $registro->domingo       = $request->input('domingo',$registro->domingo);
            $registro->sucursales_id = $request->input('idsucursales',$registro->sucursales_id);
            $registro->save();

            DB::commit();
            $this->statusCode   = 200;
            $this->result       = true;
            $this->message      = "Registro editado exitosamente";
            $this->records      = $registro;

        } catch (\Exception $e) {
            DB::rollback();
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
            DB::transaction(function() use ( $id ){
                                $registro = Planes::find( $id );
                                $registro->delete();
                            });

            $this->statusCode   = 200;
            $this->result       = true;
            $this->message      = "Registro eliminado exitosamente";
        
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
