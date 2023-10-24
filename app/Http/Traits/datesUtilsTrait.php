<?php

namespace App\Http\Traits;

trait datesUtilsTrait {
    public function getDateFinalCredit($credit) {
        $dateFinal = "";
        if ($credit->estado == 0) {
            if ($credit->fecha_finalizado != null) {
                $dateFinal = $credit->fecha_finalizado;
            } else {
                $dateFinal = $credit->fecha_fin;
            }
        } else {
            $dateFinal = date('Y-m-d');
        }

        return $dateFinal;
    }
}