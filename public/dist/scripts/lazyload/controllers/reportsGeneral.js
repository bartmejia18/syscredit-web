(function () {
    "use strict";

    angular
        .module("app.reports", [
            "app.constants",
            "app.service.report",
            "app.service.pdfs",
            "app.service.branch",
        ])

        .controller("ReportsGeneralController", [
            "$scope",
            "$interval",
            "reportService",
            "branchService",
            function (
                $scope,
                $timeout,
                reportService,
                branchService
            ) {
                $scope.positionModel = "topRight";
                $scope.toasts = [];
                $scope.customerGeneral = {};
                $scope.branchs = [];

                initLoadInfo();

                function initLoadInfo() {
                    if ($scope.usuario.tipo_usuarios_id == 1) {
                        loadBranches();
                    } else {
                        loadGeneralReport($scope.usuario.sucursales_id);
                    }
                }

                function loadBranches() {
                    branchService.branchs().then(
                        function successCallback(response) {
                            var itemAll = {};
                            itemAll.descripcion = "Todos";
                            itemAll.id = 0;
                            $scope.branchs.push(itemAll);

                            response.data.records.forEach(function (item) {
                                $scope.branchs.push(item);
                            });
                        },
                        function errorCallback(response) {
                            console.log(response);
                        }
                    );
                }

                function loadGeneralReport(branch) {
                    reportService.general(branch).then(
                        function successCallback(response) {
                            if (response.data.result) {
                                var info = response.data.records;
                                if (info != null && info != "") {
                                    $scope.customerGeneral.total = info.customers.withCredit;
                                    $scope.customerGeneral.today = info.customers.withCreditToDay;
                                    $scope.customerGeneral.notoday = info.customers.withCreditNoToDay;
                                    $scope.customerGeneral.revenueTotals = info.revenueTotals;
                                    $scope.customerGeneral.totalPendingReceivable = info.totalPendingReceivable;
                                    $scope.customerGeneral.totalReceivable = info.totalReceivable;
                                    $scope.customerGeneral.totalInvested = info.totalInvested;
                                } else {
                                    $scope.createToast(
                                        "danger",
                                        "<strong>Error: No hay datos a mostrar. </strong>"
                                    );
                                    $timeout(function () {
                                        $scope.closeAlert(0);
                                    }, 5000);
                                }
                            } else {
                                $scope.createToast(
                                    "danger",
                                    "<strong>Error: Ocurrió un error al consultar los datos. </strong>"
                                );
                                $timeout(function () {
                                    $scope.closeAlert(0);
                                }, 5000);
                            }
                        },
                        function errorCallback(response) {
                            console.log(response);
                        }
                    );
                }

                $scope.branchSelected = function (branch) {
                    loadGeneralReport(branch);
                };

                // toast function
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
            },
        ]);
})();