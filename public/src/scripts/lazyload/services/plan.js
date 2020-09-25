var plan_service = angular.module('app.service.plan', ['app.constants']);

plan_service.service('planService', ['$http', 'API_URL', function($http, API_URL)  {
    delete $http.defaults.headers.common['X-Requested-With'];

    this.plans = function () {
        return $http.get(API_URL+'planes');
    };
}]);