(function () {
    "use strict";

    angular
        .module("app.creditos", [
            "app.constants", 
            "app.service.pdfs",
            "app.service.passwordaccess",
            "app.service.clientunlock"
        ])

        .controller("CreditosController", [
            "$scope",
            "$filter",
            "$http",
            "$modal",
            "$interval",
            "pdfsService",
            "passwordAccessService",
            "clientUnlockService",
            "API_URL",
            function (
                $scope,
                $filter,
                $http,
                $modal,
                $timeout,
                pdfsService,
                passwordAccessService,
                clientUnlockService,
                API_URL
            ) {
                $scope.positionModel = "topRight";
                $scope.detalle_cliente = {};
                $scope.toasts = [];
                $scope.usuarios_cobrador = Array();
                $scope.passwordResult = 0;
                $scope.supervisor = {};
                var modal;

                const CUSTOMER_DELETED = "customer_deleted";
                const CUSTOMER_WITH_CREDIT = "customer_with_credits";

                $scope.cargarPlanes = function () {
                    $scope.planes = [];
                    $http.get(API_URL + "planes", {}).then(function (response) {
                        if (response.data.result) {
                            response.data.records.forEach(function (item) {
                                if (
                                    item.sucursales_id ==
                                    $scope.usuario.sucursales_id
                                ) {
                                    $scope.planes.push(item);
                                }
                            });
                        }
                    });
                };

                $scope.cargarMonto = function () {
                    $scope.montosprestamo = [];
                    $http
                        .get(API_URL + "montosprestamo", {})
                        .then(function (response) {
                            if (response.data.result) {
                                response.data.records.forEach(function (item) {
                                    if (
                                        item.sucursales_id ==
                                        $scope.usuario.sucursales_id
                                    ) {
                                        $scope.montosprestamo.push(item);
                                    }
                                });
                            }
                        });
                };

                $scope.cargarUsuariosCobrador = function () {
                    $scope.usuarios_cobrador = [];
                    $http
                        .get(API_URL + "listacobradores", {})
                        .then(function (response) {
                            if (response.data.result) {
                                response.data.records.forEach(function (item) {
                                    if (
                                        item.sucursales_id ==
                                            $scope.usuario.sucursales_id &&
                                        item.estado == 1
                                    ) {
                                        $scope.usuarios_cobrador.push(item);
                                    }
                                });
                            }
                        });
                };

                $scope.calcularInteresCuota = function (plan) {
                    if (plan.id > 0) {
                        $scope.detalle_cliente.interes =
                            ($scope.detalle_cliente.monto_id.monto *
                                plan.porcentaje) /
                            100;
                        $scope.detalle_cliente.cuota_diaria =
                            ($scope.detalle_cliente.interes +
                                $scope.detalle_cliente.monto_id.monto) /
                            plan.dias;
                    }
                };

                $scope.getEndDate = function (plan) {
                    switch (plan.tipo) {
                        case 2:
                            $scope.detalle_cliente.fecha_fin =
                                getEndDateWeek(plan);
                            break;
                        case 3:
                            $scope.detalle_cliente.fecha_fin =
                                getEndDateMonth(plan);
                            break;
                        default:
                            if (plan.domingo == "1") {
                                $scope.detalle_cliente.fecha_fin =
                                    getEndDateWithoutSunday(plan);
                            } else {
                                $scope.detalle_cliente.fecha_fin =
                                    getEndDate(plan);
                            }
                            break;
                    }
                };

                $scope.validarCliente = function (search_client) {
                    $http({
                        method: "GET",
                        url: API_URL + "buscarcliente",
                        params: search_client,
                    }).then(
                        function successCallback(response) {
                            if (response.data.result) {
                                $scope.modalShowInfoCustomer(
                                    response.data.records
                                );
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
                            $scope.createToast(
                                "danger",
                                "<strong>Error: Recargue la página y vuelva a intentarlo</strong>"
                            );
                        }
                    );
                };

                $scope.saveData = function (detalleCredito) {
                    var datos = {
                        idcliente: detalleCredito.id,
                        idplan: detalleCredito.planes_id.id,
                        idmonto: detalleCredito.monto_id.id,
                        idusuario: detalleCredito.usuarios_cobrador.id,
                        deudatotal: detalleCredito.monto_id.monto + detalleCredito.interes,
                        cuota_diaria: detalleCredito.cuota_diaria,
                        cuota_minima: detalleCredito.monto_id.monto / detalleCredito.planes_id.dias,
                        fecha_inicio: detalleCredito.fecha_inicio,
                        fecha_limite: detalleCredito.fecha_fin,
                    };
                    $http({
                        method: "POST",
                        url: API_URL + "creditos",
                        data: datos,
                    }).then(
                        function successCallback(response) {
                            if (response.data.result) {
                                pdfsService.ticketcredit(
                                    response.data.records.id
                                );

                                $scope.createToast(
                                    "success",
                                    "<strong>Éxito: </strong>" +
                                        response.data.message
                                );
                                $("#row-detalle").addClass("hidden");
                                $("#customerDpi").val("");
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
                        function errorCallback(response) {}
                    );
                };

                $scope.saveDataNewClient = function (cliente) {
                    $(".btn-new-customer").prop("disabled", true);
                    if ($scope.accion == "crear") {
                        $http({
                            method: "POST",
                            url: API_URL + "clientes",
                            data: cliente,
                        }).then(
                            function successCallback(response) {
                                if (response.data.result) {
                                    $("#row-detalle").removeClass("hidden");

                                    $scope.detalle_cliente = response.data.records;
                                    $scope.detalle_cliente.credito = 0;
                                    $scope.detalle_cliente.nombre = response.data.records.nombre + " " + response.data.records.apellido;

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
                            function errorCallback(response) {}
                        );
                    }
                };

                $scope.addCredit = function (cliente) {
                    $("#row-detalle").removeClass("hidden");
                    $(".btn-new-customer").prop("disabled", true);
                    modal.close();
                    updateCustomer(cliente);
                };

                $scope.addNewCredit = function (cliente) {
                    $("#row-detalle").removeClass("hidden");
                    $(".btn-new-customer").prop("disabled", true);
                    modal.close();
                    $scope.detalle_cliente = cliente;
                    $scope.detalle_cliente.credito = cliente.statusCredit == 2 ? 1 : 0;
                    $scope.detalle_cliente.nombre = cliente.nombre + " " + cliente.apellido;
                    $scope.detalle_cliente.usuarios_cobrador = cliente.cobrador;
                };

                $scope.findUser = function (dpi) {
                    if (dpi != undefined && dpi.toString().length == 13) {
                        $http({
                            method: "GET",
                            url: API_URL + "buscarcliente",
                            params: { dpi: dpi },
                        }).then(function successCallback(response) {
                            if (response.data.result) {
                                $scope.cliente.exists = true 
                                $scope.cliente = response.data.records
                            } else {
                                $scope.cliente.statusCredit = 0;
                            }
                        });
                    }
                };

                $scope.optionNo = function() {
                    modal.close()
                }

                $scope.optionYes = function() {
                    pdfsService.debtrecognition(
                        167
                    );
                    modal.close()
                }

                $scope.validatePassword = function (data) {
                    passwordAccessService
                        .valitePasswordSupervisor(data)
                        .then(function succesCallback(response) {
                            if (response.data.result == true) {
                                $scope.passwordResult = 1;
                                $scope.supervisor = response.data.records
                            } else {
                                $scope.supervisor = null
                                $scope.passwordResult = 0;
                                $("#alertPasswordIncorrect").removeClass("hidden")
                            }
                        },
                        function errorCallback(response) {
                            $scope.supervisor = null
                            $scope.passwordResult = 0;
                            $("#alertPasswordIncorrect").removeClass("hidden")
                        });
                };

                $scope.detailsUnlockClient = function (client, data) {

                    data.clientId = client.id
                    data.supervisorId = $scope.supervisor.id

                    clientUnlockService.create(data)
                        .then(function succesCallback(response) {
                            if (response.data.result) {
                                $scope.createToast(
                                    "success",
                                    "<strong>Éxito: </strong>Se ha desbloquedo el cliente"
                                );
                                $timeout(function () {
                                    $scope.closeAlert(0);
                                }, 3000);

                                $("#row-detalle").removeClass("hidden");
                                $(".btn-new-customer").prop("disabled", true);
                                modal.close();
                                updateCustomer(client);
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
                            $scope.createToast(
                                "danger",
                                "<strong>Error: </strong>" +
                                    response.data.message
                            );
                            $timeout(function () {
                                $scope.closeAlert(0);
                            }, 5000);
                        });
                }

                function updateCustomer(cliente) {
                    cliente.status = 1;
                    $http({
                        method: "PUT",
                        url: API_URL + "clientes/" + cliente.id,
                        data: cliente,
                    }).then(
                        function successCallback(response) {
                            if (response.data.result) {
                                $scope.detalle_cliente = response.data.records;
                                $scope.detalle_cliente.nombre = response.data.records.nombre +" " + response.data.records.apellido;
                                $scope.detalle_cliente.credito = 0;
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
                        function errorCallback(response) {}
                    );
                }

                //#region "load data"
                $scope.cargarPlanes();
                $scope.cargarMonto();
                $scope.cargarUsuariosCobrador();
                //#endregion

                //#region "dates"
                function getStartDate() {
                    var startDate = $("#fechainicio").val().split("-");
                    return new Date(
                        startDate[2],
                        startDate[1] - 1,
                        startDate[0]
                    );
                }

                function getEndDate(plan) {
                    var startDate = getStartDate();
                    startDate.setDate(startDate.getDate() + plan.dias - 1);
                    return $filter("date")(startDate, "dd-MM-yyyy");
                }

                function getEndDateWithoutSunday(plan) {
                    var dtInicial = getStartDate();
                    if (dtInicial.getDay() === 0) {
                        dtInicial = new Date(dtInicial.getTime() + 86400000); // se agrega un dia
                    }
                    for (var i = 0; i < parseInt(plan.dias - 1); i++) {
                        if (dtInicial.getDay() === 0) {
                            i = i - 1;
                        }
                        dtInicial = new Date(dtInicial.getTime() + 86400000); // se agrega un dia
                    }
                    return $filter("date")(dtInicial, "dd-MM-yyyy");
                }

                function getEndDateWeek(plan) {
                    var startDate = getStartDate();
                    startDate.setDate(startDate.getDate() + (plan.dias - 1) * 7);
                    return $filter("date")(startDate, "dd-MM-yyyy");
                }

                function getEndDateMonth(plan) {
                    var startDate = getStartDate();
                    startDate.setMonth(startDate.getMonth() + (plan.dias - 1));
                    return $filter("date")(startDate, "dd-MM-yyyy");
                }

                //#endregion
                //#region "modal"
                $scope.modalCreateOpen = function () {
                    $scope.cliente = {};
                    $scope.cliente.statusCredit = 0;
                    $scope.accion = "crear";

                    modal = $modal.open({
                        templateUrl: "views/creditos/modal.html",
                        scope: $scope,
                        size: "md",
                        resolve: function () {},
                        windowClass: "default",
                    });
                };

                $scope.modalShowInfoCustomer = function (customer) {
                    $scope.cliente = customer;
                    $scope.accion = "info";

                    modal = $modal.open({
                        templateUrl: "views/creditos/modal.html",
                        scope: $scope,
                        size: "md",
                        resolve: function () {},
                        windowClass: "default",
                    });
                };

                $scope.modalReconocimientoDeuda = function (customer) {
                    $scope.cliente = customer;
                    $scope.accion = "info";

                    modal = $modal.open({
                        templateUrl: "views/creditos/modalReconocimientoDeuda.html",
                        scope: $scope,
                        size: "md",
                        resolve: function () {},
                        windowClass: "default",
                    });
                };

                $scope.modalUnlockClient = function (customer) {
                    modal.close()
                    
                    $scope.cliente = customer;
                    
                    modal = $modal.open({
                        templateUrl: "views/creditos/modalUnlockClient.html",
                        scope: $scope,
                        size: "md",
                        resolve: function () {},
                        windowClass: "default",
                    });
                };

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
            },
        ]);
})();
