var credits = angular.module('app.service.credits', ['app.constants']);

credits.service('creditsService', ['$http', 'API_URL', function($http, API_URL)  {
    delete $http.defaults.headers.common['X-Requested-With'];

    this.saveEvaluateArrears = function(credit) {
        return $http.post(API_URL+'validationArrears', credit);
    }
}]);