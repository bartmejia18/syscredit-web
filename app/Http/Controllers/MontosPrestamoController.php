<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\MontosPrestamo;
use Exception;

class MontosPrestamoController extends Controller
{
    public $statusCode  = 200;
    public $result      = false;
    public $message     = "";
    public $records     = [];

    public function index()
    {
        try {
            $loanAmount = MontosPrestamo::with('sucursal')->get();

            if ($loanAmount){
                $this->statusCode   = 200;
                $this->result       = true;
                $this->message      = "Registos consultados exitosamente";
                $this->records      = $loanAmount;
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
            $nuevoRegistro = \DB::transaction( function() use ( $request ){
                                $nuevoRegistro = MontosPrestamo::create([
                                                    'monto'         => $request->input('monto'),
                                                    'sucursales_id' => $request->input('idsucursal'),
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
            $registro = MontosPrestamo::find( $id );

            if( $registro ){
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
        finally{
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
            $registro = MontosPrestamo::find( $id );
            $registro->monto            = $request->input('monto',$registro->monto);
            $registro->sucursales_id    = $request->input('idsucursal',$registro->sucursales_id);
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
            $this->message      = env('APP_DEBUG') ? $e->getMessage() : "Ocurrió un problema al editar el registro";
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
   
    public function destroy($id)
    {
        try {
            $deleteRegistro = \DB::transaction( function() use ( $id ){
                                $deleteRegistro = MontosPrestamo::find( $id );
                                $deleteRegistro->delete();
                            });

            $this->statusCode   = 200;
            $this->result       = true;
            $this->message      = "Registro eliminado exitosamente";
            
        } catch (\Exception $e) {
            $this->statusCode   = 200;
            $this->result       = false;
            $this->message      = env('APP_DEBUG') ? $e->getMessage() : "Ocurrió un problema al eliminar el registro";
        }
        finally{
            $response = [
                'result'    => $this->result,
                'message'   => $this->message,
            ];

            return response()->json($response, $this->statusCode);
        }
    }
}
