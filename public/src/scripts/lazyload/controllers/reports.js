(function () {
    "use strict";

    angular
        .module("app.reports", [
            "app.constants",
            "app.service.collector",
            "app.service.plan",
            "app.service.report",
            "app.service.pdfs",
            "app.service.branch",
        ])

        .controller("ReportsController", [
            "$scope",
            "$filter",
            "$http",
            "$modal",
            "$interval",
            "collectorService",
            "planService",
            "reportService",
            "pdfsService",
            "branchService",
            "API_URL",
            function (
                $scope,
                $filter,
                $http,
                $modal,
                $timeout,
                collectorService,
                planService,
                reportService,
                pdfsService,
                branchService
            ) {
                $scope.datas = Array();

                $scope.currentPageStores = [];
                $scope.searchKeywords = "";
                $scope.filteredData = [];
                $scope.row = "";
                $scope.numPerPageOpts = [5, 10, 25, 50, 100];
                $scope.numPerPage = $scope.numPerPageOpts[1];
                $scope.currentPage = 1;
                $scope.positionModel = "topRight";
                $scope.toasts = [];

                $scope.collectors = [];
                $scope.plans = [];
                $scope.branchs = [];
                $scope.customer = {};
                $scope.sumAmountCredits = 0;

                var branchSelected;
                var typeView = "";

                initLoadInfo();

                function initLoadInfo() {
                    if ($scope.usuario.tipo_usuarios_id == 1) {
                        loadBranches();
                    } else {
                        loadCollectors($scope.usuario.sucursales_id);
                        loadPlanes($scope.usuario.sucursales_id);
                    }
                }

                function loadCollectors(branch) {
                    $scope.collectors = [];
                    collectorService.index().then(function (response) {
                        var itemAll = {};
                        itemAll.nombre = "Todos";
                        itemAll.id = 0;
                        $scope.collectors.push(itemAll);
                        response.data.records.forEach(function (item) {
                            if (
                                item.sucursales_id == branch &&
                                item.estado == 1
                            ) {
                                $scope.collectors.push(item);
                            }
                        });
                    });
                }

                function loadPlanes(branch) {
                    $scope.plans = [];
                    planService.plans().then(function (response) {
                        var itemAll = {};
                        itemAll.descripcion = "Todos";
                        itemAll.id = 0;
                        $scope.plans.push(itemAll);
                        response.data.records.forEach(function (item) {
                            if (item.sucursales_id == branch) {
                                $scope.plans.push(item);
                            }
                        });
                    });
                }

                function loadBranches() {
                    branchService.branchs().then(
                        function successCallback(response) {
                            if ($("#view").val() == "dates") {
                                var itemAll = {};
                                itemAll.descripcion = "Todos";
                                itemAll.id = 0;
                                $scope.branchs.push(itemAll);
                            }

                            response.data.records.forEach(function (item) {
                                $scope.branchs.push(item);
                            });
                        },
                        function errorCallback(response) {
                            console.log(response);
                        }
                    );
                }

                function generateReport(typeView) {
                    var data = $scope.data;
                    var dateInit = $("#date_init").val();
                    var dateFinal = $("#date_fin").val();
                    var collector = $scope.collector;
                    var plan = $scope.plan == undefined ? "" : $scope.plan;
                    var branch =
                        $scope.usuario.tipo_usuarios_id == 1
                            ? branchSelected
                            : $scope.usuario.sucursales_id;

                    switch (typeView) {
                        case "dates":
                            if (
                                dateInit != "" &&
                                dateInit != undefined &&
                                dateFinal != "" &&
                                dateFinal != undefined
                            ) {
                                reportService
                                    .dates(dateInit, dateFinal, branch)
                                    .then(function (response) {
                                        if (response.data.result) {
                                            var info = response.data.records;
                                            $scope.customer.total = info.customers.withCredit;
                                            $scope.customer.today = info.customers.withCreditToDay;
                                            $scope.customer.notoday = info.customers.withCreditNoToDay;
                                            $scope.revenueTotals = info.revenueTotals;
                                            $scope.totalPendingReceivable = info.totalPendingReceivable;
                                            $scope.totalReceivable = info.totalReceivable;
                                        }
                                    });
                            }
                            break;
                        case "collectors":
                            if (collector != "" && collector != undefined) {
                                reportService
                                    .collector(
                                        collector,
                                        dateInit,
                                        dateFinal,
                                        plan,
                                        branch
                                    )
                                    .then(function (response) {
                                        if (response.data.result) {
                                            var info = response.data.records;
                                            $scope.customer.total = info.customers.withCredit;
                                            $scope.customer.today = info.customers.withCreditToDay;
                                            $scope.customer.notoday = info.customers.withCreditNoToDay;
                                            $scope.revenueTotals = info.revenueTotals;
                                            $scope.totalPendingReceivable = info.totalPendingReceivable;
                                            $scope.totalReceivable = info.totalReceivable;
                                            $scope.totalAmountToCollected = info.totalAmountToCollected;
                                        }
                                    });
                            } else {
                                $scope.createToast(
                                    "danger",
                                    "<strong>Error: Debe de seleccionar un cobrador. </strong>"
                                );
                                $timeout(function () {
                                    $scope.closeAlert(0);
                                }, 5000);
                            }
                            break;
                        case "credits":
                            $scope.datas = [];
                            reportService
                                .credits(
                                    data.statusCredit,
                                    data.collector,
                                    dateInit,
                                    dateFinal,
                                    data.plan,
                                    branch
                                )
                                .then(function (response) {
                                    if (response.data.result) {
                                        $(".panel-detalle").removeClass(
                                            "hidden"
                                        );

                                        $scope.datas = response.data.records.credits;
                                        $scope.sumAmountCredits = response.data.records.sumAmountCredits;
                                        $scope.sumAmountTotalCredit = response.data.records.sumAmountTotalCredit;
                                        $scope.search();
                                        $scope.select($scope.currentPage);
                                    }
                                });

                            break;
                        default:
                            break;
                    }
                }

                $scope.printResume = function () {
                    var data = $scope.data;
                    var dateInit = $("#date_init").val();
                    var dateFinal = $("#date_fin").val();
                    var plan = $scope.data.plan == undefined ? 0 : $scope.data.plan;
                    var collector = $scope.data.collector == undefined ? 0 : $scope.data.collector;
                    var branch = $scope.usuario.tipo_usuarios_id == 1
                            ? branchSelected
                            : $scope.usuario.sucursales_id;

                    pdfsService
                        .credits(
                            data.statusCredit,
                            collector,
                            dateInit,
                            dateFinal,
                            plan,
                            branch
                        )
                        .then(
                            function successCallback(response) {
                                console.log(response);
                            },
                            function errorCallback(response) {}
                        );
                };

                $scope.branchSelected = function (branch) {
                    if ($("#view").val() == "collectors") {
                        loadCollectors(branch);
                        loadPlanes(branch);
                    } else {
                        if (branch == undefined) {
                            $scope.createToast(
                                "danger",
                                "<strong>Error: Debe seleccionar alguna de las opciones de sucursales. </strong>"
                            );
                            $timeout(function () {
                                $scope.closeAlert(0);
                            }, 5000);
                        } else {
                            branchSelected = branch;
                        }
                    }
                };

                $scope.generateReport = function () {
                    typeView = $("#view").val();
                    generateReport(typeView);
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

                //#region "Datatable"
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
                //#endregion
            },
        ]);
})();
