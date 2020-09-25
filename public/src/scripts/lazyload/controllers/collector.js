; (function () {
  "use strict";

  angular.module("app.collector", ["app.constants", 
                                  'app.service.collector', 
                                  'app.service.pdfs',
                                  'app.service.cierreruta'])

    .controller("CollectorController", ["$scope", 
                                        "$filter", 
                                        "$http", 
                                        "$modal",
                                        "$interval",
                                        'collectorService', 
                                        'pdfsService',
                                        'cierreRutaService', 
                                        'API_URL', 
                                        function ($scope, 
                                                  $filter, 
                                                  $http, 
                                                  $modal, 
                                                  $timeout,
                                                  collectorService, 
                                                  pdfsService,
                                                  cierreRutaService, 
                                                  API_URL) {

      // general vars
      $scope.loadBranches = [];
      $scope.datas = Array();
      $scope.sucursales = Array();
      $scope.currentPageStores = [];
      $scope.searchKeywords = ''
      $scope.filteredData = [];
      $scope.row = '';
      $scope.numPerPageOpts = [5, 10, 25, 50, 100];
      $scope.numPerPage = $scope.numPerPageOpts[1];
      $scope.currentPage = 1;
      $scope.positionModel = 'topRight';
      $scope.toasts = [];
      $scope.showCollectorTable = true;
      $scope.collectorSelected = '';
      $scope.totalCobrar = 0
      $scope.totalMinimoCobrar = 0
      $scope.totalCartera = 0
      $scope.totalPendientePago = 0
      $scope.showButtonRouteClosure = true
      $scope.routeClosure = {}
      var modal;
      var pivotStructure = []
      var collectorSelected = {}

      var dateToday =  $filter('date')(new Date(), 'yyyy-MM-dd')
      $("#fechapago").val(dateToday);
      loadBranches()
      userCollector()
      getServiceValidateRouteClosure()

      function userCollector() {
        if ($scope.usuario.tipo_usuarios_id == 4){
          $scope.showCollectorTable = false
          collectorSelected = $scope.usuario
          showCustomer($scope.usuario)
        } else {
          $scope.showCollectorTable = true
          loadData($("branch_id").val());          
        }
      }

      function loadBranches() {
        $scope.sucursales = [];
        $http.get(API_URL + 'sucursales', {})
          .then(function successCallback(response) {
            if (response.data.result) {
              if ($scope.usuario.tipo_usuarios_id == 1)
                $scope.sucursales = response.data.records;
              else {              
                response.data.records.forEach(function (item) {
                  if (item.id == $scope.usuario.sucursales_id) {
                    $scope.sucursales.push(item)
                  }
                })
              }
            }
          });
      }

      function loadData(branch_id) {
        var branch_selectd = branch_id != null ? branch_id : $scope.usuario.sucursales_id;
        $scope.datas = [];
        collectorService.index().then(function (response) {
          response.data.records.forEach(function (item) {
            if (item.sucursales_id == branch_selectd) {
              $scope.datas.push(item)
            }
          })
          pivotStructure = $scope.datas;
          $scope.search();
          $scope.select($scope.currentPage);
        });
      }

      function totalCollection(cobradorId, date){
        collectorService.totalColletion(cobradorId, date)
          .then(function successCallback(response){     
            $scope.collectionofday = response.data.records;       					          
          },
          function errorCallback(response) {
            $scope.collectionofday = 0;						
					});
      }

      function showCustomer(data){
        var date = $("#fechapago").val()

        collectorService.detail(data.id, date).then(function(response){          
          $scope.collectorSelected = data       
          $scope.showCollectorTable = false
          $scope.totalCobrar = response.data.records.total_cobrar
          $scope.totalMinimoCobrar = response.data.records.total_minimo

          response.data.records.registros.forEach(function (element) {
            if (element.estado == 1) {
              $scope.totalCartera = $scope.totalCartera + element.deudatotal
              $scope.totalPendientePago = $scope.totalPendientePago + element.saldo
            }
          });

          totalCollection(data.id, date)
          
          $scope.datas = []
          $scope.datas = response.data.records.registros
          $scope.searchKeywords = ''
          $scope.search()
          $scope.select($scope.currentPage)
        });
      }

      function getServiceValidateRouteClosure(collectorId){  
        
        var date = $("#fechapago").val()      
        
        if ($scope.usuario.tipo_usuarios_id != 1) {
          cierreRutaService.validateCierreRuta(collectorId, date)
            .then(function successCallback(response){     
              $scope.showButtonRouteClosure = response.data.records                  			          
            },
            function errorCallback(response) {
              $scope.showButtonRouteClosure = false					
            });
        } else {
          $scope.showButtonRouteClosure = false						
        }
      }

      function printResume(routeClosureId){
        if($("#fechapago").val() != ""){
          pdfsService.resumenPaymentCollector(collectorSelected.id, $("#fechapago").val(),routeClosureId)
        }
      }

      $scope.changeDataBranch = function(branch_id){
        loadData(branch_id);
      }

      $scope.findCustomers = function(){
        if($("#fechapago").val() != ""){                    
          $scope.totalCartera = 0
          $scope.totalPendientePago = 0
          showCustomer(collectorSelected)
          getServiceValidateRouteClosure(collectorSelected.id)
        }
      }
      
      $scope.showCustomerView = function(data){      
        showCustomer(data)
        getServiceValidateRouteClosure(data.id)
        collectorSelected = data
        pivotStructure = $scope.datas
      }

      $scope.closeCustomerView = function(){
        $scope.showCollectorTable = true
        $scope.datas = []
        $scope.datas = pivotStructure
        $scope.searchKeywords = ''
        $scope.search()
        $scope.select($scope.currentPage)
        $scope.totalCobrar = 0
        $scope.totalMinimoCobrar = 0
        $scope.totalCartera = 0
        $scope.totalPendientePago = 0
      }

      $scope.confirmRouteClosure = function(dataRouteClosure){
        var newRouteClosure = {}
        newRouteClosure.branch_id =  $scope.usuario.sucursales_id
        newRouteClosure.collector_id = dataRouteClosure.collectorId
        newRouteClosure.total_amount = dataRouteClosure.closingAmount
        newRouteClosure.date = $("#fechapago").val()
        cierreRutaService.saveClosingRoute(newRouteClosure)
          .then(function successCallback(response){     
            printResume(response.data.records.id)            
            modal.close();
            $scope.showButtonRouteClosure = true
            $scope.createToast("success", "<strong>Éxito: </strong>"+response.data.message);
            $timeout( function(){ $scope.closeAlert(0); }, 3000);		          
          },
          function errorCallback(response) {
            $scope.showButtonRouteClosure = false
            $scope.createToast("danger", "<strong>Error: </strong>"+response.data.message);
							$timeout( function(){ $scope.closeAlert(0); }, 5000);	
					});
      }

      $scope.printResume = function(){
        printResume(0)
      }

      // datatable collector functions
      $scope.select = function (page) {
        var start = (page - 1) * $scope.numPerPage,
          end = start + $scope.numPerPage;

        $scope.currentPageStores = $scope.filteredData.slice(start, end);
      }

      $scope.onFilterChange = function () {
        $scope.select(1);
        $scope.currentPage = 1;
        $scope.row = '';
      }

      $scope.onNumPerPageChange = function () {
        $scope.select(1);
        $scope.currentPage = 1;
      }

      $scope.onOrderChange = function () {
        $scope.select(1);
        $scope.currentPage = 1;
      }

      $scope.search = function () {
        $scope.filteredData = $filter("filter")($scope.datas, $scope.searchKeywords);
        $scope.onFilterChange();
      }

      $scope.order = function (rowName) {
        if ($scope.row == rowName)
          return;
        $scope.row = rowName;
        $scope.filteredData = $filter('orderBy')($scope.datas, rowName);
        $scope.onOrderChange();
      }

      // modals function
      $scope.modalConfirm = function(dataCollector, amount) {			
        $scope.accion = 'confirmar';
        $scope.routeClosure.collectorName = dataCollector.nombre;
        $scope.routeClosure.collectorId = dataCollector.id;
        $scope.routeClosure.closingAmount = amount;        

        modal = $modal.open({
          templateUrl: "views/collectors/modal.html",
          scope: $scope,
          size: "md",
          resolve: function() {},
          windowClass: "default"
        });
      }

      $scope.modalClose = function () {
        modal.close();
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
        $scope.toasts.splice(index, 1);
      }
    }])
}())