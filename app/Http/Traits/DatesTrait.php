<?php

namespace App\Http\Traits;

use App\Creditos;

trait DatesTrait {
    public function getLastDayWithoutSunday($dateInitial, $days){
        //Esta pequeña funcion me crea una fecha de entrega sin sabados ni domingos  
        $maxDias = 20; //Cantidad de dias maximo para el prestamo, este sera util para crear el for  
        $fechaFinal;
        $segundos = 0;

        // DateTime class.
        $date = new \DateTime($dateInitial);
        $timestamp = $date->getTimestamp();
            //Creamos un for desde 0 hasta 3  
        for ($i=0; $i<$days-1; $i++)  {  
            //Acumulamos la cantidad de segundos que tiene un dia en cada vuelta del for  
            $segundos = $segundos + 86400;  
        
            //Obtenemos el dia de la fecha, aumentando el tiempo en N cantidad de dias, segun la vuelta en la que estemos  
            $caduca = date("D", $timestamp+$segundos);  
            
            //Comparamos si estamos en sabado o domingo, si es asi restamos una vuelta al for, para brincarnos el o los dias...  
            if ($caduca == "Sun")  {  
                $i--;  
            }  else  {  
            //Si no es sabado o domingo, y el for termina y nos muestra la nueva fecha  
                $fechaFinal = date("Y-m-d", $timestamp+$segundos);  
            }  
        }
        return $fechaFinal;
    }

    public function getLastDay($firstDate, $days){
        $lastDate = strtotime ( '+'.($days - 1).' day', strtotime ( $firstDate ));
        $lastDate = date ( 'j-m-Y' , $lastDate );
        return $lastDate;
    }
}
