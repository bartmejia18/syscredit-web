(function () {
    "use strict";

    angular
        .module("app.detallecliente", [
            "app.constants",
            "app.service.deletecredit",
            "app.service.passwordaccess",
            "app.service.historypayment",
            "app.service.credits"
        ])

        .controller("DetalleClienteController", [
            "$scope",
            "$routeParams",
            "$filter",
            "$http",
            "$modal",
            "deletCreditService",
            "passwordAccessService",
            "historyPaymentService",
            "creditsService",
            "API_URL",
            function (
                $scope,
                $routeParams,
                $filter,
                $http,
                $modal,
                deletCreditService,
                passwordAccessService,
                historyPaymentService,
                creditsService,
                API_URL
            ) {
                var customer = {};
                var modal;

                $scope.historyCredit = [];
                $scope.listCredito = [];
                $scope.listTable = [];
                $scope.listUnlocks = [];
                $scope.itemCredit = "";
                $scope.showInputSelect = false;
                $scope.toasts = [];
                $scope.infoResult = 0;
                $scope.passwordResult = 0;

                $scope.currentPageStores = [];
                $scope.searchKeywords = "";
                $scope.filteredData = [];
                $scope.row = "";
                $scope.numPerPageOpts = [5, 10, 25, 50, 100];
                $scope.numPerPage = $scope.numPerPageOpts[1];
                $scope.currentPage = 1;
                $scope.positionModel = "topRight";

                $scope.datosCliente = function (id) {
                    $http({
                        method: "GET",
                        url: API_URL + "detallecliente",
                        params: { cliente_id: id },
                    }).then(function successCallback(response) {
                        customer = response.data.records;
                        var credits = prepareListCredits(customer.creditos);

                        if (credits.length > 1) {
                            $scope.showInputSelect = true;
                            $scope.listCredito = credits;
                        }
                        $scope.itemCredit = credits[0];
                        showData(credits[0]);
                    });
                };

                $scope.datosCliente($routeParams.id);
                $scope.creditSelected = function (credit) {
                    showData(credit);
                };

                function showData(infoCredit) {
                    $scope.dpi = customer.dpi;
                    $scope.nombre = customer.nombre;
                    $scope.apellido = customer.apellido;
                    $scope.nombre_completo = customer.nombre + " " + customer.apellido;
                    $scope.sexo = customer.sexo == 1 ? "Masculino" : "Femenino";
                    $scope.direccion = customer.direccion;
                    $scope.estado_civil = customer.estado_civil == 1 ? "Soltero (a)" : "Casado (a)";
                    $scope.telefono = customer.telefono;
                    $scope.categoria = customer.categoria;
                    $scope.cantidadCreditos = customer.cantidadCreditos
                    $scope.id = infoCredit.id;
                    $scope.plan = infoCredit.planes;
                    $scope.monto_total = "Q. " + parseFloat(infoCredit.deudatotal).toFixed(2);
                    $scope.fecha_inicio = infoCredit.fecha_inicio;
                    $scope.fecha_fin = infoCredit.fecha_fin;
                    $scope.fecha_finalizado = infoCredit.fecha_finalizado
                    $scope.cobrador = infoCredit.usuariocobrador.nombre;
                    $scope.cuota_diaria = "Q. " + parseFloat(infoCredit.cuota_diaria).toFixed(2);
                    $scope.status_credit = infoCredit.estado;
                    $scope.saldo_pendiente = "Q. " + parseFloat(infoCredit.saldo).toFixed(2);
                    $scope.total_cancelado = "Q. " + parseFloat(infoCredit.total_cancelado).toFixed(2);
					$scope.cuotas_atrasadas = infoCredit.cuotas_atrasadas;
                    $scope.credit_arrears_status = infoCredit.estado_morosidad;
                    $scope.cuotas_pagadas = infoCredit.cuotas_pagados;
                    $scope.porcentaje = parseInt(infoCredit.porcentaje_pago);
                    $scope.arrearsCredits = customer.arrearsCredits;
                    $scope.unlockCount = customer.unlockCount;

                    listUnlocks(customer.unlocks)

                    if (infoCredit.estado == 2) {
                        getCreditDeleted(infoCredit.id);
                    }
                }

                function prepareListCredits(credits) {
                    var array = [];

                    credits.forEach(function (item) {
                        item.statusText = statusText(item.estado);
                        array.push(item);
                    });

                    array.sort(function (a, b) {
                        if (a.statusText > b.statusText) {
                            return 1;
                        }

                        if (a.statusText < b.statusText) {
                            return -1;
                        }
                        // a must be equal to b
                        return 0;
                    });

                    return array;
                }

                function listUnlocks(listUnlocks) {
                    $scope.listUnlocks = listUnlocks
                    $scope.search()
                    $scope.select($scope.currentPage)
                }

                function statusText(status) {
                    switch (status) {
                        case 0:
                            return "Completado";
                        case 1:
                            return "Activo";
                        case 2:
                            return "Eliminado";
                        default:
                            return "";
                    }
                }

                function getCreditDeleted(creditId) {
                    deletCreditService
                        .getCreditDeleted(creditId)
                        .then(function succesCallback(response) {
                            if (response.data.result == true) {
                                $scope.reasonForDeleted =
                                    response.data.records.motivo;
                            }
                        });
                }

                $scope.getHistory = function (creditId) {
                    historyPaymentService
                        .historyForCredit(creditId)
                        .then(function succesCallback(response) {
                            if (response.data.result == true) {
                                $scope.listTable = response.data.records;
                                $scope.search();
                                $scope.select($scope.currentPage);
                            }
                        });
                };

                $scope.validatePassword = function (password, tipo) {
                    passwordAccessService
                        .valitePasswordForAccess(password)
                        .then(function succesCallback(response) {
                            if (response.data == true) {
                                modal.close();
                                if (tipo == 1) {
                                    $scope.modalOpen(
                                        "views/clientes/modalEvaluateArrears.html"
                                    );
                                } else if (tipo == 2) {
                                    $scope.modalOpen(
                                        "views/clientes/modalDelete.html"
                                    );
                                }
                            } else {
                                $scope.passwordResult = 1;
                            }
                        },
                        function errorCallback(response) {
                            $scope.passwordResult = 1;
                        }
                    );
                };

                $scope.creditToDelete = function (data) {
                    data.creditoid = $scope.id;
                    deletCreditService.saveCreditDeleted(data).then(
                        function successCallback(response) {
                            if (response.data.result == true) {
                                modal.close();
                                location.reload();
                            } else {
                                $scope.infoResult = 1;
                            }
                        },
                        function errorCallback(response) {
                            $scope.infoResult = 1;
                        }
                    );
                };

                $scope.saveCreditEvaluation = function (data) {
                    data.credit_id = $scope.id;
                    creditsService
                        .saveEvaluateArrears(data)
                        .then(
                            function successCallback(response) {
                                console.log(response)
                                if (response.data.result == true) {
                                    modal.close();
                                    location.reload();
                                } else {
                                    $scope.infoResult = 1;
                                }
                            },
                            function errorCallback(response) {
                                $scope.infoResult = 1;
                            }
                        );
                }

                $scope.clearListTable = function() {
                    $scope.listTable = $scope.listUnlocks
                    $scope.filteredData = []
                    $scope.search();
                    $scope.select($scope.currentPage);
                }

                //#region "modal"
                $scope.modalOpen = function (templateUrl) {
                    $scope.dataCustomer = $scope;

                    modal = $modal.open({
                        templateUrl: templateUrl,
                        scope: $scope,
                        size: "md",
                        resolve: function () {},
                        windowClass: "default",
                    });
                };

                $scope.modalHistoryOpen = function (id) {
                    $scope.getHistory(id);

                    modal = $modal.open({
                        templateUrl: "views/clientes/modalHistory.html",
                        scope: $scope,
                        size: "md",
                        resolve: function () {},
                        windowClass: "default",
                    });
                };

                $scope.modalValidateAccessOpen = function (tipoMessage) {
                    $scope.tipoMessage = tipoMessage
                    modal = $modal.open({
                        templateUrl: "views/clientes/modalValidateAccess.html",
                        scope: $scope,
                        size: "md",
                        resolve: function () {},
                        windowClass: "default",
                    });
                }

                $scope.modalClose = function () {
                    modal.close();
                };
                //#endregion

                //#region "Toast"
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
                //#endregion

                //#region FUNCIONES DE DATATABLE
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
                        $scope.listTable,
                        $scope.searchKeywords
                    );
                    $scope.onFilterChange();
                };

                $scope.order = function (rowName) {
                    if ($scope.row == rowName) return;
                    $scope.row = rowName;
                    $scope.filteredData = $filter("orderBy")(
                        $scope.historyCredit,
                        rowName
                    );
                    $scope.onOrderChange();
                };
                 //#endregion
            },
        ]);
})();
