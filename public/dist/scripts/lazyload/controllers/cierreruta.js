;(function() 
{
	"use strict";

	angular.module("app.cierreRuta", ["app.constants", 'app.service.cierreruta'])

	.controller("CierreRutaController", ["$scope", "$filter", "$modal", "$interval", 'cierreRutaService', 'API_URL', function($scope, $filter, $modal, $timeout, cierreRutaService, API_URL)  {	
	
		$scope.currentPageStores = []
		$scope.searchKeywords = ""
		$scope.filteredData = []	
		$scope.row = ""
		$scope.numPerPageOpts = [5, 10, 25, 50, 100]
		$scope.numPerPage = $scope.numPerPageOpts[1]
		$scope.currentPage = 1
		$scope.positionModel = "topRight"
		$scope.toasts = []

		$scope.datas = Array()
		$scope.dateRouteClosure = $filter('date')(new Date(), 'yyyy-MM-dd')

		var modal

		showCierreRutas();
		
		function showCierreRutas() {
			var dateClosure = $scope.dateRouteClosure			
			cierreRutaService.cierreRutaList(dateClosure)
				.then(function successCallback(response) {        					        
                    $scope.datas = response.data.records;
					$scope.search();
					$scope.select($scope.currentPage);
			  	})
		}

		$scope.findRecordsDate = function(){		
			if($scope.dateRouteClosure != ""){								
				showCierreRutas()
			}
		}

		$scope.confirmeOpenRoute = function(routeClosure){
			
			cierreRutaService.updateClosingRoute(routeClosure.id) 
			.then(function successCallback(response) {
				if( response.data.result ) {
				
					showCierreRutas()
					
					modal.close();
					$scope.createToast("success", "<strong>Éxito: </strong>"+response.data.message);
					$timeout( function(){ $scope.closeAlert(0); }, 3000);
				}
				else {
					modal.close();
					$scope.createToast("danger", "<strong>Error: </strong>"+response.data.message);
					$timeout( function(){ $scope.closeAlert(0); }, 5000);	
				}
			}, 
			function errorCallback(response) {
				modal.close();
				$scope.createToast("danger", "<strong>Error: </strong>"+response.data.message);
				$timeout( function(){ $scope.closeAlert(0); }, 5000);	
			});
		}

		$scope.printInfoClosure = function(routeClosureId) {			
			cierreRutaService.printInfoClosure(routeClosureId)
		}

		// #region Toasts		
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
		// #endregion

		// #region DataTable
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
		// #endregion	

		// #region Modals
		$scope.modalOpenConfirme = function(data) {						
			$scope.record = data;

			modal = $modal.open({
				templateUrl: "views/cierreruta/modal.html",
				scope: $scope,
				size: "md",
				resolve: function() {},
				windowClass: "default"
			});
		}

		$scope.modalClose = function() {
			modal.close();
		}
		// #endregion
	}])
}())