var cierre_ruta = angular.module('app.service.cierreruta', ['app.constants']);

cierre_ruta.service('cierreRutaService', ['$window', '$http', 'API_URL', function($window, $http, API_URL)  {
    delete $http.defaults.headers.common['X-Requested-With'];

    this.cierreRutaList = function (dateClosure) {
        return $http.get(API_URL+'cierreruta?fecha_cerrado='+dateClosure);
    };

    this.validateCierreRuta = function (collectorId, date) {
        return $http.get(API_URL+'validatecierreruta?collector_id='+collectorId+'&date='+date);
    };

    this.saveClosingRoute = function(dataRouteClosure){
        return $http.post(API_URL+'cierreruta', dataRouteClosure);
    }

    this.updateClosingRoute = function(routeClosureId){        
        return $http.put(API_URL+'cierreruta/'+routeClosureId);
    }

    this.printInfoClosure = function(routeClosureId){             
        $window.location.href = API_URL+'printinfoclosure?closure_id='+routeClosureId;
    }
}]);