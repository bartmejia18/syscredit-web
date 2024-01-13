<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('dist');
});

Route::get('/boletaview', function(){
	return view('pdf.resumentodaycollector');
});

/*Route::get('/accountstatus', function(){
	return view('pdf.accountstatus');
});*/

Route::prefix('ws')->group(function () {
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
	
	Route::get('dashboard',					'DashboardController@dashboard');

	Route::get('validatecierreruta', 		'CierreRutaController@validatecierreruta');
	Route::get('printinfoclosure',			'CierreRutaController@printReportClosure');
	
	Route::get('listcustomers',				'CobradorController@listCustomers');
	Route::get('collectorpdf',				'CobradorController@generatePdf');

	Route::get('cobradorclientes',			'CreditosController@cobradorClientes');
	Route::post('registrarabonos',			'CreditosController@registrarAbono');
	Route::get('boletapdf',					'CreditosController@boletaPDF');
	Route::post('payments',					'CreditosController@payments');
	Route::post('validationArrears',		'CreditosController@validationArrearsCredit');
	Route::get('debtrecognitionpdf',		'CreditosController@debtRecognitionPDF');

	Route::get('getcreditdeleted', 			'CreditosEliminadosController@findByCreditId');
	
	Route::get('buscarcliente',				'ClientesController@findCustormerForDPI');
	Route::get('creditocliente',			'ClientesController@findCustomerForName');
	Route::get('detallecliente',			'ClientesController@detalleCreditoCliente');
	Route::get('branch/customers',			'ClientesController@customersByBranch');	
	Route::get('printaccountstatus',		'ClientesController@printPDFAccountStatus');

	Route::resource('clientUnlock',			'ClientesDesbloqueadosController');
	Route::get('clientUnlockById',			'ClientesDesbloqueadosController@findByClientId');
	
	Route::get('paymenthistory',			'HistorialPagosController@paymentHistory');
	Route::get('deletepayment',				'HistorialPagosController@deletePayment');
	Route::get('totalcolletion',			'HistorialPagosController@totalColletion');
	Route::get('getHistoryPayments',		'HistorialPagosController@historyForCustomer');
	
	Route::get('session/check',				'UsuariosController@checkSession');
	Route::get('listacobradores',			'UsuariosController@listacobradores');
	
	Route::get('reportgeneral',				'ReportsController@general');
	Route::get('reportcollector',			'ReportsController@collector');
	Route::get('reportdates',				'ReportsController@dates');
	Route::get('reportcredits',				'ReportsController@credits');
	Route::get('reportamounttocolleted',	'ReportsController@amountToColletedForCollector');
	Route::get('reportcreditspdf',			'ReportsController@reportCreditsPDF');

	Route::post('passwordaccess', 			'AccessController@randomPasswordAccess');
	Route::post('supervisoraccess',			'UsuariosController@passwordSupervisor');

	//Migraciones
	Route::get('setarrearscredits', 		'DataMigrationsController@setArrearsToCredits');
	Route::get('setcategorycustomer', 		'DataMigrationsController@setCategoryCustomers');

	Route::get('logout',function() {
		Auth::logout();
		return Redirect::to('/');
	});
});

Route::group(['prefix' => 'ws/movil'], function()
{	
	Route::any('login',				'CobradorMovilController@loginMovil');
	Route::any('listaclientes',		'CobradorMovilController@clientesActivos');
	Route::get('getHistoryPayments',		'HistorialPagosController@historyForCustomer');
	Route::get('permiso', 'PermisosController@getPermission');
});