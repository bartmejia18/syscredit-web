<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Permisos;
use Exception;

class PermisosController extends Controller
{
    public $statusCode  = 200;
    public $result      = false;
    public $message     = "";
    public $records     = null;
    
    public function getPermission()
    {
        try {
            $permiso = Permisos::first();            
            
            if ($permiso) {
                $this->statusCode = 200;
                $this->result     = true;
                $this->message    = "Registros consultados exitosamente";
                $this->records    = $permiso;
            } else {
                throw new Exception("No se encontraron registros");
            }   
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
}