var report_service = angular.module('app.service.report', ['app.constants']);

report_service.service('reportService', ['$http', 'API_URL', function($http, API_URL)  {
    delete $http.defaults.headers.common['X-Requested-With'];

    this.collector = function (collector, dateInit, dateFinal, plan, branch) {
        return $http.get(API_URL+'reportcollector?collector='+collector+'&date-init='+dateInit+'&date-final='+dateFinal+'&plan='+plan+'&branch='+branch);
    };
    this.dates = function (dateInit, dateFinal,branch) {
        return $http.get(API_URL+'reportdates?collector=&date-init='+dateInit+'&date-final='+dateFinal+'&branch='+branch);
    };
    this.general = function (branch) {
        return $http.get(API_URL+'reportgeneral?collector=&date-init=&date-final=&branch='+branch);
    };
    this.credits = function (status, collector, dateInit, dateFinal, plan, branch) {
        return $http.get(API_URL+'reportgeneral?status='+status+'&collector='+collector+'&date-init='+dateInit+'&date-final='+dateFinal+'&plan='+plan+'&branch='+branch);
    };
}]);