var eliminar_credito = angular.module('app.service.deletecredit', ['app.constants']);

eliminar_credito.service('deletCreditService', ['$http', 'API_URL', function($http, API_URL)  {
    delete $http.defaults.headers.common['X-Requested-With'];

    this.saveCreditDeleted = function(infoCreditDeleted) {
        return $http.post(API_URL+'creditoeliminado', infoCreditDeleted);
    }

    this.getCreditDeleted = function(creditId) {
        return $http.get(API_URL+'getcreditdeleted?creditoId='+creditId)
    }
}]);