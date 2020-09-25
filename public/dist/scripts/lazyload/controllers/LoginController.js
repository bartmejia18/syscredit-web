; (function () {
  "use strict";
  angular.module("login", ["LocalStorageModule",
    "ngRoute",
    "ngAnimate",
    "ngSanitize",
    "ngAria",
    "ngMaterial",
    "oc.lazyLoad",
    "ui.bootstrap",
    "angular-loading-bar",
    "FBAngular",
    "app.ctrls",
    "app.directives",
    "app.ui.ctrls",
    "app.ui.directives",
    "app.form.ctrls",
    "app.table.ctrls",
    "app.email.ctrls",
    "app.constants"])

    .controller("LoginController", ["$scope", "$http", "$window", "localStorageService", "$interval", "API_URL", function ($scope, $http, $window, localStorageService, $timeout, API_URL) {
      $scope.positionModel = "topRight";
      $scope.toasts = [];

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

      localStorageService.cookie.clearAll();
      $scope.loginUsuario = function (item) {
        $scope.item = item;
        $http({
          method: 'POST',
          url: API_URL + 'login',
          data: {
            user: item.user,
            password: item.password
          }
        }).then(function succesCallback(response) {
          if (response.data.result) {
            localStorageService.cookie.set('usuario', response.data.records[0]);
            $window.location.href = "./#/dashboard";
          } else {
            $scope.createToast("danger", "<strong>Error: </strong>" + response.data.message);
            $timeout(function () { $scope.closeAlert(0); }, 5000);
          }
        }, function errorCallback(response) {
          console.log(response)
          // $log.error( response );
        })
      }
    }])
}())