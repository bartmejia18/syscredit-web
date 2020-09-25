var customers_service = angular.module('app.service.customers', ['app.constants']);

customers_service.service('customersService', ['$http', 'API_URL', function($http, API_URL)  {
    delete $http.defaults.headers.common['X-Requested-With'];

    this.customers = function () {
        return $http.get(API_URL+'branch/customers');
    };
}]);