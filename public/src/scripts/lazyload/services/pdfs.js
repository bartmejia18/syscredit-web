var pdfs_service = angular.module('app.service.pdfs', ['app.constants']);

pdfs_service.service('pdfsService', ['$window','$http', 'API_URL', function($window, $http, API_URL)  {
    delete $http.defaults.headers.common['X-Requested-With'];
    
    this.ticketcredit = function (id) {
        $window.location.href = API_URL+'boletapdf?credito_id=' + id;
    };

    this.resumenPaymentCollector = function(cobradorId, date, routeClosureId){        
        $window.location.href = API_URL+'collectorpdf?idusuario='+cobradorId+'&closure_id='+routeClosureId+'&fecha='+date
    }
    this.credits = function (statusCredit, collector, dateInit, dateFinal, plan, branch) {
        $window.location.href = API_URL+'reportcreditspdf?status='+statusCredit+'&collector='+collector+'&dateInit='+dateInit+'&dateFinal='+dateFinal+'&plan='+plan+'&branch='+branch
    };
}]);