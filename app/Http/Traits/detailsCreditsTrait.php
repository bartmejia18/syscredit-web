<?php

namespace App\Http\Traits;

use App\Creditos;
use App\DetallePagos;
use stdClass;
use App\Http\Traits\datesUtilsTrait;
use Carbon\Carbon;
use DateTime;
use Luecano\NumeroALetras\NumeroALetras;

trait detailsCreditsTrait {

    use datesUtilsTrait;

    public function getCreditsForCustomerId($customerId) {
        $credits = Creditos::with('planes')
                        ->where('clientes_id', $customerId)
                        ->where('estado','!=',2)
                        ->orderBy('id', 'desc')
                        ->get();
        
        $credits->map(function ($item, $key) {
            if ($item->estado == 1) {
                $item->cuotas_atrasadas = $this->getTotalDaysArrears($item);
                $item->estado_morosidad = $this->getArrearsStatusForDays($item->cuotas_atrasadas);
            }
        });
        return $credits;
    }

    public function getTotalActiveCompleted($credits) {
        $total = new stdClass();
        
        $countCredits = $credits->count();

        $total->totalCredits = $countCredits;
        $total->creditsCompleted = $credits->where('estado', 0)->count();
        $total->creditsActives =  $countCredits - $total->creditsCompleted;
        $total->status = $total->creditsCompleted == $countCredits ? 3 : 2;
        
        return $total;
    }

    public function getGeneralStatusCustomer($credits) {
        $arrersCredits = $this->getArrearsForCredits($credits);
        return $this->getArrearsStatus($arrersCredits);
    }


    public function getArrearsForCredits($credits) {

        $arrearsStatus = [
            'moroso' => 0,
            'bueno' => 0,
            'excelente' => 0
        ];

        foreach ($credits as $item) {
            if ($item->estado == 0) {
                if ($item->estado_morosidad == "Moroso") {
                    $arrearsStatus['moroso'] += 1;
                } else if ($item->estado_morosidad == "Bueno") {
                    $arrearsStatus['bueno'] += 1;
                } else {
                    $arrearsStatus['excelente'] += 1;
                }
            } else {
                if ($item->cuotas_atrasadas > 9) {
                    $arrearsStatus['moroso'] += 1;
                } else if ($item->cuotas_atrasadas >= 4 && $item->cuotas_atrasadas <= 9 ) {
                        $arrearsStatus['bueno'] += 1;
                } else {
                        $arrearsStatus['excelente'] += 1;
                }
            }
        }
        return $arrearsStatus;
    }

    public function getArrearsStatus($arrearsCredits) {
        if ($arrearsCredits['moroso'] == 0 && $arrearsCredits['bueno'] == 0 && $arrearsCredits['excelente'] > 0) {
            return "A";
        } else if ($arrearsCredits['moroso'] <= 1 && $arrearsCredits['bueno'] >= 0 && $arrearsCredits['excelente'] >= 0) {
            return "B";
        } else if ($arrearsCredits['moroso'] >= 2 && $arrearsCredits['moroso'] <= 3) {
            return "C";
        } else if ($arrearsCredits['moroso'] >= 4 && $arrearsCredits['moroso'] <= 5) {
            return "D";
        } else if ($arrearsCredits['moroso'] > 5) {
            return "E";
        }
    }

    /*
        Obtiene la cantidad de atrasos menos el total pagado
    */
    public function getTotalDaysArrearsWithTotalPaid($credit, $feePaid) {
        $dateInitial = $credit->fecha_inicio;
        $dateFinal = $this->getDateFinalCredit($credit);
    
        if ($dateInitial <= date('Y-m-d')) {
            $totalDays = (strtotime($dateInitial) - strtotime($dateFinal))/86400;
            $totalDays = abs($totalDays); 
            $totalDays = floor($totalDays + 1);	
        
            if ($credit->planes->tipo == 0 || $credit->planes->tipo == 1) {
                $countSundayTemporal = 0;
                if ($credit->planes->domingo == 1) {
                    for ($i=0; $i<$totalDays; $i++)  {  
                        $dateTemporal = strtotime('+'.$i.'day', strtotime($dateInitial));
                        $dateTemporal = date('d-m-Y', $dateTemporal);
                        $dateTemporalNew = new \DateTime($dateTemporal);
                        $sundayTemporal = date("D", $dateTemporalNew->getTimestamp());

                        if ($sundayTemporal == "Sun") {
                            ++$countSundayTemporal;
                        }
                    }
                }
                $totalDays = $totalDays - $countSundayTemporal;
            } else if ($credit->planes->tipo == 2) {
                $totalDays = floor($totalDays / 7);
            } else if ($credit->planes->tipo == 3) {
                $totalDays = floor($totalDays / 30);
            } else if ($credit->planes->tipo == 4) {
                $totalDays = floor($totalDays / 14);
            }
            return $totalDays - $feePaid;
        } else {
            return 0;
        }
    }

