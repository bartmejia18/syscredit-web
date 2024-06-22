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
			background: #134794;
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
			color: #1A4F83;
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
			width: 18%;
		}
		td.secundcolumnlabel{
			width: 20%;
		}
		table.tablapago{
			width: 100%;
			border-collapse: collapse;
		}
		thead.pago{
			background: #1A4F83;
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
			<td style="width:15px"><img src="{{ public_path('images/logo_rapicredit.jpg') }}" height="45px"></td>
			<td><div class="title" style="margin-left: 90px">CONTROL DE PAGOS</div></td>
		</tr>
	</table>
		<table>
			<tr>
				<td class="firstcolumnlabel">Nombre Cliente:</td>
				<td class="firstcolumninfo">
					<span><strong><i>{!! $data->name !!}</i></strong></span>
				</td>
				<td class="secundcolumnlabel">DPI:</td>
				<td class="secundcolumninfo">
					<span><strong>{!! $data->dpi !!}</strong></span>
				</td>
			</tr>
			<tr>
				<td class="firstcolumnlabel">Dirección:</td>
				<td class="firstcolumninfo">
					<span><strong>{!! $data->address!!}</strong></span>
				</td>
				<td class="secundcolumnlabel">Teléfono:</td>
				<td class="secundcolumninfo">
					<span><strong>{!! $data->numberPhone!!}</strong></span>
				</td>
			</tr>
			<tr>
				<td class="firstcolumnlabel">Plan:</td>
				<td class="firstcolumninfo">
					<span><strong>{!! $data->plan!!}</strong></span>
				</td>
				<td class="secundcolumnlabel">Fecha de entrega:</td>
				<td class="secundcolumninfo">
					<span><strong>{!! $data->date!!}</strong></span>
				</td>
			</tr>
			<tr>
				<td class="firstcolumnlabel">Monto:</td>
				<td class="firstcolumninfo">
					<span><strong>Q. {!!number_format((float)$data->amount, 2, '.', '')!!}</strong></span>
				</td>
				<td class="secundcolumnlabel">{!! $data->tipoPlan !!}:</td>
				<td class="secundcolumninfo">
					<span><strong>Q. {!!number_format((float)$data->fees, 2, '.', '')!!}</strong></span>
				</td>
			</tr>
		
		</table>
		<table class="tablapago">
			<thead class="pago">
				<tr>
					<td class="columna">No.</td>
					<td class="columna">Monto</td>
					<td class="columna">Fecha pago</td>
					<td class="columna">No.</td>
					<td class="columna">Monto</td>
					<td class="columna">Fecha pago</td>
				</tr>
			</thead>
			<tbody>
				@foreach($data->arrayQuota as $quota)
				<tr class="primeracolumna">
					@if($quota->sundayFirst == "N")
						<td class="columnapago" bgcolor="#E4E2E1">{!!$quota->indexFirst!!}</td>
						<td class="columnapago">Q. {!!number_format((float)($quota->amountFirst), 2, '.', '')!!}</td>
						<td class="columnapago">{!!$quota->dateFirst!!}</td>
					@else
						<td class="columnapago" bgcolor="#C1C1C1" colspan="3"><strong>DOMINGO</strong></td>		
					@endif					
					@if($quota->indexSecond <= $data->days)
						@if($quota->sundaySecond == "N")
							<td class="columnapago" bgcolor="#E4E2E1">{!!$quota->indexSecond!!}</td>
							<td class="columnapago">Q. {!!number_format((float)($quota->amountSecond), 2, '.', '')!!}</td>
							<td class="columnapago">{!!$quota->dateSecond!!}</td>
						@else
							<td class="columnapago" bgcolor="#C1C1C1" colspan="3"><strong>DOMINGO</strong></td>		
						@endif
					@else
						<td class="columnapago" colspan="3"><strong></strong></td>		
					@endif
				</tr>
				@endforeach	
			</tbody>
		</table>
		<span class="note"><strong>Nota: </strong>SE COBRARÁ MORA POR DÍA ATRASADO</span>
		<table>
			<tr>
				<td class="fingerprint"></td>
				<td class="rowfirm"></td>
				<td class="rowfirm"></td>
			</tr>
			<tr>
				<td class="rowfirmlabel">Huella</td>
				<td class="rowfirmlabel">Firma Cliente</td>
				<td class="rowfirmlabel">Firma Préstamo</td>
			</tr>
		</table>
	</body>
