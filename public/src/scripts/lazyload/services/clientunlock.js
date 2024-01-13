var client_unlock_service = angular.module('app.service.clientunlock', ['app.constants']);

client_unlock_service.service('clientUnlockService', ['$http', 'API_URL', function($http, API_URL)  {
    delete $http.defaults.headers.common['X-Requested-With'];

    this.create = function (data) {
        return $http.post(API_URL+'clientUnlock', data);
    };

    this.listByBranch = function (banchId) {
        return $http.get(API_URL+'clientUnlock?branchId='+banchId);
    };

    this.listByClient = function(clientId){
        return $http.get(API_URL+'clientUnlock?clientId='+clientId);
    }
}]);