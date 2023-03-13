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
			text-align: left;
			height: 18px;
			font-size: 15px;
			color: #067851;
			font-weight: bold;
		}
		table{
			width: 100%;
			font-family: 'Roboto', sans-serif;
			font-size: 9px;
		}
		span.note{
			font-size:8px;
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
			height: 10px;
			border: 1px solid black;
			font-size: 8px;
			text-align: center;	
		}
		td.rowfirm{
			height: 40px;
			border-bottom: 1px solid black;
		}
		td.fingerprint{
			height: 40px;
			border-bottom: 1px solid black;
		}
		td.rowfirmlabel{
			font-size: 8px;
			text-align: center;	
		}

	</style>

	<body class="body-width">	
	<table>
		<tr>
			<td><div class="title" style="margin-left: 125px">BOLETA DE PAGOS</div></td>
		</tr>
	</table>
	<br>
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
					@if($quota->sundayFirst == "N")
						<td class="columnapago" style="width:10%">{!!$quota->indexFirst!!}</td>
						<td class="columnapago" style="width:40%">Q. {!!number_format((float)($quota->amountFirst), 2, '.', '')!!}</td>
						<td class="columnapago" style="width:40%">{!!$quota->dateFirst!!}</td>
					@else
						<td class="columnapago" colspan="3"><strong>DOMINGO</strong></td>		
					@endif					
					@if($quota->indexSecond <= $data->days)
						@if($quota->sundaySecond == "N")
							<td class="columnapago" style="width:10%">{!!$quota->indexSecond!!}</td>
							<td class="columnapago" style="width:40%">Q. {!!number_format((float)($quota->amountSecond), 2, '.', '')!!}</td>
							<td class="columnapago" style="width:40%">{!!$quota->dateSecond!!}</td>
						@else
							<td class="columnapago" colspan="3"><strong>DOMINGO</strong></td>		
						@endif
					@else
						<td class="columnapago" colspan="3"><strong></strong></td>		
					@endif
				</tr>
				@endforeach	
			</tbody>
		</table>
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