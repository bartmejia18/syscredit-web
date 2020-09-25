;(function() 
{
	"use strict";

	angular.module("app.dashboard", ["app.constants", 'app.service.dashboard'])

	.controller("DashboardController", ["$scope", "$http", "$modal", "$interval", 'dashboardService', "API_URL", function($scope, $http, $modal, $timeout, dashboardService, API_URL)  {	
        
        $scope.sucursales = [];
        loadData();

		function loadData(){
			dashboardService.index().then(function (response) {
                $scope.sucursales = response.data.records;
            });
        }        
	}])
}())