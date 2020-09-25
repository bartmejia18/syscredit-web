<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<style>
        <?php 	include( public_path() . '/css/fonts-roboto.css' );?>
    </style>
    <style type="text/css">
        div.title {
			font-family: 'Roboto', sans-serif;
			text-align: center;
			height: 18px;
			font-size: 18px;
			color: black;
		}

        table.table-head{			
			font-family: 'Roboto', sans-serif;
			font-size: 11px;
		}
        table.table-payment{
            width: 100%;
			font-family: 'Roboto', sans-serif;
			font-size: 11px;
            border-collapse: collapse;
        }
        thead.head-payment{
			background: #134794;            
            border: 1px solid black;
		}
        th.head-column{			
			text-align: center;
			font-size: 10px;
			color: #ffffff;
		}
        body.body-payment{            
            border: 1px solid black;
        }
        tr.tr-payment{            
            border: 1px solid black;
        }
        td.td-payment{            
            border: 1px solid black;
            text-align: center;
        }
        td.td-name-customer{
            border: 1px solid black;
            text-align: left;
        }
        table.table-resumen{
			font-family: 'Roboto', sans-serif;
			font-size: 11px;
		}
	</style>
	<body class="body-width">
		<div class="title"><strong>RESUMEN DE COBRO</strong></div>	
		<br>
		<table class="table-head">
			<tr>
				<td width="20%">Nombre cobrador:</td>
				<td width="40%">
					<span><strong>{!! $data->collector !!}</strong></span>
				</td>
				<td width="20%">Fecha:</td>
				<td width="20%">
					<span><strong>{!! $data->date !!}</strong></span>
				</td>
			</tr>
			<tr>
				<td width="20%">Sucursal:</td>
				<td width="40%">
					<span><strong>{!! $data->branch !!}</strong></span>
				</td>
			</tr>
		</table>
		<br>
		<table class="table-payment">
			<thead class="head-payment">
				<tr class="tr-payment">
					<th class="head-column" colspan="4"></th>
					<th class="head-column" colspan="4">Cuotas</th>
					<th class="head-column" colspan="1"></th>
				</tr>
				<tr class="tr-payment">
                    <th class="head-column" width="4%">No.</th>
					<th class="head-column" width="34%">Nombre cliente</th>
					<th class="head-column" width="12%">Deuda total</th>
					<th class="head-column" width="10%">Cuota diaria</th>
					<th class="head-column" width="7%">Total</th>
					<th class="head-column" width="7%">Pendientes</th>
					<th class="head-column" width="7%">Atrasadas</th>
					<th class="head-column" width="7%">Pagadas</th>
					<th class="head-column" width="12%">Monto pagado</th>				
				</tr>
			</thead>
			<tbody class="body-payment">    
            <?php $count = 0; ?>
            @foreach($data->registros as $item)
				<tr class="tr-payment">					
                    <td class="td-payment">{!! ++$count !!}</td>
                    <td class="td-name-customer">{!! $item->cliente->nombre." ".$item->cliente->apellido !!}</td>
                    <td class="td-payment">Q. {!! number_format((float)($item->deudatotal), 2, '.', '') !!}</td>
					<td class="td-payment">Q. {!! number_format((float)($item->cuota_diaria), 2, '.', '') !!}</td>
					<td class="td-payment">{!! $item->total_cuotas !!}</td>
					<td class="td-payment">{!! $item->cantidad_cuotas_pendientes !!}</td>
					<td class="td-payment">{!! $item->cuotas_atrasadas !!}</td>
                    <td class="td-payment">{!! $item->cantidad_cuotas_pagadas !!}</td>
                    <td class="td-payment">Q. {!! number_format((float)($item->monto_pagado), 2, '.', '') !!}</td>
                </tr>	
            @endforeach
			</tbody>
		</table>
		<br>
		<table class="table-resumen">
			<tr>
				<td>Total COBRADO del día:</td>
				<td>
					<span><strong>Q. {!!number_format((float)($data->total_cobrado), 2, '.', '') !!}</strong></span>
                </td>            
            </tr>
            <tr>
				<td>Total a COBRAR del día:</td>
				<td>
					<span><strong>Q. {!!number_format((float)($data->total_cobrar), 2, '.', '') !!}</strong></span>
                </td>
            </tr>
            <tr>
                <td>Total mínimo a cobrar:</td>
				<td>
					<span><strong>Q. {!!number_format((float)($data->total_minimo), 2, '.', '') !!}</strong></span>
                </td>
            </tr>
            <tr>
                <td>Total de cartera:</td>
				<td>
					<span><strong>Q. {!!number_format((float)($data->total_catera), 2, '.', '') !!}</strong></span>
				</td>
			</tr>
			<tr>
                <td>Total de pendiente a cobrar:</td>
				<td>
					<span><strong>Q. {!!number_format((float)($data->total_pendiente), 2, '.', '') !!}</strong></span>
				</td>
			</tr>		
		</table>

	</body>
