<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<head>
	<style>
        <?php 	include( public_path() . '/css/fonts-roboto.css' );?>
    </style>
    <style type="text/css" media="all">
        body {
            font-family: 'Roboto', sans-serif;
            font-size: 12px;
            margin-left: 13px;
            margin-right: 13px;
        }
        div.title {
			text-align: center;
			font-size: 20px;
			font-weight: bold;
		}
        div.subtitle {
			text-align: center;
			font-size: 20px;
            font-weight: regular;
		}
        div.content {
            font-family: 'Roboto', sans-serif;
			font-size: 15px;
            text-align: justify;
            line-height: 1.5;
		}
	</style>
	</head>
	<body class="body">
		<div class="title"><strong>PAGARE</strong></div>	
		<div class="subtitle">LIBRE DE PROTESTO</div>
		<br>
		<div class="content">
			En el municipio de {!!$data->branchCity!!}, departamento de {!!$data->branchState!!}, el {!!$data->dateInWords!!}; Yo, <strong>{!!$data->name!!}</strong>, de {!!$data->age!!} años,
			casado, guatemalteco (a), comerciante, con domicilio en el municipio de {!!$data->city!!}, departamento de {!!$data->state!!}; me identifico con el Documento Personal de
			Identificación, código único de identificación, número {!!$data->dpiInWords!!} ({!!$data->dpi!!}),
			extendido por el Registro Nacional de las Personas. Actúo en nombre propio y por medio del presente título de crédito consistente en <strong>PAGARÉ</strong>, PROMETO INCONDICIONALMENTE
			PAGAR la suma de <strong>{!!$data->amountInWords!!} QUETZALES ({!!$data->amountInQuetzal!!})</strong> a la entidad mercantil denominada INVERSIONES OCCA, SOCIEDAD ANONIMA nombre a quien deberá hacerse dicho pago
			y el lugar en que se realizará el pago es {!!$data->branchAddress!!}, municipio de {!!$data->branchCity!!}, departamento de {!!$data->branchState!!}. Las condiciones en
			que cumpliré con la presente obligación son las siguientes: <strong>I) fecha de vencimiento y cumplimiento de la obligación:</strong> La obligación de pago de este pagaré se 
			hará el {!!$data->completeDate!!}; <strong>II.) Intereses:</strong> La suma representada por este pagaré NO devengará interés alguno; sin embargo, en el
			caso que no cancele la cantidad en la forma y tiempo pactado, reconozco la obligación de pagar un interés que se aplicará inmediatamente sobre el saldo del
			crédito a razón del uno por ciento mensual, y adicionalmente me obligo a cancelar un interés moratorio sobre el saldo vencido. <strong>III.) Efectos Procesales: a)</strong> Reconozco
			como Título Ejecutivo perfecto el presente Título de Crédito; <strong>b)</strong> La falta de pago por concepto de la obligación, dará derecho a el acreedor a dar por vencido el 
			plazo y a exigir ejecutivamente el pago del saldo total de la obligación principal contenida en este título o en su caso el saldo adeudado; <strong>c)</strong> Cualquier controversia
			derivada del incumplimiento del presente Título de Crédito, serán competentes los tribunales que el tenedor de este pagaré elija; renunciando al fuero de mi 
			domicilio y señalo lugar para recibir notificaciones la siguiente dirección: {!!$data->address!!}, obligándome a comunicar por escrito al acreedor de cualquier cambio de la 
			misma y acepto desde ya como validas y bien hecha las notificaciones judiciales o extrajudiciales que se me hagan en la dirección señalada, sino cumplo con dar 
			el citado aviso de cambio de dirección; <strong>d)</strong> Todos los gastos originados por esta negociación, así como de su cancelación, correrán a cargo del deudor, incluyendo
			los de cobranza extrajudical y judicial en caso de ejecutarse la obligación; <strong>e)</strong> Este pagaré se emite libre de protesto, libre de formalidades de presentación 
			y cobro o requerimiento. Acepto como buenas, liquidas y exigibles y de plazo vencido las cuentas que el pagaré presente. En fe de lo cual firmo este pagare.
            <br>
            <br>
			<strong>ACEPTO LIBRE DE PROTESTO</strong>
            <br>
            <br>
            <br>
            <br>
			F._____________________<br>
            <b>
            <strong>
			{!!$data->name!!}<br>
			{!!$data->dpi!!}<br>
            </strong>
		</div>
	</body>
