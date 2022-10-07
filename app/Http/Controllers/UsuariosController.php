<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Usuarios;
use Auth;
use DB;
use Session;
use Exception;

//revisar el usuario que no sea repedito
//validar el password que vaya vacio

class UsuariosController extends Controller
{
    protected $status_code = 200;
    protected $result = false;
    protected $message = "Ocurrió un problema con tu transacción, intenta más tarde";
    protected $records = null;
    protected $sessionKey = 'usuario';

    public function index(Request $request)
    {
        try {
            if($request->session()->get('usuario')->tipo_usuarios_id == 1){
                $registros = Usuarios::with('tipoUsuarios','sucursal')->get();
            } else {
                $registros = Usuarios::where('sucursales_id', $request->session()->get('usuario')->sucursales_id)->with('tipoUsuarios','sucursal')->get();
            }

            if ($registros) {
                $this->status_code = 200;
                $this->result = true;
                $this->message = "Registros consultados exitosamente";
                $this->records = $registros;
            } else
                throw new Exception("No se encontraron registros");
        } catch (Exception $e) {
            $this->status_code = 404;
            $this->result = false;
            $this->message = env('APP_DEBUG') ? $e->getMessage() : $this->message;
        } finally {
            $response = [
                'result' => $this->result,
                'message' => $this->message,
                'records' => $this->records,
            ];

            return response()->json($response, $this->status_code);
        }
    }
    
    public function store(Request $request)
    {
        try {

            $registro = Usuarios::where('user',strtolower($request->input('user')))->get();

            if (count($registro) == 0) {
                $nuevoRegistro = \DB::transaction( function() use ( $request){
                                    $nuevoRegistro = Usuarios::create([
                                                        'tipo_usuarios_id'  => $request->input('tipo_usuarios_id'),
                                                        'nombre'            => $request->input('nombre'),
                                                        'user'              => strtolower($request->input('user')),
                                                        'estado'            => 1,
                                                        'sucursales_id'     => $request->input('sucursales_id'),
                                                        'password'          => \Hash::make($request->input('password')),
                                                        'password_2'        => \Hash::make($request->input('password2')),

                                                    ]);

                                    if( !$nuevoRegistro )
                                        throw new \Exception("No se pudo crear el registro");
                                    else
                                        return $nuevoRegistro;
                                });
            } else
                throw new \Exception("Usuario ingresado ya existe, favor verifica");
                  
            $this->status_code = 200;
            $this->result = true;
            $this->message = "Registro creado exitosamente";
            $this->records = $nuevoRegistro;
        } catch (\Exception $e) {
            $this->status_code   = 200;
            $this->result       = false;
            $this->message = env('APP_DEBUG') ? $e->getMessage() : $this->message;
        } finally {
            $response = [
                'result'    => $this->result,
                'message'   => $this->message,
                'records'   => $this->records,
            ];

            return response()->json($response, $this->status_code);
        }
    }

    public function show($id)
    {
        try {
            $registro = Usuarios::with('tipoUsuarios','sucursal')->find( $id );

            if ($registro) {
                $this->status_code   = 200;
                $this->result       = true;
                $this->message      = "Registro consultado exitosamente";
                $this->records      = $registro;
            } else
                throw new \Exception("No se encontró el registro");
        } catch (\Exception $e) {
            $this->status_code   = 404;
            $this->result       = false;
            $this->message = env('APP_DEBUG') ? $e->getMessage() : $this->message;
        } finally {
            $response = [
                'result'    => $this->result,
                'message'   => $this->message,
                'records'   => $this->records,
            ];

            return response()->json($response, $this->status_code);
        }
    }
    public function update(Request $request, $id)
    {
        try {

            $registroUsuario = Usuarios::where('user',strtolower($request->input('user')))->get();

            if (count($registroUsuario) == 0)
                \DB::beginTransaction();
                $registro = Usuarios::find( $id );
                $registro->tipo_usuarios_id = $request->input('idtipousuario', $registro->tipo_usuarios_id);
                $registro->nombre           = $request->input('nombre', $registro->nombre);
                $registro->user             = strtolower($request->input('user', $registro->user));
                $registro->estado           = $request->input('estado', $registro->estado);
                $registro->sucursales_id    = $request->input('idsucursal', $registro->sucursales_id);

                if($request->input("password")!="")
                    $registro->password       = \Hash::make($request->input('password'));
                if($request->input("password2")!="")   
                    $registro->password_2     = \Hash::make($request->input('password2')); 

                $registro->password_2       = $request->input('password2', $registro->password_2);
                $registro->save();

            \DB::commit();
            $this->status_code   = 200;
            $this->result       = true;
            $this->message      = "Registro editado exitosamente";
            $this->records      = $registro;
        } catch (\Exception $e) {
            \DB::rollback();
            $this->status_code   = 200;
            $this->result       = false;
            $this->message = env('APP_DEBUG') ? $e->getMessage() : $this->message;
        } finally {
            $response = [
                'result'    => $this->result,
                'message'   => $this->message,
                'records'   => $this->records,
            ];

            return response()->json($response, $this->status_code);
        }
    }

