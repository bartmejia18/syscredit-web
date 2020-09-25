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
			font-size: 12px;
		}
        table.table-payment{
            width: 100%;
			font-family: 'Roboto', sans-serif;
			font-size: 12px;
            border-collapse: collapse;
        }
        thead.head-payment{
			background: #134794;            
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
			font-size: 12px;
		}
	</style>
	<body class="body-width">
		<div class="title"><strong>ESTADO DE CUENTA</strong></div>	
		<br>
		<table class="table-head">
			<tr>
				<td width="20%">Nombre cobrador:</td>
				<td width="40%">
					<span><strong>Bartolomé Mejía</strong></span>
				</td>
				<td width="20%">PDI:</td>
				<td width="20%">
					<span><strong>13/10/2019</strong></span>
				</td>
			</tr>
			<tr>
				<td width="20%">Dirección:</td>
				<td width="40%">
					<span><strong>Retalhuleu</strong></span>
				</td>
				<td width="20%">Teléfono:</td>
				<td width="20%">
					<span><strong>13/10/2019</strong></span>
				</td>
			</tr>
			<tr>
				<td width="20%">Plan:</td>
				<td width="40%">
					<span><strong>Retalhuleu</strong></span>
				</td>
				<td width="20%">Monto:</td>
				<td width="20%">
					<span><strong>13/10/2019</strong></span>
				</td>
			</tr>
			<tr>
				<td width="20%">Fecha de inicio:</td>
				<td width="40%">
					<span><strong>Retalhuleu</strong></span>
				</td>
				<td width="20%">Fecha de Finalización:</td>
				<td width="20%">
					<span><strong>13/10/2019</strong></span>
				</td>
			</tr>
		</table>
		<br>
		<table class="table-payment">
			<thead class="head-payment">				
				<tr class="tr-payment">
                    <th class="head-column" width="5%">No.</th>
					<th class="head-column" width="19%">Fecha sugerida de pago</th>
					<th class="head-column" width="19%">Fecha de pago</th>
					<th class="head-column" width="19%">Cuotas pagadas</th>
					<th class="head-column" width="19%">Monto pagado</th>
					<th class="head-column" width="19%">Saldo</th>
				</tr>
			</thead>
			<tbody class="body-payment">    
            <?php $count = 0; ?>
            
				<tr class="tr-payment">					
                    <td class="td-payment">1</td>
                    <td class="td-name-customer">Cliente 1</td>
                    <td class="td-payment">100</td>
					<td class="td-payment">100</td>
					<td class="td-payment">100</td>
					<td class="td-payment">100</td>
                </tr>	
            
			</tbody>
		</table>
		<br>
	</body>
