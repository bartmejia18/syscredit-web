var company_service = angular.module('app.service.company', ['app.constants']);

company_service.service('companyService', ['$http', 'API_URL', function($http, API_URL)  {
    delete $http.defaults.headers.common['X-Requested-With'];

    this.companies = function () {
        return $http.get(API_URL+'empresas');
    };

    this.create = function(data){
        return $http.post(API_URL+'empresas', data);
    }

    this.update = function(id, data){        
        return $http.put(API_URL+'empresas/'+id, data);
    }

    this.delete = function(id, data){        
        return $http.put(API_URL+'empresas/'+id, data);
    }
}]);