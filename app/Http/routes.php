<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return redirect('dist');
});

Route::get('/boletaview', function(){
	return view('pdf.resumentodaycollector');
});

/*Route::get('/accountstatus', function(){
	return view('pdf.accountstatus');
});*/

Route::group(['prefix' => 'ws'], function() {
	Route::resource('tipousuarios', 		'TipoUsuariosController');
	Route::resource('sucursales', 			'SucursalesController');
	Route::resource('planes', 				'PlanesController');
	Route::resource('montosprestamo',		'MontosPrestamoController');
	Route::resource('clientes',				'ClientesController');
	Route::resource('referenciasclientes',	'ReferenciasPersonalesClientesController');
	Route::resource('creditos',				'CreditosController');
	Route::resource('usuarios',				'UsuariosController');
	Route::resource('creditoeliminado',     'CreditosEliminadosController');
	Route::post('login',					'UsuariosController@login');
	Route::resource('cierreruta',			'CierreRutaController');
	Route::get('validatecierreruta', 		'CierreRutaController@validatecierreruta');
	Route::get('session/check',				'UsuariosController@checkSession');
	Route::get('cobradorclientes',			'CreditosController@cobradorClientes');
	Route::get('listacobradores',			'UsuariosController@listacobradores');
	Route::post('registrarabonos',			'CreditosController@registrarAbono');
	Route::get('buscarcliente',				'ClientesController@buscarCliente');
	Route::get('creditocliente',			'ClientesController@buscarCreditoCliente');
	Route::get('detallecliente',			'ClientesController@detalleCreditoCliente');
	Route::get('branch/customers',			'ClientesController@customersByBranch');	
	Route::get('boletapdf',					'CreditosController@boletaPDF');
	Route::get('dashboard',					'DashboardController@dashboard');
	Route::get('paymenthistory',			'HistorialPagosController@paymentHistory');
	Route::get('deletepayment',				'HistorialPagosController@deletePayment');
	Route::get('totalcolletion',			'HistorialPagosController@totalColletion');
	Route::post('payments',					'CreditosController@payments');
	Route::get('listcustomers',				'CobradorController@listCustomers');
	Route::get('collectorpdf',				'CobradorController@generatePdf');
	Route::get('reportgeneral',				'ReportsController@general');
	Route::get('reportcollector',			'ReportsController@collector');
	Route::get('reportdates',				'ReportsController@dates');
	Route::get('printinfoclosure',			'CierreRutaController@printReportClosure');
	Route::get('printaccountstatus',		'ClientesController@printPDFAccountStatus');
	Route::post('accessdelete', 			'ClientesController@accessForDelete');
	Route::get('getcreditdeleted', 			'CreditosEliminadosController@findByCreditId');
	Route::get('getHistoryPayments',		'HistorialPagosController@historyForCustomer');
	Route::get('reportcredits',				'ReportsController@credits');

	Route::get('logout',function() {
		Auth::logout();
		return \Redirect::to('/');
	});
});

Route::group(['prefix' => 'ws/movil'], function()
{	
	Route::any('login',				'CobradorMovilController@loginMovil');
	Route::any('listaclientes',		'CobradorMovilController@clientesActivos');
	Route::get('getHistoryPayments',		'HistorialPagosController@historyForCustomer');
	Route::get('permiso', 'PermisosController@getPermission');
});
