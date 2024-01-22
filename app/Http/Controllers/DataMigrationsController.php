<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Creditos;
use App\Http\Controllers\Controller;
use App\Http\Traits\detailsCreditsTrait;
use App\CierreRuta;
use App\Clientes;
use App\Usuarios;
use Illuminate\Support\Facades\DB;

class DataMigrationsController extends Controller
{
    private $statusCode = 200;
    private $result = false;
    private $message = '';

    use detailsCreditsTrait;

    public function setArrearsToCredits(){
        try {
            $credits = Creditos::with('planes')
                            ->where('estado','!=',2)
                            ->get();

            $credits->map(function($item, $key) {
                if ($item->estado == 0 && $item->cuotas_atrasadas == 0) {
                    $this->setArrearsToCreditComplete($item);
                }
            });

            $this->statusCode   = 200;
            $this->result       = true;
            $this->message      = "Registros consultados exitosamente";
        
        } catch (\Exception $e) {
            $this->statusCode = 200;
            $this->result = false;
            $this->message = env('APP_DEBUG') ? $e->getMessage() : "Ocurrió un problema al consultar los datos";
            
        } finally {
            $response = [
                'result'    => $this->result,
                'message'   => $this->message,
            ];
            return response()->json($response, $this->statusCode);
        }
    }

    public function setCategoryCustomers() {
        try {
            $customers = Clientes::where('status', 1)->get();

            foreach($customers as $customer) {
                $credits = Creditos::where('clientes_id', $customer->id)->where('estado','!=',2)->get();

                $arrearsStatus = [
                    'moroso' => 0,
                    'bueno' => 0,
                    'excelente' => 0
                ];

                foreach($credits as $credit) {
                    if ($credit->estado_morosidad == "Moroso") {
                        $arrearsStatus['moroso'] += 1;
                    } else if ($credit->estado_morosidad == "Bueno") {
                        $arrearsStatus['bueno'] += 1;
                    } else {
                        $arrearsStatus['excelente'] += 1;
                    }
                }
                $categoryForArrearsStatus = $this->getArrearsStatus($arrearsStatus);
                $customer->categoria = $categoryForArrearsStatus;
                $customer->save();
            }

            $this->statusCode   = 200;
            $this->result       = true;
            $this->message      = "Registros actualizados correctamente";
        } catch (\Exception $e) {
            $this->statusCode = 200;
            $this->result = false;
            $this->message = env('APP_DEBUG') ? $e->getMessage() : "Ocurrió un problema al consultar los datos";
        } finally {
            $response = [
                'result'    => $this->result,
                'message'   => $this->message,
            ];
            return response()->json($response, $this->statusCode);
        }
    }

    public function setCodeUser() {
        try {
            $users = Usuarios::where('estado',1)
                            ->where('code', 0)
                            ->get();

            $users->map(function($item, $key) {
                $item->code = $item->sucursales_id . $item->tipo_usuarios_id . $item->id;
                $item->save();
            });

            $this->statusCode   = 200;
            $this->result       = true;
            $this->message      = "Registros consultados exitosamente";
        
        } catch (\Exception $e) {
            $this->statusCode = 200;
            $this->result = false;
            $this->message = env('APP_DEBUG') ? $e->getMessage() : "Ocurrió un problema al consultar los datos";
            
        } finally {
            $response = [
                'result'    => $this->result,
                'message'   => $this->message,
            ];
            return response()->json($response, $this->statusCode);
        }
    }
}
