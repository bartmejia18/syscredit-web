(function () {
    "use strict";

    angular
        .module("app.usuarios", [
            "app.service.clientunlock"
        ])

        .controller("UnlocksController", [
            "$scope",
            "$filter",
            "$modal",
            "clientUnlockService",
            function (
                $scope, 
                $filter,  
                $modal,
                clientUnlockService
            ) {
                
                $scope.sucursales = Array()
                $scope.datas = []
                $scope.aprobacion = {}

                $scope.currentPageStores = []
                $scope.searchKeywords = ""
                $scope.filteredData = []
                $scope.row = ""
                $scope.numPerPageOpts = [5, 10, 25, 50, 100]
                $scope.numPerPage = $scope.numPerPageOpts[1]
                $scope.currentPage = 1
                
                $scope.toasts = []

                var modal

                loadData();

                function loadData() {
                    clientUnlockService
                        .listByBranch($scope.usuario.sucursales_id)
                        .then(
                            function succesCallback(response) {
                                if (response.data.result == true) {
                                    $scope.datas = response.data.records;
                                    $scope.search();
                                    $scope.select($scope.currentPage);
                                }
                            },
                            function errorCallback(response) {
                                $scope.createToast(
                                    "danger",
                                    "<strong>Error: </strong>" +
                                        response.data.message
                                );
                                $timeout(function () {
                                    $scope.closeAlert(0);
                                }, 5000);
                            }    
                        );
                };

                // #region Modal

                // Funciones para Modales
                $scope.modalAprobacionesOpen = function(aprobacion) {
                    $scope.aprobacion = aprobacion
                    modal = $modal.open({
                    templateUrl: "views/desbloqueados/modal.html",
                    scope: $scope,
                    size: "md",
                    resolve: function () { },
                    windowClass: "default"
                    })
                }

                $scope.modalClose = function () {
                    modal.close()
                  }
                // #endregion

                // #region Function Table
                $scope.select = function (page) {
                    var start = (page - 1) * $scope.numPerPage,
                        end = start + $scope.numPerPage;

                    $scope.currentPageStores = $scope.filteredData.slice(
                        start,
                        end
                    );
                };

                $scope.onFilterChange = function () {
                    $scope.select(1);
                    $scope.currentPage = 1;
                    $scope.row = "";
                };

                $scope.onNumPerPageChange = function () {
                    $scope.select(1);
                    $scope.currentPage = 1;
                };

                $scope.onOrderChange = function () {
                    $scope.select(1);
                    $scope.currentPage = 1;
                };

                $scope.search = function () {
                    $scope.filteredData = $filter("filter")(
                        $scope.datas,
                        $scope.searchKeywords
                    );
                    $scope.onFilterChange();
                };

                $scope.order = function (rowName) {
                    if ($scope.row == rowName) return;
                    $scope.row = rowName;
                    $scope.filteredData = $filter("orderBy")(
                        $scope.datas,
                        rowName
                    );
                    $scope.onOrderChange();
                };
                // #endregion

                // #region Toast
                $scope.createToast = function(tipo, mensaje) {
                    $scope.toasts.push({
                        anim: "bouncyflip",
                        type: tipo,
                        msg: mensaje,
                    });
                };

                $scope.closeAlert = function (index) {
                    $scope.toasts.splice(index, 1);
                };
                // #endregion

            },
        ]);
})();
