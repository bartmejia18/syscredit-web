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
        $infoCredit->tipoPlan = "Cuota diaria";
        $infoCredit->date = $data->fecha_inicio;
        $infoCredit->amount = $data->deudatotal;
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
        $infoCredit->tipoPlan = "Cuota diaria";
        $infoCredit->date = $data->fecha_inicio;
        $infoCredit->amount = $data->deudatotal;
        $infoCredit->fees = $data->cuota_diaria;
        $infoCredit->amountDefault = $dailyAmount/2;
        $infoCredit->arrayQuota = $row;
        
        return $infoCredit;
    }

    public function getArrayWeek($data) {
        $dateInitial = $data->fecha_inicio;
        $dateFinal = $data->fecha_fin;
        $amountTotal = $data->deudatotal;
        $dailyAmount = $data->cuota_diaria;

        $totalDays = abs($data->planes->dias); 
        $totalDays = floor($totalDays + 1 );	
        
        $totalDays = ($totalDays % 2 == 0 ? $totalDays : $totalDays + 1) / 2; 
        
        $row = array();
        $countOne = 0;
        $countTwo = $totalDays;

        $cantOne = 0;
        $cantTwo = $totalDays * 7;

        for ($i=0; $i<$totalDays; $i++)  {  
            $columns = new \stdClass();            
            
            $dateFirst = strtotime ( '+'.$cantOne.' day' , strtotime ( $dateInitial) ) ;
            $dateFirst = date ( 'd-m-Y' , $dateFirst );
            $dateOne = new \DateTime($dateFirst);            

            $columns->indexFirst = ++$countOne;
            $columns->amountFirst = $amountTotal - ($dailyAmount * ($columns->indexFirst-1));
            $columns->dateFirst = $dateFirst;
        
            $dateSecond = strtotime ( '+'.$cantTwo.' day' , strtotime ( $dateInitial) ) ;
            $dateSecond = date ( 'd-m-Y' , $dateSecond );
            $dateTwo = new \DateTime($dateSecond);

            $columns->indexSecond = ++$countTwo;
            $columns->amountSecond = $amountTotal - ($dailyAmount * ($columns->indexSecond-1));
            $columns->dateSecond = $dateSecond;

            $cantOne = $cantOne + 7;
            $cantTwo = $cantTwo + 7;            
            
            array_push($row, $columns);
        }        

        $infoCredit = new \stdClass();
        $infoCredit->name = $data->cliente->nombre." ".$data->cliente->apellido;
        $infoCredit->dpi = $data->cliente->dpi;
        $infoCredit->address = $data->cliente->direccion;
        $infoCredit->numberPhone = $data->cliente->telefono;
        $infoCredit->plan = $data->planes->descripcion;
        $infoCredit->days = $data->planes->dias;
        $infoCredit->tipoPlan = "Cuota semanal";
        $infoCredit->date = $data->fecha_inicio;
        $infoCredit->amount = $data->deudatotal;
        $infoCredit->fees = $data->cuota_diaria;
        $infoCredit->amountDefault = $dailyAmount/2;
        $infoCredit->arrayQuota = $row;
        
        return $infoCredit;
    }

    public function getArrayTwoWeek($data) {
        $dateInitial = $data->fecha_inicio;
        $dateFinal = $data->fecha_fin;
        $amountTotal = $data->deudatotal;
        $dailyAmount = $data->cuota_diaria;

        $totalDays = abs($data->planes->dias); 
        $totalDays = floor($totalDays + 1 );	
        
        $totalDays = ($totalDays % 2 == 0 ? $totalDays : $totalDays + 1) / 2; 
        
        $row = array();
        $countOne = 0;
        $countTwo = $totalDays;

        $cantOne = 0;
        $cantTwo = $totalDays * 14;

        for ($i=0; $i<$totalDays; $i++)  {  
            $columns = new \stdClass();            
            
            $dateFirst = strtotime ( '+'.$cantOne.' day' , strtotime ( $dateInitial) ) ;
            $dateFirst = date ( 'd-m-Y' , $dateFirst );
            $dateOne = new \DateTime($dateFirst);            

            $columns->indexFirst = ++$countOne;
            $columns->amountFirst = $amountTotal - ($dailyAmount * ($columns->indexFirst-1));
            $columns->dateFirst = $dateFirst;
        
            $dateSecond = strtotime ( '+'.$cantTwo.' day' , strtotime ( $dateInitial) ) ;
            $dateSecond = date ( 'd-m-Y' , $dateSecond );
            $dateTwo = new \DateTime($dateSecond);

            $columns->indexSecond = ++$countTwo;
            $columns->amountSecond = $amountTotal - ($dailyAmount * ($columns->indexSecond-1));
            $columns->dateSecond = $dateSecond;

            $cantOne = $cantOne + 14;
            $cantTwo = $cantTwo + 14;            
            
            array_push($row, $columns);
        }        

        $infoCredit = new \stdClass();
        $infoCredit->name = $data->cliente->nombre." ".$data->cliente->apellido;
        $infoCredit->dpi = $data->cliente->dpi;
        $infoCredit->address = $data->cliente->direccion;
        $infoCredit->numberPhone = $data->cliente->telefono;
        $infoCredit->plan = $data->planes->descripcion;
        $infoCredit->days = $data->planes->dias;
        $infoCredit->tipoPlan = "Cuota quincenal";
        $infoCredit->date = $data->fecha_inicio;
        $infoCredit->amount = $data->deudatotal;
        $infoCredit->fees = $data->cuota_diaria;
        $infoCredit->amountDefault = $dailyAmount/2;
        $infoCredit->arrayQuota = $row;
        
        return $infoCredit;
    }

    public function getArrayMonth($data) {
        $dateInitial = $data->fecha_inicio;
        $dateFinal = $data->fecha_fin;
        $amountTotal = $data->deudatotal;
        $dailyAmount = $data->cuota_diaria;

        $totalDays = abs($data->planes->dias); 
        $totalDays = floor($totalDays + 1 );	
        
        $totalDays = ($totalDays % 2 == 0 ? $totalDays : $totalDays + 1) / 2; 
        
        $row = array();
        $countOne = 0;
        $countTwo = $totalDays;

        $cantOne = 0;
        $cantTwo = 0;

        for ($i=0; $i<$totalDays; $i++)  {  
            $columns = new \stdClass();
            $cantOne = $i;
            $cantTwo = $i + $totalDays;          
            
            $dateFirst = strtotime ( '+'.$cantOne.' months' , strtotime ( $dateInitial) ) ;
            $dateFirst = date ( 'd-m-Y' , $dateFirst );
            $dateOne = new \DateTime($dateFirst);            

            $columns->indexFirst = ++$countOne;
            $columns->amountFirst = $amountTotal - ($dailyAmount * ($columns->indexFirst-1));
            $columns->dateFirst = $dateFirst;
        
            $dateSecond = strtotime ( '+'.$cantTwo.' months' , strtotime ( $dateInitial) ) ;
            $dateSecond = date ( 'd-m-Y' , $dateSecond );
            $dateTwo = new \DateTime($dateSecond);

            $columns->indexSecond = ++$countTwo;
            $columns->amountSecond = $amountTotal - ($dailyAmount * ($columns->indexSecond-1));
            $columns->dateSecond = $dateSecond;

            $cantOne = $cantOne + 7;
            $cantTwo = $cantTwo + 7;            
            
            array_push($row, $columns);
        }        

        $infoCredit = new \stdClass();
        $infoCredit->name = $data->cliente->nombre." ".$data->cliente->apellido;
        $infoCredit->dpi = $data->cliente->dpi;
        $infoCredit->address = $data->cliente->direccion;
        $infoCredit->numberPhone = $data->cliente->telefono;
        $infoCredit->plan = $data->planes->descripcion;
        $infoCredit->days = $data->planes->dias;
        $infoCredit->tipoPlan = "Cuota mensual";
        $infoCredit->date = $data->fecha_inicio;
        $infoCredit->amount = $data->deudatotal;
        $infoCredit->fees = $data->cuota_diaria;
        $infoCredit->amountDefault = $dailyAmount/2;
        $infoCredit->arrayQuota = $row;
        
        return $infoCredit;
    }
}
