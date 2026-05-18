(function () {
    "use strict";

    angular
        .module("app.sucursales", ["app.constants", "app.service.company"])

        .controller("SucursalesController", [
            "$scope",
            "$filter",
            "$http",
            "$modal",
            "$timeout",
			"companyService",
            "API_URL",
            function ($scope, $filter, $http, $modal, $timeout, companyService, API_URL) {
                // Variables generales
                $scope.datas = [];
				$scope.companies = [];
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

                // =========================
                // DEPARTAMENTOS / MUNICIPIOS
                // =========================
                $scope.departamentos = [];
                $scope.municipiosFiltrados = [];

                $scope.cargarUbicaciones = function () {
                    $http.get("../departamentos_municipios.json").then(
                        function (response) {
                            $scope.departamentos = response.data || [];
                        },
                        function (error) {
                            console.error(
                                "Error cargando departamentos y municipios",
                                error
                            );
                        }
                    )
                }

                $scope.onDepartamentoChange = function () {
                    $scope.municipiosFiltrados = [];
                    $scope.sucursal.municipio = "";

                    if (!$scope.sucursal || !$scope.sucursal.departamento) {
                        return;
                    }

                    var departamentoSeleccionado = $scope.departamentos.find(
                        function (dep) {
                            return dep.nombre === $scope.sucursal.departamento;
                        }
                    );

                    if (departamentoSeleccionado) {
                        $scope.municipiosFiltrados =
                            departamentoSeleccionado.municipios;
                    }
                };

                $scope.cargarMunicipiosEdicion = function () {
                    $scope.municipiosFiltrados = [];

                    if (!$scope.sucursal || !$scope.sucursal.departamento) {
                        return;
                    }

                    var departamentoSeleccionado = $scope.departamentos.find(
                        function (dep) {
                            return dep.nombre === $scope.sucursal.departamento;
                        }
                    );

                    if (departamentoSeleccionado) {
                        $scope.municipiosFiltrados =
                            departamentoSeleccionado.municipios;
                    }
                };

				$scope.loadCompanies = function() {
					$scope.companies = [];
					companyService.companies().then(
						function successCallback(response) {
							if (response.data.result) {
								$scope.companies = response.data.records
							}
						}
					)
				}

                $scope.LlenarTabla = function () {
                    $scope.datas = [];
                    $http({
                        method: "GET",
                        url: API_URL + "sucursales"
                    }).then(
                        function successCallback(response) {
                            $scope.datas = response.data.records;
                            $scope.search();
                            $scope.select($scope.currentPage);
                        },
                        function errorCallback(response) {
                            console.log(response.data.message);
                        }
                    );
                };

				$scope.LlenarTabla()
                $scope.cargarUbicaciones()
				$scope.loadCompanies()

                // FUNCIONES DE DATATABLE
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

                // Función para Toast
                $scope.createToast = function (tipo, mensaje) {
                    $scope.toasts.push({
                        anim: "bouncyflip",
                        type: tipo,
                        msg: mensaje
                    });
                };

                $scope.closeAlert = function (index) {
                    $scope.toasts.splice(index, 1);
                };

                $scope.saveData = function (sucursal) {
                    if ($scope.accion == "crear") {
                        $http({
                            method: "POST",
                            url: API_URL + "sucursales",
                            data: {
                                descripcion: sucursal.descripcion,
                                direccion: sucursal.direccion,
                                telefono: sucursal.telefono,
                                departamento: sucursal.departamento,
                                municipio: sucursal.municipio,
								empresa_id: sucursal.empresa_id
                            },
                        }).then(
                            function successCallback(response) {
                                if (response.data.result) {
                                    $scope.LlenarTabla();
                                    modal.close();
                                    $scope.createToast(
                                        "success",
                                        "<strong>Éxito: </strong>" +
                                            response.data.message
                                    );
                                    $timeout(function () {
                                        $scope.closeAlert(0);
                                    }, 5000);
                                } else {
                                    $scope.createToast(
                                        "danger",
                                        "<strong>Error: </strong>" +
                                            response.data.message
                                    );
                                    $timeout(function () {
                                        $scope.closeAlert(0);
                                    }, 5000);
                                }
                            },
                            function errorCallback(response) {
                                console.log(response.data.message);
                            }
                        );
                    } else if ($scope.accion == "editar") {
                        $http({
                            method: "PUT",
                            url: API_URL + "sucursales/" + sucursal.id,
                            data: {
                                descripcion: sucursal.descripcion,
                                direccion: sucursal.direccion,
                                telefono: sucursal.telefono,
                                departamento: sucursal.departamento,
                                municipio: sucursal.municipio,
								empresa_id: sucursal.empresa_id
                            },
                        }).then(
                            function successCallback(response) {
                                if (response.data.result) {
                                    $scope.LlenarTabla();
                                    modal.close();
                                    $scope.createToast(
                                        "success",
                                        "<strong>Éxito: </strong>" +
                                            response.data.message
                                    );
                                    $timeout(function () {
                                        $scope.closeAlert(0);
                                    }, 3000);
                                } else {
                                    $scope.createToast(
                                        "danger",
                                        "<strong>Error: </strong>" +
                                            response.data.message
                                    );
                                    $timeout(function () {
                                        $scope.closeAlert(0);
                                    }, 5000);
                                }
                            },
                            function errorCallback(response) {
                                console.log(response.data.message);
                            }
                        );
                    } else if ($scope.accion == "eliminar") {
                        $http({
                            method: "DELETE",
                            url: API_URL + "sucursales/" + sucursal.id,
                        }).then(
                            function successCallback(response) {
                                if (response.data.result) {
                                    $scope.LlenarTabla();
                                    modal.close();
                                    $scope.createToast(
                                        "success",
                                        "<strong>Éxito: </strong>" +
                                            response.data.message
                                    );
                                    $timeout(function () {
                                        $scope.closeAlert(0);
                                    }, 3000);
                                } else {
                                    $scope.createToast(
                                        "danger",
                                        "<strong>Error: </strong>" +
                                            response.data.message
                                    );
                                    $timeout(function () {
                                        $scope.closeAlert(0);
                                    }, 5000);
                                }
                            },
                            function errorCallback(response) {
                                console.log(response.data.message);
                            }
                        );
                    }
                };

                // Funciones para Modales
                $scope.modalCreateOpen = function () {
                    $scope.sucursal = {
                        descripcion: "",
                        telefono: "",
                        direccion: "",
                        departamento: "",
                        municipio: "",
						empresa: ""
                    };
                    $scope.municipiosFiltrados = []
                    $scope.accion = "crear"

                    modal = $modal.open({
                        templateUrl: "views/sucursales/modal.html",
                        scope: $scope,
                        size: "md",
                        resolve: function () {},
                        windowClass: "default"
                    })
                }

                $scope.modalEditOpen = function (data) {
                    $scope.accion = "editar"

                    // Copia para no modificar la fila directamente antes de guardar
                    $scope.sucursal = angular.copy(data)

					console.log($scope.sucursal)

                    $scope.cargarMunicipiosEdicion()

                    modal = $modal.open({
                        templateUrl: "views/sucursales/modal.html",
                        scope: $scope,
                        size: "md",
                        resolve: function () {},
                        windowClass: "default"
                    })
                }

                $scope.modalDeleteOpen = function (data) {
                    $scope.accion = "eliminar"
                    $scope.sucursal = data

                    modal = $modal.open({
                        templateUrl: "views/sucursales/modal.html",
                        scope: $scope,
                        size: "md",
                        resolve: function () {},
                        windowClass: "default"
                    })
                };

                $scope.modalClose = function () {
                    modal.close();
                }
            },
        ])
})();
