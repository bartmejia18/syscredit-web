;(function() 
{
	"use strict";

	angular.module("app.detallecliente", ["app.constants"])

	.controller("DetalleClienteController", ["$scope", "$routeParams", "$filter", "$http", "$modal", "$interval", "API_URL", function($scope, $routeParams, $filter, $http, $modal, $timeout, API_URL)  {	
		
		var customer = {};
		var modal

		$scope.listCredito = [];
		$scope.itemCredit = "";
		$scope.showInputSelect = false
		$scope.toasts = [];

		$scope.datosCliente = function(id) {
			$http({
				method: 'GET',
			  	url: 	API_URL+'detallecliente',
			  	params: {cliente_id:id}
			})
			.then(function successCallback(response)  {						
				customer =  response.data.records
				var credits = prepareListCredits(customer.creditos)
				
				if (credits.length > 1) { 
					$scope.showInputSelect = true
					$scope.listCredito = credits
				}
				$scope.itemCredit = credits[0]	
				showData(credits[0])
			}, 
			function errorCallback(response)  {			
			   console.log( response.data.message );
			});
		}

		$scope.datosCliente($routeParams.id);
		$scope.creditSelected = function(credit) {
			showData(credit)
		}

		function showData(infoCredit) {	
			console.log(infoCredit)		
			$scope.dpi	= customer.dpi
			$scope.nombre = customer.nombre
			$scope.apellido = customer.apellido
			$scope.nombre_completo = customer.nombre+' '+customer.apellido
			$scope.sexo = customer.sexo == 1 ? "Masculino" : "Femenino"
			$scope.direccion = customer.direccion
			$scope.estado_civil = customer.estado_civil == 1 ? "Soltero (a)" : "Casado (a)"
			$scope.telefono = customer.telefono

			$scope.plan = infoCredit.planes.descripcion
			$scope.monto_total = "Q. "+parseFloat(infoCredit.deudatotal).toFixed(2)
			$scope.fecha_inicio = infoCredit.fecha_inicio
			$scope.fecha_fin = infoCredit.fecha_fin		
			$scope.cobrador = infoCredit.usuariocobrador.nombre
			$scope.cuota_diaria = "Q. "+parseFloat(infoCredit.cuota_diaria).toFixed(2)
			$scope.status_credit = infoCredit.estado	
			$scope.saldo_pendiente = "Q. "+parseFloat(infoCredit.saldo).toFixed(2)
			$scope.total_cancelado = "Q. "+parseFloat(infoCredit.total_cancelado).toFixed(2)
			$scope.cuotas_pagadas = infoCredit.cuotas_pagados;

			$scope.porcentaje = parseInt(infoCredit.porcentaje_pago)
		}

		function prepareListCredits(credits) {
			var array = [];
			
			credits.forEach(function (item) {    
				item.statusText = statusText(item.estado)	
				array.push(item)                			
				
			})

			array.sort(function (a, b) {
				if (a.statusText > b.statusText) {
				  return 1;
				} 
				
				if (a.statusText < b.statusText) {
				  return -1;
				}
				// a must be equal to b
				return 0;
			  });

			
			return array
		}

		function statusText(status) {
			switch (status) {
				case 0 :
					return "Completado"
					break;
				case 1 :
					return "Activo"
					break;
				case 2 :
					return "Eliminado"
					break;
				default:
					return ""
			}
		}

		$scope.validatePassword = function (item) {
			$http({
			  method: 'POST',
			  url: API_URL + 'accessdelete',
			  data: {
				password: item.password
			  }
			}).then(function succesCallback(response) {
			  if (response.data == true) {
				console.log(response)
			  } else {
				modal.close();
				$scope.createToast("danger", "<strong>Error:</strong> La contraseña ingresa es incorrecta.");
				$timeout(function () { $scope.closeAlert(0); }, 5000);
			  }
			}, function errorCallback(response) {
			  console.log(response)
			})
		  }

		//#region "modal"
		$scope.modalDeleteOpen = function() {
			modal = $modal.open({
				templateUrl: "views/clientes/modalDelete.html",
				scope: $scope,
				size: "md",
				resolve: function(){},
				windowClass: "default"
			})
		}

		$scope.modalDeleteClose = function() {
			modal.close()
		}
		//#endregion
		//#region "Toast"
		$scope.createToast = function (tipo, mensaje) {
			$scope.toasts.push({
				anim: "bouncyflip",
				type: tipo,
				msg: mensaje
			});
		}

		$scope.closeAlert = function (index) {
			$scope.toasts.splice(index, 1);	
		}
		//#endregion
	}])
}())