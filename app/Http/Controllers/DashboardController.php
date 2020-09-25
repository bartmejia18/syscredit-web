<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Session;
use App\TipoUsuarios;
use App\Creditos;
use App\Usuarios;
use App\Sucursales;
use App\Http\Traits\customerTrait;

class DashboardController extends Controller
{
    public $statusCode = 200;
    public $result = false;
    public $message = '';
    public $records;

    use customerTrait;

    public function dashboard(Request $request)
    {
        try {

            $branchId = 0;
            $customers = 0;
            $customersWithCreditToDay = [];
            $resumenDashboard = new \stdClass();
        
            if ( $request->session()->get('usuario')->tipo_usuarios_id == 4 ) {        
                $collectorId = $request->session()->get('usuario')->id;            
                $resumenDashboard->customers = Creditos::where("estado", 1)->where("usuarios_cobrador", $collectorId)->groupBy("clientes_id")->get()->count();
                $resumenDashboard->customersWithCreditToDay = $this->getCustomersWithCreditToDayForCollector($collectorId)->count();
                $resumenDashboard->collectors = 1;
                $resumenDashboard->customersWithCreditNoToDay = $resumenDashboard->customers - $resumenDashboard->customersWithCreditToDay; 

                $this->records[] = $resumenDashboard;

            } else { 
                $branchId = $request->session()->get('usuario')->tipo_usuarios_id == 1 ? 0 : $request->session()->get('usuario')->sucursales_id;
                $branchs = Sucursales::get()->map(function($item,$key){   
                                $item->customers = Creditos::where("sucursal_id", $item->id)->where("estado", 1)->groupBy("clientes_id")->get()->count();
                                $item->customersWithCreditToDay = $this->getCustomersWithCreditToDay($item->id)->count();
                                $item->collectors = Usuarios::where("tipo_usuarios_id", 4)->where("sucursales_id", $item->id)->count();
                                $item->customersWithCreditNoToDay = $item->customers - $item->customersWithCreditToDay; 
                                return $item;
                            });

                if ($branchId != 0)
                    $this->records = $branchs->filter(function ($item) use ($branchId){ return $item->id == $branchId; });        
                else 
                    $this->records = $branchs;
            }

            $this->statusCode   = 200;
            $this->result       = true;
            $this->message      = "Registro consultado exitosamente";
            
            
            
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
}