    public function getTotalDaysArrears($credit) {
        $dateInitial = $credit->fecha_inicio;
        $dateFinal = $this->getDateFinalCredit($credit);

        $detailsPayments = DetallePagos::where('credito_id',$credit->id)->get();

        $dtInit = strtotime($dateInitial);
        $dtFin = strtotime($dateFinal);
        
        $countDaysArrears = 0;
        if ($detailsPayments->count() > 0) {
            if ($credit->planes->tipo == 1) {
                /*
                    Ciclo for que aumenta día a día (86400) para plan diario
                */
                for($i = $dtInit; $i <= $dtFin; $i+=86400){
                    
                    $detailPayment = $this->findDateInPayments($detailsPayments, $i);

                    if ($credit->planes->domingo == 1) {
                        $sunday = new DateTime(date('d-m-Y', $i));
                        if (date("D", $sunday->getTimestamp()) != "Sun") {
                            if ($this->isValidPayment($detailPayment, $credit) == false) {
                                ++$countDaysArrears;
                            }
                        }
                    } else {
                        if ($this->isValidPayment($detailPayment, $credit) == false) {
                            ++$countDaysArrears;
                        }
                    }
                }
            } else if ($credit->planes->tipo == 2) {
                /*
                    Ciclo for que aumenta cada 7 día (604800) para plan semanal
                */
                for($i = strtotime($dateInitial); $i <= strtotime($dateFinal); $i+=604800) {
                    $detailPayment = $this->findDateInPayments($detailsPayments, $i);

                    if ($this->isValidPayment($detailPayment, $credit) == false) {
                        ++$countDaysArrears;
                    }                    
                }
            } else if ($credit->planes->tipo == 4) {
                /*
                    Ciclo for que aumenta cada 7 día (604800) para plan semanal
                */
                for($i = strtotime($dateInitial); $i <= strtotime($dateFinal); $i+=1209600) {
                    $detailPayment = $this->findDateInPayments($detailsPayments, $i);

                    if ($this->isValidPayment($detailPayment, $credit) == false) {
                        ++$countDaysArrears;
                    }                    
                }
            }
        } else {
            if ($dateFinal > $dateInitial) {
                $countDaysArrears = $this->getTotalDaysArrearsWithTotalPaid($credit, 0);
            }
        }
        return $countDaysArrears;
    }

    public function findDateInPayments($detailsPayments, $date) {
        return $detailsPayments->filter(function($payment) use ($date) {
                    return $payment->fecha_pago == date("Y-m-d", $date);
                })->first();
    }

    public function isValidPayment($detailPayment, $credit) {
        if ($detailPayment && $detailPayment->abono >= $credit->cuota_diaria && strtotime($detailPayment->fecha_pago) <= strtotime($credit->fecha_fin)) {
            return true;
        } else {
            return false;
        }
    }

    public function setArrearsToCreditComplete($credit) {
        $daysLate = $this->getTotalDaysArrears($credit);

        $credit->cuotas_atrasadas = $daysLate;
        $credit->estado_morosidad = $this->getArrearsStatusForDays($daysLate);
        $credit->save();
    }

    public function getArrearsStatusForDays($days) {
        if ($days > 9) {
            return 'Moroso';
        } else if ($days >= 4 && $days <= 9) {
            return 'Bueno';
        } else {
            return 'Excelente';
        }
    }

