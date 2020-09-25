(function () {
  "use strict"

  angular.module("app.montos", ["app.constants"])

    .controller("MontosController", ["$scope", "$filter", "$http", "$modal", "$interval", "API_URL", function ($scope, $filter, $http, $modal, $timeout, API_URL) {

      // Variables generales
      $scope.sucursales = []
      $scope.datas = []
      $scope.currentPageStores = []
      $scope.searchKeywords = ""
      $scope.filteredData = []
      $scope.row = ""
      $scope.numPerPageOpts = [5, 10, 25, 50, 100]
      $scope.numPerPage = $scope.numPerPageOpts[1]
      $scope.currentPage = 1
      $scope.positionModel = "topRight"
      $scope.toasts = []
      var modal


      $scope.cargarSucursales = function () {
        $scope.sucursales = [];
        $http.get(API_URL + 'sucursales', {})
        .then(function (response) {
          if (response.data.result) {
            if ($scope.usuario.tipo_usuarios_id == 1)
              $scope.sucursales = response.data.records
            else {
              response.data.records.forEach(function (item) {
                if (item.id == $scope.usuario.sucursales_id) {
                  $scope.sucursales.push(item)
                }
              })
            }
          }
        })
      }

      $scope.LlenarTabla = function (branch_id) {
     
        var branch_selectd = branch_id != null ? branch_id : $scope.usuario.sucursales_id;
        $scope.datas = [];

        $http({
          method: 'GET',
          url: API_URL + 'montosprestamo'
        })
        .then(function successCallback(response) {
          
          response.data.records.forEach(function (item) {
            if (item.sucursales_id == branch_selectd) {
              $scope.datas.push(item)
            }
          })
          
          $scope.search()
          $scope.select($scope.currentPage)
        },
        function errorCallback(response) {
          console.log(response.data.message)
        })
      }

      $scope.changeDataBranch = function (branch_id) {
        $scope.LlenarTabla(branch_id)
      }

      // FUNCIONES DE DATATABLE
      $scope.select = function (page) {
        var start = (page - 1) * $scope.numPerPage,
          end = start + $scope.numPerPage

        $scope.currentPageStores = $scope.filteredData.slice(start, end)
      }

      $scope.onFilterChange = function () {
        $scope.select(1)
        $scope.currentPage = 1
        $scope.row = ''
      }

      $scope.onNumPerPageChange = function () {
        $scope.select(1)
        $scope.currentPage = 1
      }

      $scope.onOrderChange = function () {
        $scope.select(1)
        $scope.currentPage = 1
      }

      $scope.search = function () {
        $scope.filteredData = $filter("filter")($scope.datas, $scope.searchKeywords)
        $scope.onFilterChange()
      }

      $scope.order = function (rowName) {
        if ($scope.row == rowName)
          return
        $scope.row = rowName
        $scope.filteredData = $filter('orderBy')($scope.datas, rowName)
        $scope.onOrderChange()
      }

      $scope.LlenarTabla($("branch_id").val())
      $scope.cargarSucursales()

      // Función para Toast
      $scope.createToast = function (tipo, mensaje) {
        $scope.toasts.push({
          anim: "bouncyflip",
          type: tipo,
          msg: mensaje
        })
      }

      $scope.closeAlert = function (index) {
        $scope.toasts.splice(index, 1)
      }

      $scope.saveData = function (monto) {

        if ($scope.accion == 'crear') {
          $http({
            method: 'POST',
            url: API_URL + 'montosprestamo',
            data: {
              monto: monto.monto,
              idsucursal: monto.sucursales_id
            }
          })
            .then(function successCallback(response) {
              if (response.data.result) {
                $scope.LlenarTabla($("branch_id").val())
                modal.close()
                $scope.createToast("success", "<strong>Éxito: </strong>" + response.data.message)
                $timeout(function () { $scope.closeAlert(0) }, 5000)
              }
              else {
                $scope.createToast("danger", "<strong>Error: </strong>" + response.data.message)
                $timeout(function () { $scope.closeAlert(0) }, 5000)
              }
            },
              function errorCallback(response) {
                console.log(response.data.message)
              })
        }
        else if ($scope.accion == 'editar') {
          $http({
            method: 'PUT',
            url: API_URL + 'montosprestamo/' + monto.id,
            data: {
              monto: monto.monto,
              idsucursal: monto.sucursales_id
            }
          })
            .then(function successCallback(response) {
              if (response.data.result) {
                $scope.LlenarTabla($("branch_id").val())
                modal.close()
                $scope.createToast("success", "<strong>Éxito: </strong>" + response.data.message)
                $timeout(function () { $scope.closeAlert(0) }, 3000)
              }
              else {
                $scope.createToast("danger", "<strong>Error: </strong>" + response.data.message)
                $timeout(function () { $scope.closeAlert(0) }, 5000)
              }
            },
              function errorCallback(response) {
                console.log(response.data.message)
              })
        }
        else if ($scope.accion == 'eliminar') {
        
          $http({
            method: 'DELETE',
            url: API_URL + 'montosprestamo/' + monto.id,
          })
            .then(function successCallback(response) {
              if (response.data.result) {
                $scope.LlenarTabla($("branch_id").val())
                modal.close()
                $scope.createToast("success", "<strong>Éxito: </strong>" + response.data.message)
                $timeout(function () { $scope.closeAlert(0) }, 3000)
              }
              else {
                $scope.createToast("danger", "<strong>Error: </strong>" + response.data.message)
                $timeout(function () { $scope.closeAlert(0) }, 5000)
              }
            },
              function errorCallback(response) {
                console.log(response.data.message)
              })
        }
      }

      // Funciones para Modales
      $scope.modalCreateOpen = function () {
        $scope.monto = {}
        $scope.accion = 'crear'

        modal = $modal.open({
          templateUrl: "views/montos/modal.html",
          scope: $scope,
          size: "md",
          resolve: function () { },
          windowClass: "default"
        })
      }

      $scope.modalEditOpen = function (data) {
        $scope.accion = 'editar'
        $scope.monto = data

        modal = $modal.open({
          templateUrl: "views/montos/modal.html",
          scope: $scope,
          size: "md",
          resolve: function () { },
          windowClass: "default"
        })
      }

      $scope.modalDeleteOpen = function (data) {
        $scope.accion = 'eliminar'

        modal = $modal.open({
          templateUrl: "views/montos/modal.html",
          scope: $scope,
          size: "md",
          resolve: function () { },
          windowClass: "default"
        })
      }

      $scope.modalClose = function () {
        modal.close()
      }
    }])
}())