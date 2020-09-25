; (function () {
  "use strict";

  angular.module("app.abonos", ["app.constants", 'app.service.customers'])

    .controller("AbonosController", ["$scope", "$routeParams", "$filter", "$http", "$modal", "$interval", 'customersService', "API_URL", function ($scope, $routeParams, $filter, $http, $modal, $timeout, customersService, API_URL) {

      $scope.positionModel = "topRight";
      $scope.detalle_cliente = {};
      $scope.search_client = {};
      $scope.credito = {};
      $scope.toasts = [];
      $scope.resumen = {};
      $scope.dailyFee = 0;
      var modal;
      var nameCustomer = "";
      var lastNameCustomer = "";
      var data = {}
      var modalOpen = false

      $("#customerName").focus();
      loadCustomers()

      function loadCustomers() {
        customersService.customers().then(function (response) {
          $scope.customers = response.data.records
        });
      }

      $("#customerName").change('input', function () {
        var opt = $('option[value="' + $(this).val() + '"]');
        nameCustomer = opt.length ? opt.attr('data-name') : "";
        lastNameCustomer = opt.length ? opt.attr('data-lastname') : "";

      })

      $scope.validarCliente = function () {
        if (nameCustomer != "" && lastNameCustomer != "") {
          $http({
            method: 'GET',
            url: API_URL + 'creditocliente',
            params: { name: nameCustomer, lastname: lastNameCustomer }
          }).then(function successCallback(response) {
            if (response.data.result) {
              if (response.data.records.creditos.length > 1) {
                $scope.modalcreditos(response.data.records)
              } else if(response.data.records.creditos.length == 1){   
                var nameCustomer = response.data.records.nombre + " " + response.data.records.apellido           
                $scope.showCredit(response.data.records.creditos[0], nameCustomer)
              }
             
              $scope.createToast("success", "<strong>Éxito: </strong>" + response.data.message);
              $timeout(function () { $scope.closeAlert(0); }, 5000);
            } else {
              $scope.createToast("danger", "<strong>Error: </strong>" + response.data.message);
              $timeout(function () { $scope.closeAlert(0); }, 5000);
            }
          }, function errorCallback(response) {

          });
        } else {
          $scope.createToast("danger", "<strong>Error: </strong> El nombre del cliente es incorrecto");
          $timeout(function () { $scope.closeAlert(0); }, 5000);
        }
      };

      $scope.showCredit = function(credit, nameCustomer){
        
        if(modalOpen)
          modal.close()

        $('.row-detalle').removeClass('hidden');
        $scope.detalle_cliente.credit_id = credit.id
        $scope.detalle_cliente.nombre = nameCustomer
        $scope.detalle_cliente.cuota_diaria = "Q. " + parseFloat(credit.cuota_diaria).toFixed(2)
        $scope.detalle_cliente.cobrador = credit.usuariocobrador.nombre
        $scope.detalle_cliente.plan_prestamo = credit.planes.descripcion
        $scope.detalle_cliente.monto_total = "Q. " + parseFloat(credit.deudatotal).toFixed(2)
        $scope.credito.total = "Q. " + parseFloat(credit.saldo).toFixed(2)
        $scope.credito.saldo = "Q. " + parseFloat(credit.total_cancelado).toFixed(2)
        $scope.credito.saldo_abonado = "Q. " + parseFloat(credit.saldo_abonado).toFixed(2) 
        $scope.credito.cuotas_pagados = credit.cuotas_pagados             
        $scope.dailyFee = credit.cuota_diaria
      }

      if ($routeParams) {  
        if($routeParams.name!=null && $routeParams.lastname != null){      
          $scope.search_client.nameCustomer = $routeParams.name + " " + $routeParams.lastname
          nameCustomer = $routeParams.name
          lastNameCustomer = $routeParams.lastname
          $scope.validarCliente();
        }
      }

      $scope.createToast = function (tipo, mensaje) {
        $scope.toasts.push({
          anim: "bouncyflip",
          type: tipo,
          msg: mensaje
        });
      }

      $scope.closeAlert = function (index) {
        $scope.toasts.splice(index, 1);
      };

      $scope.modalcuotas = function (cantidadAbonada) {
        if (cantidadAbonada != '' && parseFloat(cantidadAbonada) > 0) {
          $scope.resumen.cantidadabonada = $scope.cantidad_ingresada;
          $scope.resumen.cantidadcuotas = parseInt($scope.cantidad_ingresada / $scope.dailyFee);
          $scope.resumen.abonocapital = parseFloat($scope.cantidad_ingresada - ($scope.resumen.cantidadcuotas * $scope.dailyFee)).toFixed(2);        
          modal = $modal.open({
            templateUrl: "views/abonos/modal.html",
            scope: $scope,
            size: "md",
            resolve: function () { },
            windowClass: "default"
          });
        } else {
          $scope.createToast("danger", "<strong>Error: </strong>" + 'Debe ingresar una cantidad válida');
        }
      }

      $scope.modalcreditos = function (infoCredit){
        $scope.name_customer = infoCredit.nombre + ' ' + infoCredit.apellido;
        $scope.credits = infoCredit.creditos
        
        modal = $modal.open({
          templateUrl: "views/abonos/modalCredito.html",
          scope: $scope,
          size: "lg",
          resolve: function () {},
          windowClass: "default"
        });
        modalOpen = true
      }

      $scope.modalClose = function () {
        modal.close();
      }

      $scope.registrarAbono = function (cantidadAbonada) {
        $('.btn-set-payment').prop('disabled', true)
        if (cantidadAbonada != '' && parseFloat(cantidadAbonada) > 0) {
          var datos = {
            idcredito: $scope.detalle_cliente.credit_id,
            abono: cantidadAbonada
          };
          $http({
            method: 'POST',
            url: API_URL + 'payments',
            data: datos
          }).then(function successCallback(response) {
            if (response.data.result) {
              modal.close();
              $scope.createToast("success", "<strong>Éxito: </strong>" + response.data.message);
              location.reload();
            } else {
              modal.close();
              $scope.createToast("danger", "<strong>Error: </strong>" + response.data.message);
              $timeout(function () { $scope.closeAlert(0); }, 5000);
            }
          }, function errorCallback(response) {
            console.log(response.data.message);
          });
        } else {
          $scope.createToast("danger", "<strong>Error: </strong>" + 'Debe ingresar una cantidad válida');
        }
      }

      $scope.cancel = function(){
        modal.close()
      }
    }])
}())