    public function destroy($id)
    {
        try {
            $deleteRegistro = \DB::transaction( function() use ($id) {
                                $registro = Usuarios::find( $id );
                                $registro->estado = 2;
                                $registro->save();
                            });

            $this->status_code   = 200;
            $this->result       = true;
            $this->message      = "Registro eliminado exitosamente";
            
        } catch (\Exception $e) {
            $this->status_code   = 200;
            $this->result       = false;
            $this->message = env('APP_DEBUG') ? $e->getMessage() : $this->message;
        } finally {
            $response = [
                'result'    => $this->result,
                'message'   => $this->message,
            ];

            return response()->json($response, $this->status_code);
        }
    }

    public function login(Request $request)
    {
        try{
            if (Auth::attempt([
                'user'=> $request->input('user'),
                'password'=> $request->input('password'), 
                'estado' => 1
                ]) && Auth::user()->tipo_usuarios_id != 4) {
            
                //Session::push($this->sessionKey, Auth::user());
                Session::put($this->sessionKey, Auth::user());
                Session::save();

                $this->records      =   [Auth::user()];
                $this->message      =   "Sesión iniciada";
                $this->result       =   true;
                $this->status_code   =   200;
            } else {
                throw new Exception("Usuario o password incorrecto");
            }
        } catch (Exception $e) {
            $this->status_code = 200;
            $this->message = env('APP_DEBUG') ? $e->getMessage() : $this->message;
            $this->result = false;
        } finally {
            $response = [
                'message'   =>  $this->message,
                'result'    =>  $this->result,
                'records'   =>  $this->records
            ];
            
            return response()->json($response, $this->status_code);
        }
    }

    public function listaCobradores(Request $request)
    {
        try {
            $registros = Usuarios::where('tipo_usuarios_id',4)->get();

            if (count($registros) > 0) {
                $this->status_code   = 200;
                $this->result       = true;
                $this->message      = "Registros consultados exitosamente";
                $this->records      = $registros;
            } else
                throw new Exception("No existen cobradores registrados");  
        } catch (Exception $e) {
            $this->status_code   = 200;
            $this->result       = false;
            $this->message = env('APP_DEBUG') ? $e->getMessage() : $this->message;
        } finally {
            $response = [
                'result'    => $this->result,
                'message'   => $this->message,
                'records'   => $this->records,
            ];

            return response()->json($response, $this->status_code);
        }
    }

    public function checkSession(Request $request)
    {
        try {
//            if ($request->session()->get($this->sessionKey)) {
            
            if (Session::has($this->sessionKey)) {
                $this->status_code = 200;
                $this->result = true;
                $this->message = 'Active session';
            } else {
                $this->status_code = 200;
                $this->result = false;
                $this->message = 'Session expired';
            }
        } catch(Exception $e) {
            $this->status_code = 400;
            $this->result = false;
            $this->message = env('APP_DEBUG') ? $e->getMessage() : $this->message;
        } finally {
            $responseObject = [
                'result'    => $this->result,
                'message'   => $this->message,
            ];

            return response()->json($responseObject, $this->status_code);
        }
    }
}
