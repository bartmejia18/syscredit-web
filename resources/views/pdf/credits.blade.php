<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<head>
	<style>
        <?php 	include( public_path() . '/css/fonts-roboto.css' );?>
    </style>
    <style type="text/css" media="all">
        div.title {
			font-family: 'Roboto', sans-serif;
			text-align: center;
			height: 18px;
			font-size: 18px;
			color: black;
		}

        table.table-head{			
			font-family: 'Roboto', sans-serif;
			font-size: 12px;
		}
        table.table-payment{
            width: 100%;
			font-family: 'Roboto', sans-serif;
			font-size: 11px;
            border-collapse: collapse;
        }
        thead.head-payment{
			background: #21618C;            
            border: 1px solid black;
		}
        th.head-column{			
			text-align: center;
			font-size: 12px;
			color: #ffffff;
		}
        body.body-payment{            
            border: 1px solid black;
        }
        tr.tr-payment{            
            border: 1px solid black;
            height: 15px;
        }
        td.td-payment{            
            border: 1px solid black;
            font-size: 11px;
            height: 15px;
            text-align: center;
        }
        td.td-name-customer{
            border: 1px solid black;
            text-align: left;
            font-size: 11px;
        }
        table.table-resumen{
			font-family: 'Roboto', sans-serif;
			font-size: 11px;
		}
	</style>
	</head>
	<body class="body-width">
		<div class="title"><strong>REPORTE DE CRÉDITOS</strong></div>	
		<br>
		<table class="table-head">
			<tr>
				<td width="20%">Estado de créditos:</td>
				<td width="40%">
					<span><strong>{!! $data->status !!}</strong></span>
				</td>
            </tr>
            <tr>
				<td width="20%">Fecha inicio:</td>
				<td width="20%">
					<span><strong>{!! $data->fecha_inicio !!}</strong></span>
				</td>
            </tr>
            <tr>
                <td width="20%">Fecha final:</td>
				<td width="20%">
					<span><strong>{!! $data->fecha_fin !!}</strong></span>
				</td>
			</tr>
		</table>
		<br>
		<table class="table-payment">
			<thead class="head-payment">
				<tr class="tr-payment">
                    <th class="head-column" width="4%">No.</th>
					<th class="head-column" width="40%">Nombre cliente</th>
					<th class="head-column" width="12%">Monto</th>
					<th class="head-column" width="12%">Plan</th>
					<th class="head-column" width="12%">Fecha crecación</th>		
					<th class="head-column" width="12%">Fecha de inicio</th>		
                    <th class="head-column" width="20%">Cobrador</th>				
				</tr>
			</thead>
			<tbody class="body-payment">    
            <?php $count = 0; ?>
            @foreach($data->credits->credits as $item)
				<tr class="tr-payment">					
                    <td class="td-payment">{!! ++$count !!}</td>
                    <td class="td-name-customer">{!! $item->cliente->nombre." ".$item->cliente->apellido !!}</td>
                    <td class="td-payment">Q. {!! number_format((float)($item->montos->monto), 2, '.', '') !!}</td>
					<td class="td-payment">{!! $item->planes->descripcion !!}</td>
					<td class="td-payment">{!! $item->fecha_creacion !!}</td>
					<td class="td-payment">{!! $item->fecha_inicio !!}</td>
                    <td class="td-payment">{!! $item->usuariocobrador->nombre !!}</td>
                </tr>	
            @endforeach
                <tr class="tr-payment">
                    <td class="td-payment" colspan="1"></td>
                    <td class="td-payment" style="text-align: right;">Total: </td>
                    <td class="td-payment" style="background-color:#8FCFF9">Q. {!! number_format((float)($data->credits->sumAmountCredits), 2, '.', '') !!}</td>
                    <td class="td-payment" colspan="4"></td>
                </tr>
			</tbody>
		</table>
	</body>
