var dashboard_service = angular.module('app.service.dashboard', ['app.constants']);

dashboard_service.service('dashboardService', ['$http', 'API_URL', function($http, API_URL)  {
    delete $http.defaults.headers.common['X-Requested-With'];

    this.index = function() {
        return $http.get(API_URL+'dashboard');
    };
}]);