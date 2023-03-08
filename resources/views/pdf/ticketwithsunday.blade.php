	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<style>
        <?php 	include( public_path() . '/css/fonts-roboto.css' );?>
    </style>
	<style type="text/css">
		html {
			margin: 0;
		}
		body {
			margin: 2mm 8mm 2mm 8mm;
		}
		.body-width {
			width: 45%;
		}
		div.top-bar {
			height: 2px;
		}
		div.blue-bar {
			height: 20px;
			background: #067851;
		}
		div.title-bar {
			height: 140px;
			background-size: cover;
			padding: 20px 60px 20px 40px;
		}
		div.title {
			font-family: 'Roboto', sans-serif;
			text-align: center;
			height: 18px;
			font-size: 15px;
			color: #067851;
			font-weight: bold;
		}
		table{
			width: 100%;
			font-family: 'Roboto', sans-serif;
			font-size: 11px;
		}
		span.note{
			font-size:12px;
			font-family: 'Roboto', sans-serif;
		}
		td.firstcolumninfo{
			width: 45%;
		}
		td.secundcolumninfo{
			width: 18%;
		}
		td.firstcolumnlabel{
			width: 50%;
		}
		td.secundcolumnlabel{
			width: 50%;
		}
		table.tablapago{
			width: 100%;
			border-collapse: collapse;
		}
		thead.pago{
			background: #067851;
			border: 1px solid black;
		}
		td.columna{
			width: 25%;
			text-align: center;
			color: #ffffff;
			border: 1px solid black;
		}
		tr.primeracolumna{
			width: 50%;
			height: 15px;
			border: 1px solid black;
		}
		td.columnapago{
			height: 14px;
			border: 1px solid black;
			font-size: 12px;
			text-align: center;	
		}
		td.rowfirm{
			height: 80px;
			border-bottom: 1px solid black;
		}
		td.fingerprint{
			height: 80px;
			border: 2px solid black;
		}
		td.rowfirmlabel{
			font-size: 12px;
			text-align: center;	
		}

	</style>

	<body class="body-width">	
		<table>
			<tr>
				<td style="width:15px"><img src="{{ public_path('images/logo_rapicredit.jpg') }}" height="65px"></td>
				<td><div class="title" style="margin-left: 70px">BOLETA DE PAGOS</div></td>
			</tr>
		</table>
		<table style="border: 1px solid black">
			<tr>
				<td class="firstcolumnlabel">Nombre Cliente: <strong>{!! $data->name !!}</strong></td>
				<td class="secundcolumnlabel">DPI: <strong>{!! $data->dpi !!}</strong></td>
			</tr>
			<tr>
				<td class="firstcolumnlabel">Dirección: <strong>{!! $data->address!!}</strong></td>
				<td class="secundcolumnlabel">Teléfono: <strong>{!! $data->numberPhone!!}</strong></td>
			</tr>
			<tr>
				<td class="firstcolumnlabel">Plan: <strong>{!! $data->plan!!}</strong></td>
				<td class="secundcolumnlabel">Fecha de entrega: <strong>{!! $data->date!!}</strong></td>
			</tr>
			<tr>
				<td class="firstcolumnlabel">Monto: <strong>Q. {!!number_format((float)$data->amount, 2, '.', '')!!}</strong></td>
				<td class="secundcolumnlabel">Cuota diaria: <strong>Q. {!!number_format((float)$data->fees, 2, '.', '')!!}</strong></td>
			</tr>
		
		</table>
		</table>
		<br>
		<table class="tablapago">
			<thead class="pago">
				<tr>
					<td class="columna" style="width:10%">No.</td>
					<td class="columna" style="width:40%">Monto</td>
					<td class="columna" style="width:40%">Fecha pago</td>
					<td class="columna" style="width:10%">No.</td>
					<td class="columna" style="width:40%">Monto</td>
					<td class="columna" style="width:40%">Fecha pago</td>
				</tr>
			</thead>
			<tbody>
				@foreach($data->arrayQuota as $quota)
				<tr class="primeracolumna">
					
						<td class="columnapago" style="width:10%">{!!$quota->indexFirst!!}</td>
						<td class="columnapago" style="width:40%">Q. {!!number_format((float)($quota->amountFirst), 2, '.', '')!!}</td>
						<td class="columnapago" style="width:40%">{!!$quota->dateFirst!!}</td>
					
					@if($quota->indexSecond <= $data->days)
						
							<td class="columnapago" style="width:10%">{!!$quota->indexSecond!!}</td>
							<td class="columnapago" style="width:40%">Q. {!!number_format((float)($quota->amountSecond), 2, '.', '')!!}</td>
							<td class="columnapago" style="width:40%">{!!$quota->dateSecond!!}</td>
						
					@else
						<td class="columnapago" colspan="3"><strong></strong></td>		
					@endif
				</tr>
				@endforeach	
			</tbody>
		</table>		
		<br>
		<br>
		<table>
			<tr>
				<td class="rowfirm"></td>
				<td class="rowfirm"></td>
				<td class="fingerprint"></td>
			</tr>
			<tr>
				<td class="rowfirmlabel">Firma Préstamo</td>
				<td class="rowfirmlabel">Firma Cliente</td>
				<td class="rowfirmlabel">Huella</td>
			</tr>
		</table> 
	</body>
