var history_payment_service = angular.module('app.service.historypayment', ['app.constants']);

history_payment_service.service('historyPaymentService', ['$http', 'API_URL', function($http, API_URL)  {
    delete $http.defaults.headers.common['X-Requested-With'];

    this.historylist = function (id, datePayment) {
        return $http.get(API_URL+'paymenthistory?cobrador_id='+id+'&fecha_pago='+datePayment);
    };

    this.collectors = function () {
        return $http.get(API_URL+'listacobradores');
    };

    this.deleteHistory = function(id){
        return $http.get(API_URL+'deletepayment?detalle_id='+id);
    }
}]);