<?php

namespace App\Http\Traits;

use App\Creditos;
use App\CuotasClientes;

trait generateArrayForTicketTrait {

    public function getArray($data) {
        $dateInitial = $data->fecha_inicio;
        $dateFinal = $data->fecha_fin;
        $amountTotal = $data->deudatotal;
        $dailyAmount = $data->cuota_diaria;

        $totalDays = (strtotime($dateInitial)-strtotime($dateFinal))/86400;
        $totalDays = abs($totalDays); 
        $totalDays = floor($totalDays + 1 );	
        
        $totalDays = ($totalDays % 2 == 0 ? $totalDays : $totalDays + 1) / 2; 

        $dateInit = new \DateTime($dateInitial);
        $timestamp = $dateInit->getTimestamp();
        $seconds = 0;

        $countSundayTemporal = 0;
        for ($i=0; $i<$totalDays; $i++)  {  
            $dateTemporal = strtotime ( '+'.$i.' day' , strtotime ( $dateInitial) ) ;
            $dateTemporal = date ( 'd-m-Y' , $dateTemporal );
            $dateTemporalNew = new \DateTime($dateTemporal);
            $sundayTemporal = date("D", $dateTemporalNew->getTimestamp());

            if($sundayTemporal == "Sun"){
                ++$countSundayTemporal;
            }
        }

        $countOne = 0;
        $countTwo = $totalDays - $countSundayTemporal;
        $row = array();

        for ($i=0; $i<$totalDays; $i++)  {  
            
            $columns = new \stdClass();
            $cantOne = $i;
            $cantTwo = $i + $totalDays;

            $dateFirst = strtotime ( '+'.$cantOne.' day' , strtotime ( $dateInitial) ) ;
            $dateFirst = date ( 'd-m-Y' , $dateFirst );
            $dateOne = new \DateTime($dateFirst);
            $sundayFirst = date("D", $dateOne->getTimestamp());

            $dateSecond = strtotime ( '+'.$cantTwo.' day' , strtotime ( $dateInitial) ) ;
            $dateSecond = date ( 'd-m-Y' , $dateSecond );
            $dateTwo = new \DateTime($dateSecond);
            $sundaySecond = date("D", $dateTwo->getTimestamp());

            $columns->sundayFirst = $sundayFirst == "Sun" ? "S" : "N";
            $columns->indexFirst = $sundayFirst == "Sun" ? $countOne : ++$countOne;
            $columns->amountFirst = $amountTotal - ($dailyAmount * ($columns->indexFirst-1));
            $columns->dateFirst = $dateFirst;

            $columns->sundaySecond = $sundaySecond == "Sun" ? "S" : "N";
            $columns->indexSecond = $sundaySecond == "Sun" ? $countTwo : ++$countTwo;
            $columns->amountSecond = $amountTotal - ($dailyAmount * ($columns->indexSecond-1));
            $columns->dateSecond = $dateSecond;

            array_push($row, $columns);
        }        

        $infoCredit = new \stdClass();
        $infoCredit->name = $data->cliente->nombre." ".$data->cliente->apellido;
        $infoCredit->dpi = $data->cliente->dpi;
        $infoCredit->address = $data->cliente->direccion;
        $infoCredit->numberPhone = $data->cliente->telefono;
        $infoCredit->plan = $data->planes->descripcion;
        $infoCredit->days = $data->planes->dias;
        $infoCredit->date = $data->fecha_inicio;
        $infoCredit->amount = $data->montos->monto;
        $infoCredit->fees = $data->cuota_diaria;
        $infoCredit->amountDefault = $dailyAmount/2;
        $infoCredit->arrayQuota = $row;
        
        return $infoCredit;
    }

    public function getArrayWithSunday($data) {
        $dateInitial = $data->fecha_inicio;
        $dateFinal = $data->fecha_fin;
        $amountTotal = $data->deudatotal;
        $dailyAmount = $data->cuota_diaria;

        $totalDays = (strtotime($dateInitial)-strtotime($dateFinal))/86400;
        $totalDays = abs($totalDays); 
        $totalDays = floor($totalDays + 1 );	
        
        $totalDays = ($totalDays % 2 == 0 ? $totalDays : $totalDays + 1) / 2; 
        
        $dateInit = new \DateTime($dateInitial);
        $row = array();
        $countOne = 0;
        $countTwo = $totalDays;

        for ($i=0; $i<$totalDays; $i++)  {  
            
            $columns = new \stdClass();
            $cantOne = $i;
            $cantTwo = $i + $totalDays;

            $dateFirst = strtotime ( '+'.$cantOne.' day' , strtotime ( $dateInitial) ) ;
            $dateFirst = date ( 'd-m-Y' , $dateFirst );
            $dateOne = new \DateTime($dateFirst);            

            $dateSecond = strtotime ( '+'.$cantTwo.' day' , strtotime ( $dateInitial) ) ;
            $dateSecond = date ( 'd-m-Y' , $dateSecond );
            $dateTwo = new \DateTime($dateSecond);            

            $columns->indexFirst = ++$countOne;
            $columns->amountFirst = $amountTotal - ($dailyAmount * ($columns->indexFirst-1));
            $columns->dateFirst = $dateFirst;
            
            $columns->indexSecond = ++$countTwo;
            $columns->amountSecond = $amountTotal - ($dailyAmount * ($columns->indexSecond-1));
            $columns->dateSecond = $dateSecond;

            array_push($row, $columns);
        }        

        $infoCredit = new \stdClass();
        $infoCredit->name = $data->cliente->nombre." ".$data->cliente->apellido;
        $infoCredit->dpi = $data->cliente->dpi;
        $infoCredit->address = $data->cliente->direccion;
        $infoCredit->numberPhone = $data->cliente->telefono;
        $infoCredit->plan = $data->planes->descripcion;
        $infoCredit->days = $data->planes->dias;
        $infoCredit->date = $data->fecha_inicio;
        $infoCredit->amount = $data->montos->monto;
        $infoCredit->fees = $data->cuota_diaria;
        $infoCredit->amountDefault = $dailyAmount/2;
        $infoCredit->arrayQuota = $row;
        
        return $infoCredit;
    }
}