    public function detailsForPromissoryNote($data) {
        
        $date = new DateTime();
        $formatDate = $date->format('Y-m-d');

        $infoCredit = new \stdClass();
        $infoCredit->number = $data->sucursal->id . $data->id;
        $infoCredit->name = $data->cliente->nombre." ".$data->cliente->apellido;
        $infoCredit->dpi = $this->splitDPI($data->cliente->dpi);
        $infoCredit->dpiInWords = $this->convertirDPITexto($infoCredit->dpi);
        $infoCredit->address = $data->cliente->direccion;
        $infoCredit->state = $data->cliente->departamento;
        $infoCredit->city = $data->cliente->municipio;
        $infoCredit->maritalSatus = $data->cliente->estado_civil;
        $infoCredit->age = $this->ageInWords($data->cliente->fecha_nacimiento);
        $infoCredit->dateInWords = $this->dateInWords($formatDate);
        $infoCredit->amountInWords = $this->amountInWords($data->deudatotal);
        $infoCredit->amountInQuetzal = $this->amountInQuetzal($data->deudatotal);
        $infoCredit->completeDate = $this->dateInWords($data->fecha_fin);
        $infoCredit->branchAddress = $data->sucursal->direccion;
        $infoCredit->branchState = $data->sucursal->departamento;
        $infoCredit->branchCity = $data->sucursal->municipio;
        $infoCredit->company = strtoupper($data->sucursal->empresa->nombre);
        return $infoCredit;
    }

    public function splitDPI($dpi) {
        $parte1 = substr($dpi, 0, 4);
        $parte2 = substr($dpi, 4, 5);
        $parte3 = substr($dpi, 9, 4);

        return "$parte1 $parte2 $parte3";
    }

    public function amountInWords(string $amount): string {
        $conv = new NumeroALetras();
        return $conv->toWords($amount);
    }

    public function amountInQuetzal(string $amont): string {
         return "Q. " . number_format((float)$amont, 2, '.', ',');
    }

    function bloqueEnLetrasConCeros(string $bloque, NumeroALetras $conv): string {
       $bloque = trim($bloque);

        // contar ceros al inicio
        preg_match('/^0+/', $bloque, $m);
        $ceros = isset($m[0]) ? strlen($m[0]) : 0;

        $numero = (int)$bloque; // "00002" -> 2

        // Si todo era ceros (ej: "0000") => "cero cero cero cero"
        if ($numero === 0 && $ceros > 0) {
            return trim(str_repeat("cero ", $ceros));
        }

        $texto = $conv->toWords($numero);
        return trim(str_repeat("cero ", $ceros) . $texto);
    }

    function convertirDPITexto(string $cadena): string {
        $conv = new NumeroALetras();
        $bloques = preg_split('/\s+/', trim($cadena));

        $resultado = [];

        foreach ($bloques as $i => $b) {
            $texto = $this->bloqueEnLetrasConCeros($b, $conv);

            // 1er y 3er bloque, si empieza con '1' => "un " + texto
            if (($i === 0 || $i === 2) && $b !== '' && $b[0] === '1') {
                $texto = "un " . $texto;
            }

            $resultado[] = $texto;
        }

        return mb_strtolower(implode(", ", $resultado), 'UTF-8');
    }

    public function ageInWords(string $birthdate): string {
        $birthdateDT = new DateTime($birthdate);
        $today = new DateTime();

        $age = $today->diff($birthdateDT)->y;

        $conv = new NumeroALetras();
        return mb_strtolower($conv->toWords($age), 'UTF-8');
    }

    public function dateInWords(string $dateString): string {

        $date = new DateTime($dateString);

        $day = (int)$date->format('d');
        $month = (int)$date->format('m');
        $year = (int)$date->format('Y');

        $conv = new NumeroALetras();

        $dayInWords = $conv->toWords($day);
        $yearInWords = $conv->toWords($year);

        $months = [
            1 => "enero", 2 => "febrero", 3 => "marzo", 4 => "abril",
            5 => "mayo", 6 => "junio", 7 => "julio", 8 => "agosto",
            9 => "septiembre", 10 => "octubre", 11 => "noviembre", 12 => "diciembre"
        ];

        $monthInWords = $months[$month];

        return mb_strtolower("$dayInWords de $monthInWords del año $yearInWords", 'UTF-8');
    }
}