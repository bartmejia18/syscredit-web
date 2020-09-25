var collector_service = angular.module('app.service.collector', ['app.constants']);

collector_service.service('collectorService', ['$http', 'API_URL', function($http, API_URL)  {
    delete $http.defaults.headers.common['X-Requested-With'];

    this.index = function () {
        return $http.get(API_URL+'listacobradores');
    };
    
    this.detail = function (id, date) {
        return $http.get(API_URL+'listcustomers?idusuario=' + id+'&fecha='+date);
    };

    this.totalColletion = function (id, date) {
        return $http.get(API_URL+'totalcolletion?cobrador_id=' + id + '&fecha='+date);
    };
}]);