var password_access = angular.module('app.service.passwordaccess', ['app.constants']);

password_access.service('passwordAccessService', ['$http', 'API_URL', function($http, API_URL)  {
    delete $http.defaults.headers.common['X-Requested-With'];

    this.valitePasswordForAccess = function(password) {
        return $http.post(API_URL+'passwordaccess', password);
    }

    this.valitePasswordSupervisor = function(data) {
        return $http.post(API_URL+'supervisoraccess', data);
    }
}]);