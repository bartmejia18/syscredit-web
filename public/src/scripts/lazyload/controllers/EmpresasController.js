(function () {
    "use strict";

    angular
        .module("app.empresas", ["app.constants", "app.service.company"])

        .controller("EmpresasController", [
            "$scope",
            "$filter",
            "$http",
            "$modal",
            "$timeout",
            "companyService",
            "API_URL",
            function (
                $scope,
                $filter,
                $http,
                $modal,
                $timeout,
                companyService,
                API_URL,
            ) {
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

                $scope.LlenarTabla = function () {
                    $scope.datas = [];
                    companyService
                        .companies()
                        .then(function successCallback(response) {
                            $scope.datas = response.data.records;
                            $scope.search();
                            $scope.select($scope.currentPage);
                        });
                };

                $scope.LlenarTabla();

                // FUNCIONES DE DATATABLE
                $scope.select = function (page) {
                    var start = (page - 1) * $scope.numPerPage,
                        end = start + $scope.numPerPage;

                    $scope.currentPageStores = $scope.filteredData.slice(
                        start,
                        end,
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
                        $scope.searchKeywords,
                    );
                    $scope.onFilterChange();
                };

                $scope.order = function (rowName) {
                    if ($scope.row == rowName) return;
                    $scope.row = rowName;
                    $scope.filteredData = $filter("orderBy")(
                        $scope.datas,
                        rowName,
                    );
                    $scope.onOrderChange();
                };

                // Función para Toast
                $scope.createToast = function (tipo, mensaje) {
                    $scope.toasts.push({
                        anim: "bouncyflip",
                        type: tipo,
                        msg: mensaje,
                    });
                };

                $scope.closeAlert = function (index) {
                    $scope.toasts.splice(index, 1);
                };

                $scope.saveData = function (empresa) {
                    if ($scope.accion == "crear") {
                        companyService.create(empresa)
						.then(function successCallback(response) {
							if (response.data.result) {
								$scope.LlenarTabla();
								modal.close();
								$scope.createToast(
									"success",
									"<strong>Éxito: </strong>" +
										response.data.message,
								);
								$timeout(function () {
									$scope.closeAlert(0);
								}, 5000);
							} else {
								$scope.createToast(
									"danger",
									"<strong>Error: </strong>" +
										response.data.message,
								);
								$timeout(function () {
									$scope.closeAlert(0);
								}, 5000);
							}
                        },
						function errorCallback(response) {
							console.log(response.data.message);
                        });
                    } else if ($scope.accion == "editar") {
						companyService.update(empresa.id, empresa)
						.then(function successCallback(response) {
							if (response.data.result) {
								$scope.LlenarTabla();
								modal.close();
								$scope.createToast(
									"success",
									"<strong>Éxito: </strong>" +
										response.data.message,
								);
								$timeout(function () {
									$scope.closeAlert(0);
								}, 3000);
							} else {
								$scope.createToast(
									"danger",
									"<strong>Error: </strong>" +
										response.data.message,
								);
								$timeout(function () {
									$scope.closeAlert(0);
								}, 5000);
							}
						},
						function errorCallback(response) {
							console.log(response.data.message);
						});
                    } else if ($scope.accion == "eliminar") {
                        var newStatus = {}
                        newStatus.status = 0
                        companyService.delete(empresa.id, newStatus)
                        .then(function successCallback(response) {
                            if (response.data.result) {
                                $scope.LlenarTabla();
                                modal.close();
                                $scope.createToast(
                                    "success",
                                    "<strong>Éxito: </strong>" +
                                        response.data.message,
                                );
                                $timeout(function () {
                                    $scope.closeAlert(0);
                                }, 3000);
                            } else {
                                $scope.createToast(
                                    "danger",
                                    "<strong>Error: </strong>" +
                                        response.data.message,
                                );
                                $timeout(function () {
                                    $scope.closeAlert(0);
                                }, 5000);
                            }
                        },
                        function errorCallback(response) {
                            console.log(response.data.message);
                        });
                    }
                };

                // Funciones para Modales
                $scope.modalCreateOpen = function () {
                    $scope.empresa = {}
                    $scope.accion = "crear";

                    modal = $modal.open({
                        templateUrl: "views/empresas/modal.html",
                        scope: $scope,
                        size: "md",
                        resolve: function () {},
                        windowClass: "default",
                    });
                };

                $scope.modalEditOpen = function (data) {
                    $scope.accion = "editar";
                    $scope.empresa = angular.copy(data);

                    modal = $modal.open({
                        templateUrl: "views/empresas/modal.html",
                        scope: $scope,
                        size: "md",
                        resolve: function () {},
                        windowClass: "default",
                    });
                };

                $scope.modalDeleteOpen = function (data) {
                    $scope.accion = "eliminar";
                    $scope.empresa = angular.copy(data);
                    
                    modal = $modal.open({
                        templateUrl: "views/empresas/modal.html",
                        scope: $scope,
                        size: "md",
                        resolve: function () {},
                        windowClass: "default",
                    });
                };

                $scope.modalClose = function () {
                    modal.close();
                };
            },
        ]);
})();
