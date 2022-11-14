; (function () {
    "use strict";
  
    angular.module("app.reports", ["app.constants", 'app.service.collector', 'app.service.plan', 'app.service.report', 'app.service.pdfs', 'app.service.branch'])
  
      .controller("ReportsController", ["$scope", "$filter", "$http", "$modal", "$interval", 'collectorService', 'planService', 'reportService', 'pdfsService', 'branchService', 'API_URL', function ($scope, $filter, $http, $modal, $timeout, collectorService, planService, reportService, pdfsService, branchService ,API_URL) {

        $scope.positionModel = "topRight"
        $scope.toasts = [];
        $scope.collectors = []
        $scope.plans = []
        $scope.branchs = []
        $scope.customer = {}
        var branchSelected
        var typeView = ""
        
        initLoadInfo();

        function initLoadInfo() {
            console.log("--->", "intLoadInfo")
            if ($scope.usuario.tipo_usuarios_id == 1) {
                loadBranches()
            } else {
                loadCollectors($scope.usuario.sucursales_id)
                loadPlanes($scope.usuario.sucursales_id)
            }
        }

        function loadCollectors(branch){
            $scope.collectors = []
            collectorService.index().then(function (response) {
                response.data.records.forEach(function (item) {
                    if (item.sucursales_id == branch) {
                        $scope.collectors.push(item)
                    }
                })        
            })
        }

        function loadPlanes(branch){
            $scope.plans = []
            planService.plans().then(function (response) {
                var itemAll = {}
                itemAll.descripcion = "Todos";
                itemAll.id = 0;
                $scope.plans.push(itemAll)
                response.data.records.forEach(function (item) {                    
                    if (item.sucursales_id == branch) {
                        $scope.plans.push(item)
                    }                                    
                })        
            })
        }

        function loadBranches() {
            branchService.branchs().then(
                function successCallback(response) {    
                    
                    if ($("#view").val() == "dates") {
                        var itemAll = {}
                        itemAll.descripcion = "Todos";
                        itemAll.id = 0;
                        $scope.branchs.push(itemAll)
                    }

                    response.data.records.forEach(function (item) {                                        
                        $scope.branchs.push(item)                        
                    })
                },
                function errorCallback(response) {
                    console.log(response)						
            });
        }

        function generateReport(typeView){   
            var statusCredit = $scope.statusCredit           
            var collector =  $scope.collector
            var dateInit = $("#date_init").val()
            var dateFinal = $("#date_fin").val()
            var plan = $scope.plan == undefined ? "" : $scope.plan;        
            var branch = $scope.usuario.tipo_usuarios_id == 1 ? branchSelected : $scope.usuario.sucursales_id   
                     
            switch (typeView) {
                case "dates" :     
                    
                    if(dateInit != "" && dateInit != undefined && dateFinal != "" && dateFinal != undefined){
                        reportService.dates(dateInit, dateFinal, branch)
                            .then(function(response){
                                if (response.data.result) {
                                    var info = response.data.records                                    
                                    $scope.customer.total = info.customers.withCredit
                                    $scope.customer.today = info.customers.withCreditToDay
                                    $scope.customer.notoday = info.customers.withCreditNoToDay
                                    $scope.revenueTotals = info.revenueTotals
                                    $scope.totalPendingReceivable = info.totalPendingReceivable
                                    $scope.totalReceivable = info.totalReceivable
                                }
                        })
                    }
                    break
                case "collectors" :                    
                    if (collector != "" && collector != undefined) {
                        reportService.collector(collector, dateInit, dateFinal, plan, branch)
                            .then(function(response){
                                if (response.data.result) {
                                    var info = response.data.records 
                                    $scope.customer.total = info.customers.withCredit
                                    $scope.customer.today = info.customers.withCreditToDay
                                    $scope.customer.notoday = info.customers.withCreditNoToDay
                                    $scope.revenueTotals = info.revenueTotals
                                    $scope.totalPendingReceivable = info.totalPendingReceivable
                                    $scope.totalReceivable = info.totalReceivable                           
                                }
                        })
                    } else {
                        $scope.createToast("danger", "<strong>Error: Debe de seleccionar un cobrador. </strong>");
                        $timeout(function () { $scope.closeAlert(0); }, 5000);
                    }
                    break
                case "credits" :
                    console.log("--->", "status", statusCredit, "cobrador", $scope.collector, "final", dateFinal, "inicio", dateInit, "plan", plan, "branch", branch)
                    reportService.credits(statusCredit, collector, dateInit, dateFinal, plan, branch)
                        .then(function(response){
                            console.log("--->", response.data.result)
                            if (response.data.result) {

                            }
                        })
                    break
                default:
                    break
            }
        }

        $scope.branchSelected = function(branch){
            if ($("#view").val() == "collectors") {
                loadCollectors(branch)
                loadPlanes(branch)    
            } else {
                if (branch == undefined) {
                    $scope.createToast("danger", "<strong>Error: Debe seleccionar alguna de las opciones de sucursales. </strong>");
                    $timeout(function () { $scope.closeAlert(0); }, 5000);
                } else { 
                    branchSelected = branch
                }
                
            }
        }

        $scope.generateReport = function() {
            console.log("--->","generateReport")
            typeView = $("#view").val()
            generateReport(typeView)
        }

         // toast function
        $scope.createToast = function (tipo, mensaje) {
            $scope.toasts.push({
                anim: "bouncyflip",
                type: tipo,
                msg: mensaje
            });
        }

        $scope.closeAlert = function (index) {
            $scope.toasts.splice(index, 1)
        }

    }])
}())