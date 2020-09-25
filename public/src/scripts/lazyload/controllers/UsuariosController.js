(function () {
  "use strict"

  angular.module("app.usuarios", ["app.constants"])

    .controller("UsuariosController", ["$scope", "$filter", "$http", "$modal", "$interval", "API_URL", function ($scope, $filter, $http, $modal, $timeout, API_URL) {

      //Variables generales
      $scope.tipousuarios = []
      $scope.sucursales = Array()
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
      $scope.passwordRequired = true
      var modal

      $scope.cargarTipoUsuarios = function () {
        $http.get(API_URL + 'tipousuarios', {}).then(function (response) {
          if (response.data.result)
            $scope.tipousuarios = response.data.records
        })
      }

      $scope.cargarSucursales = function () {
        
        $scope.sucursales = [];

        $http.get(API_URL + 'sucursales', {}).then(function (response) {
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

      $scope.LlenarTabla = function () {
        $scope.datas = [];
        $http({
          method: 'GET',
          url: API_URL + 'usuarios'
        })
          .then(function successCallback(response) {
            if ($scope.usuario.tipo_usuarios_id == 1)
              $scope.datas = response.data.records
            else {
              response.data.records.forEach(function (item) {
                if (item.tipo_usuarios_id != 1) {
                  $scope.datas.push(item)
                }
              })
            }

            $scope.search()
            $scope.select($scope.currentPage)
          },
            function errorCallback(response) {
              console.log(response.data.message)
            })
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

      $scope.LlenarTabla()
      $scope.cargarTipoUsuarios()
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

      $scope.saveData = function (usuario) {
        if ($scope.accion == 'crear') {

          $http({
            method: 'POST',
            url: API_URL + 'usuarios',
            data: usuario
          })
            .then(function successCallback(response) {
              if (response.data.result) {
                $scope.LlenarTabla()
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
            url: API_URL + 'usuarios/' + usuario.id,
            data: {
              user: usuario.user,
              password: usuario.password,
              password2: usuario.password2,
              nombre: usuario.nombre,
              idtipousuario: usuario.tipo_usuarios_id,
              idsucursal: usuario.sucursales_id,
              estado: $scope.usuario.estado == true ? 1 : 0
            }
          })
            .then(function successCallback(response) {
              if (response.data.result) {
                $scope.LlenarTabla()
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
            url: API_URL + 'usuarios/' + usuario.id,
          })
            .then(function successCallback(response) {
              if (response.data.result) {
                $scope.LlenarTabla()
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
        $scope.usuario = {}
        $scope.accion = 'crear'
        $scope.passwordRequired = true

        modal = $modal.open({
          templateUrl: "views/usuarios/modal.html",
          scope: $scope,
          size: "md",
          resolve: function () { },
          windowClass: "default"
        })
      }

      $scope.modalEditOpen = function (data) {
        $scope.accion = 'editar'
        $scope.usuario = data
        $scope.passwordRequired = false

        data.estado == 1 ? $scope.usuario.estado = true : $scope.usuario.estado = false

        modal = $modal.open({
          templateUrl: "views/usuarios/modal.html",
          scope: $scope,
          size: "md",
          resolve: function () { },
          windowClass: "default"
        })
      }

      $scope.modalDeleteOpen = function (data) {
        $scope.accion = 'eliminar'

        $scope.usuario = data
        modal = $modal.open({
          templateUrl: "views/usuarios/modal.html",
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