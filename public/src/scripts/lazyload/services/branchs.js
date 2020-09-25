var branch_service = angular.module('app.service.branch', ['app.constants']);

branch_service.service('branchService', ['$http', 'API_URL', function($http, API_URL)  {
    delete $http.defaults.headers.common['X-Requested-With'];

    this.branchs = function () {
        return $http.get(API_URL+'sucursales');
    };
}]);