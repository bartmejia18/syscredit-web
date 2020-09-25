;(function() 
{
	"use strict";

	angular.module("app.sucursales", ["app.constants"])

	.controller("SucursalesController", ["$scope", "$filter", "$http", "$modal", "$interval", "API_URL", function($scope, $filter, $http, $modal, $timeout, API_URL)  {	
		
		// Variables generales
		$scope.datas = [];
		$scope.currentPageStores = [];
		$scope.searchKeywords = "";
		$scope.filteredData = [];	
		$scope.row = "";
		$scope.numPerPageOpts = [5, 10, 25, 50, 100];
		$scope.numPerPage = $scope.numPerPageOpts[1];
		$scope.currentPage = 1;
		$scope.positionModel = "topRight";
		$scope.toasts = [];
		var modal;

		$scope.LlenarTabla = function()
		{
			$scope.datas = [];
			$http({
				method: 'GET',
			  	url: 	API_URL+'sucursales'
			})
			.then(function successCallback(response)  {
			    $scope.datas = response.data.records;
				$scope.search();
				$scope.select($scope.currentPage);
			}, 
			function errorCallback(response)  {			
			   console.log( response.data.message );
			});
		}

		// FUNCIONES DE DATATABLE
		$scope.select = function(page) {
			var start = (page - 1)*$scope.numPerPage,
				end = start + $scope.numPerPage;

			$scope.currentPageStores = $scope.filteredData.slice(start, end);
		}

		$scope.onFilterChange = function() {
			$scope.select(1);
			$scope.currentPage = 1;
			$scope.row = '';
		}

		$scope.onNumPerPageChange = function() {
			$scope.select(1);
			$scope.currentPage = 1;
		}

		$scope.onOrderChange = function() {
			$scope.select(1);
			$scope.currentPage = 1;
		}

		$scope.search = function() {
			$scope.filteredData = $filter("filter")($scope.datas, $scope.searchKeywords);
			$scope.onFilterChange();		
		}

		$scope.order = function(rowName) {
			if($scope.row == rowName)
				return;
			$scope.row = rowName;
			$scope.filteredData = $filter('orderBy')($scope.datas, rowName);
			$scope.onOrderChange();
		}	

		$scope.LlenarTabla();

		// Función para Toast
		$scope.createToast = function(tipo, mensaje) {
			$scope.toasts.push({
				anim: "bouncyflip",
				type: tipo,
				msg: mensaje
			});
		}

		$scope.closeAlert = function(index) {
			$scope.toasts.splice(index, 1);
		}

		$scope.saveData = function( sucursal ) {
			if ($scope.accion == 'crear') {
				$http({
					method: 'POST',
				  	url: 	API_URL+'sucursales',
				  	data: { 
				  		descripcion: sucursal.descripcion,
				  		direccion: sucursal.direccion,
				  		telefono: sucursal.telefono,
				  	}
				})
				.then(function successCallback(response) {
					if( response.data.result ) {
					    $scope.LlenarTabla();
					    modal.close();
					    $scope.createToast("success", "<strong>Éxito: </strong>"+response.data.message);
					    $timeout( function(){ $scope.closeAlert(0); }, 5000);
					}
					else {
						$scope.createToast("danger", "<strong>Error: </strong>"+response.data.message);
					    $timeout( function(){ $scope.closeAlert(0); }, 5000);	
					}
				}, 
				function errorCallback(response) {
				   console.log( response.data.message );
				});
			}
			else if ($scope.accion == 'editar') {
				$http({
					method: 'PUT',
				  	url: 	API_URL+'sucursales/'+sucursal.id,
				  	data: { 
				  		descripcion: sucursal.descripcion,
				  		direccion: sucursal.direccion,
				  		telefono: sucursal.telefono,
				  	}
				})
				.then(function successCallback(response) {
					if( response.data.result ) {
					    $scope.LlenarTabla();
					    modal.close();
					    $scope.createToast("success", "<strong>Éxito: </strong>"+response.data.message);
					    $timeout( function(){ $scope.closeAlert(0); }, 3000);
					}
					else {
						$scope.createToast("danger", "<strong>Error: </strong>"+response.data.message);
					    $timeout( function(){ $scope.closeAlert(0); }, 5000);	
					}
				}, 
				function errorCallback(response) {
				   console.log( response.data.message );
				});
			}
			else if ($scope.accion == 'eliminar') {
				$http({
					method: 'DELETE',
				  	url: 	API_URL+'sucursales/'+sucursal.id,
				})
				.then(function successCallback(response) {
					if( response.data.result ) {
					    $scope.LlenarTabla();
					    modal.close();
					    $scope.createToast("success", "<strong>Éxito: </strong>"+response.data.message);
					    $timeout( function(){ $scope.closeAlert(0); }, 3000);
					}
					else {
						$scope.createToast("danger", "<strong>Error: </strong>"+response.data.message);
					    $timeout( function(){ $scope.closeAlert(0); }, 5000);	
					}
				}, 
				function errorCallback(response) {
				   console.log( response.data.message );
				});
			}
		}

		// Funciones para Modales
		$scope.modalCreateOpen = function() {
			$scope.sucursal = {};
			$scope.accion = 'crear';

			modal = $modal.open({
				templateUrl: "views/sucursales/modal.html",
				scope: $scope,
				size: "md",
				resolve: function() {},
				windowClass: "default"
			});
		}

		$scope.modalEditOpen = function(data) {			
			$scope.accion = 'editar';
			$scope.sucursal = data;

			modal = $modal.open({
				templateUrl: "views/sucursales/modal.html",
				scope: $scope,
				size: "md",
				resolve: function() {},
				windowClass: "default"
			});
		}

		$scope.modalDeleteOpen = function(data) {			
			$scope.accion = 'eliminar';

			modal = $modal.open({
				templateUrl: "views/sucursales/modal.html",
				scope: $scope,
				size: "md",
				resolve: function() {},
				windowClass: "default"
			});
		}

		$scope.modalClose = function() {
			modal.close();
		}
	}])
}())