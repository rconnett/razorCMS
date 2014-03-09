define(["angular", "angular-resource"], function(angular)
{
    angular.module("razor.services.rars", ["ngResource"])

	.factory("rars", function($http)
	{
        return {
            get: function(funcPath, id, token)
            {
                return $http.get(RAZOR_BASE_URL + "rars/" + funcPath + (!!id ? "/" + id : ""), {headers: {"Authorization": token}});
            },
            post: function(funcPath, data, token)
            {
                return $http.post(RAZOR_BASE_URL + "rars/" + funcPath, data, {headers: {"Authorization": token}});
            },
            put: function(funcPath, data, token)
            {
                return $http.put(RAZOR_BASE_URL + "rars/" + funcPath, data, {headers: {"Authorization": token}});
            },
            delete: function(funcPath, id, token)
            {
                return $http.delete(RAZOR_BASE_URL + "rars/" + funcPath + (!!id ? "/" + id : ""), {headers: {"Authorization": token}});
            }
        }
	});
});